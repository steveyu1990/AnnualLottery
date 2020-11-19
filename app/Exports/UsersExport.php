<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class UsersExport implements FromCollection
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
        $users = User::get()->toArray();

        $title = ['手机号', '征文内容', '提交时间'];

        $date[] = $title;

        foreach ($users as $user) {
            $date[] = [$user['mobile'], $user['content'], $user['create_time']];
        }

        return $date;
    }
}
