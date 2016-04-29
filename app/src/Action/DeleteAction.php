<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Fuel\Validation\Validator;
use Slim\Flash\Messages;
use App\Classes\Log;

final class DeleteAction
{
    private $view;
    private $logger;
    private $validate;
    private $flash;
    private $log;

    private $cached_errors;

    public function __construct(Twig $view, LoggerInterface $logger, Validator $validate, Messages $flash, Log $log)
    {
        $this->view     = $view;
        $this->logger   = $logger;
        $this->validate = $validate;
        $this->flash    = $flash;
        $this->log      = $log;
    }

    public function dispatch($request, $response, $args)
    {
        $this->logger->info("Delete page action dispatched");

        // CSRFチェック
        if ($request->getAttribute('csrf_status') === false) {
            $failed = $this->getCSRFValidMessage();
            return $response->write($failed);
        }

        $input = $request->getParsedBody();

        // Validation
        if (!$this->validation($input)) {
            $mes = $this->getValidationMessage();
            $this->flash->addMessage('errorMessage', $mes);
            return $response->withRedirect('/');
        }

        // 投稿を削除
        $result = false;
        try {
            $result = $this->log->deleteDataForUser($input['id'], $input['del_pass']);
        } catch (Exception $e) {
            return $response->write($e->getMessage());
        }

        if ($result === true) {
            $this->logger->info("deleted success ".$input['id']);
            $this->flash->addMessage('resultMessage', '削除に成功しました');
        } else {
            $this->logger->info("deleted failed ".$input['id']);
            $this->flash->addMessage('resultMessage', '削除に失敗しました');
        }

        return $response->withRedirect('/');
    }

    // Validation
    public function validation($input)
    {
        $this->validate->addField('id', 'ID')
                           ->required()
                               ->setMessage('{label}は必須です。')
                           ->Regex('/[0-9a-zA-Z]/')
                               ->setMessage('{label}が英数字ではありません。')
                       ->addField('del_pass', '削除パス')
                           ->required()
                               ->setMessage('{label}は必須です。')
                           ->Regex('/\w/')
                               ->setMessage('{label}が一致しません。');

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

    public function getCSRFValidMessage()
    {
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
        return $failed;
    }
}
