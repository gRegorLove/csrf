<?php

namespace Odan\Test;

use Odan\Slim\Csrf\CsrfMiddleware;
use PHPUnit\Framework\TestCase;

/**
 * BaseTest
 */
abstract class AbstractTest extends TestCase
{

    /**
     * @return CsrfMiddleware
     * @param mixed $sessionId
     */
    public function newInstance($sessionId = 'sessionid')
    {
        return new CsrfMiddleware($sessionId);
    }
}
