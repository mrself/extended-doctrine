<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Functional\Dev\FixtureCreator;

use Doctrine\ORM\EntityManager;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\ExtendedDoctrine\Dev\FixtureCreator\FixtureDataProviderInterface;
use Mrself\ExtendedDoctrine\Dev\FixtureCreator\FixtureFactory;
use Mrself\ExtendedDoctrine\DoctrineProvider;
use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use PHPUnit\Framework\TestCase;

class FixtureFactoryTest extends TestCase
{
    public function testOk()
    {
        $instance = new FixtureFactory();
        $instance->addProvider(FixtureProvider::class);
        $instance->init();
        /** @var Fixture $fixture */
        $fixture = $instance->create(Fixture::class, []);
        $this->assertEquals(1, $fixture->getA());
    }

    public function testItWorksViaSetProviders()
    {
        $instance = new FixtureFactory();
        $instance->setProviders([FixtureProvider::class]);
        $instance->init();
        /** @var Fixture $fixture */
        $fixture = $instance->create(Fixture::class, []);
        $this->assertEquals(1, $fixture->getA());
    }

    public function testWithPassedSource()
    {
        $instance = new FixtureFactory();
        $instance->setProviders([FixtureProvider::class]);
        $instance->init();
        /** @var Fixture $fixture */
        $fixture = $instance->create(Fixture::class, ['a' => 2]);
        $this->assertEquals(2, $fixture->getA());
    }

    protected function setUp()
    {
        parent::setUp();
        ContainerRegistry::reset();
        DoctrineProvider::make()->register();
        $em = $this->createMock(EntityManager::class);
        ContainerRegistry::get('Mrself\\ExtendedDoctrine')
            ->set(EntityManager::class, $em);
    }
}


class Fixture implements EntityInterface
{
    use EntityTrait;

    private $a;

    public function setA($value)
    {
        $this->a = $value;
    }

    public function getA()
    {
        return $this->a;
    }
}

class FixtureProvider implements FixtureDataProviderInterface
{
    public function getDefaults()
    {
        return [
            'a' => 1
        ];
    }

    public static function getClass()
    {
        return Fixture::class;
    }
}