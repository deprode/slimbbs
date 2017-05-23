<?php

namespace App\Action;

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

    public function __construct(
        Twig $view,
        LoggerInterface $logger,
        Validator $validate,
        Session $session,
        Messages $flash,
        Log $log,
        Config $config
    ) {
        $this->view     = $view;
        $this->logger   = $logger;
        $this->validate = $validate;
        $this->session  = $session;
        $this->flash    = $flash;
        $this->log      = $log;
        $this->config   = $config;
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
        if (!$this->checkConsecutivePost($input)) {
            $this->flash->addMessage('errorMessage', '時間をおいて書き込んでください。');
            return $response->withRedirect('/');
        }

        $data = $this->formatInput($input);

        // ログの保存
        try {
            $this->log->saveData($data);
            $this->log->createDailyLog();
            $this->log->writeDailyLog($data);
            $this->logger->info("saved log");
        } catch (\Exception $e) {
            return $response->write('log file is not found or not readable.');
        }

        $this->flash->addMessage('resultMessage', '書き込みに成功しました');

        return $response->withRedirect('/');
    }

    // パスワード生成
    private function createPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // 保存用に入力を整形する
    private function formatInput($input)
    {
        $now = new \DateTime();
        $pass = mb_strlen($input['del_pass']) > 0 ? $this->createPassword($input['del_pass']) : '';

        return [
            'id'       => bin2hex(openssl_random_pseudo_bytes(6)),
            'name'     => $input['name'],
            'subject'  => $input['subject'],
            'body'     => $input['body'],
            'email'    => $input['email'],
            'url'      => $input['url'],
            'del_pass' => $pass,
            'host'     => $input['host'],
            'created'  => $now->format('Y-m-d H:i:s')
        ];
    }

    // 短時間に連続して書き込んでいるかチェックする
    private function checkConsecutivePost($data)
    {
        $log = $this->log->readDataWithNo(0);
        if ($log === null) {
            return true;
        }

        $time = $this->config->get('consecutive');
        $pre_date = \DateTime::createFromFormat('Y-m-d H:i:s', $log->created);
        $check_date = new \DateTime("$time sec ago");

        if ($log->host === $data['host'] && $pre_date < $check_date) {
            return true;
        }

        return false;
    }
}
