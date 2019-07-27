<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Entity;

interface EntityInterface
{
    /**
     * @return string|int
     */
    public function getId();
}