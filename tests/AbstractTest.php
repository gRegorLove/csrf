<?php

namespace Odan\Csrf\Test;

use Nyholm\Psr7\Factory\Psr17Factory;
use Odan\Csrf\CsrfMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * BaseTest.
 */
abstract class AbstractTest extends TestCase
{
    /**
     * Factory.
     *
     * @param mixed $sessionId
     *
     * @return CsrfMiddleware
     */
    protected function newInstance($sessionId = 'sessionid'): CsrfMiddleware
    {
        return new CsrfMiddleware(new Psr17Factory(), $sessionId);
    }

    /**
     * Factory.
     *
     * @return ServerRequestInterface
     */
    protected function createRequest(): ServerRequestInterface
    {
        return (new Psr17Factory())->createServerRequest('GET', '/');
    }

    /**
     * Factory.
     *
     * @return ResponseInterface
     */
    protected function createResponse(): ResponseInterface
    {
        return (new Psr17Factory())->createResponse();
    }

    /**
     * Factory.
     *
     * @param ResponseInterface $response
     *
     * @return RequestHandlerInterface
     */
    protected function createRequestHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new class($response) implements RequestHandlerInterface {
            private $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }
}
