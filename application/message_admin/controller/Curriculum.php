<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 课程管理 ]
namespace app\message_admin\controller;

class Curriculum extends Base
{

    protected $curriculum_model;

    public function __construct()
    {
        parent::__construct();
        $this->curriculum_model = model('Curriculum');
    }

    /**
     * @return \think\response\View
     * 课程列表
     */
    public function curriculum_lists()
    {
        if (request()->isPost()) {
            //所有一级菜单
            $lists = $this->curriculum_model->get_all_data(['pid' => 0]);

            //二级菜单
            foreach ($lists as $key => $value) {
                $lists[$key]['children'] = $this->curriculum_model->get_all_data(['pid' => $value['id']]);
                if($lists[$key]['children']){
                    foreach ($lists[$key]['children'] as $k => $v) {
                        $lists[$key]['children'][$k]['children'] = $this->curriculum_model->get_all_data(['pid' => $v['id']]);
                    }
                }
            }

            //重组树形数组
            $node_lists = [];
            foreach ($lists as $key => $value) {
                $node_lists[$key]['name'] = $value['username'];
                $node_lists[$key]['id'] = $value['id'];
                $node_lists[$key]['level'] = $value['level'];
                $node_lists[$key]['spread'] = true;
                $node_lists[$key]['children'] = $value['children'];
            }
            //重组三位数组
            foreach ($lists as $key => $value) {
                if (is_array($value['children'])) {
                    $child_lists = [];
                    foreach ($value['children'] as $k => $v) {
                        $child_lists[$k]['name'] = $v['username'];
                        $child_lists[$k]['id'] = $v['id'];
                        $child_lists[$k]['level'] = $v['level'];
                        $child_lists[$k]['spread'] = true;
                        if(is_array($v['children'])){
                            $three_child_lists = [];
                            foreach ($v['children'] as $i => $n) {
                                $three_child_lists[$i]['name'] = $n['username'];
                                $three_child_lists[$i]['id'] = $n['id'];
                                $three_child_lists[$i]['level'] = $n['level'];
                                $three_child_lists[$i]['spread'] = true;
                            }
                            $child_lists[$k]['children'] = $three_child_lists;
                        }

                    }
                    $node_lists[$key]['children'] = $child_lists;
                }
            }
            $this->success('获取成功', '', $node_lists);
        } else {
            $return_data = [];
            return view('', $return_data);
        }
    }

    /**
     * 添加
     */
    public function curriculum_add()
    {
        $param = input('');
        $id = $param['id']?:0;
        $level = $param['level']?:0;
        $return_data = [];
        $return_data['id'] = $id;
        $return_data['level'] = $level;
        return view('', $return_data);
    }

    /**
     * 执行添加操作
     */
    public function get_curriculum_add()
    {
        $param = input('');
        if (request()->post()) {
            $id = $param['id'];
            $level = $param['level'];
            //组装数据
            $sqlmap = [];
            $sqlmap['username'] = $param['username'];
            $sqlmap['pid'] = $id;
            if($level == 0 && $id == 0){
                if((int)$param['one'] + (int)$param['team'] == 8){
                    $sqlmap['level'] = $level;
                    $sqlmap['one'] = $param['one'];
                    $sqlmap['team'] = $param['team'];
                }else{
                    $this->error('一对一加团体课数量为8');
                }
            }else{
                $sqlmap['level'] = $level+1;
                if($level == 0){
                    $type = $param['type'];
                    $curriculum_info = $this->curriculum_model->get_one_data(['id'=>$id],'','one,team');
                    if($type == 1){
                        $nums = $this->curriculum_model->get_all_count(['pid' => $id,'type' => $type]);
                        if($nums >= $curriculum_info['one']){
                            $this->error('课程已满！');
                        }
                    }else if($type == 2){
                        $nums = $this->curriculum_model->get_all_count(['pid' => $id,'type' => $type]);
                        if($nums >= $curriculum_info['team']){
                            $this->error('课程已满！');
                        }
                    }
                    $sqlmap['type'] = $type;
                }
            }
            //新增数据
            $ret = $this->curriculum_model->add_data($sqlmap);
            if ($ret) {
                $this->success('添加成功');
            } else {
                $this->error('添加出错，请重试');
            }
        }
    }

    /**
     * 编辑
     */
    public function curriculum_edit()
    {
        $param = input('');
        $id = $param['id']?:0;
        $curriculum_info = $this->curriculum_model->get_one_data(['id'=>$id]);
        $return_data = [];
        $return_data['curriculum_info'] = $curriculum_info;
        return view('', $return_data);
    }

    /**
     * 执行编辑操作
     */
    public function get_curriculum_edit()
    {
        $param = input('');
        if (request()->post()) {
            //组装数据
            $sqlmap = [];
            $sqlmap['username'] = $param['username'];
            if($param['level'] == 0){
                if((int)$param['one'] + (int)$param['team'] == 8){
                    $sqlmap['one'] = $param['one'];
                    $sqlmap['team'] = $param['team'];
                }else{
                    $this->error('一对一加团体课数量为8');
                }
            }
            //新增数据
            $ret = $this->curriculum_model->update_data($sqlmap,['id' => $param['id']]);
            if ($ret) {
                $this->success('修改成功');
            } else {
                $this->error('修改出错，请重试');
            }
        }
    }
}