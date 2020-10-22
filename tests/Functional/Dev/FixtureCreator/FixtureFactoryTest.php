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
    /**
     * @var EntityManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $em;

    /**
     * @var FixtureFactory
     */
    private $factory;

    public function testOk()
    {
        $factory = FixtureFactory::make(['providers' => [FixtureProvider::class]]);
        /** @var Fixture $fixture */
        $fixture = $factory->create(Fixture::class, []);
        $this->assertEquals(1, $fixture->getA());
    }

    public function testItWorksViaSetProviders()
    {
        $factory = FixtureFactory::make([
            'providers' => [FixtureProvider::class]
        ]);
        /** @var Fixture $fixture */
        $fixture = $factory->create(Fixture::class, []);
        $this->assertEquals(1, $fixture->getA());
    }

    public function testWithPassedSource()
    {
        $factory = FixtureFactory::make([
            'providers' => [FixtureProvider::class]
        ]);
        /** @var Fixture $fixture */
        $fixture = $factory->create(Fixture::class, ['a' => 2]);
        $this->assertEquals(2, $fixture->getA());
    }

    public function testCreateNestedByEmptyArray()
    {
        $this->em
            ->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn(new class {
                public function getFieldMapping()
                {
                    return ['type' => FixtureA::class];
                }
            });

        $factory = FixtureFactory::make([
            'providers' => [FixtureProvider::class, FixtureAProvider::class]
        ]);

        /** @var Fixture $fixture */
        $fixture = $factory->create(Fixture::class, [
            'a' => []
        ]);
        $this->assertEquals(3, $fixture->getA()->getB());
    }

    public function testCreateWhenThereIsNoProvider()
    {
        $factory = FixtureFactory::make();
        /** @var Fixture $fixture */
        $fixture = $factory->create(Fixture::class, ['a' => 2]);
        $this->assertEquals(2, $fixture->getA());
    }

    public function testWithEmptySource()
    {
        $factory = FixtureFactory::make(['providers' => [FixtureProvider::class]]);
        /** @var Fixture $fixture */
        $fixture = $factory->create(Fixture::class);
        $this->assertEquals(1, $fixture->getA());
    }

    protected function setUp()
    {
        parent::setUp();
        ContainerRegistry::reset();
        DoctrineProvider::make()->register();
        $this->em = $this->createMock(EntityManager::class);
        ContainerRegistry::get('Mrself\\ExtendedDoctrine')
            ->set(EntityManager::class, $this->em);
    }
}


class Fixture implements EntityInterface
{
    use EntityTrait;

    /**
     * @var FixtureA
     */
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

class FixtureA implements EntityInterface
{
    use EntityTrait;

    /**
     * @var FixtureA
     */
    private $b;

    public function setB($value)
    {
        $this->b = $value;
    }

    public function getB()
    {
        return $this->b;
    }
}

class FixtureAProvider implements FixtureDataProviderInterface
{
    public function getDefaults(): array
    {
        return [
            'b' => 3
        ];
    }

    public static function getClass(): string
    {
        return FixtureA::class;
    }
}

class FixtureProvider implements FixtureDataProviderInterface
{
    public function getDefaults(): array
    {
        return [
            'a' => 1
        ];
    }

    public static function getClass(): string
    {
        return Fixture::class;
    }
}