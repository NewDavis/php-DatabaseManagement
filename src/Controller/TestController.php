<?php

namespace NewDavis\DatabaseManagement\Controller;

use NewDavis\DatabaseManagement\Demo\Account\AccountDefinition;
use NewDavis\DatabaseManagement\Demo\Account\AccountEntity;
use NewDavis\DatabaseManagement\Demo\Role\RoleDefinition;
use NewDavis\DatabaseManagement\Demo\Token\TokenDefinition;
use NewDavis\DatabaseManagement\Entity\Builder\Table\TableBuilder;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\EntityRepository;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
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
        path: '/orm/test/write',
        name: 'orm.test.write',
        methods: ['GET']
    )]
    public function write(Request $request)
    {
        $accountId = Uuid::uuid4();
        //$tokenId = Uuid::fromString("685aa2afb3ac4d8da059667b0ed438ea");
        $roleId = Uuid::fromString("A9A4795083C94947ADE47D2DFE914391");

        $this->accountRepository->create([
            [
                'id' => $accountId,
                'username' => 'admin',
                'email' => 'admin@newdavis.me',
                'password' => 'password',
                'primaryRoleId' => $roleId,
                'primaryRole' => [
                    'id' => $roleId,
                    'name' => 'Administrator',
                ],
                'roles' => [
                    [
                        'id' => $roleId,
                        'name' => 'Administrator'
                    ]
                ],
                /*'tokenId' => $tokenId,
                'token' => [
                    'id' => $tokenId,
                    'token' => 'eyjfidwegfouehrghsefojigseijt'
                ]*/
            ],
        ]);

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
        $accountId = Uuid::fromString("E7D8B424536D4D5E92CBA8F534F85BCC");
        $criteria = new Criteria([$accountId]);

        $entities = $this->accountRepository->searchIds($criteria);

        dd($entities);

        return new JsonResponse([
            'success' => true
        ]);
    }
}
