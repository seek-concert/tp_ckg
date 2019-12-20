<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 后台登录 ]
namespace app\message_admin\controller;

use think\Controller;

class Login extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->admin_model = model('Admin');

    }

    /**
     * 登录页面
     * @author    leimon <leimon1314@gmail.com>
     */
    public function index()
    {
        return view();
    }

    /**
     * 登录操作
     */
    public function do_login()
    {
        if (request()->post()) {
            $param = input('post.');
            //获取数据
            $username = isset($param['username']) ? $param['username'] : '';
            $password = isset($param['password']) ? $param['password'] : '';
            $vercode = isset($param['vercode']) ? $param['vercode'] : '';
            //判断数据
            if (!$username) {
                $this->error('请输入用户名');
            }
            if (!$password) {
                $this->error('请输入登录密码');
            }
            if (!$vercode) {
                $this->error('请输入验证码');
            }

            if (!captcha_check($vercode)) {
                $this->error('验证码错误');
            };
            //获取管理员详细信息
            $admin_info = $this->admin_model->get_one_data(['username' => $username]);
            if (!$admin_info) {
                $this->error('该用户不存在');
            }
            //验证密码
            $user_password = md5(md5($password) . $admin_info['code']);
            if ($user_password != $admin_info['password']) {
                $this->error('登录密码错误');
            }
            //验证是否被禁止
            if ($admin_info['status'] == 2) {
                $this->error('该账户已被禁止登录');
            }
            session('username', $admin_info['username']);
            session('password', $admin_info['password']);
            session('type', $admin_info['type']);
            session('id', $admin_info['id']);

            //写入管理员操作记录
            set_admin_log($admin_info['id'], '管理员[ID:' . $admin_info['id'] . ']登录平台');
            $this->success('登录成功');
        }
    }
}
