<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 老师管理 ]
namespace app\message_admin\controller;

class Teacher extends Base
{

    //老师模型
    protected $teacher_model;
    //教室模型
    protected $classroom_model;
    //学科模型
    protected $curriculum_model;

    public function __construct()
    {
        parent::__construct();
        $this->teacher_model = model('Teacher');
        $this->classroom_model = model('Classroom');
        $this->curriculum_model = model('Curriculum');
    }

    /**
     * @return \think\response\View
     * 老师列表
     */
    public function teacher_lists()
    {
        return view();
    }

    /**
     * 获取老师列表数据
     */
    public function get_teacher_lists()
    {
        //查询条件
        $param = input('');
        $page = isset($param['page'])?(int)$param['page']:1;
        $limit = isset($param['limit'])?(int)$param['limit']:10;
        //条件组装
        $sqlmap = [];
        //列表信息
        $lists = $this->teacher_model->get_all_data_page($sqlmap, $page, $limit, 'id desc', 'id,identifier,username,classroom_id,curriculum,team,status,input_time',['classroom'])?:[];
        foreach ($lists as $k=>$v) {
            $curriculum = explode(',',$v['curriculum']);
            $curriculums = '';
            foreach ($curriculum as $z=>$x) {
                $curriculums .= $this->curriculum_model->get_one_value(['id'=>$x],'username').'、';
            }
            if(!empty($curriculums)){
                $curriculums = rtrim($curriculums, "、") ;
            }
            $lists[$k]['curriculum'] = $curriculums;
        }
        $return_data = [];
        if(empty($lists)){
            $return_data['code'] = 0;
            $return_data['data'] = [];
            $return_data['msg'] = 'no data';
        }else{
            $return_data['code'] = 1;
            $return_data['count'] = $this->teacher_model->get_all_count($sqlmap);
            $return_data['data'] = $lists;
        }
        return $return_data;
    }

    /**
     * 添加老师
     */
    public function teacher_add()
    {
        $param = input('');
        if (request()->post()) {
            $rule = [
                ['identifier', 'require', 'Number cannot be empty'],
                ['username', 'require', 'Teacher cannot be empty'],
                ['classroom_id', 'require', 'Classroom cannot be empty'],
                ['curriculum', 'require', 'Subject cannot be empty'],
                ['team', 'require', 'Group class cannot be empty'],
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
            $sqlmap['identifier'] = $param['identifier'];
            $sqlmap['username'] = $param['username'];
            $sqlmap['classroom_id'] = $param['classroom_id'];
            $sqlmap['curriculum'] = implode(',', $param['curriculum']);
            $sqlmap['team'] = $param['team'];
            $sqlmap['status'] = $param['status'];
            //启动事务
            $this->teacher_model->startTrans();
            //新增数据
            $teacher_add = $this->teacher_model->add_data($sqlmap);
            //更新教室所属
            $classroom_edit = $this->classroom_model->update_data(['teacher' => 2],['id' => $param['classroom_id']]);
            if ($teacher_add && $classroom_edit) {
                //提交
                $this->teacher_model->commit();
                $this->success('Added successfully');
            } else {
                //回滚
                $this->teacher_model->rollback();
                $this->error('Add error, please try again');
            }
        }
        //获取教室
        $classroom_info = $this->classroom_model->get_all_data(['status' => 1,'teacher' => 1], '', 'id,username')?:[];
        //获取学科
        $curriculum_info = $this->curriculum_model->get_all_data(['level' => 0], '', 'id,username')?:[];
        $return_data = [];
        $return_data['classroom_info'] = $classroom_info;
        $return_data['curriculum_info'] = $curriculum_info;
        return view('',$return_data);
    }

    /**
     * 编辑老师
     */
    public function teacher_edit()
    {
        $param = input('');
        $id = isset($param['id'])?(int)$param['id']:0;
        if($id == 0){
            $this->error('Do not access illegally');
        }
        //获取老师信息
        $teacher_info = $this->teacher_model->get_one_data(['id'=>$id]);
        if (request()->post()) {
            $rule = [
                ['identifier', 'require', 'Number cannot be empty'],
                ['username', 'require', 'Teacher cannot be empty'],
                ['classroom_id', 'require', 'Classroom cannot be empty'],
                ['curriculum', 'require', 'Subject cannot be empty'],
                ['team', 'require', 'Group class cannot be empty'],
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
            $sqlmap['identifier'] = $param['identifier'];
            $sqlmap['username'] = $param['username'];
            $sqlmap['classroom_id'] = $param['classroom_id'];
            $sqlmap['curriculum'] = implode(',', $param['curriculum']);
            $sqlmap['team'] = $param['team'];
            $sqlmap['status'] = $param['status'];
            //启动事务
            $this->teacher_model->startTrans();
            //更改数据
            $teacher_edit = $this->teacher_model->update_data($sqlmap,['id' => $id]);
            if($sqlmap['classroom_id'] != $teacher_info['classroom_id']){
                //更新教室所属
                $classroom_new = $this->classroom_model->update_data(['teacher' => 2],['id' => $sqlmap['classroom_id']]);
                $classroom_old = $this->classroom_model->update_data(['teacher' => 1],['id' => $teacher_info['classroom_id']]);
            }else{
                $classroom_new = true;
                $classroom_old = true;
            }
            if ($teacher_edit && $classroom_new && $classroom_old) {
                //提交
                $this->teacher_model->commit();
                $this->success('Successfully modified');
            } else {
                //回滚
                $this->teacher_model->rollback();
                $this->error('Edit error, please try again');
            }
        }
        $teacher_info['curriculum'] = explode(',',$teacher_info['curriculum']);
        $teacher_classroom = $this->classroom_model->get_one_data(['id'=>$teacher_info['classroom_id']]);
        //获取教室
        $classroom_info = $this->classroom_model->get_all_data(['status' => 1,'teacher' => 1], '', 'id,username')?:[];
        //获取学科
        $curriculum_info = $this->curriculum_model->get_all_data(['level' => 0], '', 'id,username')?:[];
        $return_data = [];
        $return_data['classroom_info'] = $classroom_info;
        $return_data['curriculum_info'] = $curriculum_info;
        $return_data['teacher_info'] = $teacher_info;
        $return_data['teacher_classroom'] = $teacher_classroom;
        return view('',$return_data);
    }

}