<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Functional\Model;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Mrself\Container\Container;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\ExtendedDoctrine\AbstractModel;
use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ModelTestCase extends TestCase
{
    public function setUp()
    {
        ContainerRegistry::reset();
        $container = Container::make([
            'services' => [
                ValidatorInterface::class => Validation::createValidator(),
                EventDispatcherInterface::class => new EventDispatcher(),
                Slugify::class => new Slugify(),
                EntityManager::class => $this->createMock(EntityManager::class)
            ]
        ]);
        ContainerRegistry::add('Mrself\\ExtendedDoctrine', $container);
    }

    protected function makeEntity(): EntityInterface
    {
        return new class implements EntityInterface {
            use EntityTrait;
        };
    }
}