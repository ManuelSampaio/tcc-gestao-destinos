<?php

namespace Config;

use PDO;
use Exception;

class Database {
    private static ?PDO $conn = null;

    private static function getEnv(string $key, string $default = ''): string {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function getConnection(): PDO {
        if (self::$conn === null) {
            try {
                $host = self::getEnv('DB_HOST', 'localhost');
                $db_name = self::getEnv('DB_NAME', 'turismo_angola');
                $username = self::getEnv('DB_USER', 'root');
                $password = self::getEnv('DB_PASS', '');
                $charset = 'utf8mb4';

                $dsn = "mysql:host={$host};dbname={$db_name};charset={$charset}";

                self::$conn = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false, // 🔒 Segurança contra SQL Injection
                ]);
            } catch (Exception $e) {
                error_log("[Erro] Falha na conexão com o banco: " . $e->getMessage());
                throw new Exception("Erro ao conectar ao banco de dados. Verifique as configurações.");
            }
        }

        return self::$conn;
    }
}
