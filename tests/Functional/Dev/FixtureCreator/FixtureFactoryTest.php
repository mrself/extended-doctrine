<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Functional\Dev\FixtureCreator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    public function testCreateNestedBySource()
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
            'a' => ['bb' => 0]
        ]);
        $this->assertEquals(3, $fixture->getA()->getB());
        $this->assertEquals(0, $fixture->getA()->getBb());
    }

    public function testCreateNestedCollectionByArraySource()
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
            'collection' => [
                ['b' => 9]
            ]
        ]);
        $this->assertFalse($fixture->getCollection()->isEmpty());
        $this->assertEquals(9, $fixture->getCollection()->first()->getB());
    }

    public function testItIsAllowedToPassReadyEntitiesAsNestedAssociations()
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
            'collection' => [
                new FixtureA()
            ]
        ]);
        $this->assertFalse($fixture->getCollection()->isEmpty());
        $this->assertInstanceOf(FixtureA::class, $fixture->getCollection()->first());
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

    /**
     * @var Collection
     */
    private $collection;

    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }

    public function setA($value)
    {
        $this->a = $value;
    }

    public function getA()
    {
        return $this->a;
    }

    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    public function setCollection(array $collection): void
    {
        $this->setAssociations(
            $collection,
            'a',
            'collection'
        );
    }
}

class FixtureA implements EntityInterface
{
    use EntityTrait;

    /**
     * @var FixtureA
     */
    private $b;

    private $bb;

    private $a;

    public function setB($value)
    {
        $this->b = $value;
    }

    public function setBb($value)
    {
        $this->bb = $value;
    }

    public function getBb()
    {
        return $this->bb;
    }

    public function getB()
    {
        return $this->b;
    }

    public function setA($a)
    {
        $this->a = $a;
    }

    public function getA()
    {
        return $this->a;
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