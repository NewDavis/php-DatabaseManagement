<?php

namespace NewDavis\DatabaseManagement\Core\Driver;

use NewDavis\DatabaseManagement\DatabaseManagementBundle;
use PDO;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Container;

class Connection
{

    private PDO|null $pdo = null;

    public function __construct(
        #[Autowire(service: 'service_container')] private readonly Container $container
    ) {
    }
    
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

    public static $executedQueries = 0;

    public function prepare(Statement $query): array|null
    {
        if($this->connect() == null) return null;

        $result = null;

        if($this->pdo->beginTransaction()) {
            if(DatabaseManagementBundle::DEBUG) {
                print_r("\n");
                print_r("\n" . $query->getStatement());
                print_r("\n" . implode(', ', array_values($query->getParameters())));
                print_r("\n");
            }
            $statement = $this->pdo->prepare($query->getStatement());

            $parameters = [];
            foreach ($query->getParameters() as $key => $value) {
                if(!is_numeric($key)) {
                    $statement->bindValue($key, $value);
                    continue;
                }

                $parameters[] = $value;
            }

            if ($statement->execute($parameters)) {
                $this->pdo->commit();
                $result = $statement->fetchAll();
                self::$executedQueries++;
            } else {
                $this->pdo->rollBack();
            }
        }

        // TODO Events

        return $result;
    }

    /*public function prepareQueries(array $queries): array|null
    {
        if($this->connect() == null) return null;

        $results = [];

        foreach ($queries as $timestamps) {
            foreach ($timestamps as $tables) {
                foreach ($tables as $table) {
                    if ($this->pdo->beginTransaction()) {
                        if(DatabaseManagementBundle::DEBUG) {
                            print_r("\n");
                            print_r("\n" . $table['query']);
                            print_r("\n" . implode(', ', $table['parameters']));
                            print_r("\n");
                        }
                        $statement = $this->pdo->prepare($table['query']);

                        foreach ($table['parameters'] as $key => $value) {
                            $statement->bindValue($key, $value);
                        }

                        if ($statement->execute()) {
                            $this->pdo->commit();
                            $results[$table['query']] = $statement->fetchAll();
                        } else {
                            $this->pdo->rollBack();
                        }
                    }
                }
            }
        }

        // TODO Events

        return $results;
    }*/

    public function query(string $query): void
    {
        if($this->connect() == null) return;

        if(DatabaseManagementBundle::DEBUG) {
            print_r("\n");
            print_r("\n" . $query);
            print_r("\n");
        }
        $this->pdo->query($query);
    }


    public function exec(string $query): void
    {
        if($this->connect() == null) return;

        if(DatabaseManagementBundle::DEBUG) {
            print_r("\n");
            print_r("\n" . $query);
            print_r("\n");
        }
        $this->pdo->exec($query);
    }

    public function disconnect()
    {
        if($this->pdo == null) return null;

        // flush all changes.
        /*$repositories = [];

        foreach ($this->container->getServiceIds() as $serviceId) {
            if(!str_ends_with($serviceId, '.repository')) continue;

            $repositories[] = $this->container->get($serviceId);
        }

        foreach ($repositories as $repository) {
            if(!($repository instanceof EntityRepository)) continue;

            $repository->flush();
        }*/

        // disconnect the database connection.
        $this->pdo = null;
    }

}