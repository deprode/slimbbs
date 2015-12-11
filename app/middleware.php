<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);
$guard = new Slim\Csrf\Guard();
$guard->setFailureCallable(function ($request, $response, $next) {
    $request = $request->withAttribute("csrf_status", false);
    return $next($request, $response);
});
$app->add($guard);

$app->add(
    new \RKA\Middleware\IpAddress()
);