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
    protected $course_model;
    protected $teacher_model;
    protected $curriculum_model;

    public function __construct()
    {
        parent::__construct();
        $this->classroom_model = model('Classroom');
        $this->course_model = model('Course');
        $this->teacher_model = model('Teacher');
        $this->curriculum_model = model('Curriculum');
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
                ['username', 'require', 'Classroom cannot be empty'],
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
                $this->success('Added successfully');
            } else {
                $this->error('Add error, please try again');
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
            $this->error('Do not access illegally');
        }
        if (request()->post()) {
            $rule = [
                ['username', 'require', 'Classroom cannot be empty'],
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
            $sqlmap['status'] = $param['status'];
            //修改数据
            $ret = $this->classroom_model->update_data($sqlmap,['id' => $id]);
            if ($ret) {
                $this->success('Successfully modified');
            } else {
                $this->error('Edit error, please try again');
            }
        }
        $classroom_info = $this->classroom_model->get_one_data(['id'=>$id]);
        $return_data = [];
        $return_data['classroom_info'] = $classroom_info;
        return view('',$return_data);
    }

    /**
     * 教室列表
     * @return \think\response\View
     */
    public function lists()
    {
        return view();
    }

    /**
     * 获取教室列表
     */
    public function get_lists()
    {
        //查询条件
        $param = input('');
        $page = isset($param['page'])?(int)$param['page']:1;
        $limit = isset($param['limit'])?(int)$param['limit']:10;
        $arrival = isset($param['arrival'])?$param['arrival']:date('Y-m-d');
        //列表信息
        $lists = $this->teacher_model->get_all_data_page(['status' => 1], $page, $limit, 'classroom_id asc', 'id,username,classroom_id',['classroom']);
        foreach ($lists as $k=>$v){
            $s1 = $this->course_model->get_one_value(['num'=>1,'teacher_id'=>$v['id'],'arrival' =>['<= time',$arrival],'leave' =>['>= time',$arrival]],'textbook_id')?:'';
            $s1 = $this->curriculum_model->get_one_value(['id' => $s1],'username')?:'';
            $lists[$k]['s1'] = $s1;
            $s2 = $this->course_model->get_one_value(['num'=>2,'teacher_id'=>$v['id'],'arrival' =>['<= time',$arrival],'leave' =>['>= time',$arrival]],'textbook_id')?:'';
            $s2 = $this->curriculum_model->get_one_value(['id' => $s2],'username')?:'';
            $lists[$k]['s2'] = $s2;
            $s3 = $this->course_model->get_one_value(['num'=>3,'teacher_id'=>$v['id'],'arrival' =>['<= time',$arrival],'leave' =>['>= time',$arrival]],'textbook_id')?:'';
            $s3 = $this->curriculum_model->get_one_value(['id' => $s3],'username')?:'';
            $lists[$k]['s3'] = $s3;
            $s4 = $this->course_model->get_one_value(['num'=>4,'teacher_id'=>$v['id'],'arrival' =>['<= time',$arrival],'leave' =>['>= time',$arrival]],'textbook_id')?:'';
            $s4 = $this->curriculum_model->get_one_value(['id' => $s4],'username')?:'';
            $lists[$k]['s4'] = $s4;
            $s5 = $this->course_model->get_one_value(['num'=>5,'teacher_id'=>$v['id'],'arrival' =>['<= time',$arrival],'leave' =>['>= time',$arrival]],'textbook_id')?:'';
            $s5 = $this->curriculum_model->get_one_value(['id' => $s5],'username')?:'';
            $lists[$k]['s5'] = $s5;
            $s6 = $this->course_model->get_one_value(['num'=>6,'teacher_id'=>$v['id'],'arrival' =>['<= time',$arrival],'leave' =>['>= time',$arrival]],'textbook_id')?:'';
            $s6 = $this->curriculum_model->get_one_value(['id' => $s6],'username')?:'';
            $lists[$k]['s6'] = $s6;
            $s7 = $this->course_model->get_one_value(['num'=>7,'teacher_id'=>$v['id'],'arrival' =>['<= time',$arrival],'leave' =>['>= time',$arrival]],'textbook_id')?:'';
            $s7 = $this->curriculum_model->get_one_value(['id' => $s7],'username')?:'';
            $lists[$k]['s7'] = $s7;
            $s8 = $this->course_model->get_one_value(['num'=>8,'teacher_id'=>$v['id'],'arrival' =>['<= time',$arrival],'leave' =>['>= time',$arrival]],'textbook_id')?:'';
            $s8 = $this->curriculum_model->get_one_value(['id' => $s8],'username')?:'';
            $lists[$k]['s8'] = $s8;
            $s9 = $this->course_model->get_one_value(['num'=>9,'teacher_id'=>$v['id'],'arrival' =>['<= time',$arrival],'leave' =>['>= time',$arrival]],'textbook_id')?:'';
            $s9 = $this->curriculum_model->get_one_value(['id' => $s9],'username')?:'';
            $lists[$k]['s9'] = $s9;
        }
        $return_data = [];
        $return_data['code'] = 1;
        $return_data['count'] = $this->teacher_model->get_all_count(['status' => 1]);
        $return_data['data'] = $lists;
        return $return_data;
    }
}