<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter;

class FilterResult
{
    private array $joins;
    private string $condition;
    private array $parameters = [];

    public function __construct(
        string $condition = '',
        array $parameters = []
    ) {
        $this->joins = [];
        $this->condition = $condition;
        $this->parameters = $parameters;
    }

    /**
     * @return array<string>
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @param string $join
     */
    public function addJoin(string $join): void
    {
        if(in_array($join, $this->joins)) return;

        $this->joins[] = $join;
    }

    /**
     * @param string $condition
     */
    public function setCondition(string $condition): void
    {
        $this->condition = $condition;
    }

    /**
     * @return string
     */
    public function getCondition(): string
    {
        return $this->condition;
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