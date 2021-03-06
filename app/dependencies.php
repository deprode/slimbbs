<?php
// DIC configuration

$container = $app->getContainer();

// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------

// Twig
$container['view'] = function ($c) {
    $settings = $c->get('settings');
    $view = new \Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);

    // Add extensions
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};

// Flash messages
$container['flash'] = function ($c) {
    return new \Slim\Flash\Messages;
};

// Validation
$container['validate'] = function ($c) {
    return new Respect\Validation\Validator();
};

// Seesion
$container['session'] = function ($c) {
    return new \RKA\Session();
};

// Password
$container['password'] = function ($c) {
    $settings = $c->get('settings');
    return new \App\Classes\Password($settings['auth']['id'], $settings['auth']['password']);
};

// File
$container['file'] = function ($c) {
    return new \App\Classes\File();
};

// Posts log
$container['log'] = function ($c) {
    $settings = $c->get('settings');
    $max = $settings['log']['max'] ?? PHP_INT_MAX;
    $log = new \App\Classes\Log($c->get('file'), $c->get('password'), $settings['log']['path'], $settings['log']['past'], $max);
    return $log;
};

// config
$container['config'] = function ($c) {
    $settings = $c->get('settings');
    return new \App\Classes\Config($settings['config']['path']);
};

// pagination
$container['pagination'] = function ($c) {
    return new \App\Classes\Pagination();
};

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new \Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['path'], \Monolog\Logger::DEBUG));
    return $logger;
};

// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------

$container['App\Action\HomeAction'] = function ($c) {
    $action = new App\Action\HomeAction(
        $c->get('view'),
        $c->get('logger'),
        $c->get('session'),
        $c->get('flash'),
        $c->get('log'),
        $c->get('config'),
        $c->get('pagination')
    );
    return $action;
};

$container['App\Action\SaveAction'] = function ($c) {
    $action = new App\Action\SaveAction(
        $c->get('view'),
        $c->get('logger'),
        $c->get('validate'),
        $c->get('session'),
        $c->get('flash'),
        $c->get('log'),
        $c->get('config'),
        $c->get('password')
    );
    return $action;
};

$container['App\Action\DeleteAction'] = function ($c) {
    $action = new App\Action\DeleteAction(
        $c->get('view'),
        $c->get('logger'),
        $c->get('validate'),
        $c->get('session'),
        $c->get('flash'),
        $c->get('log')
    );
    return $action;
};

$container['App\Action\PastAction'] = function ($c) {
    $action = new App\Action\PastAction(
        $c->get('view'),
        $c->get('logger'),
        $c->get('flash'),
        $c->get('log')
    );
    return $action;
};

$container['App\Action\AuthAction'] = function ($c) {
    $action = new App\Action\AuthAction(
        $c->get('view'),
        $c->get('logger'),
        $c->get('session'),
        $c->get('flash')
    );
    return $action;
};

$container['App\Action\AdminAction'] = function ($c) {
    $action = new App\Action\AdminAction(
        $c->get('view'),
        $c->get('logger'),
        $c->get('session'),
        $c->get('flash'),
        $c->get('log'),
        $c->get('password')
    );
    return $action;
};

$container['App\Action\AdminDeleteAction'] = function ($c) {
    $action = new App\Action\AdminDeleteAction(
        $c->get('view'),
        $c->get('logger'),
        $c->get('validate'),
        $c->get('session'),
        $c->get('flash'),
        $c->get('log')
    );
    return $action;
};

$container['App\Action\AdminConfigAction'] = function ($c) {
    $action = new App\Action\AdminConfigAction(
        $c->get('view'),
        $c->get('logger'),
        $c->get('validate'),
        $c->get('session'),
        $c->get('flash'),
        $c->get('config')
    );
    return $action;
};
