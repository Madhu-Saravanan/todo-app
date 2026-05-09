<?php
// ============================================================
// DB-backed session handler – required on Vercel (serverless
// instances don't share a temp filesystem between requests).
// Registered automatically by config.php when Database is available.
// ============================================================

class DbSessionHandler implements SessionHandlerInterface {
    private PDO $pdo;
    private int $lifetime;

    public function __construct(PDO $pdo, int $lifetime = 86400) {
        $this->pdo      = $pdo;
        $this->lifetime = $lifetime;
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS `sessions` (
                `id`         VARCHAR(128) NOT NULL,
                `data`       MEDIUMTEXT   NOT NULL,
                `expires_at` DATETIME     NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_sessions_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function open(string $path, string $name): bool { return true; }
    public function close(): bool                          { return true; }

    public function read(string $id): string|false {
        $stmt = $this->pdo->prepare(
            'SELECT `data` FROM `sessions` WHERE `id` = ? AND `expires_at` > NOW()'
        );
        $stmt->execute([$id]);
        return $stmt->fetchColumn() ?: '';
    }

    public function write(string $id, string $data): bool {
        $expires = date('Y-m-d H:i:s', time() + $this->lifetime);
        $stmt = $this->pdo->prepare(
            'INSERT INTO `sessions` (`id`, `data`, `expires_at`) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `data` = VALUES(`data`), `expires_at` = VALUES(`expires_at`)'
        );
        return $stmt->execute([$id, $data, $expires]);
    }

    public function destroy(string $id): bool {
        $stmt = $this->pdo->prepare('DELETE FROM `sessions` WHERE `id` = ?');
        return $stmt->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false {
        $stmt = $this->pdo->prepare('DELETE FROM `sessions` WHERE `expires_at` < NOW()');
        $stmt->execute();
        return $stmt->rowCount();
    }
}

// Register the handler (silently fall back to file sessions on failure)
try {
    $__dbHandler = new DbSessionHandler(db(), SESSION_LIFETIME);
    session_set_save_handler($__dbHandler, true);
    unset($__dbHandler);
} catch (Throwable $e) {
    error_log('[Session] DB handler failed, using file sessions: ' . $e->getMessage());
}
