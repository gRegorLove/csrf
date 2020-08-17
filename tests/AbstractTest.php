<?php

namespace Selective\Csrf\Test;

use Nyholm\Psr7\Factory\Psr17Factory;
use Selective\Csrf\CsrfMiddleware;
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
     * @param mixed $salt The salt
     *
     * @return CsrfMiddleware The middleware
     */
    protected function newInstance($salt = 'salt'): CsrfMiddleware
    {
        return new CsrfMiddleware(new Psr17Factory(), $salt);
    }

    /**
     * Factory.
     *
     * @return ServerRequestInterface The request
     */
    protected function createRequest(): ServerRequestInterface
    {
        return (new Psr17Factory())->createServerRequest('GET', '/');
    }

    /**
     * Factory.
     *
     * @return ResponseInterface The response
     */
    protected function createResponse(): ResponseInterface
    {
        return (new Psr17Factory())->createResponse();
    }

    /**
     * Factory.
     *
     * @param ResponseInterface $response The response
     *
     * @return RequestHandlerInterface The request handler
     */
    protected function createRequestHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new class ($response) implements RequestHandlerInterface {
            /**
             * @var ResponseInterface The response
             */
            private $response;

            /**
             * The constructor.
             *
             * @param ResponseInterface $response The response
             */
            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            /**
             * The handler.
             *
             * @param ServerRequestInterface $request The request
             *
             * @return ResponseInterface The response
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }
}
