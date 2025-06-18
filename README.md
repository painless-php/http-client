# http-client

Http messaging related functionality, implementing psr-18.

## Installation

`composer require painless-php/http-client`

## Public API

#### General

- Client
- ClientRequest
- ClientResponse
- ParsedBody
- Redirection
- RequestSettings
- RequestResolution
- RequestResolutionCollection

#### Middleware

- RequestMiddlewareStack
- ResponseMiddlewareStack
- **RequestMiddleware** - Manipulate request before it is sent
    - LogRequest
- **ResponseMiddleware** - Manipulate response before it is returned
    - LogResponse
    - ParseResponseBody
    - ExpectResponseCode

#### Exceptions

- **MessageException** - psr-18 compatible exceptions
    - ClientException
    - CommunicationException
        - RequestException
        - NetworkException

- **ResponseException** - response processing exceptions that can be created by middlewares
    - ResponseParsingException
    - ResponseContentException
    - UnexpectedStatusCodeException

## Quickstart

```php
use PainlessPHP\Http\Client\Client;

$client = new Client(
    settings: [
        'timeout' => 20,
        'maxRedirections' => 5
    ]
);

// Send a single request
$response = $client->request(
    method: 'GET',
    uri: 'https://google.com',
    body: 'foo',
    headers: [
        'content-type' => 'text/html'
    ]
);

// Use psr-17 to create a request
$request = $client->createRequest(
    method: 'GET',
    uri: 'https://google.com',
    body: 'foo',
    headers: [
        'content-type' => 'text/html'
    ]
);

// Send a psr-7 RequestInterface request
$client->sendRequest($request);

// Send multiple psr-7 RequestInterface requests
$resolutions = $client->sendRequests([
     $client->createRequest('GET', 'https://google.com?param=foo'),
     $client->createRequest('GET', 'https://google.com?param=bar'),
]);
```
