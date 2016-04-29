<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Fuel\Validation\Validator;
use Slim\Flash\Messages;
use App\Classes\Config;

final class AdminConfigAction
{
    private $view;
    private $logger;
    private $flash;
    private $validate;
    private $config;
    private $cached_errors;

    public function __construct(Twig $view, LoggerInterface $logger, Validator $validate, Messages $flash, Config $config)
    {
        $this->view    = $view;
        $this->logger  = $logger;
        $this->validate = $validate;
        $this->flash   = $flash;
        $this->config  = $config;
    }

    public function dispatch($request, $response, $args)
    {
        $this->logger->info("Config page action dispatched");

        $response = $response->withHeader('X-Frame-Options', 'SAMEORIGIN');

        $csrf_name = $request->getAttribute('csrf_name');
        $csrf_value = $request->getAttribute('csrf_value');
        
        $configs = $this->config->getConfigs();

        $error = $this->flash->getMessage('errorMessage');

        $this->view->render(
            $response,
            'config.twig',
            [
                'csrf_name' => $csrf_name,
                'csrf_value' => $csrf_value,
                'config' => $configs,
                'error' => $error[0]
            ]
        );
        return $response;
    }

    public function save($request, $response, $args)
    {
        $input = $request->getParsedBody();

        if (!$this->validation($input)) {
            $mes = $this->getValidationMessage();
            $this->flash->addMessage('errorMessage', $mes);
            return $response->withRedirect('/admin/config/');
        }

        $config = $this->format($input);
        $this->config->setConfigs($config);
        $this->config->saveConfig();

        return $response->withRedirect('/admin/config/');
    }

    // 入力をiniで保存するために配列に入れる
    public function format($input)
    {
        $ngword = $input["ngword"];
        $consecutive = $input["consecutive"];

        $ngword = array_filter($ngword, function ($var) {
            return !empty($var);
        });

        $data = [
            'ngword' => $ngword,
            'consecutive' => $consecutive
        ];

        return $data;
    }

    public function validation($input)
    {
        $val = $this->validate;
        $val->addCustomRule('isArray', '\App\Rule\IsArrayRule');
        $val->addField('consecutive', '投稿間隔')
                   ->required()
                       ->setMessage('投稿間隔が空欄です')
                   ->number()
                   ->numericMin(0)
            ->addField('ngword', '禁止ワード')
                   ->isArray()
                       ->setMessage('禁止ワードが不正な形式です');

        $result = $this->validate->run($input);

        $this->cached_errors = $result->getErrors();

        return $result->isValid();
    }
    
    public function getValidationMessage()
    {
        $mes = '';
        $errors = $this->cached_errors;
        foreach ($errors as $error) {
            $mes = $mes . '' . $error . PHP_EOL;
        }
        return $mes;
    }
}
