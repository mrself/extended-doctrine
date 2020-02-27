<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Repository;

use Doctrine\ORM\QueryBuilder;
use Mrself\ExtendedDoctrine\Tests\Unit\Repository\WhereBuilderTest;
use Mrself\Options\Annotation\Option;
use Mrself\Options\WithOptionsTrait;

/**
 * Helper for QueryBuilder
 * Add 'where' parts to query where all 'where's use 'and' condition
 * @see WhereBuilderTest
 */
class WhereBuilder
{
    use WithOptionsTrait;

    /**
     * @Option()
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var bool
     */
    protected $isDirty = false;

    public static function makeFromQueryBuilder(QueryBuilder $qb): self
    {
        return static::make(['qb' => $qb]);
    }

    public function add(string $statement): self
    {
        if ($this->isDirty) {
            $this->qb->andWhere($statement);
        } else {
            $this->qb->where($statement);
        }
        $this->isDirty = true;
        return $this;
    }
}