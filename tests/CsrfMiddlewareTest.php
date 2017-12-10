<?php

namespace Odan\Test;

use Odan\Slim\Csrf\CsrfMiddleware;

/**
 * AssetCacheTest
 *
 * @coversDefaultClass \Odan\Slim\Csrf\CsrfMiddleware
 */
class CsrfMiddlewareTest extends AbstractTest
{

    /**
     * Test create object.
     *
     * @return void
     * @covers ::__construct
     * @covers ::setSessionId
     */
    public function testInstance()
    {
        $middleware = $this->newInstance('session');
        $this->assertInstanceOf(CsrfMiddleware::class, $middleware);
    }

    /**
     * Test create object.
     *
     * @return void
     * @covers ::__construct
     * @covers ::setSessionId
     * @expectedException \RuntimeException
     */
    public function testInstanceError()
    {
        $this->newInstance('');
    }
}
