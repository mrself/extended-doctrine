<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Model;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use Mrself\ExtendedDoctrine\Entity\SluggableTrait;
use Mrself\ExtendedDoctrine\Entity\SyncFromArray;
use Mrself\ExtendedDoctrine\Model\Event\AbstractEvent;
use Mrself\ExtendedDoctrine\Model\Event\UpdatedEvent;
use Mrself\ExtendedDoctrine\Repository\AbstractRepository;
use Mrself\NamespaceHelper\NamespaceHelper;
use Mrself\Options\Annotation\Option;
use Mrself\Options\WithOptionsTrait;
use Mrself\Sync\Sync;
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
     * @Option(related=true)
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

    /**
     * @Option(required=false)
     * @var string
     */
    protected $fromArraySyncClass;

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
     * @throws \Mrself\Container\Registry\NotFoundException
     * @throws \Mrself\Property\EmptyPathException
     * @throws \Mrself\Property\InvalidSourceException
     * @throws \Mrself\Property\InvalidTargetException
     * @throws \Mrself\Property\NonValuePathException
     * @throws \Mrself\Property\NonexistentKeyException
     * @throws \Mrself\Sync\ValidationException
     */
    public function from($data = [])
    {
        if (is_array($data)) {
            $this->fromArray($data);
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
     * @param array|EntityInterface|null $data
     * @param bool $ignoreValidation
     * @return $this
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Mrself\Container\Registry\NotFoundException
     * @throws \Mrself\Property\EmptyPathException
     * @throws \Mrself\Property\InvalidSourceException
     * @throws \Mrself\Property\InvalidTargetException
     * @throws \Mrself\Property\NonValuePathException
     * @throws \Mrself\Property\NonexistentKeyException
     * @throws \Mrself\Sync\ValidationException
     */
    public function save($data = null, bool $ignoreValidation = false)
    {
        $this->from($data);
        $this->beforeSave();
        if (!$ignoreValidation) {
            $this->ensureValid();
        }
        if ($this->entity->getId()) {
            $this->repository->update($this->entity);
            $this->dispatchEvent('updated', UpdatedEvent::make([
                'model' => $this
            ]));
        } else {
            $this->beforeCreate();
            $this->repository->create($this->entity);
            $this->onCreate();
            $this->dispatchEvent('created', UpdatedEvent::make([
                'model' => $this
            ]));
        }
        $this->onSave();
        return $this;
    }

    protected function ensureValid()
    {
        $errors = $this->validate();
        if (count($errors)) {
            throw new InvalidEntityException($this->entity, $errors);
        }
    }

    public function validate(): ConstraintViolationListInterface
    {
        return $this->validator->validate($this->entity);
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
        $this->dispatchEvent('deleted', UpdatedEvent::make([
            'model' => $this
        ]));
    }

    /**
     * @param array $data
     * @param string|null $syncClass
     * @throws \Mrself\Container\Registry\NotFoundException
     * @throws \Mrself\Property\EmptyPathException
     * @throws \Mrself\Property\InvalidSourceException
     * @throws \Mrself\Property\InvalidTargetException
     * @throws \Mrself\Property\NonValuePathException
     * @throws \Mrself\Property\NonexistentKeyException
     * @throws \Mrself\Sync\ValidationException
     */
    public function fromArray(array $data, string $syncClass = null)
    {
        if (!$this->entity) {
            $entityClass = $this->entityClass;
            $this->entity = new $entityClass;
        }

        $syncClass = $this->defineFromArraySyncClass($syncClass);
        /** @var Sync $syncClass */
        $syncClass::make([
            'source' => $data,
            'target' => $this->entity
        ])->sync();
    }

    private function defineFromArraySyncClass(string $syncClass = null)
    {
        if ($syncClass) {
            return $syncClass;
        }

        if ($this->fromArraySyncClass) {
            return $this->fromArraySyncClass;
        }
        return SyncFromArray::class;
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
            $this->entityClass = $this->getRelatedClass('entity');
        }
    }

    public function isEqual($target)
    {
        $targetId = is_array($target) ? $target['id'] : $target->getId();
        return $this->entity->getId() === $targetId;
    }

    protected function dispatchEvent(string $name, AbstractEvent $event)
    {
        $namespace = $this->namespace->clone()
            ->append($name)
            ->toDotted();
        $this->eventDispatcher->dispatch($event, $namespace);
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

    protected function getOptionsSelfName(): string
    {
        return 'model';
    }
}