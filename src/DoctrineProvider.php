<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine;

use ICanBoogie\Inflector;
use Mrself\Container\Container;
use Mrself\Container\Registry\ContainerRegistry;

class DoctrineProvider
{
    public function boot()
    {
        $container = new Container();
        ContainerRegistry::add('Mrself\\ExtendedDoctrine', $container);
        $container->set(Inflector::class, Inflector::get());
    }
}