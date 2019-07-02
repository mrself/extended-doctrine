<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Entity\AssociationSetter;

use Doctrine\ORM\EntityManager;
use ICanBoogie\Inflector;
use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use Mrself\ClassHelper\ClassHelper;
use Doctrine\Common\Collections\Collection;
use Mrself\Options\Annotation\Option;
use Mrself\Options\WithOptionsTrait;

class AssociationSetter
{
    use WithOptionsTrait;

    /**
     * @Option()
     * @var EntityTrait|EntityInterface
     */
    protected $entity;

    /**
     * @Option()
     * @var array
     */
    protected $associations;

    /**
     * Existing entity association collection
     * @var Collection
     */
    protected $collection;

    /**
     * @Option()
     * Name of inverse association
     * @var string
     */
    protected $inverseName;

    /**
     * @Option()
     * @var string
     */
    protected $associationName;

    /**
     * @Option()
     * @var Inflector
     */
    protected $inflector;

    /**
     * @var bool
     */
    protected $isManyToMany;

    /**
     * @Option()
     * @var bool
     */
    protected $removeAssociation = false;

    /**
     * @Option()
     * @var EntityManager
     */
    protected $em;

    /**
     * Runs setter with specific parameters
     * @param EntityInterface $entity
     * @param array $associations
     * @param string $inverseName
     * @param string $associationName
     */
    public static function runWith(EntityInterface $entity, array $associations, string $inverseName, string $associationName)
    {
        $self = static::make([
            'entity' => $entity,
            'associations' => $associations,
            'inverseName' => $inverseName,
            'associationName' => $associationName
        ]);
        $self->run();
    }

    protected function getOptionsSchema()
    {
        return [
            'normalizers' => [
                'inverseName' => function (string $name) {
                    return ucfirst($name);
                }
            ]
        ];
    }

    /**
     * Runs setting
     */
    public function run()
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
                if (method_exists($this->entity, $method)) {
                    $this->entity->$method($item);
                } else {
                    $item->{'get' . $this->inverseName}()
                        ->removeElement($this->entity);
                    $this->removeAssociationItem($item);
                }
                if ($this->removeAssociation) {
                    $this->em->remove($item);
                }
            }
        }
    }

    protected function removeAssociationItem($item)
    {
        if ($this->collection->contains($item)) {
            $this->collection->removeElement($item);
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

        $method = $this->getAddInverseMethod($association);
        if (method_exists($association, $method)) {
            $association->$method($this->entity);
        } else {
            $association->{'get' . $this->inverseName}()
                ->add($this->entity);
        }
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
        return $method;
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