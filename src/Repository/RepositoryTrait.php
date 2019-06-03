<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Mrself\ExtendedDoctrine\Repository\Exception\InvalidEntitySourceException;
use Mrself\ExtendedDoctrine\Entity\SluggableInterface;
use Mrself\ExtendedDoctrine\Entity\SluggableTrait;

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

    public function findByAppId($id)
    {
        if ($this->isSluggable()) {
            return $this->findOneBy(['slug' => $id]);
        }

        return $this->find($id);
    }

    public function isSluggable()
    {
        $class = $this->getClassName();
        return $class instanceof SluggableInterface;
    }
}