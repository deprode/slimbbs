<?php
// Application middleware

// CSRFミドルウェアの設定
$guard = new Slim\Csrf\Guard();
$guard->setFailureCallable(function (\Slim\Http\Request $request, \Slim\Http\Response $response, $next) {
    $request = $request->withAttribute("csrf_status", false);
    return $next($request, $response);
});
$app->add($guard);

// IPアドレスを取得するミドルウェアの設定
$app->add(
    new \RKA\Middleware\IpAddress()
);

// デバッグ補助
$settings = $container->get('settings')['debugbar'];
$provider = new Kitchenu\Debugbar\ServiceProvider($settings);
$provider->register($app);