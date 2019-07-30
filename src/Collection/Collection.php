<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Collection;

use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use Mrself\ExtendedDoctrine\Repository\AbstractRepository;
use Mrself\Options\Annotation\Option;
use Mrself\Options\WithOptionsTrait;

class Collection
{
    use WithOptionsTrait;

    /**
     * @Option(related=true)
     * @var AbstractRepository
     */
    protected $repository;

    /**
     * @var EntityInterface[]|EntityTrait
     */
    protected $entities;

    public function fromIds(array $ids)
    {
        $this->from($this->repository->findBy(['id' => $ids]));
    }

    public function from(array $entities)
    {
        $this->entities = $entities;
    }

    public function getIds()
    {
        return array_map(function (EntityInterface $entity) {
            return $entity->getId();
        }, $this->entities);
    }

    public function toArray(): array
    {
        return $this->entities;
    }

    /**
     * @param int|string $id Entity id
     * @return EntityInterface
     * @throws NotFoundException
     */
    public function get($id)
    {
        $id = (int) $id;
        $filtered = array_filter($this->entities, function (EntityInterface $entity) use ($id) {
            return $entity->getId() === $id;
        });
        if (!count($filtered)) {
            throw new NotFoundException($id);
        }

        return reset($filtered);
    }

    public function find($id)
    {
        try {
            return $this->get($id);
        } catch (NotFoundException $e) {
            return null;
        }
    }
}