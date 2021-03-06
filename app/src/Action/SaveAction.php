<?php

namespace App\Action;

use App\Classes\Password;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator;
use RKA\Session;
use Slim\Flash\Messages;
use App\Classes\Log;
use App\Classes\Config;

final class SaveAction
{
    private $view;
    private $logger;
    private $validate;
    private $session;
    private $flash;
    private $config;

    private $log;
    private $password;

    public function __construct(
        Twig $view,
        LoggerInterface $logger,
        Validator $validate,
        Session $session,
        Messages $flash,
        Log $log,
        Config $config,
        Password $password
    ) {
        $this->view     = $view;
        $this->logger   = $logger;
        $this->validate = $validate;
        $this->session  = $session;
        $this->flash    = $flash;
        $this->log      = $log;
        $this->config   = $config;
        $this->password = $password;
    }

    public function dispatch(Request $request, Response $response)
    {
        $this->logger->info("Save page action dispatched");

        if ($request->getAttribute('csrf_status') === false) {
            $response = $response->withStatus(403);
            $this->view->render($response, 'csrf.twig');
            return $response;
        }
        $input = $request->getParsedBody();

        // * 名前とemailはフォームで入力制限しているため、validationの前に書いてある
        $this->session->set('name', $input['name']);
        $this->session->set('email', $input['email']);

        // Validation
        if($request->getAttribute('has_errors')){
            $errors = $request->getAttribute('errors');
            $this->session->set('errors', $errors);
            $this->flash->addMessage('errorMessage', '入力に不適切な箇所があったため、書き込みを中断しました');
            return $response->withRedirect('/');
        }

        // IPアドレスの取得
        $input['host'] = gethostbyaddr($request->getAttribute('ip_address'));
        if (!$this->checkConsecutivePost($input['host'])) {
            $this->flash->addMessage('errorMessage', '時間をおいて書き込んでください。');
            return $response->withRedirect('/');
        }

        $data = $this->log->generateLogData($input);

        // ログの保存
        try {
            $this->log->updateData($data, null, $this->log->getLogMax());
            $this->log->createDailyLog();
            $this->log->updateDailyLog($data);
            $this->logger->info("saved log");
        } catch (\Exception $e) {
            return $response->write('log file is not found or not readable.');
        }

        $this->flash->addMessage('resultMessage', '書き込みに成功しました');

        return $response->withRedirect('/');
    }

    // 短時間に連続して書き込んでいるかチェックする
    private function checkConsecutivePost($host)
    {
        $log = $this->log->getPreviousPost();
        if ($log === null) {
            return true;
        }

        $time = $this->config->get('consecutive');
        $pre_date = \DateTime::createFromFormat('Y-m-d H:i:s', $log->created);
        $check_date = new \DateTime("$time sec ago");

        if ($log->host === $host && $pre_date < $check_date) {
            return true;
        }

        return false;
    }
}
