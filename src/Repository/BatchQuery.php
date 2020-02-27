<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mrself\Options\Annotation\Option;
use Mrself\Options\WithOptionsTrait;

class BatchQuery
{
    use WithOptionsTrait;

    /**
     * @Option(required=false)
     * @var Query
     */
    private $query;

    /**
     * @Option(required=false)
     * @var integer
     */
    private $batchSize = 50;

    /**
     * @Option(required=false)
     * @var bool
     */
    private $flush = true;

    /**
     * @Option(required=false)
     * @var array|callable
     */
    private $clearCallback;

    /**
     * @Option(required=false)
     * @var array|callable
     */
    private $createQueryBuilder;

    /**
     * @Option()
     * @var EntityManager
     */
    private $em;

    /**
     * @Option()
     * @var array|callable
     */
    private $callback;

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function run()
    {
        $this->defineQuery();

        $iterableResult = $this->query->iterate();
        foreach ($iterableResult as $index => $result) {
            $this->runCallback($index, $result);
        }

        $this->clearAndFlush();
    }

    /**
     * @param int $index
     * @param array $result
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function runCallback(int $index, array $result)
    {
        call_user_func($this->callback, reset($result));
        if (($index % $this->batchSize) === 0) {

            $this->clearAndFlush();
        }
    }

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function clearAndFlush()
    {
        if ($this->flush) {
            $this->em->flush();
        }

        if ($this->clearCallback) {
            call_user_func($this->clearCallback, $this->em);
        } else {
            $this->em->clear();
        }
    }

    private function defineQuery()
    {
        if (!$this->query) {
            /** @var QueryBuilder $qb */
            $qb = call_user_func($this->createQueryBuilder, 'a');
            $this->query = $qb->getQuery();
        }
    }
}