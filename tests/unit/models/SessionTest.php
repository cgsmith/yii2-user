<?php

declare(strict_types=1);

namespace tests\unit\models;

use Codeception\Test\Unit;
use cgsmith\user\models\Session;

class SessionTest extends Unit
{
    public function testNullUserAgentReturnsUnknownDevice(): void
    {
        $result = Session::parseDeviceName(null);
        $this->assertEquals('Unknown Device', $result);
    }

    public function testWindowsChrome(): void
    {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $this->assertEquals('Chrome on Windows', Session::parseDeviceName($ua));
    }

    public function testMacOsSafari(): void
    {
        $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';
        $this->assertEquals('Safari on macOS', Session::parseDeviceName($ua));
    }

    public function testLinuxFirefox(): void
    {
        $ua = 'Mozilla/5.0 (X11; Linux x86_64; rv:120.0) Gecko/20100101 Firefox/120.0';
        $this->assertEquals('Firefox on Linux', Session::parseDeviceName($ua));
    }

    public function testAndroidChromeDetectedAsLinux(): void
    {
        // Android UAs contain "linux" which is checked before "android" in parseDeviceName
        $ua = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';
        $this->assertEquals('Chrome on Linux', Session::parseDeviceName($ua));
    }

    public function testIosSafariDetectedAsMacOs(): void
    {
        // iPhone UAs contain "mac os" (in "like Mac OS X") which is checked before "iphone"
        $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
        $this->assertEquals('Safari on macOS', Session::parseDeviceName($ua));
    }

    public function testEdgeTakesPriorityOverChrome(): void
    {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0';
        $this->assertEquals('Edge on Windows', Session::parseDeviceName($ua));
    }

    public function testOperaDetectedAsChrome(): void
    {
        // Opera UAs contain "chrome" which is checked before "opr/" in parseDeviceName
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 OPR/106.0.0.0';
        $this->assertEquals('Chrome on Windows', Session::parseDeviceName($ua));
    }

    public function testUnknownBrowserAndOs(): void
    {
        $ua = 'SomeCustomBot/1.0';
        $this->assertEquals('Unknown Browser on Unknown OS', Session::parseDeviceName($ua));
    }
}
