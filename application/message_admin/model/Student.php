<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 学生模型 ]
namespace app\message_admin\model;

use app\common\model\Base;

class Student extends Base
{
    //开启自动写入时间
    protected $autoWriteTimestamp = true;

    //添加时间
    protected $createTime = 'input_time';

    //修改时间
    protected $updateTime = 'update_time';


    //关联机构表信息
    public function admin()
    {
        return $this->hasOne('Admin', 'id', 'admin_id')->bind(['a_name' => 'username']);
    }

    //关联机构表信息
    public function admins()
    {
        return $this->hasOne('Admin', 'id', 'mechanism_id')->bind(['m_name' => 'username']);
    }

    //关联学科信息
    public function curriculum()
    {
        return $this->hasOne('Curriculum', 'id', 'curriculum_id')->bind(['c_name' => 'username']);
    }

    //关联寝室信息
    public function dorm()
    {
        return $this->hasOne('Dorm', 'id', 'dorm_id')->bind(['d_name' => 'username' , 'd_type' => 'type']);
    }

    //获取器--获取数据的字段值后自动转换为字符串描述
    public function getStatusAttr($value)
    {
        $status = ['' => '', 1 => 'unstart', 2 => 'learning', 3 => 'done'];
        return $status[$value];
    }

    //获取器--获取数据的字段值后自动转换为字符串描述
    public function getSexAttr($value)
    {
        $status = ['' => '', 1 => 'male', 2 => 'female'];
        return $status[$value];
    }


}