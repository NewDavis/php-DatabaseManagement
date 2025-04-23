<?php

namespace NewDavis\DatabaseManagement\Core\Driver;

use PDO;
use Symfony\Component\DependencyInjection\Container;

class Connection
{

    private PDO|null $pdo = null;

    public function __construct(private Container $container)
    {}
    
    private function connect()
    {
        if($this->pdo === null) {
            $databaseUrl = $_ENV['DATABASE_URL'];

            $parsedUrl = parse_url($databaseUrl);

            $scheme = $parsedUrl['scheme']; // e.g., "mysql"
            $user = $parsedUrl['user']; // e.g., "username"

            $pass = '';
            if(isset($parsedUrl['pass'])) {
                $pass = $parsedUrl['pass']; // e.g., "password"
            }

            $host = $parsedUrl['host']; // e.g., "127.0.0.1"
            $port = $parsedUrl['port']; // e.g., "3306"
            $path = ltrim($parsedUrl['path'], '/'); // e.g., "database_name"

            $dsn = sprintf('%s:host=%s;port=%d;dbname=%s', $scheme, $host, $port, $path);

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                $this->pdo = new PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        
        return $this->pdo;
    }

}