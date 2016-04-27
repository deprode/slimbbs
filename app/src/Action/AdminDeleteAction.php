<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Fuel\Validation\Validator;
use Slim\Flash\Messages;
use App\Classes\Log;

final class AdminDeleteAction
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
        $this->logger->info("Deletes page action dispatched");

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

        if (!$this->validation($input)) {
            $mes = $this->getValidationMessage();
            $this->flash->addMessage('errorMessage', $mes);
            return $response->withRedirect('/admin/');
        }


        $inputs = $request->getParsedBody();
        $ids = $inputs['del'];
        if ($request->getAttribute('csrf_status') !== false && count($ids) > 0) {
            $count = 0;
            foreach ($ids as $id) {
                $count += $this->log->deleteDataForAdming($id);
            }
            $message = $this->flash->getMessage($this->getResultMessage($count));
            $this->logger->info("Admin deleted $count posts");
        }

        return $response->withRedirect('/admin/');
    }

    public function getResultMessage($count)
    {
        if ((int)$count === 0) {
            return '削除できませんでした。';
        } else {
            return $count . '件の削除を完了しました。';
        }
    }

    public function validation($input)
    {
        $val = $this->validate;
        $val->addCustomRule('arrayRule', '\App\Rule\ArrayRule');
        $val->addField('del', 'ID')
                   ->arrayRule()
                       ->setMessage('IDは必須です。');

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
}
