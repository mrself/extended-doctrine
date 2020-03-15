<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Unit\Entity\EntityTrait;

use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use PHPUnit\Framework\TestCase;

class FromArrayTest extends TestCase
{
    public function testItSetsPropertiesFromArray()
    {
        $entity = new class {
            use EntityTrait;

            public $value;

            public function __construct()
            {
                $this->entityConstruct();
            }

            public function setField(string $value)
            {
                $this->value = $value;
            }
        };
        $entity->fromArray([
            'field' => 'value'
        ]);
        $this->assertEquals('value', $entity->value);
    }

    public function testItAddPropertyFromArray()
    {
        $entity = new class {
            use EntityTrait;

            public $value;

            public function __construct()
            {
                $this->entityConstruct();
            }

            public function addField(string $value)
            {
                $this->value = $value;
            }
        };
        $entity->fromArray([
            'field' => 'value'
        ]);
        $this->assertEquals('value', $entity->value);
    }
}