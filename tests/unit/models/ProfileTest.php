<?php

declare(strict_types=1);

namespace tests\unit\models;

use Codeception\Test\Unit;
use cgsmith\user\models\Profile;

class ProfileTest extends Unit
{
    private Profile $profile;

    protected function _before(): void
    {
        $this->profile = new Profile();
    }

    public function testGravatarUrlGeneratesCorrectHash(): void
    {
        $url = $this->profile->getGravatarUrl('test@example.com');
        $expected = 'https://www.gravatar.com/avatar/' . md5('test@example.com') . '?s=200&d=identicon';
        $this->assertEquals($expected, $url);
    }

    public function testGravatarUrlHandlesCaseAndWhitespace(): void
    {
        $url1 = $this->profile->getGravatarUrl('  Test@Example.COM  ');
        $url2 = $this->profile->getGravatarUrl('test@example.com');
        $this->assertEquals($url1, $url2);
    }

    public function testGravatarUrlRespectsCustomSize(): void
    {
        $url = $this->profile->getGravatarUrl('test@example.com', 80);
        $this->assertStringContainsString('s=80', $url);
    }

    public function testTimezoneListIsNotEmpty(): void
    {
        $list = Profile::getTimezoneList();
        $this->assertNotEmpty($list);
    }

    public function testTimezoneListKeysAreValidIdentifiers(): void
    {
        $list = Profile::getTimezoneList();
        $validIdentifiers = \DateTimeZone::listIdentifiers();

        foreach (array_keys($list) as $key) {
            $this->assertContains($key, $validIdentifiers);
        }
    }

    public function testTimezoneListValuesReplaceUnderscores(): void
    {
        $list = Profile::getTimezoneList();

        $this->assertEquals('America/New York', $list['America/New_York']);
        $this->assertEquals('Europe/London', $list['Europe/London']);
    }
}
