<?php
// ============================================================
// AntiGravity Todo App – User Model
// ============================================================
require_once __DIR__ . '/../config/database.php';

class UserModel {

    /**
     * Find a user by email address.
     */
    public static function findByEmail(string $email): array|false {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Find a user by primary key.
     */
    public static function findById(int $id): array|false {
        $stmt = db()->prepare('SELECT id, name, email, avatar, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Register a new user.
     * Returns the new user's ID on success, false on failure.
     */
    public static function create(string $name, string $email, string $password): int|false {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = db()->prepare(
            'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hash]);
        $newId = (int)db()->lastInsertId();
        return $newId > 0 ? $newId : false;
    }

    /**
     * Validate login credentials.
     * Returns user row on success, false otherwise.
     */
    public static function authenticate(string $email, string $password): array|false {
        $user = self::findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    /**
     * Store a password-reset token (hashed) for a user.
     */
    public static function setResetToken(int $userId, string $token): void {
        $hash    = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        $stmt    = db()->prepare(
            'UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?'
        );
        $stmt->execute([$hash, $expires, $userId]);
    }

    /**
     * Find a user by a valid (un-expired) reset token.
     */
    public static function findByResetToken(string $token): array|false {
        $hash = hash('sha256', $token);
        $stmt = db()->prepare(
            'SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1'
        );
        $stmt->execute([$hash]);
        return $stmt->fetch();
    }

    /**
     * Update a user's password and clear the reset token.
     */
    public static function updatePassword(int $userId, string $newPassword): void {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = db()->prepare(
            'UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?'
        );
        $stmt->execute([$hash, $userId]);
    }
}
