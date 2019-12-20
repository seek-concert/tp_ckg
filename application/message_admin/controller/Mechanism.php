<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 机构管理 ]
namespace app\message_admin\controller;

class Mechanism extends Base
{

    protected $mechanism_model;

    public function __construct()
    {
        parent::__construct();
        $this->mechanism_model = model('Admin');
    }

    /**
     * @return \think\response\View
     * 机构列表
     */
    public function mechanism_lists()
    {
        return view();
    }

    /**
     * 获取机构列表数据
     */
    public function get_mechanism_lists()
    {
        //查询条件
        $param = input('');
        $page = isset($param['page'])?(int)$param['page']:1;
        $limit = isset($param['limit'])?(int)$param['limit']:10;
        //条件组装
        $sqlmap = [];
        $sqlmap['type'] = 2;
        //列表信息
        $lists = $this->mechanism_model->get_all_data_page($sqlmap, $page, $limit, 'id desc', 'id,username,status,input_time');
        $return_data = [];
        $return_data['code'] = 1;
        $return_data['count'] = $this->mechanism_model->get_all_count($sqlmap);
        $return_data['data'] = $lists;
        return $return_data;
    }

    /**
     * 添加机构
     */
    public function mechanism_add()
    {
        $param = input('');

        if (request()->post()) {
            $rule = [
                ['username', 'require', '机构不能为空'],
                ['password', 'require|length:6,25', '密码不能为空|密码长度为6-25位'],
            ];
            //验证数据
            $result = $this->validate($param, $rule);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
            }
            $code = generate_password(6);
            $password = md5(md5($param['password']).$code);
            //组装数据
            $sqlmap = [];
            $sqlmap['username'] = $param['username'];
            $sqlmap['password']  = $password;
            $sqlmap['code']  = $code;
            //新增数据
            $ret = $this->mechanism_model->add_data($sqlmap);
            if ($ret) {
                $this->success('添加成功');
            } else {
                $this->error('添加出错，请重试');
            }
        }
        return view();
    }

    /**
     * 编辑机构
     */
    public function mechanism_edit()
    {
        $param = input('');
        $id = isset($param['id'])?(int)$param['id']:0;
        if($id == 0){
            $this->error('请勿非法访问');
        }
        if (request()->post()) {
            $rule = [
                ['username', 'require', '机构不能为空'],
            ];
            //验证数据
            $result = $this->validate($param, $rule);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
            }
            //组装数据
            $sqlmap = [];
            //检查是否修改密码
            if(!empty($param['password'])){
                $rule = [
                    ['password', 'length:6,25', '密码长度为6-25位'],
                ];
                //验证数据
                $result = $this->validate($param, $rule);
                if (true !== $result) {
                    // 验证失败 输出错误信息
                    $this->error($result);
                }
                $code = generate_password(6);
                $password = md5(md5($param['password']).$code);
                $sqlmap['password']  = $password;
                $sqlmap['code']  = $code;
            }
            $sqlmap['username'] = $param['username'];
            $sqlmap['status'] = $param['status'];
            //修改数据
            $ret = $this->mechanism_model->update_data($sqlmap,['id' => $id]);
            if ($ret) {
                $this->success('修改成功');
            } else {
                $this->error('修改出错，请重试');
            }
        }
        $mechanism_info = $this->mechanism_model->get_one_data(['id'=>$id]);
        $return_data = [];
        $return_data['mechanism_info'] = $mechanism_info;
        return view('',$return_data);
    }

}