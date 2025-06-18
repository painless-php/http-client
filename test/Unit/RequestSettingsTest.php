<?php

namespace Test\Unit;

use PainlessPHP\Http\Client\RequestSettings;
use PHPUnit\Framework\TestCase;

class RequestSettingsTest extends TestCase
{
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

    public function testExplicitReturnsSettingsThatWereDefinedInConstuctor()
    {
        $settings = new RequestSettings(timeout: 20);
        $this->assertSame(['timeout' => 20], $settings->getExplicit());
    }

    public function testExplicitReturnsExplictlyDefinedSettingsEvenIfTheyAreSameAsDefaults()
    {
        $defaultSettings = new RequestSettings()->toArray();
        $settings = RequestSettings::createFromArray($defaultSettings);
        $this->assertSame($defaultSettings, $settings->getExplicit());
    }
}
