<?php

namespace App\Models\Traits;

trait SerializeDate
{
    /**
     * 为数组 / JSON序列化准备一个日期
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
