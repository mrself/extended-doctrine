<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Entity;


class EntityUtil
{
    /**
     * @var array
     */
    protected static $camelCache = [];

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
}