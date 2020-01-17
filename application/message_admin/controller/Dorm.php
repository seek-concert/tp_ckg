<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 寝室管理 ]
namespace app\message_admin\controller;

class Dorm extends Base
{

    protected $dorm_model;

    public function __construct()
    {
        parent::__construct();
        $this->dorm_model = model('Dorm');
    }

    /**
     * @return \think\response\View
     * 寝室列表
     */
    public function dorm_lists()
    {
        return view();
    }

    /**
     * 获取寝室列表数据
     */
    public function get_dorm_lists()
    {
        //查询条件
        $param = input('');
        $page = isset($param['page'])?(int)$param['page']:1;
        $limit = isset($param['limit'])?(int)$param['limit']:10;
        //条件组装
        $sqlmap = [];
        //列表信息
        $lists = $this->dorm_model->get_all_data_page($sqlmap, $page, $limit, 'id desc', 'id,username,type,sex,status,input_time');
        $return_data = [];
        if(empty($lists)){
            $return_data['code'] = 0;
            $return_data['data'] = [];
            $return_data['msg'] = 'no data';
        }else {
            $return_data['code'] = 1;
            $return_data['count'] = $this->dorm_model->get_all_count($sqlmap);
            $return_data['data'] = $lists;
        }
        return $return_data;
    }

    /**
     * 添加寝室
     */
    public function dorm_add()
    {
        $param = input('');

        if (request()->post()) {
            $rule = [
                ['username', 'require|unique:dorm', 'Bed cannot be empty|Bed repetition'],
                ['sex', 'require', 'Sex cannot be empty'],
                ['type', 'require', 'Type cannot be empty'],
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
            $sqlmap['sex'] = $param['sex'];
            $sqlmap['type'] = $param['type'];
            //新增数据
            $ret = $this->dorm_model->add_data($sqlmap);
            if ($ret) {
                $this->success('Added successfully');
            } else {
                $this->error('Add error, please try again');
            }
        }
        return view();
    }

    /**
     * 编辑寝室
     */
    public function dorm_edit()
    {
        $param = input('');
        $id = isset($param['id'])?(int)$param['id']:0;
        if($id == 0){
            $this->error('Do not access illegally');
        }
        if (request()->post()) {
            $rule = [
                ['username', 'require|unique:dorm', 'Bed cannot be empty|Bed repetition'],
                ['type', 'require', 'Sex cannot be empty'],
                ['sex', 'require', 'Sex cannot be empty'],
                ['status', 'require', 'Status cannot be empty'],
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
            $sqlmap['type'] = $param['type'];
            $sqlmap['sex'] = $param['sex'];
            $sqlmap['status'] = $param['status'];
            //修改数据
            $ret = $this->dorm_model->update_data($sqlmap,['id' => $id]);
            if ($ret) {
                $this->success('Successfully modified');
            } else {
                $this->error('Edit error, please try again');
            }
        }
        $dorm_info = $this->dorm_model->get_one_data(['id'=>$id]);
        $return_data = [];
        $return_data['dorm_info'] = $dorm_info;
        return view('',$return_data);
    }

}