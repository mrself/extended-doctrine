<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Functional\Model\ModelTest;

use Mrself\ExtendedDoctrine\Model\AbstractModel;
use Mrself\ExtendedDoctrine\Tests\Functional\Model\ModelMock;
use Mrself\ExtendedDoctrine\Tests\Functional\Model\ModelTestCase;
use Mrself\Options\Annotation\Option;

class MakeTest extends ModelTestCase
{
    public function testMakeCanTakeEntityAsArgument()
    {
        $model = new ModelMock();
        $entity = $this->makeEntity();
        $model = $model::make($entity);
        $this->assertInstanceOf(AbstractModel::class, $model);
    }

    public function testMakeCanTakeOptionsArray()
    {
        $model = new class extends ModelMock {
            /**
             * @Option()
             * @var string
             */
            public $option1;
        };
        $model = $model::make(['option1' => 'value1']);
        $this->assertEquals('value1', $model->option1);
    }
}