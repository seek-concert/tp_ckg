<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 教室模型 ]
namespace app\message_admin\model;

use app\common\model\Base;

class Classroom extends Base
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
}