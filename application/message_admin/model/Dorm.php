<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 寝室模型 ]
namespace app\message_admin\model;

use app\common\model\Base;

class Dorm extends Base
{
    //开启自动写入时间
    protected $autoWriteTimestamp = true;

    //添加时间
    protected $createTime = 'input_time';

    //修改时间
    protected $updateTime = 'update_time';

    //获取器--获取数据的字段值后自动转换为字符串描述
    public function getStatusAttr($value)
    {
        $status = ['' => '', 1 => 'Enable', 2 => 'Disable'];
        return $status[$value];
    }

    //获取器--获取数据的字段值后自动转换为字符串描述
    public function getSexAttr($value)
    {
        $status = ['' => '', 1 => 'Male', 2 => 'Female'];
        return $status[$value];
    }

    //获取器--获取数据的字段值后自动转换为字符串描述
    public function getTypeAttr($value)
    {
        $status = ['' => '', 1 => 'Single', 2 => 'Double', 3 => 'Triple', 4 => 'Quadruple'];
        return $status[$value];
    }

    //关联学生信息
    public function student()
    {
        return $this->hasOne('Student', 'id', 'dorm_id')->bind(
            ['student_id'=>'student_id','username'=>'name','s_arrival' => 'arrival' , 'd_leave' => 'leave']);
    }
}