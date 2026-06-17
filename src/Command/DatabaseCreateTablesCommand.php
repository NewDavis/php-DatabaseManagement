<?php

namespace NewDavis\DatabaseManagement\Command;

use NewDavis\DatabaseManagement\Entity\Builder\Table\TableBuilder;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DatabaseCreateTablesCommand extends Command
{
    private const COMMAND_NAME = 'database:create-tables';
    private const COMMAND_DESC = 'Run this command to automatically create defined database tables';

    public function __construct(
        #[Autowire(service: EntityRegistry::class)] private readonly EntityRegistry $registry
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription(self::COMMAND_DESC);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tableBuilders = array_map(
            fn(EntityDefinitionInterface $definition) => TableBuilder::fromDefinition($this->registry, $definition),
            array_values($this->registry->getDefinitions())
        );

        $statements = ['SET FOREIGN_KEY_CHECKS = 0;'];

        foreach ($tableBuilders as $builder) {
            $tableStatements = $this->buildStatements($builder->build());

            foreach ($tableStatements as $statement) {
                $statements[] = $statement;
            }
        }

        $statements[] = 'SET FOREIGN_KEY_CHECKS = 0;';

        try {
            $this->registry->getConnection()->execute($statements);

            $output->writeln('<info>Database tables have been created</info>');

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('Something went wrong during creation of database tables:');
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
        }

        return Command::FAILURE;
    }

    private function buildStatements(string $query): array
    {
        if (substr_count($query, 'CREATE') <= 1) {
            return [$query];
        }

        $queries = explode(';', $query);
        // remove last empty string
        array_pop($queries);

        return array_map(
            fn(string $s) => $s . ';',
            $queries
        );
    }
}