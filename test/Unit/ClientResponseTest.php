<?php

namespace Test\Unit;

use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Redirection;
use PHPUnit\Framework\TestCase;

class ClientResponseTest extends TestCase
{
    private ClientResponse $response;

    public function setUp() : void
    {
        $this->response = new ClientResponse(
            request: new ClientRequest('GET', 'https://google.com'),
            status: 200
        );
    }

    public function testGetEffectiveUriWorksLikeGetUrlWhenThereAreNoRedirections()
    {
        $this->assertEquals('https://google.com', $this->response->getEffectiveUri());
    }

    public function testGetEffectiveUriReturnsLastUrlWhenThereAreRedirections()
    {
        $firstUrl = 'foo.com';
        $secondUrl = 'bar.com';
        $thirdUrl = 'baz.com';

        $response = $this->response->withRedirection(new Redirection($firstUrl, $secondUrl, 302));
        $response = $this->response->withRedirection(new Redirection($secondUrl, $thirdUrl, 302));

        $this->assertEquals($thirdUrl, $response->getEffectiveUri());
    }
}
