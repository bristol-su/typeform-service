<?php

namespace BristolSU\Service\Typeform\Fields;

use FormSchema\Schema\Field;

class TypeformAuthCode extends Field
{

    /**
     * @inheritDoc
     */
    public function getAppendedAttributes(): array
    {
        return [];
    }

    public function getType(): string
    {
        return 'p-typeform-auth-code';
    }
}
