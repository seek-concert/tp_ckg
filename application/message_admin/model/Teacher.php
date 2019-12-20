<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 老师模型 ]
namespace app\message_admin\model;

use app\common\model\Base;

class Teacher extends Base
{
    //开启自动写入时间
    protected $autoWriteTimestamp = true;

    //添加时间
    protected $createTime = 'input_time';

    //修改时间
    protected $updateTime = 'update_time';

    //关联教室信息
    public function classroom()
    {
        return $this->hasOne('Classroom', 'id', 'classroom_id')->bind(['c_name' => 'username']);
    }

    //获取器--获取数据的字段值后自动转换为字符串描述
    public function getStatusAttr($value)
    {
        $status = ['' => '', 1 => '启用', 2 => '停用'];
        return $status[$value];
    }

    //获取器--获取数据的字段值后自动转换为字符串描述
    public function getTeamAttr($value)
    {
        $status = ['' => '', 1 => '无', 2 => '有'];
        return $status[$value];
    }
}