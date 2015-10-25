<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use RKA\Session;
use Slim\Flash\Messages;
use App\Classes\Log;

final class AdminAction
{
    private $view;
    private $logger;
    private $session;
    private $flash;
    private $log;

    private $admin_id;
    private $admin_pass;

    public function __construct(Twig $view, LoggerInterface $logger, Session $session, Messages $flash, Log $log)
    {
        $this->view    = $view;
        $this->logger  = $logger;
        $this->session = $session;
        $this->flash   = $flash;
        $this->log     = $log;
    }

    public function setAdminAuth($id, $password)
    {
        $this->admin_id = $id;
        $this->admin_pass = $password;
    }

    public function dispatch($request, $response, $args)
    {
        if (!$this->session->get('auth')) {
            if ($request->getAttribute('csrf_status') === false) {
                return $response->withRedirect('/');
            }
            $inputs = $request->getParsedBody();
            $id = $inputs['id'];
            $password = $inputs['password'];

            if ((string)$id !== $this->admin_id && !password_verify($password, $this->admin_pass)) {
                $this->flash->addMessage('errorMessage', 'IDかパスワードが間違っています。');
                return $response->withRedirect('/auth/');
            }
            $this->session->regenerate();
            $this->session->set('userid', $id);
            $this->session->set('auth', true);
            $this->logger->info("Admin $id logged in");
        }

        return $this->renderAdmin($request, $response, $args);
    }

    public function renderAdmin($request, $response, $args)
    {
        $csrf_name = $request->getAttribute('csrf_name');
        $csrf_value = $request->getAttribute('csrf_value');

        $error = $this->flash->getMessage('errorMessage');

        $data = $this->log->dataRead();

        $this->view->render($response,
                            'admin.twig',
                            [
                                'csrf_name' => $csrf_name,
                                'csrf_value' => $csrf_value,
                                'error' => $error[0],
                                'data' => $data
                            ]
                            );
        return $response;
    }
}
