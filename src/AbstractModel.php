<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use Mrself\ExtendedDoctrine\Entity\SluggableTrait;
use Mrself\ExtendedDoctrine\Repository\AbstractRepository;
use Mrself\NamespaceHelper\NamespaceHelper;
use Mrself\Options\Annotation\Option;
use Mrself\Options\WithOptionsTrait;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractModel
{
    use WithOptionsTrait {
        make as parentMake;
    }

    /**
     * @Option()
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @Option(required=false)
     * @var EntityInterface|SluggableTrait|EntityTrait
     */
    protected $entity;

    /**
     * @var AbstractRepository
     */
    protected $repository;

    /**
     * @Option()
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @Option()
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityTrait|EntityInterface
     */
    protected $entityClass;

    /**
     * @Option()
     * @var Slugify
     */
    protected $slugifier;

    /**
     * @var NamespaceHelper
     */
    protected $namespace;

    /**
     * @Option(required=false)
     * @var string
     */
    protected $stringNamespace;

    public function __construct()
    {
        $this->defineEntityClass();
    }

    /**
     * @param array|EntityInterface $options Options or entity
     * @return static
     */
    public static function make($options = [])
    {
        if ($options instanceof EntityInterface) {
            $options = ['entity' => $options];
        }
        return static::parentMake($options);
    }

    /**
     * @param array|EntityInterface $data Array to create entity or entity itself
     * @return static
     * @throws Entity\InvalidArrayNameException
     */
    public function from($data = [])
    {
        if (is_array($data)) {
            $this->arrayToEntity($data);
        } elseif ($this->isEntity($data)) {
            $this->entity = $data;
        }

        return $this;
    }

    public function persist()
    {
        $this->repository->persist($this->entity);
    }

    /**
     * @param null $data
     * @return $this
     * @throws Entity\InvalidArrayNameException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save($data = null)
    {
        $this->from($data);
        $this->beforeSave();
        if ($this->entity->getId()) {
            $this->repository->update($this->entity);
        } else {
            $this->beforeCreate();
            $this->repository->create($this->entity);
            $this->onCreate();
        }
        $this->onSave();
        return $this;
    }

    protected function beforeSave()
    {
    }

    protected function onSave()
    {
    }

    protected function beforeCreate()
    {
    }

    protected function onCreate()
    {
    }

    protected function isEntity($source)
    {
        if (!is_object($source)) {
            return false;
        }

        return $source instanceof EntityInterface;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete()
    {
        $this->repository->delete($this->entity);
    }

    /**
     * @param array $data
     * @throws Entity\InvalidArrayNameException
     */
    protected function arrayToEntity(array $data)
    {
        if ($this->entity) {
            $this->entity->fromArray($data);
        } else {
            $entityClass = $this->entityClass;
            $this->entity = $entityClass::sfromArray($data);
        }
    }

    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    protected function defineEntityClass()
    {
        if (method_exists($this, 'getEntityClass')) {
            $this->entityClass = $this->getEntityClass();
        } else {
            $this->entityClass = preg_replace(
                '/Model/',
                'Entity',
                get_class($this),
                1
            );
            $this->entityClass = str_replace('Model', '', $this->entityClass);
        }
    }

    public function isEqual($target)
    {
        $targetId = is_array($target) ? $target['id'] : $target->getId();
        return $this->entity->getId() === $targetId;
    }

    protected function onOptionsResolve()
    {
        $this->namespace = NamespaceHelper::fromDotted($this->getStringNamespace());
    }

    protected function getStringNamespace(): string
    {
        if ($this->stringNamespace) {
            return $this->stringNamespace;
        }

        // todo: complete implementation
    }
}