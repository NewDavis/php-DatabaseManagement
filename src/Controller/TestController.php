<?php

namespace NewDavis\DatabaseManagement\Controller;

use NewDavis\DatabaseManagement\Demo\Account\AccountDefinition;
use NewDavis\DatabaseManagement\Demo\Role\RoleDefinition;
use NewDavis\DatabaseManagement\Demo\Token\TokenDefinition;
use NewDavis\DatabaseManagement\Entity\Builder\Table\TableBuilder;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\EntityRepository;
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
        dd($this->accountRepository->create([
            [
                'username' => 'admin',
                'email' => 'admin@newdavis.me',
                'password' => 'password',
                'primaryRole' => [
                    'name' => 'Administrator',
                ],
                'roles' => [
                    [
                        'name' => 'Administrator'
                    ]
                ],
                'token' => [
                    'token' => 'eyjfidwegfouehrghsefojigseijt'
                ]
            ]
        ]));

        return new JsonResponse([
            'success' => true
        ]);
    }
}
