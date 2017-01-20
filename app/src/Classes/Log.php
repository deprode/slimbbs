<?php
namespace App\Classes;

class Log
{

    private $log_path;
    private $past_dir;
    private $log_max;

    // クラス設定
    public function __construct($path, $past, $max)
    {
        $this->log_path = $path;
        $this->past_dir = $past;
        $this->log_max  = $max;
    }

    // N番目の投稿を読み込む
    public function dataReadWithNo($post_no)
    {
        $data = $this->dataRead();
        $datum = array_splice($data, $post_no, 1);
        if (count($datum) === 0) {
            return null;
        }
        return $datum[0];
    }

    // 指定ページの投稿を読み込む
    public function dataReadWithPage($page, $num)
    {
        $page = (int)$page;
        $data = $this->dataRead();
        return array_splice($data, $page * $num, $num);
    }

    // 現在のログファイルから全てのデータの読み込み
    public function dataRead($path = null)
    {
        if (is_null($path)) {
            $path = $this->log_path;
        }
        $old_data = file_get_contents($path);
        return json_decode($old_data);
    }

    // 投稿の書き込み
    public function dataWrite($data, $path = null)
    {
        if (is_null($path)) {
            $path = $this->log_path;
        }
        file_put_contents($path, json_encode($data), LOCK_EX);
    }

    // 投稿を保存する
    public function saveData($formatted_input)
    {
        if (!is_readable($this->log_path)) {
            throw new \Exception("log file is not found or not readable.");
        }

        $data = $this->dataRead($this->log_path);
        $data = $this->insertInput($data, $formatted_input);

        // 最大保存数を超えた分を切る
        if (count($data) > $this->log_max) {
            array_splice($data, $this->log_max);
        }

        $this->dataWrite($data);
    }

    // 投稿をログの先頭に入れる
    public function insertInput($data, $formatted_input)
    {
        if (is_array($data) && count($data) > 0) {
            array_unshift($data, $formatted_input);
        } else {
            $data = [$formatted_input];
        }

        return $data;
    }

    // 日別ログのパスを取得
    private function getDailyLogPath($date_str = null)
    {
        // パスがなければ今日の日付でパスを作成
        if (is_null($date_str)) {
            $date_str = date_format(date_create(), 'Ymd');
        }

        $date = new \DateTime();
        $filepath = $this->past_dir . '/' . $date_str . '.dat';

        return $filepath;
    }

    // 日別ログファイルを作成
    public function createDailyLog()
    {
        if (!is_dir($this->past_dir)) {
            throw new \Exception("past directory is not found or not readable.");
        }

        $filepath = $this->getDailyLogPath();

        // ログファイルがないときに作成
        if (!file_exists($filepath)) {
            touch($filepath);
        }
    }

    // 日別ログに書き込み
    public function writeDailyLog($formatted_input)
    {
        if (!is_dir($this->past_dir)) {
            throw new \Exception("past directory is not found or not readable.");
        }

        $filepath = $this->getDailyLogPath();
        if (is_writable($filepath)) {
            $data = $this->dataRead($filepath);
            $data = $this->insertInput($data, $formatted_input);
            $this->dataWrite($data, $filepath);
        }
    }

    // ログ内にある指定した投稿IDのインデックスを返す
    public function indexOfPostData($data, $id)
    {
        for ($i=0; $i < count($data); $i++) {
            if ($data[$i]->id === (string)$id) {
                return $i;
            }
        }
        return -1;
    }

    // ログの削除
    private function deleteData($id, $del_pass = null)
    {
        if (!is_readable($this->log_path)) {
            throw new \Exception("log file is not found or not readable.");
        }

        $data = $this->dataRead();
        $index = $this->indexOfPostData($data, $id);

        // エラーチェック
        if ($index < 0) {
            // 投稿が見つからない
            return false;
        } elseif (!$this->checkPassword($del_pass, $data[$index]->del_pass)) {
            // 削除パスが違う
            return false;
        }

        $del_data = array_splice($data, $index, 1);
        $del_data = $del_data[0];
        $this->dataWrite($data);

        // 日別ログから削除
        if (isset($this->past_dir)) {
            $past_log = $this->past_dir . '/' . date_format(date_create($del_data->created), 'Ymd') . '.dat';
            $past_data = $this->dataRead($past_log);
            $index = $this->indexOfPostData($past_data, $id);
            array_splice($past_data, $index, 1);
            $this->dataWrite($past_data, $past_log);
        }

        return true;
    }

    // 削除（ユーザ側からパスをつけて呼ぶ）
    public function deleteDataForUser($id, $del_pass)
    {
        return $this->deleteData($id, $del_pass);
    }

    // 削除（管理側からパスなしで呼ぶ）
    public function deleteDataForAdmin($id)
    {
        return $this->deleteData($id);
    }

    private function checkPassword($input_pass, $del_pass)
    {
        return is_null($input_pass) || password_verify((string)$input_pass, $del_pass);
    }
}
