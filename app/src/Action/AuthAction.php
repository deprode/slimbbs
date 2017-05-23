<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use RKA\Session;
use Slim\Flash\Messages;

final class AuthAction
{
    private $view;
    private $logger;
    private $session;
    private $flash;

    public function __construct(Twig $view, LoggerInterface $logger, Session $session, Messages $flash)
    {
        $this->view    = $view;
        $this->logger  = $logger;
        $this->session = $session;
        $this->flash   = $flash;
    }

    public function dispatch(Request $request, Response $response)
    {
        $this->logger->info("Auth");

        return $this->renderAuth($request, $response);
    }

    // ログアウト処理
    public function logout(Request $request, Response $response)
    {
        $this->session->destroy();
        return $response->withRedirect('/');
    }

    // ログイン画面
    private function renderAuth(Request $request, Response $response)
    {
        $csrf_name = $request->getAttribute('csrf_name');
        $csrf_value = $request->getAttribute('csrf_value');
        $error = $this->flash->getMessage('errorMessage');

        $this->view->render(
            $response,
            'auth.twig',
            [
                'csrf_name'  => $csrf_name,
                'csrf_value' => $csrf_value,
                'error'      => $error[0]
            ]
        );

        return $response;
    }
}
