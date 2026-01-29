<?php

declare(strict_types=1);

namespace tests\unit\helpers;

use Codeception\Test\Unit;
use cgsmith\user\helpers\Password;

class PasswordTest extends Unit
{
    public function testHashCreatesValidHash(): void
    {
        $password = 'testPassword123!';
        $hash = Password::hash($password);

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertStringStartsWith('$2y$', $hash);
    }

    public function testHashWithCustomCost(): void
    {
        $password = 'testPassword123!';
        $hash = Password::hash($password, 10);

        $this->assertNotEmpty($hash);
        $this->assertTrue(Password::validate($password, $hash));
    }

    public function testValidateReturnsTrueForCorrectPassword(): void
    {
        $password = 'testPassword123!';
        $hash = Password::hash($password);

        $this->assertTrue(Password::validate($password, $hash));
    }

    public function testValidateReturnsFalseForIncorrectPassword(): void
    {
        $password = 'testPassword123!';
        $wrongPassword = 'wrongPassword456!';
        $hash = Password::hash($password);

        $this->assertFalse(Password::validate($wrongPassword, $hash));
    }

    public function testGenerateCreatesPasswordOfSpecifiedLength(): void
    {
        $length = 16;
        $password = Password::generate($length);

        $this->assertEquals($length, strlen($password));
    }

    public function testGenerateDefaultLength(): void
    {
        $password = Password::generate();

        $this->assertEquals(12, strlen($password));
    }

    public function testGenerateCreatesUniquePasswords(): void
    {
        $passwords = [];
        for ($i = 0; $i < 10; $i++) {
            $passwords[] = Password::generate();
        }

        $uniquePasswords = array_unique($passwords);
        $this->assertCount(10, $uniquePasswords);
    }

    public function testCheckStrengthWeakPassword(): void
    {
        $result = Password::checkStrength('abc');

        $this->assertEquals(0, $result['score']);
        $this->assertNotEmpty($result['feedback']);
    }

    public function testCheckStrengthMediumPassword(): void
    {
        $result = Password::checkStrength('Password1');

        $this->assertGreaterThan(1, $result['score']);
        $this->assertLessThanOrEqual(4, $result['score']);
    }

    public function testCheckStrengthStrongPassword(): void
    {
        $result = Password::checkStrength('MyStr0ng!Passw0rd');

        $this->assertEquals(4, $result['score']);
        $this->assertEmpty($result['feedback']);
    }

    public function testCheckStrengthFeedbackForShortPassword(): void
    {
        $result = Password::checkStrength('short');

        $this->assertContains('Password should be at least 8 characters.', $result['feedback']);
    }

    public function testCheckStrengthFeedbackForNoUppercase(): void
    {
        $result = Password::checkStrength('lowercase123!');

        $this->assertContains('Add uppercase letters.', $result['feedback']);
    }

    public function testCheckStrengthFeedbackForNoLowercase(): void
    {
        $result = Password::checkStrength('UPPERCASE123!');

        $this->assertContains('Add lowercase letters.', $result['feedback']);
    }

    public function testCheckStrengthFeedbackForNoNumbers(): void
    {
        $result = Password::checkStrength('NoNumbersHere!');

        $this->assertContains('Add numbers.', $result['feedback']);
    }

    public function testCheckStrengthFeedbackForNoSpecialChars(): void
    {
        $result = Password::checkStrength('NoSpecial123');

        $this->assertContains('Add special characters.', $result['feedback']);
    }

    public function testCheckStrengthMaxScoreIsFour(): void
    {
        $result = Password::checkStrength('ThisIsAnExtremelyLongAndSecurePassword123!@#');

        $this->assertEquals(4, $result['score']);
    }
}
