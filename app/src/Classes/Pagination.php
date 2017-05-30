<?php

namespace App\Classes;

class Pagination
{
    private $all_data_length;
    private $current_page;
    private $per_page;

    public function setting($all_data_length, $current_page, $per_page)
    {
        $this->all_data_length = (int)$all_data_length;
        $this->current_page = ($current_page > 0) ? (int)$current_page : 0;
        $this->per_page = ($per_page > 0) ? (int)$per_page : 1;
    }

    public function getCurrentPage()
    {
        return $this->current_page;
    }

    public function getOffset()
    {
        return $this->current_page * $this->per_page;
    }

    public function getCount()
    {
        return $this->all_data_length;
    }

    public function getPerPage()
    {
        return $this->per_page;
    }

    public function isEnable()
    {
        return $this->all_data_length > $this->per_page;
    }

    public function lastPageNum()
    {
        if ($this->per_page <= 0 || $this->all_data_length <= 0) {
            return 0;
        }
        return (ceil($this->all_data_length / $this->per_page))-1;
    }

    public function hasNextPage()
    {
        return $this->all_data_length >= ($this->getOffset() + $this->per_page);
    }

    public function isFirstPage()
    {
        return $this->current_page > 0;
    }

    public function isLastPage()
    {
        return $this->current_page < $this->lastPageNum();
    }
}