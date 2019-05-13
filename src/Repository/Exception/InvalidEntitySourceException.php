<?php declare(strict_types=1);

namespace Mrself\DoctrineRepository\Exception;

class InvalidEntitySourceException extends RepositoryException
{
    private $source;

    public function __construct($source)
    {
        $this->source = $source;
        $encodedSource = json_encode($source);
        parent::__construct("Invalid source for converting it to entity. The source can be app id or entity itself. Source: $encodedSource");
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source): void
    {
        $this->source = $source;
    }
}