<?php

use Respect\Validation\Validator as v;
use App\Classes\Config;

$container = $app->getContainer();
$ng_word = $container->config->get('ng_word');

$translator = function ($message) {
    $messages = [
        'These rules must pass for {{name}}'                                => '{{name}}で守られていないルールがあります',
        'All of the required rules must pass for {{name}}'                  => '{{name}}で守られていないルールがあります',
        '{{name}} must have a length lower than {{maxValue}}'               => '{{name}}は{{maxValue}}文字以下で入力してください',
        '{{name}} must be a string'                                         => '{{name}}には文字列を入力してください',
        '{{name}} must not be empty'                                        => '{{name}}は必須です',
        '{{name}} must have a length between {{minValue}} and {{maxValue}}' => '{{name}}は{{minValue}}〜{{maxValue}}字の範囲で入力してください',
        '{{name}} must be valid email'                                      => '{{name}}にはEメールアドレスのみ書き込めます',
        '{{name}} must be an URL'                                           => '{{name}}にはURLのみ書き込めます',
        '{{name}} must validate against {{regex}}'                          => '{{name}}には英数字かアンダーバーを使ってください',
        '{{name}} must be of the type array'                                => '{{name}}が選択されていません',
        'Each item in {{name}} must be valid'                               => '{{name}}が不正な形式です',
        '{{name}} must not be in {{haystack}}'                              => '{{name}}にNGワードが含まれています',
        '{{name}} must be a valid date. Sample format: {{format}}'          => '{{name}}が日付の形式（{{format}}）ではありません',
        '{{name}} must be greater than or equal to {{interval}}'            => '{{name}}は{{interval}}より大きい値にしてください'
    ];
    return $messages[$message];
};

$saveValidators = [
    'name'     => v::optional(v::stringType()->length(null, 50))->setName('名前'),
    'subject'  => v::stringType()->length(null, 50)->setName('タイトル'),
    'body'     => v::stringType()->notEmpty()->length(null, 2000)->not(v::in($ng_word))->setName('本文'),
    'email'    => v::optional(v::email())->setName('Eメール'),
    'url'      => v::optional(v::url())->setName('URL'),
    'del_pass' => v::optional(v::regex('/\w/'))->setName('削除パス')
];

$deleteValidators = [
    'id' => v::notEmpty()->alnum()->setName('ID'),
    'del_pass' => v::notEmpty()->regex('/\w/')->setName('削除パス')
];

$adminDeleteValidators = [
    'del' => v::arrayType()->notEmpty()->each(v::alnum())->setName('削除する投稿')
];

$adminConfigValidators = [
    'consecutive' => v::intVal()->min(0)->setName('投稿間隔'),
    'ng_word' => v::arrayType()->notEmpty()->each(v::stringType())->setName('NGワード'),
    'per_page' => v::intVal()->min(1)->setName('1ページの表示数')
];

$pastValidators = [
    'date' => v::optional(v::date('Y-m-d'))->setName('日付')
];