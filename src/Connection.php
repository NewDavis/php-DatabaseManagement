<?php

namespace NewDavis\DatabaseManagement;

use NewDavis\DatabaseManagement\Entity\Read\EntityReadStatement;
use NewDavis\DatabaseManagement\Entity\Read\EntityReadStatementCollection;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Connection
{
    public static int $totalQueries = 0;
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

    public function execute(array $statements): void
    {
        if (count($statements) == 0) return;

        try {
            foreach ($statements as $statement) {
                $this->pdo->exec($statement);
            }
        } catch (\PDOException $e) {
            throw $e;
        }
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
            dd($e, $statements);
            throw $e;
        }
    }

    public function query(EntityReadStatementCollection $statements): array
    {
        if ($statements->count() == 0) return [];

        $data = [];

        /** @var EntityReadStatement $statement */
        for ($i = 0; $i < $statements->count(); $i++) {
            $statement = $statements->getStatements()[$i];

            $stmt = $this->pdo
                ->prepare($statement->getQuery());
            self::$totalQueries++;

            if (!$stmt->execute($statement->getParameters())) {
                $data[$i] = $stmt->errorInfo();

                continue;
            }

            $data[$i] = [
                'data' => $stmt->fetchAll(),
                'rows' => $stmt->rowCount()
            ];
        }

        return $data;
    }
}