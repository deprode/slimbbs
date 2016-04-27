<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Fuel\Validation\Validator;
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

    private $cached_errors;

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

    public function dispatch($request, $response, $args)
    {
        $this->logger->info("Save page action dispatched");

        if ($request->getAttribute('csrf_status') === false) {
            $failed = <<<EOT
<!DOCTYPE html>
<html>
<head><title>CSRF test</title></head>
<body>
    <h1>Error</h1>
    <p>An error occurred with your form submission.
       Please start again.</p>
</body>
</html>
EOT;
            return $response->write($failed);
        }
        $input = $request->getParsedBody();

        // * 名前とemailはフォームで入力制限しているため、validationの前に書いてある
        $this->session->set('name', $input['name']);
        $this->session->set('email', $input['email']);

        if (!$this->validation($input)) {
            $mes = $this->getValidationMessage();
            $this->flash->addMessage('errorMessage', $mes);
            return $response->withRedirect('/');
        }

        $input['host'] = gethostbyaddr($request->getAttribute('ip_address'));
        if (!$this->checkConsecutivePost($input)) {
            $this->flash->addMessage('errorMessage', '時間をおいて書き込んでください。');
            return $response->withRedirect('/');
        }

        $data = $this->formatInput($input);

        try {
            $this->log->saveData($data);
            $this->log->createDailyLog();
            $this->log->writeDailyLog($data);
            $this->logger->info("saved log");
        } catch (Exception $e) {
            return $response->write('log file is not found or not readable.');
        }

        $this->flash->addMessage('resultMessage', '書き込みに成功しました');

        return $response->withRedirect('/');
    }

    public function validation($input)
    {
        $ngwords = $this->config->getConfig('ngword');

        $val = $this->validate;
        $val->addCustomRule('notMatchCollectionRule', '\App\Rule\NotMatchCollectionRule');

        $val->addField('name', '名前')
               ->maxLength(50)
               ->setMessage('{label} は50文字までです。')
            ->addField('subject', 'タイトル')
               ->maxLength(50)
               ->setMessage('{label} は50文字までです。')
            ->addField('body', '本文')
               ->required()
               ->setMessage('{label} は必須です。')
               ->maxLength(2000)
               ->setMessage('{label} は2000文字までです。')
               ->notMatchCollectionRule($ngwords)
               ->setMessage('NGワードが含まれています。')
            ->addField('email', 'メールアドレス')
               ->email()
               ->setMessage('{label} 欄にはメールアドレスを書いてください。')
            ->addField('url', 'URL')
               ->url()
               ->setMessage('{label} 欄にはURLを書いてください。')
            ->addField('del_pass', '削除パス')
               ->Regex('/\w/')
               ->setMessage('{label} は英数字とアンダースコアを使ってください。');

        $result = $this->validate->run($input);

        $this->cached_errors = $result->getErrors();

        return $result->isValid();
    }

    public function getValidationMessage()
    {
        $mes = '';
        $errors = $this->cached_errors;
        foreach ($errors as $error) {
            $mes = $mes . '' . $error . PHP_EOL;
        }
        return $mes;
    }

    public function createPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function formatInput($input)
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

    public function checkConsecutivePost($data)
    {
        $log = $this->log->dataReadWithNo(0);
        if ($log === null) {
            return true;
        }
        $time = $this->config->getConfig('consecutive');
        $pre_date = \DateTime::createFromFormat('Y-m-d H:i:s', $log->created);
        $check_date = new \DateTime("$time sec ago");

        if ($log->host === $data['host'] && $pre_date < $check_date) {
            return true;
        }

        return false;
    }
}
