<?php

namespace Odan\Test;

use Odan\Csrf\CsrfMiddleware;
use PHPUnit\Framework\TestCase;

/**
 * BaseTest.
 */
abstract class AbstractTest extends TestCase
{
    /**
     * @param mixed $sessionId
     *
     * @return CsrfMiddleware
     */
    public function newInstance($sessionId = 'sessionid')
    {
        return new CsrfMiddleware($sessionId);
    }
}
