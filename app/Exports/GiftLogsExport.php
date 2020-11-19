<?php

namespace App\Exports;

use App\Models\GiftLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class GiftLogsExport implements FromCollection
{
    protected $data;

    //构造函数传值
    public function __construct()
    {
    }
    //数组转集合
    public function collection()
    {
        return new Collection($this->createData());
    }
    //业务代码
    public function createData()
    {
        $gift_logs = GiftLog::get()->toArray();

        $title = ['手机号', '礼物', '抽奖时间'];

        $date[] = $title;

        foreach ($gift_logs as $gift_log) {
            $date[] = [$gift_log['mobile'], $gift_log['gift_name'], $gift_log['create_time']];
        }

        return $date;
    }
}
