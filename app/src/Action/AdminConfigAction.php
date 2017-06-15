<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator;
use RKA\Session;
use Slim\Flash\Messages;
use App\Classes\Config;

final class AdminConfigAction
{
    private $view;
    private $logger;
    private $flash;
    private $validate;
    private $config;

    public function __construct(Twig $view, LoggerInterface $logger, Validator $validate, Session $session, Messages $flash, Config $config)
    {
        $this->view    = $view;
        $this->logger  = $logger;
        $this->validate = $validate;
        $this->session  = $session;
        $this->flash   = $flash;
        $this->config  = $config;
    }

    public function dispatch(Request $request, Response $response)
    {
        $this->logger->info("Config page action dispatched");

        $response = $response->withHeader('X-Frame-Options', 'SAMEORIGIN');

        $csrf_name = $request->getAttribute('csrf_name');
        $csrf_value = $request->getAttribute('csrf_value');

        $configs = $this->config->getConfigs();

        $message = $this->flash->getMessage('resultMessage');
        $error = $this->flash->getMessage('errorMessage');
        $errors = $this->session->get('errors');

        $this->view->render(
            $response,
            'config.twig',
            [
                'csrf_name' => $csrf_name,
                'csrf_value' => $csrf_value,
                'config' => $configs,
                'error' => $error[0],
                'errors' => $errors,
                'message' => $message[0]
            ]
        );

        return $response;
    }

    public function save(Request $request, Response $response)
    {
        // CSRFチェック
        if ($request->getAttribute('csrf_status') === false) {
            $response = $response->withStatus(403);
            $this->view->render($response, 'csrf.twig');
            return $response;
        }

        // Validation
        if($request->getAttribute('has_errors')){
            $errors = $request->getAttribute('errors');
            $this->session->set('errors', $errors);
            $this->flash->addMessage('errorMessage', '入力に不適切な箇所があったため、書き込みを中断しました');
            return $response->withRedirect('/admin/config/');
        }

        $input = $request->getParsedBody();

        $config = $this->config->format($input);
        $this->config->setConfigs($config);
        $this->config->save();

        $this->flash->addMessage('errorMessage', '設定を更新しました');

        return $response->withRedirect('/admin/config/');
    }
}
