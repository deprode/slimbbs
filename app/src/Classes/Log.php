<?php

namespace App\Classes;

class Log
{
    private $log_path;
    private $past_dir;
    private $log_max;

    private $password;

    const DEFAULT_PER_PAGE = 10;

    // クラス設定
    public function __construct(string $path, string $past, int $max)
    {
        $this->log_path = $path;
        $this->past_dir = $past;
        $this->log_max  = $max;
    }

    public function setPassword(Password $password)
    {
        $this->password = $password;
    }

    // N番目の投稿を読み込む
    public function readDataWithNo(int $post_no)
    {
        $data = $this->readData();
        $datum = array_splice($data, $post_no, 1);
        if (count($datum) === 0) {
            return null;
        }
        return $datum[0];
    }

    // データを表示するサイズに切り取る
    public static function spliceData(array $data, int $page, int $per_page = self::DEFAULT_PER_PAGE)
    {
        if ($data) {
            $data = array_splice($data, $page * $per_page, $per_page);
        }

        return $data;
    }

    // 現在のログファイルから全てのデータの読み込み
    public function readData(string $path = null)
    {
        if (is_null($path)) {
            $path = $this->log_path;
        }
        $data = null;
        if (file_exists($path)) {
            $data = file_get_contents($path);
        }
        return json_decode($data);
    }

    // 投稿の書き込み
    public function writeData(array $data, string $path = null)
    {
        if (is_null($path)) {
            $path = $this->log_path;
        }
        file_put_contents($path, json_encode($data), LOCK_EX);
    }

    // 投稿を保存する
    public function saveData(array $formatted_input)
    {
        if (!is_readable($this->log_path)) {
            throw new \Exception("log file is not found or not readable.");
        }

        $data = $this->readData($this->log_path);
        $data = $this->insertInput($data, $formatted_input);

        // 最大保存数を超えた分を切る
        if (count($data) > $this->log_max) {
            array_splice($data, $this->log_max);
        }

        $this->writeData($data);
    }

    // 投稿をログの先頭に入れる
    public function insertInput(array $data = null, array $formatted_input = null)
    {
        if (is_array($data) && count($data) > 0) {
            array_unshift($data, $formatted_input);
        } else {
            $data = [$formatted_input];
        }

        return $data;
    }

    // 日別ログのパスを取得
    private function getDailyLogPath(string $date_str = null)
    {
        // パスがなければ今日の日付でパスを作成
        if (is_null($date_str)) {
            $date_str = date_format(date_create(), 'Ymd');
        }

        $date = new \DateTime();
        $filepath = $this->past_dir . '/' . $date_str . '.dat';

        return $filepath;
    }

    // 過去ログを取得
    public function readDailyLog(string $date_str = null)
    {
        $path = $this->getDailyLogPath($date_str);
        return $this->readData($path);
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
    public function writeDailyLog(array $formatted_input)
    {
        if (!is_dir($this->past_dir)) {
            throw new \Exception("past directory is not found or not readable.");
        }

        $filepath = $this->getDailyLogPath();
        if (is_writable($filepath)) {
            $data = $this->readData($filepath);
            $data = $this->insertInput($data, $formatted_input);
            $this->writeData($data, $filepath);
        }
    }

    // ログ内にある指定した投稿IDのインデックスを返す
    public function indexOfPostData(array $data, string $id)
    {
        for ($i=0; $i < count($data); $i++) {
            if ($data[$i]->id === $id) {
                return $i;
            }
        }
        return -1;
    }

    // ログの削除
    private function deleteData(string $id, string $del_pass = null)
    {
        if (!is_readable($this->log_path)) {
            throw new \Exception("log file is not found or not readable.");
        }

        $data = $this->readData();
        $index = $this->indexOfPostData($data, $id);

        // エラーチェック
        if ($index < 0) {
            // 投稿が見つからない
            return false;
        } elseif (isset($this->password) && !$this->password->checkPassword($del_pass, $data[$index]->del_pass)) {
            // 削除パスが違う
            return false;
        }

        $del_data = array_splice($data, $index, 1);
        $del_data = $del_data[0];
        $this->writeData($data);

        // 日別ログから削除
        if (isset($this->past_dir)) {
            $past_log = $this->past_dir . '/' . date_format(date_create($del_data->created), 'Ymd') . '.dat';
            $past_data = $this->readData($past_log);
            $index = $this->indexOfPostData($past_data, $id);
            array_splice($past_data, $index, 1);
            $this->writeData($past_data, $past_log);
        }

        return true;
    }

    // 削除（ユーザ側からパスをつけて呼ぶ）
    public function deleteDataForUser(string $id, string $del_pass)
    {
        return $this->deleteData($id, $del_pass);
    }

    // 削除（管理側からパスなしで呼ぶ）
    public function deleteDataForAdmin(string $id)
    {
        return $this->deleteData($id);
    }
}
