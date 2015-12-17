<?php
namespace App\Classes;

class Log {

    private $log_path;
    private $past_dir;
    private $log_max;

    public function setLogSetting($path, $past, $max)
    {
        $this->log_path = $path;
        $this->past_dir = $past;
        $this->log_max  = $max;
    }

    public function dataReadWithNo($no)
    {
        $data = $this->dataRead();
        $datum = array_splice($data, $no, 1);
        if (count($datum) === 0) {
            return null;
        }
        return $datum[0];
    }

    public function dataReadWithPage($page, $num)
    {
        $page = (int)$page;
        $data = $this->dataRead();
        return array_splice($data, $page * $num, $num);
    }

    public function dataRead($path = null)
    {
        if (is_null($path)) {
            $path = $this->log_path;
        }
        $old_data = file_get_contents($path);
        return json_decode($old_data);
    }

    public function dataWrite($data, $path = null)
    {
        if (is_null($path)) {
            $path = $this->log_path;
        }
        file_put_contents($path, json_encode($data), LOCK_EX);
    }

    public function saveData($formatted_input)
    {
        if (!is_readable($this->log_path)) {
            throw new Exception("log file is not found or not readable.");
        }

        $data = $this->dataRead($this->log_path);
        $data = $this->insertInput($data, $formatted_input);

        // $maxを超えた分を切る
        if (count($data) > $this->log_max) {
            array_splice($data, $this->log_max);
        }

        $this->dataWrite($data);
    }

    public function insertInput($data, $formatted_input)
    {
        if (is_array($data) && count($data) > 0) {
            array_unshift($data, $formatted_input);
        } else {
            $data = [$formatted_input];
        }

        return $data;
    }

    private function getDailyLogPath($date_str = null)
    {
        if (is_null($date_str)) {
            $date_str = date_format(date_create(), 'Ymd');
        }
        $date = new \DateTime();
        $filepath = $this->past_dir . '/' . $date_str . '.dat';

        return $filepath;
    }

    public function createDailyLog()
    {
        if (!is_dir($this->past_dir)) {
            throw new Exception("past directory is not found or not readable.");
        }

        $filepath = $this->getDailyLogPath();
        if (!file_exists($filepath)) {
            touch($filepath);
        }
    }

    public function writeDailyLog($formatted_input)
    {
        if (!is_dir($this->past_dir)) {
            throw new Exception("past directory is not found or not readable.");
        }

        $filepath = $this->getDailyLogPath();
        if (is_writable($filepath)) {
            $data = $this->dataRead($filepath);
            $data = $this->insertInput($data, $formatted_input);
            $this->dataWrite($data, $filepath);
        }
    }

    public function indexOfPostData($data, $id)
    {
        for ($i=0; $i < count($data); $i++) {
            if ($data[$i]->id === (string)$id) {
                return $i;
            }
        }
        return -1;
    }

    public function deleteData($id, $del_pass = null)
    {
        if (!is_readable($this->log_path)) {
            throw new Exception("log file is not found or not readable.");
        }

        $data = $this->dataRead();
        $index = $this->indexOfPostData($data, $id);

        // error
        if ($index < 0) {
            return false;
        } elseif (!is_null($del_pass) && !password_verify((string)$del_pass, $data[$index]->del_pass) ) {
            return false;
        }

        $del_data = array_splice($data, $index, 1);
        $this->dataWrite($data);

        // 過去ログ
        $past_log = $this->past_dir . '/' . date_format(date_create($del_data->created), 'Ymd') . '.dat';
        $past_data = $this->dataRead($past_log);
        $index = $this->indexOfPostData($past_data, $id);
        array_splice($past_data, $index, 1);
        $this->dataWrite($past_data, $past_log);

        return true;
    }

    public function deleteDataForUser($id, $del_pass)
    {
        return $this->deleteData($id, $del_pass);
    }

    public function deleteDataForAdming($id)
    {
        return $this->deleteData($id);
    }
};