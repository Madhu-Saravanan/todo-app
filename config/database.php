<?php
// ============================================================
// AntiGravity Todo App – PDO Database Connection (Singleton)
// ============================================================
require_once __DIR__ . '/config.php';

class Database {
    /** @var PDO|null Singleton PDO instance */
    private static ?PDO $instance = null;

    /**
     * Returns the singleton PDO connection.
     * Throws an exception on failure (caught by error handler).
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_FOUND_ROWS   => true,
            ];

            // TiDB Cloud and other remote hosts require SSL
            if (!in_array(DB_HOST, ['localhost', '127.0.0.1'], true)) {
                // Find the system CA bundle (path varies by Linux distro)
                $caBundles = [
                    '/etc/ssl/certs/ca-certificates.crt',  // Debian/Ubuntu
                    '/etc/pki/tls/certs/ca-bundle.crt',    // Amazon Linux / CentOS
                    '/etc/ssl/cert.pem',                    // Alpine
                ];
                foreach ($caBundles as $ca) {
                    if (file_exists($ca)) {
                        $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
                        break;
                    }
                }
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('[DB Error] ' . $e->getMessage());
                die(json_encode([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'host'    => DB_HOST,
                    'port'    => DB_PORT,
                    'db'      => DB_NAME,
                    'user'    => DB_USER,
                ]));
            }
        }
        return self::$instance;
    }

    // Prevent cloning or unserialization of singleton
    private function __clone() {}
    public function __wakeup(): never { throw new \Exception('Cannot unserialize singleton.'); }
}

/**
 * Convenience helper – returns the PDO instance.
 * Usage: $pdo = db();
 */
function db(): PDO {
    return Database::getInstance();
}
