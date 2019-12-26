<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 管理员管理 ]
namespace app\message_admin\controller;

class Admin extends Base
{

    protected $admin_model;

    public function __construct()
    {
        parent::__construct();
        $this->admin_model = model('Admin');
    }

    /**
     * 修改管理员密码
     *
     *
     */
    public function edit_password()
    {
        if (request()->isPost()) {
            $param = input('post.');
            $oldpassword = isset($param['oldpassword']) ? $param['oldpassword'] : '';
            $password = isset($param['password']) ? $param['password'] : '';
            $repassword = isset($param['repassword']) ? $param['repassword'] : '';
            if ($oldpassword == '' || $password == '' || $repassword == '') {
                $this->error('Do not access illegally');
            }
            if ($repassword != $password) {
                $this->error('The passwords entered twice are inconsistent');
            }
            $id = session('id');
            if (!$id) {
                $this->error('Do not access illegally');
            }
            //获取管理员资料
            $admin_info = $this->admin_model->get_one_data(['id' => $id]);
            //组装原密码
            $user_password = md5(md5($oldpassword) . $admin_info['code']);
            if ($user_password != $admin_info['password']) {
                $this->error('The original password is wrong');
            }
            //组装新密码
            $new_password = md5(md5($password) . $admin_info['code']);
            //更新密码
            $ret = $this->admin_model->update_data(['password' => $new_password], ['id' => $id]);
            if ($ret) {
                $this->success('The login password was changed successfully, please log in again');
            } else {
                $this->error('Error changing login password, please try again');
            }
        }
        return view();
    }
}