<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
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

    public function getResult(QueryBuilder $qb): array
    {
        return $qb->getQuery()->getResult();
    }
}