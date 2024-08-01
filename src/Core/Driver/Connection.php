<?php

namespace DatabaseManagement\Core\Driver;

use DatabaseManagement\Core\Entity\EntityCollection;
use DatabaseManagement\Core\Entity\EntityRepository;
use Doctrine\DBAL\Driver\PDO\PDOException;
use PDO;
use Symfony\Component\DependencyInjection\Container;

class Connection
{

    private PDO|null $pdo = null;

    public function __construct(private Container $container)
    {
        $databaseUrl = $_ENV['TEST_DATABASE_URL'];

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
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function prepare(string $query, array $params = []): array|null
    {
        if($this->pdo == null) return null;

        $results = [];

        if($this->pdo->beginTransaction()) {
            //print_r('<br>' . $query);
            $statement = $this->pdo->prepare($query);

            foreach ($params as $key => $value) {
                $statement->bindValue($key, $value);
            }

            if ($statement->execute()) {
                $this->pdo->commit();
                $results[$query] = $statement->fetchAll();
            } else {
                $this->pdo->rollBack();
            }
        }

        // TODO Events

        return $results;
    }

    public function prepareQueries(array $queries): array|null
    {
        if($this->pdo == null) return null;

        $results = [];

        foreach ($queries as $timestamps) {
            foreach ($timestamps as $tables) {
                foreach ($tables as $table) {
                    if ($this->pdo->beginTransaction()) {
                        //print_r('<br>' . $table['query']);
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
    }

    public function query(string $query): void
    {
        //print_r('<br>' . $query);
        $this->pdo->query($query);
    }


    public function exec(string $query): void
    {
        //print_r('<br>' . $query);
        $this->pdo->exec($query);
    }

    public function disconnect()
    {
        // flush all changes.
        $repositories = [];

        foreach ($this->container->getServiceIds() as $serviceId) {
            if(!str_ends_with($serviceId, '.repository')) continue;

            $repositories[] = $this->container->get($serviceId);
        }

        foreach ($repositories as $repository) {
            if(!($repository instanceof EntityRepository)) continue;

            $repository->flush();
        }

        // disconnect the database connection.
        $this->pdo = null;
    }

}