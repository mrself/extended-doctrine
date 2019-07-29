<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Functional\Model;

use Mrself\ExtendedDoctrine\Model\AbstractModel;

class ModelMock extends AbstractModel
{

    public function getOptionsContainerNamespace(): string
    {
        return 'Mrself\\ExtendedDoctrine';
    }

    protected function getStringNamespace(): string
    {
        return 'product';
    }

    protected function getOptionsSelfName(): string
    {
        return 'model';
    }

    protected function getClassName()
    {
        return 'App\\Model\\ProductModel';
    }
}