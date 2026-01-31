<?php

declare(strict_types=1);

namespace tests\unit\models;

use Codeception\Test\Unit;
use cgsmith\user\models\Token;

class TokenTest extends Unit
{
    public function testTypeConstantsExist(): void
    {
        $this->assertEquals('confirmation', Token::TYPE_CONFIRMATION);
        $this->assertEquals('recovery', Token::TYPE_RECOVERY);
        $this->assertEquals('email_change', Token::TYPE_EMAIL_CHANGE);
    }

    public function testIsExpiredReturnsTrueForPastDate(): void
    {
        $token = new Token();
        $token->expires_at = date('Y-m-d H:i:s', time() - 3600);

        $this->assertTrue($token->getIsExpired());
    }

    public function testIsExpiredReturnsFalseForFutureDate(): void
    {
        $token = new Token();
        $token->expires_at = date('Y-m-d H:i:s', time() + 3600);

        $this->assertFalse($token->getIsExpired());
    }

    public function testValidationRulesContainRequiredFields(): void
    {
        $token = new Token();
        $rules = $token->rules();

        $requiredRule = null;
        foreach ($rules as $rule) {
            if ($rule[1] === 'required') {
                $requiredRule = $rule[0];
                break;
            }
        }

        $this->assertNotNull($requiredRule);
        $this->assertContains('user_id', $requiredRule);
        $this->assertContains('type', $requiredRule);
        $this->assertContains('token', $requiredRule);
        $this->assertContains('expires_at', $requiredRule);
    }

    public function testValidationRulesContainTypeRange(): void
    {
        $token = new Token();
        $rules = $token->rules();

        $inRule = null;
        foreach ($rules as $rule) {
            if ($rule[1] === 'in' && in_array('type', (array) $rule[0])) {
                $inRule = $rule;
                break;
            }
        }

        $this->assertNotNull($inRule);
        $this->assertContains(Token::TYPE_CONFIRMATION, $inRule['range']);
        $this->assertContains(Token::TYPE_RECOVERY, $inRule['range']);
        $this->assertContains(Token::TYPE_EMAIL_CHANGE, $inRule['range']);
    }

    public function testValidationRulesContainTokenMaxLength(): void
    {
        $token = new Token();
        $rules = $token->rules();

        $stringRule = null;
        foreach ($rules as $rule) {
            if ($rule[1] === 'string' && in_array('token', (array) $rule[0]) && isset($rule['max'])) {
                $stringRule = $rule;
                break;
            }
        }

        $this->assertNotNull($stringRule);
        $this->assertEquals(64, $stringRule['max']);
    }
}
