<?php

namespace Odan\Test;

use Odan\Slim\Csrf\CsrfMiddleware;

/**
 * Tests
 *
 * @coversDefaultClass \Odan\Slim\Csrf\CsrfMiddleware
 */
class CsrfMiddlewareTest extends AbstractTest
{

    /**
     * Test.
     *
     * @return void
     */
    public function testInstance()
    {
        $middleware = $this->newInstance('session');
        $this->assertInstanceOf(CsrfMiddleware::class, $middleware);
    }

    /**
     * Test.
     *
     * @return void
     * @expectedException \RuntimeException
     */
    public function testInstanceError()
    {
        $this->newInstance('');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testInjectFormHiddenFieldToResponse()
    {
        $middleware = $this->newInstance('session');

        // Single form
        $actual = $middleware->injectFormHiddenFieldToResponse('<form></form>', 'token');
        $this->assertSame('<form><input type="hidden" name="__token" value="token"></form>', $actual);

        // Multiple forms
        $actual = $middleware->injectFormHiddenFieldToResponse('<form></form>' . "\n" . '<form></form>', 'token');
        $this->assertSame('<form><input type="hidden" name="__token" value="token"></form>' . "\n" .
            '<form><input type="hidden" name="__token" value="token"></form>', $actual);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testInjectFormHiddenFieldToResponseWithAttributes()
    {
        $middleware = $this->newInstance('session');
        $actual = $middleware->injectFormHiddenFieldToResponse('<form id="contact" method="post"></form>', 'token');
        $this->assertSame('<form id="contact" method="post"><input type="hidden" name="__token" value="token"></form>',
            $actual);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testInjectFormHiddenFieldToResponseWithGetAttribute()
    {
        $middleware = $this->newInstance('session');
        $actual = $middleware->injectFormHiddenFieldToResponse('<form id="contact" method="get"></form>', 'token');
        $this->assertSame('<form id="contact" method="get"><input type="hidden" name="__token" value="token"></form>',
            $actual);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testInjectFormHiddenFieldToResponseWithGetDisabledAttribute()
    {
        $middleware = $this->newInstance('session');
        $middleware->protectForms(true, false);
        $actual = $middleware->injectFormHiddenFieldToResponse('<form id="contact" method="GET"></form>', 'token');
        $this->assertSame('<form id="contact" method="GET"></form>', $actual);

        $actual = $middleware->injectFormHiddenFieldToResponse('<form id="contact" method="get"></form>', 'token');
        $this->assertSame('<form id="contact" method="get"></form>', $actual);

        $actual = $middleware->injectFormHiddenFieldToResponse('<form id="contact"  method="get"></form>', 'token');
        $this->assertSame('<form id="contact"  method="get"></form>', $actual);

        $actual = $middleware->injectFormHiddenFieldToResponse('<form></form>', 'token');
        $this->assertSame('<form><input type="hidden" name="__token" value="token"></form>', $actual);

        $actual = $middleware->injectFormHiddenFieldToResponse('<form id="contact" method="post"></form>', 'token');
        $this->assertSame('<form id="contact" method="post"><input type="hidden" name="__token" value="token"></form>',
            $actual);

        // Multiple GET forms
        $actual = $middleware->injectFormHiddenFieldToResponse('<form method="get"></form><form method="get"></form>', 'token');
        $this->assertSame('<form method="get"></form><form method="get"></form>', $actual);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testInjectFormHiddenFieldToResponseNoChanges()
    {
        $middleware = $this->newInstance('session');
        $actual = $middleware->injectFormHiddenFieldToResponse('<div></div>', 'token');
        $this->assertSame('<div></div>', $actual);
    }
}
