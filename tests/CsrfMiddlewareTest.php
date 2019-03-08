<?php

namespace Odan\Test;

use Odan\Csrf\CsrfMiddleware;

/**
 * AssetCacheTest.
 *
 * @coversDefaultClass \Odan\Csrf\CsrfMiddleware
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
    public function testInstance(): void
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
     */
    public function testInstanceError(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->newInstance('');
    }
}
