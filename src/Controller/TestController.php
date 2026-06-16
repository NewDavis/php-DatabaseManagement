<?php

namespace NewDavis\DatabaseManagement\Controller;

use NewDavis\DatabaseManagement\Connection;
use NewDavis\DatabaseManagement\Demo\Account\AccountDefinition;
use NewDavis\DatabaseManagement\Demo\Account\AccountEntity;
use NewDavis\DatabaseManagement\Demo\Role\RoleDefinition;
use NewDavis\DatabaseManagement\Demo\Token\TokenDefinition;
use NewDavis\DatabaseManagement\Entity\Builder\Table\TableBuilder;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\EntityRepository;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\EqualsAnyFilter;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\EqualsFilter;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\MultiFilter;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\MultiFilterType;
use PhpParser\Node\Expr\AssignOp\Mul;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    public function __construct(
        #[Autowire(service: EntityRegistry::class)] private readonly EntityRegistry $registry,
        #[Autowire(service: AccountDefinition::class)] private readonly AccountDefinition $accountDefinition,
        #[Autowire(service: RoleDefinition::class)] private readonly RoleDefinition $roleDefinition,
        #[Autowire(service: TokenDefinition::class)] private readonly TokenDefinition $tokenDefinition,
        #[Autowire(service: 'account.repository')] private readonly EntityRepository $accountRepository
    ) {
    }

    #[Route(
        path: '/orm/test/table-creation',
        name: 'orm.test.table-creation',
        methods: ['GET']
    )]
    public function tableCreation(Request $request)
    {
        $accountTable = TableBuilder::fromDefinition($this->registry, $this->accountDefinition);
        $roleTable = TableBuilder::fromDefinition($this->registry, $this->roleDefinition);
        $tokenTable = TableBuilder::fromDefinition($this->registry, $this->tokenDefinition);

        dd(
            $accountTable->build(),
            $roleTable->build(),
            $tokenTable->build(),

            implode("\n", [
                "SET FOREIGN_KEY_CHECKS = 0;",
                $accountTable->build(),
                $roleTable->build(),
                $tokenTable->build(),
                "SET FOREIGN_KEY_CHECKS = 1;",
            ])
        );
    }

    #[Route(
        path: '/orm/test/create',
        name: 'orm.test.create',
        methods: ['GET']
    )]
    public function create(Request $request)
    {
        $start = microtime(true);

        $accountId = /*Uuid::fromString("57e02de6-42db-47e5-83ea-03952dea1d4a");*/Uuid::uuid4();
        $tokenId = Uuid::fromString("685aa2afb3ac4d8da059667b0ed438ea");
        $roleId = Uuid::fromString("a9a47950-83c9-4947-ade4-7d2dfe914391");

        $dataSets = [];
        for ($i = 0; $i < 100; $i++) {
            $accountId = /*Uuid::fromString("57e02de6-42db-47e5-83ea-03952dea1d4a");*/Uuid::uuid4();

            $dataSets[] = [
                'id' => $accountId,
                'username' => 'admin' . substr($accountId->toString(), 0, 6),
                'email' => 'admin' . substr($accountId->toString(), 0, 6) . '@newdavis.me',
                'password' => 'password',
                'primaryRole' => [
                    'id' => $roleId,
                    'name' => 'Administrator',
                ],
                'roles' => [
                    [
                        'id' => $roleId->getBytes(),
                        'name' => 'Administrator'
                    ]
                ],
                /*'tokenId' => $tokenId,
                'token' => [
                    'id' => $tokenId,
                    'accountId' => $accountId,
                    'token' => 'eyjfidwegfouehrghsefojigseijt'
                ]*/
            ];
        }

        $result = $this->accountRepository->create($dataSets);

        $end = microtime(true);
        $took = $end - $start;

        dd($result, "took {$took}s");

        return new JsonResponse([
            'success' => true
        ]);
    }

    #[Route(
        path: '/orm/test/update',
        name: 'orm.test.update',
        methods: ['GET']
    )]
    public function update(Request $request)
    {
        $start = microtime(true);

        $accountId = Uuid::fromString("57e02de6-42db-47e5-83ea-03952dea1d4a");
        //$tokenId = Uuid::fromString("685aa2afb3ac4d8da059667b0ed438ea");
        $roleId = Uuid::fromString("A9A4795083C94947ADE47D2DFE914391");
        $secondRoleId = Uuid::fromString("685aa2afb3ac4d8da059667b0ed438ea");

        $result = $this->accountRepository->update([
            [
                'id' => $accountId,
                'username' => 'NewDavis',
                'email' => 'info@newdavis.me',
                'roles' => [
                    [
                        'id' => $roleId->getBytes(),
                    ],
                    [
                        'id' => $secondRoleId->getBytes(),
                        'name' => 'Entwickler'
                    ]
                ]
            ],
        ]);

        $end = microtime(true);
        $took = $end - $start;

        dd($result, "took {$took}s");

        return new JsonResponse([
            'success' => true
        ]);
    }

    #[Route(
        path: '/orm/test/read',
        name: 'orm.test.read',
        methods: ['GET']
    )]
    public function read(Request $request)
    {
        $start = microtime(true);

        $accountId = Uuid::fromString("57e02de6-42db-47e5-83ea-03952dea1d4a");
        $criteria = new Criteria(/*[$accountId, '1DC0D0B1D9A44DF2973BB05CA4893D11', '570787C36B164D3594BB0D1963E8B1FA']*/);
        $criteria->addAssociation('roles.primaryUsage');
        /*$criteria->addFilter(new EqualsFilter('token.account.username', 'NewDavis'));
        $criteria->addFilter(new EqualsFilter('token.account.roles.name', 'Administrator'));
        $criteria->addFilter(new MultiFilter([
            new EqualsFilter('token.account.username', 'NewDavis'),
            new EqualsFilter('token.account.autoIncrement', 1),
            new EqualsFilter('token.account.email', 'info@newdavis.me'),
        ], MultiFilterType::OR));*/

        $entities = $this->accountRepository->search($criteria);

        $end = microtime(true);
        $took = $end - $start;

        $queries = Connection::$totalQueries;

        dump(memory_get_peak_usage(true) / 1024 / 1024 . " MB");
        dd($entities, "took {$took}s. Queries executed: {$queries}");

        return new JsonResponse([
            'success' => true
        ]);
    }

    #[Route(
        path: '/orm/test/read-ids',
        name: 'orm.test.read-ids',
        methods: ['GET']
    )]
    public function readIds(Request $request)
    {
        $start = microtime(true);

        $accountIds = [
            Uuid::fromString("57e02de6-42db-47e5-83ea-03952dea1d4a"),
            Uuid::fromString("1DC0D0B1D9A44DF2973BB05CA4893D11"),
            Uuid::fromString('570787C36B164D3594BB0D1963E8B1FA')
        ];
        $criteria = new Criteria($accountIds);

        $ids = $this->accountRepository->searchIds($criteria);

        $end = microtime(true);
        $took = $end - $start;

        dd($ids, "took {$took}s");

        return new JsonResponse([
            'success' => true
        ]);
    }

    #[Route(
        path: '/orm/test/count',
        name: 'orm.test.count',
        methods: ['GET']
    )]
    public function count(Request $request)
    {
        $start = microtime(true);

        $accountIds = [
            Uuid::fromString("57e02de6-42db-47e5-83ea-03952dea1d4a"),
            Uuid::fromString("1DC0D0B1D9A44DF2973BB05CA4893D11"),
            Uuid::fromString('570787C36B164D3594BB0D1963E8B1FA')
        ];
        $criteria = new Criteria(/*$accountIds*/);

        $ids = $this->accountRepository->count($criteria);

        $end = microtime(true);
        $took = $end - $start;

        dd($ids, "took {$took}s");

        return new JsonResponse([
            'success' => true
        ]);
    }
}
