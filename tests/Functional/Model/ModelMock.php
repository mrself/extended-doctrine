<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Tests\Functional\Model;

use Mrself\ExtendedDoctrine\AbstractModel;

class ModelMock extends AbstractModel {

    public function getOptionsContainerNamespace(): string
    {
        return 'Mrself\\ExtendedDoctrine';
    }

    protected function getStringNamespace(): string
    {
        return 'model';
    }
}