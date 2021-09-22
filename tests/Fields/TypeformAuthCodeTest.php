<?php

namespace BristolSU\Service\Tests\Typeform\Fields;

use BristolSU\Service\Tests\Typeform\TestCase;
use BristolSU\Service\Typeform\Fields\TypeformAuthCode;
use FormSchema\Schema\Field;

class TypeformAuthCodeTest extends TestCase
{

    /** @test */
    public function it_extends_the_abstract_field(){
        $field = new TypeformAuthCode();

        $this->assertInstanceOf(Field::class, $field);
    }

    /** @test */
    public function it_has_no_appended_fields(){
        $field = new TypeformAuthCode();
        $this->assertEquals([], $field->getAppendedAttributes());
    }

    /** @test */
    public function it_has_a_type(){
        $field = new TypeformAuthCode();
        $this->assertEquals('p-typeform-auth-code', $field->getType());
    }

}
