<?php

namespace NewDavis\DatabaseManagement\Controller;

use NewDavis\DatabaseManagement\Core\Driver\Connection;
use NewDavis\DatabaseManagement\Core\Entity\EntityRepository;
use NewDavis\DatabaseManagement\Core\Schema\TableSchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Sorting\FieldSorting;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Sorting\Sorting;
use NewDavis\DatabaseManagement\Test\Account\AccountDefinition;
use NewDavis\DatabaseManagement\Test\Account\AccountEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{

    /**
     * @param EntityRepository<AccountEntity> $accountRepository
     */
    public function __construct(
        #[Autowire(service: 'account.repository')] private readonly EntityRepository $accountRepository,
        #[Autowire(service: Connection::class)] private readonly Connection $connection,
    ) {
    }

    #[Route(path: '/', name: 'database-management.home', methods: ['GET'])]
    public function test()
    {
        /*$this->connection->exec(
            TableSchemaBuilder::build(AccountDefinition::class)
        );*/

        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('autoIncrement', Sorting::SORT_ASCENDING));

        $accounts = $this->accountRepository->search($criteria);
        $this->accountRepository->upsert([
            [
                'id' => '63a3bcb0-7626-4602-94ac-f79b20e932ee',
                'admin' => false,
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'username' => 'admini',
                'admin' => true,
            ]
        ]);

        dd($accounts->first());

        $response = [
            'queries' => [
                'table' => TableSchemaBuilder::createTable(AccountDefinition::class),
                'flag' => TableSchemaBuilder::setFlags(AccountDefinition::class),
                'relation' => TableSchemaBuilder::addRelations(AccountDefinition::class),
                'search' => [
                    'basic' => $accounts
                ]
            ]
        ];

        return new JsonResponse($response);
    }

}