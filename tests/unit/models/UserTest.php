<?php

declare(strict_types=1);

namespace tests\unit\models;

use Codeception\Test\Unit;
use cgsmith\user\models\User;

class UserTest extends Unit
{
    public function testStatusConstantsExist(): void
    {
        $this->assertEquals('pending', User::STATUS_PENDING);
        $this->assertEquals('active', User::STATUS_ACTIVE);
        $this->assertEquals('blocked', User::STATUS_BLOCKED);
    }

    public function testIsBlockedReturnsTrueWhenStatusBlocked(): void
    {
        $user = new User();
        $user->status = User::STATUS_BLOCKED;

        $this->assertTrue($user->getIsBlocked());
    }

    public function testIsBlockedReturnsTrueWhenBlockedAtIsSet(): void
    {
        $user = new User();
        $user->status = User::STATUS_ACTIVE;
        $user->blocked_at = '2025-01-01 00:00:00';

        $this->assertTrue($user->getIsBlocked());
    }

    public function testIsBlockedReturnsFalseForActiveUser(): void
    {
        $user = new User();
        $user->status = User::STATUS_ACTIVE;
        $user->blocked_at = null;

        $this->assertFalse($user->getIsBlocked());
    }

    public function testIsConfirmedReturnsTrueWhenEmailConfirmed(): void
    {
        $user = new User();
        $user->email_confirmed_at = '2025-01-01 00:00:00';

        $this->assertTrue($user->getIsConfirmed());
    }

    public function testIsConfirmedReturnsFalseWhenEmailNotConfirmed(): void
    {
        $user = new User();
        $user->email_confirmed_at = null;

        $this->assertFalse($user->getIsConfirmed());
    }

    public function testValidationRulesRequireEmail(): void
    {
        $user = new User();
        $rules = $user->rules();

        $hasEmailRequired = false;
        foreach ($rules as $rule) {
            if ($rule[1] === 'required' && in_array('email', (array) $rule[0])) {
                $hasEmailRequired = true;
                break;
            }
        }

        $this->assertTrue($hasEmailRequired);
    }

    public function testValidationRulesContainEmailFormat(): void
    {
        $user = new User();
        $rules = $user->rules();

        $hasEmailValidator = false;
        foreach ($rules as $rule) {
            if ($rule[1] === 'email' && in_array('email', (array) $rule[0])) {
                $hasEmailValidator = true;
                break;
            }
        }

        $this->assertTrue($hasEmailValidator);
    }

    public function testValidationRulesContainUsernamePattern(): void
    {
        $user = new User();
        $rules = $user->rules();

        $hasPattern = false;
        foreach ($rules as $rule) {
            if ($rule[1] === 'match' && in_array('username', (array) $rule[0])) {
                $hasPattern = true;
                break;
            }
        }

        $this->assertTrue($hasPattern);
    }
}
