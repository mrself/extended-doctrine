<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Entity;

use Mrself\ExtendedDoctrine\Entity\AssociationSetter\AssociationSetter;
use Mrself\Sync\SyncTrait;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

trait EntityTrait {

    use SyncTrait {
        toArray as parentToArray;
    }

	protected $id;

    /**
     * @var array
     */
	protected $serializerIgnoredAttributes = [];

	protected function entityConstruct()
    {
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
        return EntityUtil::fromArray($this, $array);
	}

	public static function sfromArray(array $array): self
    {
        return (new static())->fromArray($array);
    }

    public function toArray(array $keys = null): array
    {
        if ($this->getUseSync()) {
            return $this->parentToArray($keys);
        }

        return EntityUtil::toArray($this, $this->getExportKeys($keys));
    }

    protected function getUseSync(): bool
    {
        return false;
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
            'inflector',
            'useSync'
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