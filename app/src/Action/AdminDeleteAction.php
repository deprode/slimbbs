<?php
namespace App\Action;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Fuel\Validation\Validator;
use Slim\Flash\Messages;
use App\Classes\Log;
use App\Traits\Validation;

final class AdminDeleteAction
{
    private $view;
    private $logger;
    private $validate;
    private $flash;
    private $log;

    use Validation {
        Validation::__construct as private __vConstruct;
    }

    public function __construct(Twig $view, LoggerInterface $logger, Validator $validate, Messages $flash, Log $log)
    {
        $this->view     = $view;
        $this->logger   = $logger;
        $this->validate = $validate;
        $this->flash    = $flash;
        $this->log      = $log;

        $this->__vConstruct();
    }

    public function dispatch($request, $response, $args)
    {
        $this->logger->info("Deletes page action dispatched");

        // CSRFチェック
        if ($request->getAttribute('csrf_status') === false) {
            $failed = $this->getCSRFValidMessage();
            return $response->write($failed);
        }

        $input = $request->getParsedBody();

        // Validation
        if (!$this->validation($this->validate, $input)) {
            $mes = $this->getValidationMessage();
            $this->flash->addMessage('errorMessage', $mes);
            return $response->withRedirect('/admin/');
        }

        $inputs = $request->getParsedBody();

        // 投稿を削除
        $ids = (isset($inputs['del'])) ? $inputs['del'] : [];
        if ($request->getAttribute('csrf_status') !== false && count($ids) > 0) {
            $count = 0;
            foreach ($ids as $id) {
                $count += $this->log->deleteDataForAdmin($id);
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

    public function validation($val, $input)
    {
        $val->addCustomRule('arrayRule', '\App\Rule\ArrayRule');
        $val->addField('del', 'ID')
                   ->arrayRule()
                       ->setMessage('IDは必須です。');

        $result = $val->run($input);

        $this->cached_errors = $result->getErrors();

        return $result->isValid();
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
