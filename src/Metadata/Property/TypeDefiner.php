<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Metadata\Property;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\MappingException;
use Mrself\Options\Annotation\Option;
use Mrself\Options\OptionableInterface;
use Mrself\Options\WithOptionsTrait;

class TypeDefiner implements OptionableInterface
{
    use WithOptionsTrait;

    /**
     * @Option()
     * @var EntityManager
     */
    private $em;

    public function define(string $class, string $property): array
    {
        $metadata = $this->em->getClassMetadata($class);

        try{
            $type = $metadata->getFieldMapping($property)['type'];
            $isAssociation = false;
        } catch (MappingException $e) {
            try {
                $type = $metadata->getAssociationMapping($property)['targetEntity'];
                $isAssociation = true;
            } catch (MappingException $e) {
                throw new \RuntimeException('The entity field "' . $property . '" does not exist in the class "' . $class . '".');
            }
        }

        return ['type' => $type, 'isAssociation' => $isAssociation];
    }
}