<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Entity;


use Mrself\Property\Property;
use Mrself\Util\ArrayUtil;

class EntityUtil
{
    /**
     * @var array
     */
    protected static $camelCache = [];

    /**
     * @var Property
     */
    protected static $property;

    public static function register()
    {
        static::$property = Property::make();
    }

    public static function fromArray($entity, array $array)
    {
        foreach ($array as $name => $value) {
            $camelName = static::camelize($name);
            $method = 'set' . $camelName;

            if (method_exists($entity, $method)) {
                $entity->$method($value);
                continue;
            }

            $method = 'add' . $camelName;
            if (method_exists($entity, $method)) {
                $entity->$method($value);
                continue;
            }

            throw new InvalidArrayNameException($name);

        }
        return $entity;
    }

    public static function camelize(string $name): string
    {
        if (isset(static::$camelCache[$name])) {
            return static::$camelCache[$name];
        }

        $result = str_replace('_', '', ucwords($name, '_'));
        static::$camelCache[$name] = $result;
        return $result;
    }

    /**
     * @param $entity
     * @param array $keys
     * @return string[]
     * @throws \Mrself\Property\EmptyPathException
     * @throws \Mrself\Property\InvalidSourceException
     */
    public static function toArray($entity, array $keys = []): array
    {
        $array = [];
        foreach ($keys as $key) {
            $array[$key] = static::$property->get($entity, $key);
        }
        return $array;
    }
}