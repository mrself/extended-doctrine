<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Mrself\ExtendedDoctrine\Repository\Exception\InvalidEntitySourceException;
use Mrself\ExtendedDoctrine\Entity\SluggableInterface;

/**
 * @mixin EntityRepository
 */
trait RepositoryTrait
{
    /**
     * Converts input source to entity.
     * It is useful in case when method accepts
     * entity id (it can be slug or simple id) or entity itself.
     *
     * @param $source
     * @return object|null
     * @throws InvalidEntitySourceException
     * @throws \ReflectionException
     */
    public function toEntity($source)
    {
        if (is_string($source) || is_int($source)) {
            return $this->findByAppId($source);
        }

        if ($this->isEntity($source)) {
            return $source;
        }

        throw new InvalidEntitySourceException($source);
    }

    public function isEntity($source): bool
    {
        $class = $this->getClassName();
        return $source instanceof $class;
    }

    public function fromIdOrEntity($source)
    {
        if ($this->isEntity($source)) {
            return $source;
        }

        return $this->find($source);
    }

    public function getByIdOrEntity($source)
    {
        if ($this->isEntity($source)) {
            return $source;
        }

        return $this->get($source);
    }

    public function get($id)
    {
        $entity = $this->find($id);
        if ($entity) {
            return $entity;
        }

        throw new \RuntimeException('Can not find an entity by id: ' . $id);
    }

    /**
     * @param $id
     * @return object|null
     * @throws \ReflectionException
     */
    public function findByAppId($id)
    {
        if ($this->isSluggable()) {
            return $this->findOneBy(['slug' => $id]);
        }

        return $this->find($id);
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    public function isSluggable()
    {
        $class = $this->getClassName();
        $reflection = new \ReflectionClass($class);
        return $reflection->implementsInterface(SluggableInterface::class);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function truncate()
    {
        $connection = $this->_em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0');
        $query = $platform->getTruncateTableSQL($this->getTableName(), false);
        $connection->executeUpdate($query);
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function getTableName(): string
    {
        return $this->getClassMetadata()->getTableName();
    }

    /**
     * Perform operations on in batch on the whole table
     * @param array $options
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function doBatch(array $options)
    {
        $options['createQueryBuilder'] = [$this, 'createQueryBuilder'];
        BatchQuery::make($options)->run();
    }

    public function getResult(QueryBuilder $qb): array
    {
        return $qb->getQuery()->getResult();
    }

    public function startWhere($qb): WhereBuilder
    {
        return WhereBuilder::makeFromQueryBuilder($qb);
    }
}