<?php
namespace App\Action;

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

    public function dataSplice($data, $page, $per_page)
    {
        if ($data) {
            $data = array_splice($data, $page * $per_page, $per_page);
        }

        return $data;
    }

    public function dispatch($request, $response, $args)
    {
        $this->logger->info("Home page action dispatched");

        $response = $response->withHeader('X-Frame-Options', 'SAMEORIGIN');

        $csrf_name = $request->getAttribute('csrf_name');
        $csrf_value = $request->getAttribute('csrf_value');
        $name = $request->getParam('name');

        $page = $request->getParam('page');
        $per_page = 10;
        $all_data = $this->log->dataRead();
        $data_count = count($all_data);
        $data = $this->dataSplice($all_data, $page, $per_page);

        $message = $this->flash->getMessage('resultMessage');
        $error = $this->flash->getMessage('errorMessage');

        $name = $this->session->get('name');
        $email = $this->session->get('email');

        $this->view->render(
            $response,
            'home.twig',
            [
                'csrf_name' => $csrf_name,
                'csrf_value' => $csrf_value,
                'data' => $data,
                'message' => $message[0],
                'error' => $error[0],
                'count' => $data_count,
                'current_page' => $page,
                'per_page' => $per_page,
                'name' => $name,
                'email' => $email
            ]
        );
        return $response;
    }
}
