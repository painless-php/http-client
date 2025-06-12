<?php

namespace Test\Unit;

use PainlessPHP\Http\Client\RequestSettings;
use PHPUnit\Framework\TestCase;

class RequestSettingsTest extends TestCase
{
    protected $settings;

    public function setUp() : void
    {
        $this->settings = new RequestSettings();
    }

    public function testSettingNegativeValueForTimeoutThrowsException()
    {
        $this->expectExceptionMessage("timeout can't be negative");
        new RequestSettings(timeout: -1);
    }

    public function testSettingNegativeValueForMaxRedirectionsThrowsException()
    {
        $this->expectExceptionMessage("max_redirections can't be negative");
        new RequestSettings(maxRedirections: -1);
    }
}
