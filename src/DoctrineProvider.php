<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine;

use ICanBoogie\Inflector;
use Mrself\Container\Container;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\ExtendedDoctrine\Entity\EntityUtil;
use Mrself\Property\PropertyProvider;

class DoctrineProvider
{
    protected static $isRegistered = false;

    public function register(bool $force = false)
    {
        if (static::$isRegistered && !$force) {
            return;
        }

        $container = Container::make([
            'fallbackContainers' => ['App']
        ]);
        ContainerRegistry::add('Mrself\\ExtendedDoctrine', $container);
        $container->set(Inflector::class, Inflector::get());

        PropertyProvider::make()->register();
        EntityUtil::register();

        static::$isRegistered = true;
    }

    public function forceRegister()
    {
        $this->register(true);
    }
}