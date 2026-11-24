<?php

namespace App\Install;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Creates the site's MySQL/MariaDB database and a user with rights on that
 * database only, using a privileged login that is used once and never saved.
 *
 * Plain PHP (PDO) so the /install wizard can use it before Laravel boots.
 */
class DatabaseCreator
{
    /**
     * Suggest names and a password from the site name, e.g. "Acme Ltd" gives
     * database acme_ltd, user acme_ltd_usr
     *
     * @param string $site_name
     * @return array{database: string, username: string, password: string}
     */
    public static function suggest(string $site_name): array
    {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $site_name));
        $slug = trim($slug, '_') ?: 'cms';
        $slug = substr($slug, 0, 24);

        return [
            'database' => $slug,
            'username' => substr($slug, 0, 28) . '_usr',
            'password' => self::password(),
        ];
    }

    /**
     * A strong random password without characters that need escaping in .env
     */
    public static function password(int $length = 24): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789-_.';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }

    /**
     * Open a connection (no database selected)
     */
    public static function connect(string $host, int $port, string $username, string $password, ?string $database = null): PDO
    {
        $dsn = 'mysql:host=' . $host . ';port=' . $port . ($database ? ';dbname=' . $database : '') . ';charset=utf8mb4';

        try {
            return new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT            => 5,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Could not connect to MySQL at ' . $host . ':' . $port . ' as ' . $username . ': ' . self::cleanMessage($e));
        }
    }

    /**
     * Create the database and user, and grant the user rights on that
     * database only. Safe to run again: existing objects are reused and the
     * user's password is reset to the one given.
     *
     * @return array<int, string> What was done, for showing to the user
     */
    public function create(
        string $host,
        int $port,
        string $admin_user,
        string $admin_password,
        string $database,
        string $username,
        string $password
    ): array {
        self::assertName($database, 'database name', 64);
        self::assertName($username, 'database user name', 32);
        if (strlen($password) < 12) {
            throw new RuntimeException('Use a database password of at least 12 characters.');
        }

        $Pdo = self::connect($host, $port, $admin_user, $admin_password);
        $done = [];

        try {
            $exists = $Pdo->query('SHOW DATABASES LIKE ' . $Pdo->quote($database))->fetch();
            if ($exists) {
                $tables = $Pdo->query('SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = ' . $Pdo->quote($database))->fetch();
                if ((int) ($tables['n'] ?? 0) > 0) {
                    throw new RuntimeException('The database ' . $database . ' already exists and has tables in it. Choose another name, or use it as an existing database.');
                }
                $done[] = 'Using the existing empty database ' . $database;
            } else {
                $Pdo->exec('CREATE DATABASE `' . $database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                $done[] = 'Created the database ' . $database;
            }

            foreach (self::userHosts($host) as $user_host) {
                $account = $Pdo->quote($username) . '@' . $Pdo->quote($user_host);
                $Pdo->exec('CREATE USER IF NOT EXISTS ' . $account . ' IDENTIFIED BY ' . $Pdo->quote($password));
                $Pdo->exec('ALTER USER ' . $account . ' IDENTIFIED BY ' . $Pdo->quote($password));
                $Pdo->exec('GRANT ALL PRIVILEGES ON `' . $database . '`.* TO ' . $account);
            }
            $Pdo->exec('FLUSH PRIVILEGES');
            $done[] = 'Created the user ' . $username . ' with rights on ' . $database . ' only';
        } catch (PDOException $e) {
            throw new RuntimeException('MySQL refused: ' . self::cleanMessage($e) . '. The login you gave may not be allowed to create databases or users.');
        }

        // Prove the new login works before it goes into .env
        self::connect($host, $port, $username, $password, $database);
        $done[] = 'Checked that ' . $username . ' can connect';

        return $done;
    }

    /**
     * Check a login can use an existing database
     */
    public function checkExisting(string $host, int $port, string $username, string $password, string $database): void
    {
        self::assertName($database, 'database name', 64);
        $Pdo = self::connect($host, $port, $username, $password, $database);

        try {
            $Pdo->exec('CREATE TABLE IF NOT EXISTS `__install_check` (id INT)');
            $Pdo->exec('DROP TABLE `__install_check`');
        } catch (PDOException $e) {
            throw new RuntimeException($username . ' can connect to ' . $database . ' but can\'t create tables: ' . self::cleanMessage($e));
        }

        $tables = $Pdo->query('SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = ' . $Pdo->quote($database) . ' AND table_name = ' . $Pdo->quote('migrations'))->fetch();
        if ((int) ($tables['n'] ?? 0) > 0) {
            throw new RuntimeException('The database ' . $database . ' already has a site installed in it. Choose an empty database.');
        }
    }

    /**
     * Hosts to grant the user from: a local server is reached as both
     * localhost and 127.0.0.1, so grant both; a remote one gets %
     *
     * @return array<int, string>
     */
    public static function userHosts(string $host): array
    {
        return in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true)
            ? ['localhost', '127.0.0.1']
            : ['%'];
    }

    protected static function assertName(string $name, string $what, int $max): void
    {
        if (!preg_match('/^[A-Za-z0-9_\-]{1,' . $max . '}$/', $name)) {
            throw new RuntimeException('The ' . $what . ' may only use letters, numbers, - and _, up to ' . $max . ' characters.');
        }
    }

    protected static function cleanMessage(PDOException $e): string
    {
        return preg_replace('/^SQLSTATE\[[^\]]+\]\s*(\[\d+\]\s*)?/', '', $e->getMessage());
    }
}
