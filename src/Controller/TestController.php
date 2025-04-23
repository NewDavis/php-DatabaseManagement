<?php

namespace NewDavis\DatabaseManagement\Controller;

use NewDavis\DatabaseManagement\Core\Entity\EntityRepository;
use NewDavis\DatabaseManagement\Core\Schema\TableSchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\EqualsAnyFilter;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\MultiNotFilter;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Sorting\FieldSorting;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Sorting\Sorting;
use NewDavis\DatabaseManagement\Test\Account\AccountDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{

    public function __construct(
        #[Autowire(service: 'account.repository')] private readonly EntityRepository $accountRepository
    ) {
    }

    #[Route(path: '/', name: 'database-management.home', methods: ['GET'])]
    public function test()
    {
        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('autoIncrement', Sorting::SORT_ASCENDING));
        $criteria->addFilter(new MultiNotFilter(MultiNotFilter::OPERATOR_AND, [
            new EqualsAnyFilter('username', ['Admin', 'test'])
        ]));

        $response = [
            'queries' => [
                'table' => TableSchemaBuilder::buildTableSQL(AccountDefinition::class),
                'flag' => TableSchemaBuilder::buildFlagSQL(AccountDefinition::class),
                'relation' => TableSchemaBuilder::buildRelationSQL(AccountDefinition::class),
                'search' => [
                    'basic' => $this->accountRepository->search($criteria)
                ]
            ]
        ];

        return new JsonResponse($response);
    }

}