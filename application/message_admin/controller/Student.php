<?php
// +----------------------------------------------------------------------
// | p7ing.com [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2029 http://p7ing.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leimon <leimon1314@gmail.com>
// +----------------------------------------------------------------------

// [ 学生管理 ]
namespace app\message_admin\controller;

use app\common\service\Office;

class Student extends Base
{

    //学生模型
    protected $student_model;
    //学科模型
    protected $curriculum_model;
    //寝室模型
    protected $dorm_model;
    //课表模型
    protected $course_model;
    //老师模型
    protected $teacher_model;
    //住宿记录
    protected $dorm_log_model;
    //机构模型
    protected $admin_model;

    public function __construct()
    {
        parent::__construct();
        $this->student_model = model('Student');
        $this->curriculum_model = model('Curriculum');
        $this->dorm_model = model('Dorm');
        $this->course_model = model('Course');
        $this->teacher_model = model('Teacher');
        $this->dorm_log_model = model('DormLog');
        $this->admin_model = model('Admin');
    }

    /**
     * @return \think\response\View
     * 学生列表
     */
    public function student_lists()
    {
        return view();
    }

    /**
     * 获取学生列表数据
     */
    public function get_student_lists()
    {
        //查询条件
        $param = input('');
        $page = isset($param['page'])?(int)$param['page']:1;
        $limit = isset($param['limit'])?(int)$param['limit']:10;
        $student_id = isset($param['student_id'])?$param['student_id']:'';
        $english = isset($param['english'])?$param['english']:'';
        $arrival = isset($param['arrival'])?$param['arrival']:'';
        $status = isset($param['status'])?$param['status']:'';
        //条件组装
        $sqlmap = [];
        if (!empty($student_id)) {
            $sqlmap['student_id'] = $student_id;
        }
        if (!empty($english)) {
            $sqlmap['english'] = ['like', '%' . $english . '%'];
        }
        if (!empty($arrival)) {
            $arrival = explode(' ',$arrival);
            $sqlmap['arrival'] = ['between time',[$arrival[0],$arrival[2]]];
        }
        if (!empty($status)) {
            $sqlmap['status'] = $status;
        }
        //是否为机构用户，机构用户自能查询自己添加的学生
        if(session('type') == 2){
            $sqlmap['mechanism_id'] = session('id');
        }
        //列表信息
        $lists = $this->student_model->get_all_data_page($sqlmap, $page, $limit, 'id desc', 'id,student_id,name,status,phone,english,sex,age,nationality,passport,address,arrival,flight,curriculum_id,days,dorm_id,mechanism_id,leave,remarks,admin_id', ['admin','admins','curriculum','dorm'])?:[];
        foreach ($lists as $k=>$v){
            $lists[$k]['arrival'] = $v['arrival']=='0000-00-00'?'':$v['arrival'];
            $lists[$k]['leave'] = $v['leave']=='0000-00-00'?'':$v['leave'];
            $lists[$k]['course'] = $this->course_model->get_all_count(['student_id'=>$v['id']]);
        }
        $return_data = [];
        $return_data['code'] = 1;
        $return_data['count'] = $this->student_model->get_all_count($sqlmap);
        $return_data['data'] = $lists;
        return $return_data;
    }

    /**
     * 添加学生
     */
    public function student_add()
    {
        $param = input('');
        //生成学号
        $student_id = $this->student_model->get_one_value('','id','id desc')?:0;
        $student_id = "c".str_pad(($student_id+1),5,"0",STR_PAD_LEFT );
        if (request()->post()) {
            $rule = [
                ['name', 'require', 'Name cannot be empty'],
                ['phone', 'require', 'ContactNumber can not be blank'],
                ['sex', 'require', 'Sex can not be blank'],
                ['age', 'require', 'Age can not be blank'],
                ['nationality', 'require', 'Nationality can not be blank'],
                ['curriculum_id', 'require', 'Subject cannot be empty'],
                ['arrival', 'require', 'ArrivalDate cannot be empty'],
                ['leave', 'require', 'LeaveDate cannot be empty'],
                ['dorm_id', 'require', 'RoomNumber cannot be empty']
            ];
            //验证数据
            $result = $this->validate($param, $rule);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
            }
            //组装数据
            $sqlmap = [];
            $sqlmap['student_id'] = $student_id;
            $sqlmap['name']  = isset($param['name'])?$param['name']:'';
            $sqlmap['status']  = isset($param['status'])?$param['status']:1;
            $sqlmap['english']  = isset($param['english'])?$param['english']:'';
            $sqlmap['phone']  = isset($param['phone'])?$param['phone']:'';
            $sqlmap['sex']  = isset($param['sex'])?$param['sex']:'';
            $sqlmap['age']  = isset($param['age'])?$param['age']:'';
            $sqlmap['nationality']  = isset($param['nationality'])?$param['nationality']:'';
            $sqlmap['passport']  = isset($param['passport'])?$param['passport']:'';
            $sqlmap['address']  = isset($param['address'])?$param['address']:'';
            $sqlmap['flight']  = isset($param['flight'])?$param['flight']:'';
            $sqlmap['curriculum_id']  = isset($param['curriculum_id'])?$param['curriculum_id']:'';
            $sqlmap['days']  = isset($param['days'])?$param['days']:'';
            $sqlmap['school']  = isset($param['school'])?$param['school']:'';
            $sqlmap['arrival']  = isset($param['arrival'])?$param['arrival']:'';
            $sqlmap['leave']  = isset($param['leave'])?$param['leave']:'';
            $sqlmap['dorm_id']  = isset($param['dorm_id'])?$param['dorm_id']:'';
            $sqlmap['remarks']  = isset($param['remarks'])?$param['remarks']:'';
            $sqlmap['pay']  = isset($param['pay'])?$param['pay']:1;
            $sqlmap['mechanism_id']  = session('id');
            $sqlmap['admin_id']  = session('id');
            //启动事务
            $this->student_model->startTrans();
            //新增数据
            $student_add = $this->student_model->add_data($sqlmap);
            if(empty($sqlmap['dorm_id'])){
                $dorm_log_add = true;
            }else{
                $log = [];
                $log['student_id'] = $this->student_model->id;
                $log['type'] = 1;
                $log['dorm_id'] = $sqlmap['dorm_id'];
                $log['inputtime'] = strtotime($sqlmap['arrival']);
                $log['leavetime'] = strtotime($sqlmap['leave']);
                $dorm_log_add = $this->dorm_log_model->add_data($log);
            }
            if ($student_add && $dorm_log_add) {
                //提交
                $this->teacher_model->commit();
                $this->success('Added successfully');
            } else {
                //回滚
                $this->teacher_model->rollback();
                $this->error('Add error, please try again');
            }
        }
        //获取学科
        $curriculum_info = $this->curriculum_model->get_all_data(['level' => 0], '', 'id,username')?:[];
        $return_data = [];
        $return_data['student_id'] = $student_id;
        $return_data['curriculum_info'] = $curriculum_info;
        return view('',$return_data);
    }

    /**
     * 编辑学生
     */
    public function student_edit()
    {
        $param = input('');
        $id = isset($param['id'])?(int)$param['id']:0;
        if($id == 0){
            $this->error('Do not access illegally');
        }
        if (request()->post()) {
            $rule = [
                ['name', 'require', 'Name cannot be empty'],
                ['phone', 'require', 'ContactNumber can not be blank'],
                ['sex', 'require', 'Sex can not be blank'],
                ['age', 'require', 'Age can not be blank'],
                ['nationality', 'require', 'Nationality can not be blank'],
                ['curriculum_id', 'require', 'Subject cannot be empty'],
                ['arrival', 'require', 'ArrivalDate cannot be empty'],
                ['leave', 'require', 'LeaveDate cannot be empty'],
                ['dorm_id', 'require', 'RoomNumber cannot be empty']
            ];
            //验证数据
            $result = $this->validate($param, $rule);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
            }
            //组装数据
            $sqlmap = [];
            $sqlmap['name']  = isset($param['name'])?$param['name']:'';
            $sqlmap['status']  = isset($param['status'])?$param['status']:1;
            $sqlmap['english']  = isset($param['english'])?$param['english']:'';
            $sqlmap['phone']  = isset($param['phone'])?$param['phone']:'';
            $sqlmap['sex']  = isset($param['sex'])?$param['sex']:'';
            $sqlmap['age']  = isset($param['age'])?$param['age']:'';
            $sqlmap['nationality']  = isset($param['nationality'])?$param['nationality']:'';
            $sqlmap['passport']  = isset($param['passport'])?$param['passport']:'';
            $sqlmap['address']  = isset($param['address'])?$param['address']:'';
            $sqlmap['flight']  = isset($param['flight'])?$param['flight']:'';
            $sqlmap['curriculum_id']  = isset($param['curriculum_id'])?$param['curriculum_id']:'';
            $sqlmap['days']  = isset($param['days'])?$param['days']:'';
            $sqlmap['school']  = isset($param['school'])?$param['school']:'';
            $sqlmap['arrival']  = isset($param['arrival'])?$param['arrival']:'';
            $sqlmap['leave']  = isset($param['leave'])?$param['leave']:'';
            $sqlmap['dorm_id']  = isset($param['dorm_id'])?$param['dorm_id']:'';
            $sqlmap['remarks']  = isset($param['remarks'])?$param['remarks']:'';
            $sqlmap['pay']  = isset($param['pay'])?$param['pay']:1;
            $sqlmap['admin_id']  = session('id');
            //启动事务
            $this->student_model->startTrans();
            //修改数据
            $student_edit = $this->student_model->update_data($sqlmap,['id' => $id]);
            $dorm_log_info = $this->dorm_log_model->get_one_data(['student_id' => $id]);
            $log = [];
            $log['dorm_id'] = $sqlmap['dorm_id'];
            $log['inputtime'] = strtotime($sqlmap['arrival']);
            $log['leavetime'] = strtotime($sqlmap['leave']);
            if($dorm_log_info){
                if($log['dorm_id'] == $dorm_log_info['dorm_id']){
                    $dorm_log_edit = true;
                }else{
                    $dorm_log_edit = $this->dorm_log_model->update_data($log,['id' => $dorm_log_info['id']]);
                }
            }else{
                $log['student_id'] = $id;
                $log['type'] = 1;
                $dorm_log_edit = $this->dorm_log_model->add_data($log);
            }
            if ($student_edit && $dorm_log_edit) {
                //提交
                $this->teacher_model->commit();
                $this->success('Successfully modified');
            } else {
                //回滚
                $this->teacher_model->rollback();
                $this->error('Edit error, please try again');
            }
        }
        //获取学生信息
        $student_info = $this->student_model->get_one_data(['id'=>$id], 'id desc', 'id,student_id,name,status,phone,english,sex,age,nationality,passport,address,arrival,flight,curriculum_id,days,dorm_id,mechanism_id,school,pay,leave,remarks,admin_id', ['admins','dorm']);
        //获取学科
        $curriculum_info = $this->curriculum_model->get_all_data(['level' => 0], '', 'id,username')?:[];
        //获取可用寝室
        //查询出所有寝室床位
        $sex = $student_info['sex']=="male"?1:2;
        $start = strtotime($student_info['arrival']);
        $dorm = $this->dorm_model->get_all_data(['status' => 1,'sex' => $sex],'','id');
        if(!empty($dorm)){
            foreach ($dorm as $k=>$v)
            {
                //查询寝室床位对应学生
                $student_dorm[] = $this->dorm_log_model->get_all_data(['dorm_id' =>$v['id'],'leavetime' =>['>',$start]],'','id')?'':$v['id'];
            }
            //寝室id
            $dorm_id = implode(',',array_merge(array_filter($student_dorm)));
            if(!empty($dorm_id)){
                $dorm = $this->dorm_model->get_all_data(['id'=>['in',$dorm_id]],'','id,username,type');
            }else{
                $dorm = [];
            }
        }else{
            $dorm = [];
        }
        $return_data = [];
        $return_data['curriculum_info'] = $curriculum_info;
        $return_data['student_info'] = $student_info;
        $return_data['dorm_info'] = $dorm;
        return view('',$return_data);
    }

    /**
     * 床位查询
     * @return \think\response\View
     */
    public function dorm()
    {
        return view();
    }
    /**
     * 获取寝室
     */
    public function student_dorm()
    {
        $param = input('');
        $start = isset($param['start'])?strtotime($param['start']):'';
        $end = isset($param['end'])?$param['end']:'';
        $sex = isset($param['sex'])?$param['sex']:'';
        if(empty($start) || empty($end) || empty($sex)){
            $this->error('Please complete time, gender');
        }
        //查询出所有寝室床位
        $dorm = $this->dorm_model->get_all_data(['status' => 1,'sex' => $sex],'','id');
        if(empty($dorm)){
            $this->error('No bed');
        }
        foreach ($dorm as $k=>$v)
        {
            //查询寝室床位对应学生
            $student_dorm[] = $this->dorm_log_model->get_all_data(['dorm_id' =>$v['id'],'leavetime' =>['>',$start]],'','id')?'':$v['id'];
        }
        //寝室id
        $dorm_id = implode(',',array_merge(array_filter($student_dorm)));
//        $dorm_id = array_search('2',$student_dorm);
        if(empty($dorm_id)){
            $this->error('No bed');
        }
        $dorm = $this->dorm_model->get_all_data(['id'=>['in',$dorm_id]],'','id,username,type');
        $this->success('With bed','',$dorm);
    }

    /**
     * 编辑课程
     * @return \think\response\View
     */
    public function student_curriculum()
    {
        $param = input('');
        $id = isset($param['id'])?(int)$param['id']:0;
        if($id == 0){
            $this->error('Do not access illegally');
        }
        //查询课表信息
        $course_info = $this->course_model->get_all_data(['student_id' => $id],'id asc','id,curriculum_id,teacher_id,textbook_id',['teacher','classroom']);
        //获取学生信息
        $student_info = $this->student_model->get_one_data(['id' => $id],'','id,curriculum_id,arrival,leave');
        //获取课程
        $curriculum_info = $this->curriculum_model->get_all_data(['pid' => $student_info['curriculum_id']],'','id,username')?:[];
        //组装数据
        $return_data = [];
        $return_data['student_id'] = $id;
        if(!empty($course_info)){
            foreach ($course_info as $k=>$v){
                $course_info[$k]['book'] = $this->curriculum_model->get_all_data(['pid' => $v['curriculum_id']],'','id,username')?:[];
            }
            $return_data['course_info'] = $course_info;
            $return_data['curriculum_info'] = $curriculum_info;
            return view('curriculum_edit',$return_data);
        }else{
            $return_data['curriculum_info'] = $curriculum_info;
            return view('curriculum_add',$return_data);
        }
    }

    /**
     * 获取老师
     */
    public function get_teacher($num = '',$curriculum_id='',$student_id='')
    {
        //获取课程信息
        $curriculum_info = $this->curriculum_model->get_one_data(['id' =>$curriculum_id],'','id,username,pid,type');
        $row = [];
        $row['cid'] = $curriculum_id;
        if($curriculum_info['type'] == 2){
            //获取老师
            $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1 and team = 2','','id,username,classroom_id');
            $row['teacher_info'] = $teacher_info;
            return $row;
        }else{
            //查询课表
            $course_info = $this->course_model->get_all_data(['curriculum_id' =>$curriculum_id , 'num' => $num],'','id,leave,teacher_id');
            //获取学生信息
            $student_info = $this->student_model->get_one_data(['id' => $student_id],'','id,arrival');
            if (empty($course_info)) {
                //获取老师
                $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1','','id,username,classroom_id');
                $ids = [];
                foreach ($teacher_info as $k=>$v){
                    $ids[] = $v['id'];
                }
                $ids = implode(",", $ids);
                //查询课表
                $course_info = $this->course_model->get_all_data(['num' => $num,'teacher_id'=>['in',$ids]],'','id,leave,teacher_id');
                if(empty($course_info)){
                    $row['teacher_info'] = $teacher_info;
                    return $row;
                }else{
                    //清除课表内结束时间小于该学生开学时间的记录
                    foreach ($course_info as $k=>$v){
                        if(strtotime($v['leave']) < strtotime($student_info['arrival'])){
                            unset($course_info[$k]);
                        }
                    }
                    if(empty($course_info)){
                        $row['teacher_info'] = $teacher_info;
                        return $row;
                    }else{
                        //获取需要去掉的老师id
                        $ids = [];
                        foreach ($course_info as $k=>$v){
                            $ids[] = $v['teacher_id'];
                        }
                        $ids = implode(",", $ids);
                        //获取老师
                        $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1 and id not in('.$ids.')','','id,username,classroom_id');
                        $row['teacher_info'] = $teacher_info;
                        return $row;
                    }
                }
            }else{
                //获取老师
                $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1','','id,username,classroom_id');
                //清除课表内结束时间小于该学生开学时间的记录
                foreach ($course_info as $k=>$v){
                    if(strtotime($v['leave']) < strtotime($student_info['arrival'])){
                        unset($course_info[$k]);
                    }
                }
                if(empty($course_info)){
                    $row['teacher_info'] = $teacher_info;
                    return $row;
                }else{
                    //获取需要去掉的老师id
                    $ids = [];
                    foreach ($course_info as $k=>$v){
                        $ids[] = $v['teacher_id'];
                    }
                    $ids = implode(",", $ids);
                    //获取老师
                    $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1 and id not in('.$ids.')','','id,username,classroom_id');
                    $row['teacher_info'] = $teacher_info;
                    return $row;
                }
            }
        }
    }

    /**
     * 获取老师 -- 作废
     */
    public function get_teachers()
    {
        $param = input('');
        //选中的第几节
        $num = $param['num'];
        //获取课程信息
        $curriculum_info = $this->curriculum_model->get_one_data(['id' =>$param['curriculum_id']],'','id,username,pid,type');
        //查询课表
        $course_info = $this->course_model->get_all_data(['curriculum_id' =>$param['curriculum_id'] , 'num' => $num],'','id,leave,teacher_id');
        $row = [];
        $row['cid'] = $param['curriculum_id'];
        //获取学生信息
        $student_info = $this->student_model->get_one_data(['id' => $param['student_id']],'','id,arrival');
        if (empty($course_info)) {
            if($curriculum_info['type'] == 2){
                //获取老师
                $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1 and team = 2','','id,username,classroom_id');
            }else{
                //获取老师
                $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1','','id,username,classroom_id');
            }
            $ids = [];
            foreach ($teacher_info as $k=>$v){
                $ids[] = $v['id'];
            }
            $ids = implode(",", $ids);
            //查询课表
            $course_info = $this->course_model->get_all_data(['num' => $num,'teacher_id'=>['in',$ids]],'','id,leave,teacher_id');
            if(empty($course_info)){
                $row['teacher_info'] = $teacher_info;
                return $row;
            }else{
                //清除课表内结束时间小于该学生开学时间的记录
                foreach ($course_info as $k=>$v){
                    if(strtotime($v['leave']) < strtotime($student_info['arrival'])){
                        unset($course_info[$k]);
                    }
                }
                if(empty($course_info)){
                    $row['teacher_info'] = $teacher_info;
                    return $row;
                }else{
                    //获取需要去掉的老师id
                    $ids = [];
                    foreach ($course_info as $k=>$v){
                        $ids[] = $v['teacher_id'];
                    }
                    $ids = implode(",", $ids);
                    if($curriculum_info['type'] == 2){
                        //获取老师
                        $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1 and team = 2 and id not in('.$ids.')','','id,username,classroom_id');
                    }else{
                        //获取老师
                        $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1 and id not in('.$ids.')','','id,username,classroom_id');
                    }
                    $row['teacher_info'] = $teacher_info;
                    return $row;
                }
            }
        }else{
            if($curriculum_info['type'] == 2){
                //获取老师
                $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1 and team = 2','','id,username,classroom_id');
            }else{
                //获取老师
                $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1','','id,username,classroom_id');
            }
            //清除课表内结束时间小于该学生开学时间的记录
            foreach ($course_info as $k=>$v){
                if(strtotime($v['leave']) < strtotime($student_info['arrival'])){
                    unset($course_info[$k]);
                }
            }
            if(empty($course_info)){
                $row['teacher_info'] = $teacher_info;
                return $row;
            }else{
                //获取需要去掉的老师id
                $ids = [];
                foreach ($course_info as $k=>$v){
                    $ids[] = $v['teacher_id'];
                }
                $ids = implode(",", $ids);
                if($curriculum_info['type'] == 2){
                    //获取老师
                    $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1 and team = 2 and id not in('.$ids.')','','id,username,classroom_id');
                }else{
                    //获取老师
                    $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1 and id not in('.$ids.')','','id,username,classroom_id');
                }
                $row['teacher_info'] = $teacher_info;
                return $row;
            }
        }
    }

    /**
     * 获取教室和课本
     */
    public function get_textbook($teacher_id='',$curriculum_id='')
    {
        //获取教室
        $teacher_info = $this->teacher_model->get_one_data(['id' => $teacher_id],'','classroom_id',['classroom']);
        //获取课本
        $textbook_info = $this->curriculum_model->get_all_data(['pid' =>$curriculum_id]);
        $row = [];
        $row['teacher_info'] = $teacher_info;
        $row['textbook_info'] = $textbook_info;
        return $row;
    }

    /**
     * 执行课程分配
     */
    public function student_curriculum_add()
    {
        $param = input('');
        if (request()->post()) {
            $rule = [
                ['curriculum', 'require', 'Course cannot be empty'],
                ['teacher', 'require', 'Teacher cannot be empty'],
                ['textbook', 'require', 'Textbook cannot be empty'],
            ];
            //验证数据
            $result = $this->validate($param, $rule);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
            }
            //获取学生信息
            $student_info = $this->student_model->get_one_data(['id' => $param['student_id']],'','id,arrival,leave');
            //组装数据
            $sqlmap = [];
            foreach ($param['curriculum'] as $k=>$v){
                $sqlmap[$k]['student_id'] = $param['student_id'];
                $sqlmap[$k]['curriculum_id'] = $v;
                $sqlmap[$k]['teacher_id'] = $param['teacher'][$k];
                $sqlmap[$k]['textbook_id'] = $param['textbook'][$k];
                $sqlmap[$k]['num'] = $k;
                $sqlmap[$k]['arrival'] = $student_info['arrival'];
                $sqlmap[$k]['leave'] = $student_info['leave'];
                $sqlmap[$k]['input_time'] = time();
                $sqlmap[$k]['update_time'] = time();
            }
            //新增数据
            $ret = $this->course_model->saveAll($sqlmap);
            if ($ret) {
                $this->success('Edited successfully');
            } else {
                $this->error('Edit error, please try again');
            }
        }
    }

    /**
     * 执行课程修改
     */
    public function student_curriculum_edit()
    {
        $param = input('');
        if (request()->post()) {
            $rule = [
                ['curriculum', 'require', 'Course cannot be empty'],
                ['teacher', 'require', 'Teacher cannot be empty'],
                ['textbook', 'require', 'Textbook cannot be empty'],
            ];
            //验证数据
            $result = $this->validate($param, $rule);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
            }
            //组装数据
            $sqlmap = [];
            foreach ($param['id'] as $k=>$v){
                $sqlmap[$k]['id'] = $v;
                $sqlmap[$k]['curriculum_id'] = $param['curriculum'][$k];
                $sqlmap[$k]['teacher_id'] = $param['teacher'][$k];
                $sqlmap[$k]['textbook_id'] = $param['textbook'][$k];
            }
            //修改数据
            $ret = $this->course_model->isUpdate()->saveAll($sqlmap);
            if ($ret) {
                $this->success('Edited successfully');
            } else {
                $this->error('Edit error, please try again');
            }
        }
    }

    /**
     * 自动排课
     */
    public function curriculum_automatic()
    {
        $param = input('');
        //获取学生信息
        $student_info = $this->student_model->get_one_data(['id' => $param['student_id']],'','id,curriculum_id,arrival,leave');
        //获取学科对应课程节数
        $xk_info = $this->curriculum_model->get_one_data(['id' => $student_info['curriculum_id']],'','id,username,one,team');
        //获取一对一课程
        $kc_one = $this->curriculum_model->get_all_data(['pid' => $student_info['curriculum_id'],'type' => 1],'','id,username');
        //获取团体课程
        $kc_team = $this->curriculum_model->get_all_data(['pid' => $student_info['curriculum_id'],'type' => 2],'','id,username');
        //课程数
        $num = ['0'=>'','1'=>'','2'=>'','3'=>'','4'=>'','5'=>'','6'=>'','7'=>''];
        //放入课程
        $data = [];
        for ($i=1;$i<=$xk_info['one'];$i++){
            $rand = array_rand($num);
            $data[$rand] = $kc_one[array_rand($kc_one)]['id'];
            unset($num[$rand]);
        }
        for ($i=1;$i<=$xk_info['team'];$i++){
            $rand = array_rand($num);
            $data[$rand] = $kc_team[array_rand($kc_team)]['id'];
            unset($num[$rand]);
        }
        //依靠键排正序
        ksort($data);
        //组合课程
        $rows = [];
        for ($i=1;$i<10;$i++){
            for ($j=0;$j<count($data);$j++){
                //查询老师
                $teacher = $this->get_teacher($i,$data[$j],$student_info['id']);
                //是否有老师
                if($teacher['teacher_info']){
                    //选择老师
                    $count = count($teacher['teacher_info']);
                    if($count > 1){
                        //组装每个老师目前有教多少课
                        $teacher_num = [];
                        foreach ($teacher['teacher_info'] as $k=>$v) {
                            $teacher_num[$v['id']] = $this->course_model->get_all_count(['teacher_id'=>$v['id']]);
                        }
                        //找出最小值
                        $min = min($teacher_num);
                        //找出课最少的老师
                        $teacher_min = [];
                        foreach($teacher_num as $k=>$v){
                            if($v == $min){
                                $teacher_min[$k] = $v;
                            }
                        }
                        $rows[$i]['teacher_id'] = array_rand($teacher_min);
                    }else{
                        $rows[$i]['teacher_id'] = $teacher['teacher_info'][0]['id'];
                    }
                    $rows[$i]['curriculum_id'] = $data[$j];
                    $rows[$i]['student_id'] = $student_info['id'];
                    $rows[$i]['arrival'] = $student_info['arrival'];
                    $rows[$i]['leave'] = $student_info['leave'];
                    $rows[$i]['num'] = $i;
                    unset($data[$j]);
                    $data = array_merge($data);
                    break;
                }
                //达到最大时没有找到老师，填充为自习
                if($j == count($data) && empty($rows[$i])){
                    $rows[$i]['curriculum_id'] = 0;
                    $rows[$i]['teacher_id'] = 0;
                    $rows[$i]['textbook_id'] = 0;
                    $rows[$i]['student_id'] = $student_info['id'];
                    $rows[$i]['arrival'] = $student_info['arrival'];
                    $rows[$i]['leave'] = $student_info['leave'];
                    $rows[$i]['num'] = $i;
                }
            }
        }
        if(count($rows) == 8){
            $rows[9]['curriculum_id'] = 0;
            $rows[9]['teacher_id'] = 0;
            $rows[9]['textbook_id'] = 0;
            $rows[9]['student_id'] = $student_info['id'];
            $rows[9]['arrival'] = $student_info['arrival'];
            $rows[9]['leave'] = $student_info['leave'];
            $rows[9]['num'] = 9;
        }
        //新增数据
        $ret = $this->course_model->saveAll($rows);
        if ($ret) {
            $this->success('Automatic class schedule succeeded');
        } else {
            $this->error('Auto-schedule failed, please try again');
        }
    }

    /**
     * 学生导入
     */
    public function import()
    {
        if (request()->isAjax()) {
            $file = request()->file('file');
            $file_info = $file->getInfo();
            $type = $file_info['type'];
            if($type == 'application/vnd.ms-excel' || $type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
                $name = explode(".",$file_info['name']);
                if($name['0'] == '学生信息' && ($name[1] == 'xlsx' || $name[1] == 'xls')){
                    // 移动到框架应用根目录/public/uploads/ 目录下
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
                    if ($info) {
                        $src = 'upload' . '/' . date('Ymd') . '/' . $info->getFilename();
                        $excel = new Office();
                        //读取EXCEL表数据
                        $row = $excel->importExecl($src);
                        //清楚第一行
                        unset($row[1]);
                        //组装为自己的数据
                        foreach ($row as $k=>$v){
                            $data[$k]['name'] = $v['A'];
                            $data[$k]['english'] = $v['B'];
                            $data[$k]['arrival'] = gmdate('Y-m-d', ($v['C'] - 25569) * 86400);
                            $data[$k]['leave'] = gmdate('Y-m-d', ($v['D'] - 25569) * 86400);
                            $data[$k]['age'] = $v['E'];
                            $data[$k]['sex'] = $v['F'];
                            $data[$k]['days'] = $v['G'];
                            $data[$k]['passport'] = $v['H'];
                            $data[$k]['curriculum_id'] = $v['I'];
                            $data[$k]['phone'] = $v['J'];
                            $data[$k]['address'] = $v['K'];
                            $data[$k]['flight'] = $v['L'];
                            $data[$k]['mechanism_id'] = $v['M'];
                            $data[$k]['remarks'] = $v['N'];
                            $data[$k]['admin_id'] = session('id');
                        }
                        //生成学号
                        $student_id = $this->student_model->get_one_value('','id','id desc')?:0;
                        foreach ($data as $k=>$v){
                            $i = $k-1;
                            $data[$k]['student_id'] = "c".str_pad(($student_id+$i),5,"0",STR_PAD_LEFT );
                            $data[$k]['sex'] = $v['sex']=='male'?1:2;
                            $data[$k]['curriculum_id'] = $this->curriculum_model->get_one_value(['username' => $v['curriculum_id']],'id')?:'';
                            $data[$k]['mechanism_id'] = $this->admin_model->get_one_value(['username' => $v['mechanism_id']],'id')?:'';
                        }
                        $import_student = $this->student_model->saveAll($data);
                        if($import_student){
                            $this->success('Import succeeded');
                        }else{
                            $this->error('Import failed, please try again');
                        }
                    } else {
                        // 上传失败获取错误信息
                        $this->error('Import failed, please try again');
                    }
                }else{
                    $this->error('Import failed, please try again');
                }
            }else{
                $this->error('Import failed, please try again');
            }
        }
    }
}