<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 课程模型 ]
namespace app\message_admin\model;

use app\common\model\Base;

class Curriculum extends Base
{
    //开启自动写入时间
    protected $autoWriteTimestamp = true;

    //添加时间
    protected $createTime = 'input_time';

    //修改时间
    protected $updateTime = 'update_time';

}