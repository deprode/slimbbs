<?php

namespace App\Action;

use App\Classes\Log;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;

final class PastAction
{
    private $view;
    private $logger;
    private $flash;
    private $log;

    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash, Log $log)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->flash  = $flash;
        $this->log    = $log;
    }

    public function dispatch(Request $request, Response $response)
    {
        // Validation
        if($request->getAttribute('has_errors')){
            $this->flash->addMessage('errorMessage', '日付の形式が正しくありません。');
            return $response->withRedirect('/past/');
        }

        $form_date = $request->getParam('date');
        $date = isset($form_date) ? str_replace('-', '', $form_date) : date_format(date_create(), 'Ymd');

        $data = $this->log->readDailyLog($date);

        $message = (!$data || empty($data)) ? '過去ログがありません。' : '';
        $error = $this->flash->getMessage('errorMessage');

        $this->view->render(
            $response,
            'past.twig',
            [
                'form_date' => $form_date,
                'date'      => $date,
                'data'      => $data,
                'error'     => $error[0],
                'message'  => $message
            ]
        );

        return $response;
    }
}