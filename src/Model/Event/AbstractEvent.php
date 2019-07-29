<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Model\Event;

use Mrself\ExtendedDoctrine\AbstractModel;
use Mrself\Options\Annotation\Option;
use Mrself\Options\WithOptionsTrait;

abstract class AbstractEvent
{
    use WithOptionsTrait;

    /**
     * @Option()
     * @var AbstractModel
     */
    protected $model;

    /**
     * @return AbstractModel
     */
    public function getModel()
    {
        return $this->model;
    }
}