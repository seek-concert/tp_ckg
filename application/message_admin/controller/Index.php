<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 后台首页 ]
namespace app\message_admin\controller;

class Index extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 后端框架 目录加载
     *
     * @return void
     */
    public function index()
    {
        return view();
    }

    /**
     * 后端首页
     *
     * @return void
     */
    public function home()
    {
        return view();
    }
}
