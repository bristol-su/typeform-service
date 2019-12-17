<?php

namespace BristolSU\Service\Typeform\Fields;

use FormSchema\Schema\Field;

class TypeformAuthCode extends Field
{

    protected $type = 'typeformAuthCode';
    
    /**
     * @inheritDoc
     */
    public function getAppendedAttributes(): array
    {
        return [];
    }
}