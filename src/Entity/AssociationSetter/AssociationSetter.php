<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Entity\AssociationSetter;

use ICanBoogie\Inflector;
use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use Mrself\ClassHelper\ClassHelper;
use Doctrine\Common\Collections\Collection;

class AssociationSetter
{

    /**
     * @var EntityTrait|EntityInterface
     */
    protected $entity;

    /**
     * @var array
     */
    protected $associations;

    /**
     * Existing entity association collection
     * @var Collection
     */
    protected $collection;

    /**
     * Name of inverse association
     * @var string
     */
    protected $inverseName;

    /**
     * @var string
     */
    protected $associationName;

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     * @var bool
     */
    protected $isManyToMany;

    /**
     * Runs setter with specific parameters
     * @param EntityInterface $entity
     * @param array $associations
     * @param string $inverseName
     * @param string $associationName
     */
    public static function runWith(EntityInterface $entity, array $associations, string $inverseName, string $associationName)
    {
        $self = new static();
        $self->inflector = Inflector::get();
        $self->entity = $entity;
        $self->associations = $associations;
        $self->inverseName = ucfirst($inverseName);
        $self->associationName = $associationName;
        $self->run();
    }

	/**
	 * Runs setting
	 */
    protected function run()
    {
        $this->defineAssociationType();
        $this->defineCollection();
        array_walk($this->associations, [$this, 'setSingle']);
        $this->removeUnnecessaryAssociations();
    }

    protected function defineAssociationType()
    {
        $pluralized = $this->inflector->pluralize($this->inverseName);
        $this->isManyToMany = $pluralized === $this->inverseName;
    }

    /**
     * Defines associations collection property of entity
     */
    protected function defineCollection()
    {
        $methodGet = 'get' . ucfirst($this->associationName);
        $this->collection = $this->entity->$methodGet();
    }

    /**
     * Removes associations from existing collection which are not in
     * new association values
     */
    protected function removeUnnecessaryAssociations()
    {
        $method = $this->getRemoveMethod();
        foreach ($this->collection as $item) {
            if (!in_array($item, $this->associations, true)) {
                $this->entity->$method($item);
            }
        }
    }

    /**
     * Sets / adds association to existing entity property
     * @param * $association
     * @throws \Mrself\ExtendedDoctrine\Entity\AssociationSetter\InvalidAssociationException
     */
    protected function setSingle($association)
    {
        if ($this->collection->contains($association)) {
            return;
        }

        $association->{$this->getAddInverseMethod($association)}($this->entity);
        if (!$this->collection->contains($association)) {
            $this->collection->add($association);
        }
    }

    /**
     * Returns method to call on association (inverse side) to set current
     * entity
     * @param * $association
     * @return string
     * @throws InvalidAssociationException
     */
    protected function getAddInverseMethod($association): string
    {
        if ($this->isManyToMany) {
            $method  = 'add' . $this->inflector->singularize($this->inverseName);
        } else {
            $method = 'set' . $this->inverseName;
        }
        if (method_exists($association, $method)) {
            return $method;
        }
        throw new InvalidAssociationException($this->associationName, $this->inverseName);
    }

    protected function getRemoveMethod()
    {
        return 'remove' . ucfirst($this->inflector->singularize($this->associationName));
    }

    /**
     * Returns inverse association name
     * @return string
     */
    protected function getInverseName(): string
    {
        if ($this->inverseName) {
            return $this->inverseName;
        }

        return ClassHelper::make($this->entity)->getName();
    }

}