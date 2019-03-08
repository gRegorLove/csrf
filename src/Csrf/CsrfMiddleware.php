<?php

namespace Odan\Csrf;

use RuntimeException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;

/**
 * CSRF protection middleware.
 */
final class CsrfMiddleware
{
    /**
     * @var string
     */
    private $name = '__token';

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var string
     */
    private $salt = '';

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    private $protectForms = true;

    /**
     * @var bool
     */
    private $protectJqueryAjax = true;

    /**
     * Constructor.
     *
     * @param string|null $sessionId the session id
     */
    public function __construct(string $sessionId = null)
    {
        if (!empty($sessionId)) {
            $this->setSessionId($sessionId);
        }
    }

    /**
     * Set session id.
     *
     * @param string $sessionId the session id
     */
    public function setSessionId(string $sessionId): void
    {
        if (empty($sessionId)) {
            throw new RuntimeException('CSRF middleware failed. SessionId not found!');
        }

        $this->sessionId = $sessionId;
    }

    /**
     * Set salt.
     *
     * @param string $salt the salt
     *
     * @return void
     */
    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }

    /**
     * Set token manually.
     *
     * @param string $token
     *
     * @return void
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Set token name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setTokenName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Enable automatic form protection.
     *
     * @param bool $enabled
     *
     * @return void
     */
    public function protectForms(bool $enabled): void
    {
        $this->protectForms = $enabled;
    }

    /**
     * Enable automatic jQuery ajax requests.
     *
     * @param bool $enabled
     *
     * @return void
     */
    public function protectJqueryAjax(bool $enabled): void
    {
        $this->protectJqueryAjax = $enabled;
    }

    /**
     * Invoke.
     *
     * @param Request $request the request
     * @param Response $response the response
     * @param callable $next next callback
     *
     * @return Response the response
     */
    public function __invoke(Request $request, Response $response, $next): Response
    {
        $tokenValue = $this->getToken();

        $this->validate($request, $tokenValue);

        // Attach Request Attributes
        $request = $request->withAttribute('csrf_token', $tokenValue);

        /* @var Response $response */
        $response = $next($request, $response);

        return $this->injectTokenToResponse($response, $tokenValue);
    }

    /**
     * Get CSRF token.
     *
     * @return string the token
     */
    public function getToken(): string
    {
        if (!empty($this->token)) {
            return $this->token;
        }

        return hash('sha256', $this->sessionId . $this->salt);
    }

    /**
     * Validate token.
     *
     * @param Request $request
     * @param string $tokenValue tokenValue
     *
     * @throws RuntimeException If invalid token is given
     *
     * @return bool Success
     */
    public function validate(Request $request, string $tokenValue): bool
    {
        // Validate POST, PUT, DELETE, PATCH requests
        $method = $request->getMethod();
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return true;
        }

        $postData = $request->getParsedBody();

        $requestCsrfToken = null;
        if (isset($postData[$this->name])) {
            $requestCsrfToken = $postData[$this->name];
        }
        if (!$requestCsrfToken) {
            // Reads the value and adds it to the request as the X-XSRF-TOKEN header.
            $headers = $request->getHeader('X-CSRF-Token');
            $requestCsrfToken = reset($headers);
        }

        if ($requestCsrfToken !== $tokenValue) {
            throw new RuntimeException('CSRF middleware failed. Invalid CSRF token. This looks like a cross-site request forgery.');
        }

        return true;
    }

    /**
     * Inject token to response object.
     *
     * @param Response $response the response
     * @param string $tokenValue token value
     *
     * @throws RuntimeException
     *
     * @return Response the response
     */
    private function injectTokenToResponse(Response $response, string $tokenValue): Response
    {
        // Check if response is html
        $contentTypes = $response->getHeader('content-type');
        $contentType = reset($contentTypes) ?: '';
        if (strpos($contentType, 'text/html') === false) {
            return $response;
        }

        $content = $response->getBody()->__toString();

        if ($this->protectForms) {
            $content = $this->injectFormHiddenFieldToResponse($content, $tokenValue);
        }

        if ($this->protectJqueryAjax) {
            $content = $this->injectJqueryToResponse($content, $tokenValue);
        }

        $stream = fopen('php://memory', 'r+');

        if ($stream === false) {
            throw new RuntimeException('Creating memory stream failed');
        }

        fwrite($stream, $content);
        rewind($stream);

        return $response->withBody(new Stream($stream));
    }

    /**
     * Inject hidden field.
     *
     * @param string $body body
     * @param string $tokenValue token
     *
     * @return string html
     */
    public function injectFormHiddenFieldToResponse(string $body, string $tokenValue): string
    {
        $regex = '/(<form\b[^>]*>)(.*?)(<\/form>)/is';
        $htmlHiddenField = sprintf('$1<input type="hidden" name="%s" value="%s">$2$3', $this->name, $tokenValue);
        $body = preg_replace($regex, $htmlHiddenField, $body);

        return (string)$body;
    }

    /**
     * Inject jquery code.
     *
     * @param string $body body data
     * @param string $tokenValue token value
     *
     * @return string html
     */
    public function injectJqueryToResponse(string $body, string $tokenValue): string
    {
        $regex = '/(.*?)(<\/body>)/is';
        $jQueryCode = sprintf(
            '<script>$.ajaxSetup({beforeSend: function (xhr) { xhr.setRequestHeader("X-CSRF-Token","%s"); }});</script>',
            $tokenValue
        );
        $body = preg_replace($regex, '$1' . $jQueryCode . '$2', $body, -1, $count) ?? '';

        if (!$count) {
            // Inject JS code anyway
            $body .= $jQueryCode;
        }

        return $body;
    }
}
