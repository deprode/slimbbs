<?php

namespace App\Classes;

class Log
{
    private $log_path;
    private $past_dir;
    private $log_max;
    private $file;
    private $password;

    const DEFAULT_PER_PAGE = 10;

    // クラス設定
    public function __construct(File $file, Password $password, string $path, string $past, int $max)
    {
        if (!is_dir($past)) {
            throw new \Exception("past directory is not found or not readable.");
        }
        if (!is_readable($path)) {
            throw new \Exception("log file is not found or not readable.");
        }

        $this->file     = $file;
        $this->password = $password;
        $this->log_path = $path;
        $this->past_dir = $past;
        $this->log_max  = $max;
    }

    public function getLogMax()
    {
        return $this->log_max;
    }

    public function generateLogData(array $data)
    {
        return new LogData($data);
    }

    // 直前の投稿を読み込む
    public function getPreviousPost()
    {
        $data = $this->readData();
        $datum = array_splice($data, 0, 1);
        if (count($datum) === 0) {
            return null;
        }
        return $datum[0];
    }

    // データを表示するサイズに切り取る
    public static function spliceData(array $data = null, int $page, int $per_page = self::DEFAULT_PER_PAGE)
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
            $this->file->open($path, "c+b");
            $data = $this->file->read();
        }
        return json_decode($data);
    }

    // 投稿の書き込み
    public function writeData(array $data, string $path = null)
    {
        if (is_null($path)) {
            $path = $this->log_path;
        }
        if (file_exists($path)) {
            $this->file->open($path, "wb");
            $this->file->write(json_encode($data));
        }
    }

    // 投稿を保存する
    public function updateData(LogData $log_data, $path = null, $max = PHP_INT_MAX)
    {
        if (is_null($path) || !is_writable($path)) {
            $path = $this->log_path;
        }

        // ファイル読み込み
        $data = null;
        if (file_exists($path)) {
            $this->file->open($path, "c+b");
            $data = json_decode($this->file->read());
        }

        // 新規データを先頭に挿入
        $data = $this->insertInput($data, $log_data);

        // 最大保存数を超えた分を切る
        if (count($data) > $max) {
            array_splice($data, $max);
        }

        // ファイル書き込み
        $this->file->write(json_encode($data));
    }

    // 投稿をログの先頭に入れる
    private function insertInput(array $data = null, LogData $log_data = null)
    {
        if (is_array($data) && count($data) > 0) {
            array_unshift($data, $log_data->getData());
        } else {
            $data = [$log_data->getData()];
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

        $file_path = $this->past_dir . '/' . $date_str . '.dat';

        return $file_path;
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
        $this->file->create($this->getDailyLogPath());
    }

    // 日別ログに書き込み
    public function updateDailyLog(LogData $log_data)
    {
        $file_path = $this->getDailyLogPath();
        $this->updateData($log_data, $file_path, PHP_INT_MAX);
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

    private function searchData(string $path, string $id)
    {
        if (is_null($path) || !is_writable($path)) {
            $path = $this->log_path;
        }

        // ファイル読み込み
        $data = null;
        if (file_exists($path)) {
            $this->file->open($path, "c+b");
            $data = json_decode($this->file->read());
        }
        $index = -1;
        if (!is_null($data)) {
            $index = $this->indexOfPostData($data, $id);
        }

        return ['data' => $data, 'index' => $index];
    }

    // ログの削除
    private function deleteData(array $data, string $index, string $path)
    {
        $del_data = array_splice($data, $index, 1);
        $del_data = $del_data[0];
        $this->writeData($data, $path);

        return $del_data;
    }

    private function deleteDailyLog($created, $id)
    {
        // 日別ログから削除
        if (isset($this->past_dir)) {
            $past_path = $this->past_dir . '/' . date_format(date_create($created), 'Ymd') . '.dat';
            $past_log = $this->searchData($past_path, $id);
            $this->deleteData($past_log['data'], $past_log['index'], $past_path);
        }
    }

    private function isDelete(array $data, int $index, string $del_pass): bool
    {
        // エラーチェック
        if ($index < 0) {
            // 投稿が見つからない
            return false;
        } elseif (isset($this->password) && !$this->password->checkPassword($del_pass, $data[$index]->del_pass)) {
            // 削除パスが違う
            return false;
        }
        return true;
    }

    // 削除（ユーザ側からパスをつけて呼ぶ）
    public function deleteDataForUser(string $id, string $del_pass)
    {
        $log = $this->searchData($this->log_path, $id);

        if ($this->isDelete($log['data'], $log['index'], $del_pass)) {
            $del_data = $this->deleteData($log['data'], $log['index'], $this->log_path);

            $this->deleteDailyLog($del_data->created, $id);
            return true;
        }

        return false;
    }

    // 削除（管理側からパスなしで呼ぶ）
    public function deleteDataForAdmin(string $id)
    {
        $log = $this->searchData($this->log_path, $id);
        $del_data = $this->deleteData($log['data'], $log['index'], $this->log_path);

        $this->deleteDailyLog($del_data->created, $id);

        return true;
    }
}
