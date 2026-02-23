<?php

namespace NewDavis\DatabaseManagement\Controller;

use NewDavis\DatabaseManagement\Demo\Account\AccountDefinition;
use NewDavis\DatabaseManagement\Demo\Role\RoleDefinition;
use NewDavis\DatabaseManagement\Demo\Token\TokenDefinition;
use NewDavis\DatabaseManagement\Entity\Converter\Table\Table;
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
        dd(
            Table::fromDefinition(AccountDefinition::class)->build(),
            Table::fromDefinition(RoleDefinition::class)->build(),
            Table::fromDefinition(TokenDefinition::class)->build(),
        );
    }
}