<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Index;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarField;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\DefaultSerializer;

class FkField extends ScalarField implements RelationalFieldInterface
{
    public function __construct(
        string $internalName,
        string $storageName,
        private readonly string $relatedToDefinition,
        private readonly string $relatedToInternalName = 'id',
        array $flags = []
    ) {
        parent::__construct(
            $internalName,
            $storageName,
            'BINARY',
            16,
            [new Index(), ...$flags]
        );
    }

    public function getRelatedToDefinition(): string
    {
        return $this->relatedToDefinition;
    }

    public function getRelatedToInternalName(): string
    {
        return $this->relatedToInternalName;
    }

    public function shouldAutoload(): bool
    {
        return true;
    }

    public function getSerializer(): AbstractFieldSerializer
    {
        return new DefaultSerializer($this);
    }
}
