<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mrself\ExtendedDoctrine\ExtendedDoctrineException;
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
     * @Option(required=false)
     * @var array|callable
     */
    private $callback;

    /**
     * @Option(required=false)
     * @var array|callable
     */
    private $batchCallback;

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function run()
    {
        $this->defineQuery();

        $iterableResult = $this->query->iterate();
        $this->passResultToCallback($iterableResult);
        $this->clearAndFlush();
    }

    protected function passResultToCallback(IterableResult $result)
    {
        if ($this->callback) {
            $this->execCallbackForEachEntity($result);
        } else {
            $this->execCallbackForBatch($result);
        }
    }

    protected function execCallbackForBatch(IterableResult $result)
    {
        $batchResult = [];
        foreach ($result as $index => $item) {
            $index++;
            $batchResult[] = reset($item);

            if (($index % $this->batchSize) === 0) {
                call_user_func($this->batchCallback, $batchResult);
                $batchResult = [];
            }
        }
    }

    /**
     * @param IterableResult $result
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execCallbackForEachEntity(IterableResult $result)
    {
        foreach ($result as $index => $item) {
            if ($this->execCallbackForEntity($index, $item) === false) {
                break;
            }
        }
    }

    /**
     * @param int $index
     * @param array $result
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function execCallbackForEntity(int $index, array $result)
    {
        $result = call_user_func($this->callback, reset($result));
        if (($index % $this->batchSize) === 0) {

            $this->clearAndFlush();
        }

        return $result;
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

    protected function onInit()
    {
        $this->assertCallback();
    }

    private function assertCallback()
    {
        if ($this->callback || $this->batchCallback) {
            return;
        }

        throw new ExtendedDoctrineException('No callback provided for BatchQuery');
    }
}