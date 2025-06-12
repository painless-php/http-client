<?php

namespace PainlessPHP\Http\Client\Exception;

use Exception;

/**
 * An exception that indicates a problem with a given response.
 *
 * NOTE: this should not be thrown by the Client since this
  *is outside of the psr client spec.
 *
 */
class ResponseException extends Exception
{
}
