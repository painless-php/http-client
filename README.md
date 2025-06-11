# http-client

Http messaging related functionality, implementing psr-18.

## Installation

`composer require painless-php/http-client`

## Public API

#### Middleware

- **RequestMiddleware** - Manipulate request before it is sent
- **ResponseMiddleware** - Manipulate response before it is returned
- **ClientMiddleware** - Take a request and produce a response

#### Exceptions

- MessageException
    - ClientException
    - CommunicationException
        - RequestException
        - NetworkException
