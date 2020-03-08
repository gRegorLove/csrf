<?php

namespace Odan\Csrf\Test;

use RuntimeException;

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
    public function testInstanceError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->newInstance()->setSessionId('');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testProcessGet(): void
    {
        $middleware = $this->newInstance();
        $request = $this->createRequest();
        $response = $this->createResponse();
        $response->getBody()->write('<form></form>');

        $handler = $this->createRequestHandler($response);

        $response = $middleware->process($request, $handler);

        $content = (string)$response->getBody();
        $this->assertSame('<form></form>', $content);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testProcessEmptyPost(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'CSRF middleware failed. Invalid CSRF token. This looks like a cross-site request forgery.'
        );

        $middleware = $this->newInstance();
        $request = $this->createRequest()->withMethod('POST');
        $response = $this->createResponse();
        $response->getBody()->write('<form></form>');

        $handler = $this->createRequestHandler($response);

        $response = $middleware->process($request, $handler);

        $content = (string)$response->getBody();
        $this->assertSame('<form></form>', $content);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testProcessWithValidPost(): void
    {
        $middleware = $this->newInstance();
        $middleware->protectForms(true);
        $middleware->protectJqueryAjax(true);
        $token = $middleware->getToken();

        $request = $this->createRequest()->withMethod('POST')->withHeader('X-CSRF-Token', $token);
        $response = $this->createResponse()->withHeader('Content-Type', 'text/html');

        $response->getBody()->write('<form></form>');
        $response = $middleware->process($request, $this->createRequestHandler($response));

        $content = (string)$response->getBody();

        $expected = sprintf('<form><input type="hidden" name="__token" value="%s"></form>' .
            '<script>$.ajaxSetup({beforeSend: function (xhr) { xhr.setRequestHeader("X-CSRF-Token","%s"); }});' .
            '</script>', $token, $token);

        $this->assertSame($expected, $content);
    }
}
