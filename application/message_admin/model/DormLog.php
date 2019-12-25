<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 寝室入住记录模型 ]
namespace app\message_admin\model;

use app\common\model\Base;

class DormLog extends Base
{
    //   //获取器--获取数据的字段值后自动转换为字符串描述
    //   public function getInputtimeAttr($value)
    //   {
    //       return date('Y-m-d H:i:s',$value);
    //   }

    //    //获取器--获取数据的字段值后自动转换为字符串描述
    //    public function getLeavetimeAttr($value)
    //    {
    //        return date('Y-m-d H:i:s',$value);
    //    }

    //关联学生信息
    public function student()
    {
        return $this->hasOne('Student', 'id', 'student_id')->bind(
            ['name','student_code'=>'student_id']);
    }
}