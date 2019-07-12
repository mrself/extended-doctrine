<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Unit\Repository\RepositoryTrait;

use Mrself\ExtendedDoctrine\Entity\SluggableInterface;
use Mrself\ExtendedDoctrine\Repository\RepositoryTrait;
use PHPUnit\Framework\TestCase;

class IsSluggableTest extends TestCase
{
    public function testItReturnsTrueIfIsSluggable()
    {
        $repository = new class {
            use RepositoryTrait;

            public function getClassName()
            {
                return SluggableEntity::class;
            }
        };
        $this->assertTrue($repository->isSluggable());
    }

    public function testItReturnsFalseIfItIsNotSluggable()
    {
        $repository = new class {
            use RepositoryTrait;

            public function getClassName()
            {
                return \stdClass::class;
            }
        };
        $this->assertFalse($repository->isSluggable());
    }
}

class SluggableEntity implements SluggableInterface
{

}