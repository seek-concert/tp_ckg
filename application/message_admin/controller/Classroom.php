<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 教室管理 ]
namespace app\message_admin\controller;

class Classroom extends Base
{

    protected $classroom_model;

    public function __construct()
    {
        parent::__construct();
        $this->classroom_model = model('Classroom');
    }

    /**
     * @return \think\response\View
     * 教室列表
     */
    public function classroom_lists()
    {
        return view();
    }

    /**
     * 获取教室列表数据
     */
    public function get_classroom_lists()
    {
        //查询条件
        $param = input('');
        $page = isset($param['page'])?(int)$param['page']:1;
        $limit = isset($param['limit'])?(int)$param['limit']:10;
        //条件组装
        $sqlmap = [];
        //列表信息
        $lists = $this->classroom_model->get_all_data_page($sqlmap, $page, $limit, 'id desc', 'id,username,status,input_time');
        $return_data = [];
        $return_data['code'] = 1;
        $return_data['count'] = $this->classroom_model->get_all_count($sqlmap);
        $return_data['data'] = $lists;
        return $return_data;
    }

    /**
     * 添加教室
     */
    public function classroom_add()
    {
        $param = input('');

        if (request()->post()) {
            $rule = [
                ['username', 'require', '教室不能为空'],
            ];
            //验证数据
            $result = $this->validate($param, $rule);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
            }
            //组装数据
            $sqlmap = [];
            $sqlmap['username'] = $param['username'];
            //新增数据
            $ret = $this->classroom_model->add_data($sqlmap);
            if ($ret) {
                $this->success('添加成功');
            } else {
                $this->error('添加出错，请重试');
            }
        }
        return view();
    }

    /**
     * 编辑教室
     */
    public function classroom_edit()
    {
        $param = input('');
        $id = isset($param['id'])?(int)$param['id']:0;
        if($id == 0){
            $this->error('请勿非法访问');
        }
        if (request()->post()) {
            $rule = [
                ['username', 'require', '教室不能为空'],
                ['status', 'require', '状态不能为空'],
            ];
            //验证数据
            $result = $this->validate($param, $rule);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
            }
            //组装数据
            $sqlmap = [];
            $sqlmap['username'] = $param['username'];
            $sqlmap['status'] = $param['status'];
            //修改数据
            $ret = $this->classroom_model->update_data($sqlmap,['id' => $id]);
            if ($ret) {
                $this->success('修改成功');
            } else {
                $this->error('修改出错，请重试');
            }
        }
        $classroom_info = $this->classroom_model->get_one_data(['id'=>$id]);
        $return_data = [];
        $return_data['classroom_info'] = $classroom_info;
        return view('',$return_data);
    }

}