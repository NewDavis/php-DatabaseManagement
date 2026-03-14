<?php

namespace NewDavis\DatabaseManagement;

use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Connection
{
    private readonly \PDO $pdo;

    public function __construct(
        #[Autowire('%env(DATABASE_URL)%')] private readonly string $databaseUrl
    ) {
        $parsedUrl = parse_url($this->databaseUrl);

        $driver = $parsedUrl['scheme'];
        $host = $parsedUrl['host'];
        $port = $parsedUrl['port'] ?? 3306;
        $databaseName = ltrim($parsedUrl['path'], '/');
        $username = $parsedUrl['user'];
        $password = $parsedUrl['pass'];
        $query = $parsedUrl['query'];

        $dsn = "$driver:host=$host;port=$port;dbname=$databaseName;$query";

        $this->pdo = new \PDO(
            $dsn,
            $username,
            $password,
            [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_STRINGIFY_FETCHES => false,
                \PDO::ATTR_TIMEOUT => 5
            ]
        );
    }

    public function write(EntityWriteStatementCollection $statements): void
    {
        if ($statements->count() == 0) return;

        try {
            $this->pdo->beginTransaction();

            /** @var EntityWriteStatement $statement */
            foreach ($statements as $statement) {
                $this->pdo
                    ->prepare($statement->getQuery())
                    ->execute($statement->getParameters());
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}