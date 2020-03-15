<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Unit\Entity\EntityUtil;

use Mrself\ExtendedDoctrine\DoctrineProvider;
use Mrself\ExtendedDoctrine\Entity\EntityUtil;
use PHPUnit\Framework\TestCase;

class ToArrayTest extends TestCase
{
    public function testIt()
    {
        $object = (object) ['a' => 1];
        $result = EntityUtil::toArray($object, ['a']);
        $this->assertEquals(['a' => 1], $result);
    }

    public function testIs()
    {
        $object = new class {
            public function isA()
            {
                return true;
            }
        };

        $result = EntityUtil::toArray($object, ['a']);
        $this->assertEquals(['a' => true], $result);
    }

    protected function setUp()
    {
        parent::setUp();
        DoctrineProvider::register();
    }
}