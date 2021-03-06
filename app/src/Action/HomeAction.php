<?php

namespace App\Action;

use App\Classes\Config;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use RKA\Session;
use Slim\Flash\Messages;
use App\Classes\Log;
use App\Classes\Pagination;

final class HomeAction
{
    private $view;
    private $logger;
    private $flash;
    private $session;
    private $log;
    private $config;
    private $pagination;

    public function __construct(Twig $view, LoggerInterface $logger, Session $session, Messages $flash, Log $log, Config $config, Pagination $pagination)
    {
        $this->view       = $view;
        $this->logger     = $logger;
        $this->flash      = $flash;
        $this->session    = $session;
        $this->log        = $log;
        $this->config     = $config;
        $this->pagination = $pagination;
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
        $per_page = $this->config->get('per_page');
        $page = ($request->getParam('page')) ? $request->getParam('page') : 0;
        $all_data = $this->log->readData();
        $data = $this->log->spliceData($all_data, $page, $per_page);
        $data_count = count($all_data);

        $this->pagination->setting($data_count, $page, $per_page);

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
                'name'         => $name,
                'email'        => $email,
                'pagination'   => $this->pagination
            ]
        );

        return $response;
    }
}
