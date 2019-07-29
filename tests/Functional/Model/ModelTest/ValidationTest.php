<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Functional\Model\ModelTest;

use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Entity\EntityTrait;
use Mrself\ExtendedDoctrine\Tests\Functional\Model\ModelMock;
use Mrself\ExtendedDoctrine\Tests\Functional\Model\ModelTestCase;
use Symfony\Component\Validator\Constraints as Assert;

class ValidationTest extends ModelTestCase
{
    public function testValidateValidatesEntity()
    {
        $model = new class extends ModelMock
        {
            public function getEntityClass()
            {
                return ValidationEntity::class;
            }
        };
        $errors = $model->init()->from(['field' => 2])->validate();
        $this->assertCount(1, $errors);
    }

    /**
     * @expectedException \Mrself\ExtendedDoctrine\Model\InvalidEntityException
     */
    public function testEnsureValidThrowsExceptionIfEntityIsInvalid()
    {
        $model = new class extends ModelMock
        {
            public function getEntityClass()
            {
                return ValidationEntity::class;
            }
        };
        $model->init()->save(['field' => 2]);
    }
}

class ValidationEntity implements EntityInterface
{
    use EntityTrait;

    /**
     * @Assert\Range(max="1")
     */
    public $field;
}