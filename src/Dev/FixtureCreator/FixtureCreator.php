<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Dev\FixtureCreator;

use Doctrine\ORM\EntityManager;
use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Metadata\Property\TypeDefiner;
use Mrself\Options\Annotation\Option;
use Mrself\Options\WithOptionsTrait;
use Mrself\Util\ArrayUtil;

class FixtureCreator
{
    use WithOptionsTrait;

    /**
     * @Option()
     * @var array
     */
    private $defaults;

    /**
     * @Option()
     * @var array
     */
    private $source;

    /**
     * @Option()
     * @var string
     */
    private $class;

    /**
     * @Option()
     * @var EntityManager
     */
    private $em;

    /**
     * @Option()
     * @var TypeDefiner
     */
    private $typeDefiner;

    /**
     * @Option(required=false)
     * @var callable
     */
    private $nestedCallback;

    public function create(): EntityInterface
    {
        $entity = $this->makeEntity();
        $this->em->persist($entity);
        $this->em->flush();
        return $entity;
    }

    private function makeEntity(): EntityInterface
    {
        return $this->class::sfromArray($this->makeSourceData());
    }

    private function makeSourceData(): array
    {
        $source = $this->source + $this->defaults;

        return ArrayUtil::map($source, function ($value, string $key) {
            return $this->formatValue($key, $value);
        });
    }

    private function formatValue(string $key, $value)
    {
        if (is_array($value)) {
            $this->processArrayValue($key, $value);
        }

        return $value;
    }

    private function createNested(string $type, $value)
    {
        return ($this->nestedCallback)($type, $value);
    }

    private function processArrayValue(string $key, array $value)
    {
        $propertyMeta = $this->typeDefiner->define($this->class, $key);
        if ($propertyMeta['isAssociation']) {
            return $this->createNestedEntities($propertyMeta['type'], $value);
        }

        return $this->createNested($propertyMeta['type'], $value);
    }

    private function createNestedEntities(string $type, $value)
    {
        return ArrayUtil::map($value, function (array $item) use ($type) {
            return $this->createNested($type, $item);
        });
    }
}