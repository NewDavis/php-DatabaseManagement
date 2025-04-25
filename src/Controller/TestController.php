<?php

namespace NewDavis\DatabaseManagement\Controller;

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
        #[Autowire(service: 'account.repository')] private readonly EntityRepository $accountRepository
    ) {
    }

    #[Route(path: '/', name: 'database-management.home', methods: ['GET'])]
    public function test()
    {
        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('autoIncrement', Sorting::SORT_ASCENDING));

        $accounts = $this->accountRepository->search($criteria);
        $this->accountRepository->upsert([
            [
                'id' => Uuid::uuid4()->toString(),
                'username' => 'Davis',
                'admin' => true,
                'createdAt' => new \DateTimeImmutable(),
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