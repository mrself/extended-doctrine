<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Unit\Entity\AssociationSetterTest;

use Doctrine\ORM\EntityManager;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\ExtendedDoctrine\DoctrineProvider;
use Mrself\ExtendedDoctrine\Entity\AssociationSetter\AssociationSetter;
use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssociationSetterTest extends TestCase
{
    /**
     * @var EntityManager|MockObject
     */
    protected $em;

    public function testItAddItemToRelativeCollection()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            var $isCalled = false;
            function setTarget($target) {
                $this->isCalled = true;
            }
            function getTarget()
            {
            }
        };
        $owner->setRelativeItems([$association]);
        $this->assertContains($association, $owner->relativeItems);
    }

    public function testItCallsAddMethodOnInverseSide()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            var $isCalled = false;
            function setTarget($target) {
                $this->isCalled = true;
            }
        };
        $owner->setRelativeItems([$association]);

        $this->assertTrue($association->isCalled);
    }

    public function testItDoesNotAddItemToRelativeCollectionIfCollectionHasIt()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            var $isCalled = false;
            function setTarget($target) {
                $this->isCalled = true;
            }
        };
        $owner->relativeItems = new ArrayCollection([$association]);
        $owner->setRelativeItems([$association]);

        $this->assertContains($association, $owner->relativeItems);
        $this->assertCount(1, $owner->relativeItems);
        $this->assertFalse($association->isCalled);
    }

    public function testItRemovesItemFromCollectionWhichDoesNotExistInParam()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'targets',
                    'relativeItems'
                );
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }

            function removeRelativeItem($item)
            {
                $this->relativeItems->removeElement($item);
            }
        };

        $association = new class {
            var $isCalled = false;
            function setTarget($target) {
                $this->isCalled = true;
            }
            function getTarget()
            {
                return new ArrayCollection();
            }
        };
        $owner->relativeItems = new ArrayCollection([$association]);
        $owner->setRelativeItems([]);

        $this->assertNotContains($association, $owner->relativeItems);
        $this->assertCount(0, $owner->relativeItems);
        $this->assertFalse($association->isCalled);
    }

    public function testItCalesRemoveMethodOnInverseSideIfItExists()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            var $isRemoveCalled = false;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'targets',
                    'relativeItems'
                );
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }

            function removeRelativeItem($relativeItem)
            {
                $this->isRemoveCalled = true;
            }
        };

        $association = new class {
            public function __construct()
            {
                $this->collection = new ArrayCollection();
            }

            function addTarget($target) {
                $this->collection->add($target);
            }
            function removeTarget($target) {
                $this->collection->remove($target);
            }

        };
        $owner->setRelativeItems([$association]);
        $owner->setRelativeItems([]);

        $this->assertTrue($owner->isRemoveCalled);
    }
    /**
     * @expectedException \Mrself\ExtendedDoctrine\Entity\AssociationSetter\InvalidAssociationException
     */
    public function testItThrowsExceptionIfThereIsNoInverseMethod()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'target',
                    'relativeItems'
                );
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {};
        $owner->setRelativeItems([$association]);
    }

    public function testItCallsAddAsInverseMethod()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'targets',
                    'relativeItems'
                );
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            var $isCalled = false;
            function addTarget($target) {
                $this->isCalled = true;
            }
        };
        $owner->setRelativeItems([$association]);

        $this->assertTrue($association->isCalled);
    }

    public function testItUsesFirstAddMethodIfItExists()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::runWith(
                    $this,
                    $values,
                    'targets',
                    'relativeItems'
                );
            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            var $isCalled = false;
            function setTarget($target) {
                $this->isCalled = false;
            }
            function addTarget($target) {
                $this->isCalled = true;
            }
        };
        $owner->setRelativeItems([$association]);

        $this->assertTrue($association->isCalled);
    }

    public function testRemoveAssociation()
    {
        $owner = new class implements EntityInterface {
            use EntityTrait;

            var $relativeItems;

            function __construct()
            {
                $this->relativeItems = new ArrayCollection();
            }

            function setRelativeItems($values)
            {
                AssociationSetter::make([
                    'entity' => $this,
                    'associations' => $values,
                    'inverseName' => 'target',
                    'associationName' => 'relativeItems',
                    'removeAssociation' => true
                ])->run();
            }

            function removeRelativeItem($item)
            {

            }

            function getRelativeItems()
            {
                return $this->relativeItems;
            }
        };

        $association = new class {
            function setTarget($target) {

            }
            function addTarget($target) {

            }
            function removeTarget($target) {

            }
        };
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['remove'])
            ->getMock();
        $em->expects($this->once())
            ->method('remove');
        ContainerRegistry::get('Mrself\\ExtendedDoctrine')
            ->set(EntityManager::class, $em, true);

        $owner->setRelativeItems([$association]);
        $owner->setRelativeItems([]);

    }

    protected function setUp()
    {
        parent::setUp();
        ContainerRegistry::reset();
        (new DoctrineProvider())->boot();
        $em = $this->createMock(EntityManager::class);
        ContainerRegistry::get('Mrself\\ExtendedDoctrine')
            ->set(EntityManager::class, $em);
    }
}