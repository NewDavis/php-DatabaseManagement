<?php

namespace NewDavis\DatabaseManagement\Command;

use NewDavis\DatabaseManagement\Core\Driver\Connection;
use NewDavis\DatabaseManagement\Core\Entity\EntityRepository;
use NewDavis\DatabaseManagement\Core\Schema\TableSchemaBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Container;

class CreateTablesCommand extends Command
{

    public function __construct(
        #[Autowire(service: 'service_container')] private readonly Container $container,
        #[Autowire(service: Connection::class)] private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    const COMMAND_NAME = 'create:tables';
    const COMMAND_DESC = 'Create tables';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription(self::COMMAND_DESC);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repositories = array_map(
            function ($repositoryId) {
                return $this->container->get($repositoryId);
            },
            array_values(array_filter(
                $this->container->getServiceIds(),
                fn($serviceId) => str_ends_with($serviceId, '.repository'),
            ))
        );

        $definitionMapping = [];
        /** @var EntityRepository $repository */
        foreach ($repositories as $repository) {
            $definition = $repository->getDefinition();

            $definitionMapping[$definition] = [
                TableSchemaBuilder::build($definition)
            ];

            $manyToManyTables = TableSchemaBuilder::createManyToManyTables($definition);

            if(count($manyToManyTables) > 0) {
                $definitionMapping[$definition][] = $manyToManyTables;
            }
        }

        /**
         * 0: create tables
         * 1: add flags
         * 2: add foreign keys
         */
        for ($i = 0; $i < 3; $i++) {
            foreach ($definitionMapping as $definition => $queries) {
                // create tables
                $query = $queries[0][$i];
                $this->connection->exec($query);

                if(count($queries) > 1) {
                    for ($j = 1; $j < count($queries); $j++) {
                        $query = $queries[$j][$i];
                        $this->connection->exec($query);
                    }
                }

                if($i == 2) {
                    $entityName = $definition::getEntityName();
                    $output->writeln('<fg=green>Table "<fg=bright-green;options=bold>' . $entityName . '</>" created successfully.</>');
                }
            }
        }

        return Command::SUCCESS;
    }

}