<?php
// Routes

$app->get('/', 'App\Action\HomeAction:dispatch')
    ->setName('home');

$app->post('/save/', 'App\Action\SaveAction:dispatch')
    ->setName('save');

$app->post('/delete/', 'App\Action\DeleteAction:dispatch')
    ->setName('delete');

$app->get('/auth/', 'App\Action\AuthAction:dispatch')
    ->setName('auth');

$app->post('/logout/', 'App\Action\AuthAction:logout')
    ->setName('logout');

$app->get('/admin/', 'App\Action\AdminAction:dispatch')
    ->setName('admin');
$app->post('/admin/', 'App\Action\AdminAction:dispatch')
    ->setName('admin');

$app->post('/adel/', 'App\Action\AdminDeleteAction:dispatch')
    ->setName('admin_del');

$app->get('/admin/config/', 'App\Action\AdminConfigAction:dispatch')
    ->setName('admin_config');
$app->post('/admin/config/save', 'App\Action\AdminConfigAction:save')
    ->setName('admin_config_save');
