<?php

namespace NewDavis\DatabaseManagement\Controller;

use NewDavis\DatabaseManagement\Demo\Account\AccountDefinition;
use NewDavis\DatabaseManagement\Demo\Role\RoleDefinition;
use NewDavis\DatabaseManagement\Demo\Token\TokenDefinition;
use NewDavis\DatabaseManagement\Entity\Builder\Table\TableBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route(
        path: '/orm/test/table-creation',
        name: 'orm.test.table-creation',
        methods: ['GET']
    )]
    public function tableCreation(Request $request)
    {
        $accountTable = TableBuilder::fromDefinition(AccountDefinition::class);
        $roleTable = TableBuilder::fromDefinition(RoleDefinition::class);
        $tokenTable = TableBuilder::fromDefinition(TokenDefinition::class);

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
}