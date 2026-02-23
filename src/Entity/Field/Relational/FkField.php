<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Index;
use NewDavis\DatabaseManagement\Entity\Field\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarField;
use Ramsey\Uuid\Uuid;

class FkField extends ScalarField implements RelatedToInterface
{
    public function __construct(
        string $internalName,
        string $storageName,
        private readonly string $relatedToDefinition,
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
}