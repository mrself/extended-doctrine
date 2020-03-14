<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Unit\Collection;

use Mrself\ExtendedDoctrine\Collection\Collection;
use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use PHPUnit\Framework\TestCase;

class ToIdsTest extends TestCase
{
    public function testItReturnsArrayOfEntityIds()
    {
        $entity = new class implements EntityInterface {
            public function getId()
            {
                return 1;
            }
        };
        $options = ['entities' => [$entity], '.silent' => true];
        $ids = Collection::make($options)->toIds();
        $this->assertArraySubset($ids, [1]);
    }
}