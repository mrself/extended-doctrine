<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Entity;

use Mrself\ExtendedDoctrine\Entity\AssociationSetter\AssociationSetter;
use ICanBoogie\Inflector;
use Mrself\Sync\SyncTrait;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

trait EntityTrait {

    use SyncTrait;

	protected $id;

    /**
     * @var Inflector
     */
	protected $inflector;

    /**
     * @var array
     */
	protected $serializerIgnoredAttributes = [];

	protected function entityConstruct()
    {
        $this->inflector = Inflector::get();
    }

    public function getId()
    {
        return $this->id;
	}

    /**
     * @param array $array
     * @return static
     * @throws InvalidArrayNameException
     */
    public function fromArray(array $array): self
    {
        foreach ($array as $name => $value) {
            $method = 'set' . $this->inflector
				->camelize($name, Inflector::DOWNCASE_FIRST_LETTER);
            if (!method_exists($this, $method)) {
                throw new InvalidArrayNameException($name);
            }
        }
        return $this;
	}

	public static function sfromArray(array $array): self
    {
        return (new static())->fromArray($array);
    }

    /**
     * Set associations of 'OneToMany' and "ManyToMany' relations
     * @param null|array $associations Array of associations or null
     * @param string $inverseName
     * @param string $associationName
     * @return static
     */
    protected function setAssociations(
        $associations,
        string $inverseName,
        string $associationName
    ) {
        AssociationSetter::runWith(
            $this,
            $associations,
            $inverseName,
            $associationName
        );
        return $this;
    }

    protected function getIgnoredExportKeys()
    {
        return array_merge($this->getSerializerIgnoredAttributes(), [
            'serializerIgnoredAttributes',
            'entityOptions',
            'inflector'
        ]);
    }

    protected function getNormalizer(string $class = null)
    {
        if (is_null($class)) {
            $class = PropertyNormalizer::class;
        }
        $ignoreAttributes = array_merge($this->getSerializerIgnoredAttributes(), [
            'serializerIgnoredAttributes',
            'entityOptions',
            'inflector'
        ]);
        /** @var AbstractNormalizer $normalizer */
        $normalizer = new $class();
        return $normalizer
            ->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            })
            ->setIgnoredAttributes($ignoreAttributes);
    }

    protected function getSerializer($encoder)
    {
        $encoder = $encoder ?: new JsonEncoder();
        $normalizer = $this->getNormalizer();
        return new Serializer([$normalizer], [$encoder]);
    }

    protected function getSerializerIgnoredAttributes(): array
    {
        return $this->serializerIgnoredAttributes;
    }

	/**
	 * Serializes entity
	 * @param EncoderInterface $encoder
	 * @return string
	 */
    public function serialize($encoder = null)
    {
        $encoder = $encoder ?: new JsonEncoder();
        return $this->getSerializer($encoder)->serialize($this, $encoder::FORMAT);
    }

	public function getEntityOptions()
	{
		return [];
    }
}