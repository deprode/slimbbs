<?php
// Application middleware

// CSRFミドルウェアの設定
$guard = new Slim\Csrf\Guard();
$guard->setFailureCallable(function ($request, $response, $next) {
    $request = $request->withAttribute("csrf_status", false);
    return $next($request, $response);
});
$app->add($guard);

// IPアドレスを取得するミドルウェアの設定
$app->add(
    new \RKA\Middleware\IpAddress()
);
