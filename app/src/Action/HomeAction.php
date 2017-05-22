<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use RKA\Session;
use Slim\Flash\Messages;
use App\Classes\Log;

final class HomeAction
{
    private $view;
    private $logger;
    private $flash;
    private $session;

    private $log;

    public function __construct(Twig $view, LoggerInterface $logger, Session $session, Messages $flash, Log $log)
    {
        $this->view    = $view;
        $this->logger  = $logger;
        $this->flash   = $flash;
        $this->session = $session;
        $this->log     = $log;
    }

    // ホーム画面（トップページ）の表示
    public function dispatch(Request $request, Response $response)
    {
        $this->logger->info("Home page action dispatched");

        $response = $response->withHeader('X-Frame-Options', 'SAMEORIGIN');

        // CSRF
        $csrf_name = $request->getAttribute('csrf_name');
        $csrf_value = $request->getAttribute('csrf_value');

        // 表示するログの用意
        $per_page = $this->log->getDefaultPerPage();
        $page = $request->getParam('page');
        $all_data = $this->log->readData();
        $data = $this->log->spliceData($all_data, $page, $per_page);
        $data_count = count($all_data);

        $message = $this->flash->getMessage('resultMessage');
        $error = $this->flash->getMessage('errorMessage');

        // 書き込んだときの情報を読み込み
        $name = $this->session->get('name');
        $email = $this->session->get('email');
        $errors = $this->session->get('errors');

        $this->view->render(
            $response,
            'home.twig',
            [
                'csrf_name'    => $csrf_name,
                'csrf_value'   => $csrf_value,
                'data'         => $data,
                'message'      => $message[0],
                'error'        => $error[0],
                'errors'       => $errors,
                'count'        => $data_count,
                'current_page' => $page,
                'per_page'     => $per_page,
                'name'         => $name,
                'email'        => $email
            ]
        );

        return $response;
    }
}
