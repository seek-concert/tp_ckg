<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 后台底层 ]
namespace app\message_admin\controller;

use think\Controller;

class Base extends Controller
{

    protected $admin_model;

    public function _initialize()
    {
        $this->admin_model = model('Admin');
        //用户是否登录
        $this->is_login();

    }

    /**
     * 判断用户登录数据
     *
     * @return boolean
     */
    public function is_login()
    {
        //判断登录
        if (empty(session('username')) || empty(session('password'))) {
            $loginUrl = url('login/index');
            $this->error('please sign in');
            echo "<script language='javascript'>parent.parent.location.href='$loginUrl'</script>";
        }
        $username = session('username');
        $password = session('password');
        //判断session用户名密码与数据库是否吻合
        $is_admin = $this->admin_model->get_all_count(['username' => $username, 'password' => $password]);
        if (!$is_admin) {
            $loginUrl = url('login/index');
            $this->error('please sign in');
            echo "<script language='javascript'>parent.parent.location.href='$loginUrl'</script>";
        }
    }

    /**
     * 退出登录
     *
     * @return void
     */
    public function out_login()
    {
        session('username', null);
        session('password', null);
        session('id', null);
        return $this->redirect(url('/message_admin/login/index'));
    }
}
