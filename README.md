# Slim 3 CSRF protection middleware

[![Latest Version on Packagist](https://img.shields.io/github/release/odan/slim-csrf.svg)](https://github.com/odan/slim-csrf/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Build Status](https://travis-ci.org/odan/slim-csrf.svg?branch=master)](https://travis-ci.org/odan/slim-csrf)
[![Code Coverage](https://scrutinizer-ci.com/g/odan/slim-csrf/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/odan/slim-csrf/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/odan/slim-csrf/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/odan/slim-csrf/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/odan/slim-csrf.svg)](https://packagist.org/packages/odan/slim-csrf)


## Installation

```
composer install odan/slim-csrf
```

## Integration

### Register the middleware

In your `config/container.php` or wherever you add your service factories:

```php
$container[\Odan\Slim\Csrf\CsrfMiddleware::class] = function (Container $container) {
    // get the current session id
    $sessionId = session_id();
    return new \Odan\Slim\Csrf\CsrfMiddleware($sessionId);
};
```

Add the middleware in `config/middleware.php`.

```php
// Csrf protection middleware
$app->add(function (Request $request, Response $response, $next) {
    /* @var \Slim\Container $this */
    $csrf = $this->get(\Odan\Slim\Csrf\CsrfMiddleware::class);
    return $csrf->__invoke($request, $response, $next);
});
```

### Using the Aura.Session token

If you are already using the [Aura.Session](https://github.com/auraphp/Aura.Session) library you can use their Session-ID and CSRF token.

In your `config/container.php` or wherever you add your service factories:

```php
$container[\Odan\Slim\Csrf\CsrfMiddleware::class] = function (Container $container) {
    $session = $container->get(\Aura\Session\Session::class);
    $token = $session->getCsrfToken()->getValue();
    $sessionId = $session->getId();
    $csrf = new \App\Middleware\CsrfMiddleware($sessionId);

    // Use the token from the aura session object
    $csrf->setToken($token);
    
    return $csrf;
};
```

Add the middleware in `config/middleware.php`.

```php
// Csrf protection middleware
$app->add(function (Request $request, Response $response, $next) {
    /* @var \Slim\Container $this */
    $csrf = $this->get(\Odan\Slim\Csrf\CsrfMiddleware::class);
    return $csrf->__invoke($request, $response, $next);
});
```

## Options

For security reasons all security related settings are enabled by default.

```php
// Set a secret password to increase the security of the token
$csrf->setSalt('secret');

// Set the name of hidden input field
$csrf->setTokenName('__token');

// Enable jQuery ajax protection against CSRF attacks
$csrf->protectJqueryAjax(true);

// Enable form protection against CSRF attacks
$csrf->protectForms(true);
```

## Rendering the CSRF field in Twig

Sometimes you want a variable to be accessible to all the templates you use. 
This is possible inside your `config/container.php` file:

```php
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('path/to/templates', [
        'cache' => 'path/to/cache'
    ]);
    
    // Add a global twig variable
    $csrfToken = $c->get(\Odan\Slim\Csrf\CsrfMiddleware::class)->getToken();
    $view->getEnvironment()->addGlobal('csrf_token', $csrfToken);
    
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};
```

Now, the variable `csrf_token` is available in all Twig templates:

```twig
<!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <meta name="csrf_token" content="{{ csrf_token }}">
    </head>
    <body>
    </body>
</html>
```