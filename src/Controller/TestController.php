<?php

namespace NewDavis\DatabaseManagement\Controller;

use NewDavis\DatabaseManagement\Core\Driver\Connection;
use NewDavis\DatabaseManagement\Core\Entity\EntityRepository;
use NewDavis\DatabaseManagement\Core\Schema\TableSchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\BetweenFilter;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\GreatherThanFilter;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\LessThanFilter;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Sorting\FieldSorting;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Sorting\Sorting;
use NewDavis\DatabaseManagement\Test\Account\AccountDefinition;
use NewDavis\DatabaseManagement\Test\Account\AccountEntity;
use NewDavis\DatabaseManagement\Test\Role\RoleDefinition;
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

        /*$this->accountRepository->upsert([
            [
                'id' => Uuid::uuid4()->toString(),
                'username' => 'Test',
                'admin' => false,
                'customFields' => [
                    'role' => [
                        [
                            'name' => 'admin',
                            'permissions' => [
                                '*'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'username' => 'Test2',
                'admin' => true,
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'username' => 'Test3',
                'admin' => true,
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'username' => 'Test4',
                'admin' => true,
            ],
            [
                'id' => Uuid::uuid4()->toString(),
                'username' => 'Test5',
                'admin' => false,
            ]
        ]);*/

        $this->accountRepository->upsert([
            [
                'id' => 'f9331499-c235-4057-aa22-0ca556158aa1',
                'username' => 'Admin',
                'admin' => true,
                'primaryRole' => [
                    'id' => '4b26402e-6ecd-4478-a7e7-26fcb2d28a8f',
                    'name' => 'Administrator'
                ],
                'roles' => [
                    [
                        'id' => '4b26402e-6ecd-4478-a7e7-26fcb2d28a8f',
                        'name' => 'Administrator'
                    ],
                    [
                        'id' => '6c9a9ada-e05a-4dad-a663-44b0f1512333',
                        'name' => 'Manager'
                    ],
                    [
                        'id' => '9f885d07-e5cc-4c44-850b-c67666f3e00a',
                        'name' => 'Entwickler'
                    ],
                    [
                        'id' => '2f7a545e-2442-4d6c-bb18-749167b216fe',
                        'name' => 'Nutzer'
                    ]
                ]
            ],
            [
                'id' => '07ffbbbd-bb52-4edb-a95c-92881554e11d',
                'username' => 'NewDavis',
                'admin' => true,
                'primaryRoleId' => '6c9a9ada-e05a-4dad-a663-44b0f1512333'
            ]
        ]);

        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('autoIncrement', Sorting::SORT_ASCENDING));

        $accounts = $this->accountRepository->search($criteria);
        dd($accounts);

        $response = [
            'basic' => $accounts
        ];

        return new JsonResponse($response);
    }

}