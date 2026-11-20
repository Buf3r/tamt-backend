<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations
     * and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to
     * use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     */
    public array $default = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'db_online_auction',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * This database connection is used when
     * running PHPUnit database tests.
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => 'utf8_general_ci',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }

        // Sobrescribir datos de conexión desde variables de entorno si existen
        $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? null);
        $user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? null);
        $pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? null);
        $name = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? null);
        $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? null);
        $ssl  = getenv('DB_SSL')  ?: ($_ENV['DB_SSL']  ?? null);

        if ($host) {
            $this->default['hostname'] = $host;
        }
        if ($user !== null && $user !== false) {
            $this->default['username'] = $user;
        }
        if ($pass !== null && $pass !== false) {
            $this->default['password'] = $pass;
        }
        if ($name) {
            $this->default['database'] = $name;
        }
        if ($port) {
            $this->default['port'] = (int) $port;
        }

        // Activar TLS/SSL en MySQLi cuando DB_SSL sea true/1/yes
        $useSsl = filter_var($ssl, FILTER_VALIDATE_BOOLEAN);
        if ($useSsl) {
            $this->default['encrypt'] = [
                'ssl' => [
                    'verify_peer'       => true,
                    'verify_peer_name'  => true,
                ],
            ];
        }
    }
}
