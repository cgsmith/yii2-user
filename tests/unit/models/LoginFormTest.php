<?php

declare(strict_types=1);

namespace tests\unit\models;

use Codeception\Test\Unit;
use cgsmith\user\models\LoginForm;

class LoginFormTest extends Unit
{
    private LoginForm $form;

    protected function _before(): void
    {
        $this->form = new LoginForm();
    }

    public function testLoginAndPasswordAreRequired(): void
    {
        $rules = $this->form->rules();

        $requiredRule = null;
        foreach ($rules as $rule) {
            if ($rule[1] === 'required') {
                $requiredRule = $rule[0];
                break;
            }
        }

        $this->assertNotNull($requiredRule);
        $this->assertContains('login', $requiredRule);
        $this->assertContains('password', $requiredRule);
    }

    public function testRememberMeIsBoolean(): void
    {
        $rules = $this->form->rules();

        $hasBooleanRule = false;
        foreach ($rules as $rule) {
            if ($rule[1] === 'boolean' && in_array('rememberMe', (array) $rule[0])) {
                $hasBooleanRule = true;
                break;
            }
        }

        $this->assertTrue($hasBooleanRule);
    }

    public function testRulesArrayStructure(): void
    {
        $rules = $this->form->rules();

        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);

        foreach ($rules as $rule) {
            $this->assertIsArray($rule);
            $this->assertArrayHasKey(0, $rule);
            $this->assertArrayHasKey(1, $rule);
        }
    }
}
