<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Unit\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use Mrself\ExtendedDoctrine\Repository\WhereBuilder;
use PHPUnit\Framework\TestCase;

class WhereBuilderTest extends TestCase
{
    /**
     * @var WhereBuilder
     */
    private $service;

    /**
     * @var QueryBuilder
     */
    private $qb;

    public function testItAddsWhereStatementToQueryBuilder()
    {
        $this->service->add('field1 = value1');

        $parts = $this->getParts();

        $this->assertEquals('field1 = value1', $parts[0]);
    }

    public function testItUsesAndForMultipleWhereStatements()
    {
        $this->service
            ->add('field1 = value1')
            ->add('field2 = value2');

        $parts = $this->getParts();

        $this->assertEquals('field1 = value1', $parts[0]);
        $this->assertEquals('field2 = value2', $parts[1]);
    }

    private function getParts(): array
    {
        /** @var Andx $wherePart */
        $wherePart = $this->qb->getDQLPart('where');
        return $wherePart->getParts();
    }

    protected function setUp()
    {
        parent::setUp();
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['where', 'andWhere'])
            ->getMock();
        $this->qb = new QueryBuilder($em);
        $this->service = WhereBuilder::makeFromQueryBuilder($this->qb);
    }
}