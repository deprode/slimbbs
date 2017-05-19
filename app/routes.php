<?php
// Routes

require_once __DIR__ . '/src/Classes/Validator.php';

$app->get('/', 'App\Action\HomeAction:dispatch')
    ->setName('home');

$app->post('/save/', 'App\Action\SaveAction:dispatch')
    ->setName('save')
    ->add(new \DavidePastore\Slim\Validation\Validation($saveValidators, $translator));

$app->post('/delete/', 'App\Action\DeleteAction:dispatch')
    ->setName('delete')
    ->add(new \DavidePastore\Slim\Validation\Validation($deleteValidators, $translator));

$app->get('/auth/', 'App\Action\AuthAction:dispatch')
    ->setName('auth');

$app->post('/logout/', 'App\Action\AuthAction:logout')
    ->setName('logout');

$app->get('/admin/', 'App\Action\AdminAction:dispatch')
    ->setName('admin');
$app->post('/admin/', 'App\Action\AdminAction:dispatch')
    ->setName('admin');

$app->post('/adel/', 'App\Action\AdminDeleteAction:dispatch')
    ->setName('admin_del')
    ->add(new \DavidePastore\Slim\Validation\Validation($adminDeleteValidators, $translator));

$app->get('/admin/config/', 'App\Action\AdminConfigAction:dispatch')
    ->setName('admin_config');
$app->post('/admin/config/save', 'App\Action\AdminConfigAction:save')
    ->setName('admin_config_save')
    ->add(new \DavidePastore\Slim\Validation\Validation($adminConfigValidators, $translator));
