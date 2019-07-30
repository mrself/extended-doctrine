<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Collection;

class NotFoundException extends CollectionException
{
    /**
     * Absent entity id
     * @var int
     */
    private $id;

    public function __construct($id)
    {
        $this->id = $id;

        parent::__construct('An entity by id ' . $id . ' was not found');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}