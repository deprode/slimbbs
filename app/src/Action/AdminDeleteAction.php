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
use App\Traits\Validation;

final class AdminDeleteAction
{
    private $view;
    private $logger;
    private $validate;
    private $session;
    private $flash;
    private $log;

    public function __construct(Twig $view, LoggerInterface $logger, Validator $validate, Session $session, Messages $flash, Log $log)
    {
        $this->view     = $view;
        $this->logger   = $logger;
        $this->validate = $validate;
        $this->session  = $session;
        $this->flash    = $flash;
        $this->log      = $log;
    }

    public function dispatch(Request $request, Response $response)
    {
        $this->logger->info("Deletes page action dispatched");

        // CSRFチェック
        if ($request->getAttribute('csrf_status') === false) {
            $response = $response->withStatus(403);
            $this->view->render($response, 'csrf.twig');
            return $response;
        }

        $input = $request->getParsedBody();

        // Validation
        if($request->getAttribute('has_errors')){
            $errors = $request->getAttribute('errors');
            $this->session->set('errors', $errors);
            $this->flash->addMessage('errorMessage', '入力に不適切な箇所があったため、書き込みを中断しました');
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
            $this->flash->addMessage('resultMessage', $this->getResultMessage($count));
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
}
