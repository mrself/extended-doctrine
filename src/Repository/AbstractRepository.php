<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AbstractRepository extends ServiceEntityRepository
{
    use RepositoryTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, $this->defineEntityClass());
    }

    protected function defineEntityClass()
    {
        $class = get_class($this);
        $class = substr($class, 0, -strlen('Repository'));
        return str_replace('Repository', 'Entity', $class);
    }

    /**
     * @param EntityInterface $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(EntityInterface $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function persist(EntityInterface $entity)
    {
        try {
            $this->getEntityManager()->persist($entity);
        } catch (ORMException $e) {
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param EntityInterface $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(EntityInterface $entity)
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param EntityInterface|object $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete($entity)
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * @param EntityInterface $entity
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function ensureDeleted($entity)
    {
        $dbEntity = $this->find($entity->getId());
        if ($dbEntity) {
            $this->delete($dbEntity);
        }
    }

    /**
     * Perform operations on in batch on the whole table
     * @param callable $cb Each entity is passed to callback
     * @param int $batchSize
     * @throws MappingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function doBatch(callable $cb, $batchSize = 50)
    {
        $em = $this->getEntityManager();
        $query = $this->createQueryBuilder('a')->getQuery();
        $iterableResult = $query->iterate();
        foreach ($iterableResult as $index => [$entity]) {
            $cb($entity);
            if (($index % $batchSize) === 0) {
                $em->flush();
                $em->clear();
            }
        }
        $em->flush();
        $em->clear();
    }
}