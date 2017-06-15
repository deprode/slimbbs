<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use RKA\Session;
use Slim\Flash\Messages;
use App\Classes\Log;
use App\Classes\Password;

final class AdminAction
{
    private $view;
    private $logger;
    private $session;
    private $flash;
    private $log;
    private $password;

    public function __construct(Twig $view, LoggerInterface $logger, Session $session, Messages $flash, Log $log, Password $password)
    {
        $this->view     = $view;
        $this->logger   = $logger;
        $this->session  = $session;
        $this->flash    = $flash;
        $this->log      = $log;
        $this->password = $password;
    }

    public function dispatch(Request $request, Response $response)
    {
        // 認証されたとき
        if (!$this->session->get('auth')) {
            // CSRF用トークンのチェック
            if ($request->getAttribute('csrf_status') === false) {
                return $response->withRedirect('/');
            }

            $inputs = $request->getParsedBody();
            $admin_id = $inputs['id'];
            $password = $inputs['password'];

            // IDとパスワードのチェック
            if (!$this->password->checkAdminPassword($admin_id, $password)) {
                $this->flash->addMessage('errorMessage', 'IDかパスワードが間違っています。');
                return $response->withRedirect('/auth/');
            }

            $this->session->regenerate();
            // 認証情報の設定
            $this->session->set('userid', $admin_id);
            $this->session->set('auth', true);

            $this->logger->info("Admin $admin_id logged in");
        }

        return $this->renderAdmin($request, $response);
    }

    // 管理画面を描画
    private function renderAdmin(Request $request, Response $response)
    {
        $csrf_name = $request->getAttribute('csrf_name');
        $csrf_value = $request->getAttribute('csrf_value');

        $error = $this->flash->getMessage('errorMessage');
        $message = $this->flash->getMessage('resultMessage');

        $data = $this->log->readData();

        $this->view->render(
            $response,
            'admin.twig',
            [
                'csrf_name'  => $csrf_name,
                'csrf_value' => $csrf_value,
                'error'      => $error[0],
                'message'    => $message[0],
                'data'       => $data
            ]
        );
        return $response;
    }
}
