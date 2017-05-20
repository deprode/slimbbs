<?php
namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator;
use Slim\Flash\Messages;
use RKA\Session;
use App\Classes\Log;
use App\Traits\Validation;

final class DeleteAction
{
    private $view;
    private $logger;
    private $validate;
    private $flash;
    private $session;
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
        $this->logger->info("Delete page action dispatched");

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
            return $response->withRedirect('/');
        }

        // 投稿を削除
        $result = false;
        try {
            $result = $this->log->deleteDataForUser($input['id'], $input['del_pass']);
        } catch (\Exception $e) {
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
}
