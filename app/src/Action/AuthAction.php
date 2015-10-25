<?php
namespace App\Action;

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

    private $log_path;

    public function __construct(Twig $view, LoggerInterface $logger, Session $session, Messages $flash)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->session = $session;
        $this->flash = $flash;
    }

    public function dispatch($request, $response, $args)
    {
        $this->logger->info("Auth");

        return $this->renderAuth($request, $response, $args);
    }

    public function logout($request, $response, $args)
    {
        $this->session->destroy();
        return $response->withRedirect('/');
    }

    public function renderAuth($request, $response, $args)
    {
        $csrf_name = $request->getAttribute('csrf_name');
        $csrf_value = $request->getAttribute('csrf_value');
        $error = $this->flash->getMessage('errorMessage');

        $this->view->render($response,
                            'auth.twig',
                            [
                                'csrf_name' => $csrf_name,
                                'csrf_value' => $csrf_value,
                                'error' => $error[0]
                            ]
                            );
        return $response;
    }
}
