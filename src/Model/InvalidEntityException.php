<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Model;

use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidEntityException extends ModelException
{
    /**
     * @var EntityInterface
     */
    protected $entity;

    /**
     * @var ConstraintViolationListInterface
     */
    protected $errors;

    public function __construct(EntityInterface $entity, ConstraintViolationListInterface $errors)
    {
        $this->entity = $entity;
        $this->errors = $errors;

        $entityType = get_class($entity);
        $stringErrors = (string) $errors;

        parent::__construct("Entity $entityType is invalid. Validation errors:\n$stringErrors");
    }

    /**
     * @return EntityInterface
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getErrors(): ConstraintViolationListInterface
    {
        return $this->errors;
    }
}