<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Mrself\Container\Container;
use Mrself\Container\ServiceProvider;
use Mrself\ExtendedDoctrine\Entity\EntityUtil;
use Mrself\ExtendedDoctrine\Metadata\Property\TypeDefiner;
use Mrself\Property\PropertyProvider;

class DoctrineProvider extends ServiceProvider
{
    protected function getContainer(): Container
    {
        $container = Container::make([
            'fallbackContainers' => ['App']
        ]);
        $container->set(Inflector::class, InflectorFactory::create()->build());
        $container->setMaker(TypeDefiner::class);
        PropertyProvider::make()->register();
        EntityUtil::register();
        return $container;
    }

    protected function getNamespace(): string
    {
        return 'Mrself\\ExtendedDoctrine';
    }
}