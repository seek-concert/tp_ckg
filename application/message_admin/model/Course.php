<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 课表模型 ]
namespace app\message_admin\model;

use app\common\model\Base;

class Course extends Base
{
    //开启自动写入时间
    protected $autoWriteTimestamp = true;

    //添加时间
    protected $createTime = 'input_time';

    //修改时间
    protected $updateTime = 'update_time';

    //关联老师信息
    public function teacher()
    {
        return $this->hasOne('Teacher', 'id', 'teacher_id')->bind(['t_name' => 'username', 't_classroom_id' => 'classroom_id']);
    }

    //关联教室信息
    public function classroom()
    {
        return $this->hasOne('Classroom', 'id', 't_classroom_id')->bind(['c_name' => 'username']);
    }
}