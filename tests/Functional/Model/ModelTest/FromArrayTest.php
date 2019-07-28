<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Functional\Model\ModelTest;

use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use Mrself\ExtendedDoctrine\Tests\Functional\Model\ModelMock;
use Mrself\ExtendedDoctrine\Tests\Functional\Model\ModelTestCase;
use Mrself\Sync\Sync;

class FromArrayTest extends ModelTestCase
{
    public function testItCopiesDataFromArrayToEntity()
    {
        $model = new class extends ModelMock {
            public function getEntityClass()
            {
                return Entity::class;
            }
        };
        $model->init()->fromArray(['prop' => 'value']);
        $this->assertEquals('value', $model->getEntity()->prop);
    }

    public function testItUsesSyncClassIfItIsSpecified()
    {
        $model = new class extends ModelMock {
            public function getEntityClass()
            {
                return Entity::class;
            }
        };
        $model->init()->fromArray(['prop' => 'value'], CustomSync::class);
        $this->assertEquals('value1', $model->getEntity()->prop);
    }

    public function testItUsesOptionsSyncClassIfItIsSpecified()
    {
        $model = new class extends ModelMock {
            public function getEntityClass()
            {
                return Entity::class;
            }
        };
        $model->init(['fromArraySyncClass' => CustomSync::class])
            ->fromArray(['prop' => 'value']);
        $this->assertEquals('value1', $model->getEntity()->prop);
    }
}

class Entity implements EntityInterface {
    use EntityTrait;

    public $prop;

    public function setProp($value)
    {
        $this->prop = $value;
    }
}

class CustomSync extends Sync
{
    protected function formatEach(string &$key, &$value)
    {
        $value = $value . '1';
    }
}