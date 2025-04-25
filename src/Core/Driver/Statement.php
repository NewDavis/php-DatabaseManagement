<?php

namespace NewDavis\DatabaseManagement\Core\Driver;

class Statement
{
    private string $statement;
    private array $parameters = [];

    public function __construct(
        string $statement = '',
        array $parameters = []
    ) {
        $this->statement = $statement;
        $this->parameters = $parameters;
    }

    /**
     * @param string $statement
     */
    public function setStatement(string $statement): void
    {
        $this->statement = $statement;
    }

    /**
     * @return string
     */
    public function getStatement(): string
    {
        return $this->statement;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addParameter(string $key, mixed $value): void
    {
        if($key === '?') {
            $this->parameters[] = $value;
        }else{
            $this->parameters[$key] = $value;
        }
    }

    /**
     * @param string $key
     * @return void
     */
    public function removeParameter(string $key): void
    {
        unset($this->parameters[$key]);
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}