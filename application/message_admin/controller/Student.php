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

    public function __construct()
    {
        parent::__construct();
        $this->student_model = model('Student');
        $this->curriculum_model = model('Curriculum');
        $this->dorm_model = model('Dorm');
        $this->course_model = model('Course');
        $this->teacher_model = model('Teacher');
        $this->dorm_log_model = model('DormLog');
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
            $sqlmap['arrival'] = ['= time',$arrival];
        }
        if (!empty($status)) {
            $sqlmap['status'] = $status;
        }
        //是否为机构用户，机构用户自能查询自己添加的学生
        if(session('type') == 2){
            $sqlmap['mechanism_id'] = session('id');
        }
        //列表信息
        $lists = $this->student_model->get_all_data_page($sqlmap, $page, $limit, 'id desc', 'id,student_id,name,status,phone,english,sex,age,nationality,passport,address,arrival,flight,curriculum_id,days,dorm_id,mechanism_id,leave,remarks,admin_id', ['admin','admins','curriculum','dorm']);
        foreach ($lists as $k=>$v){
            $lists[$k]['arrival'] = $v['arrival']=='0000-00-00'?'':$v['arrival'];
            $lists[$k]['leave'] = $v['leave']=='0000-00-00'?'':$v['leave'];
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
        $student_id = $this->student_model->get_one_value('','id','id desc');
        $student_id = "c".str_pad(($student_id+1),5,"0",STR_PAD_LEFT );
        if (request()->post()) {
            $rule = [
                ['name', 'require', '姓名不能为空'],
                ['phone', 'require', '联系电话不能为空'],
                ['curriculum_id', 'require', '学科不能为空'],
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
                $this->success('添加成功');
            } else {
                //回滚
                $this->teacher_model->rollback();
                $this->error('添加出错，请重试');
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
            $this->error('请勿非法访问');
        }
        if (request()->post()) {
            $rule = [
                ['name', 'require', '姓名不能为空'],
                ['phone', 'require', '联系电话不能为空'],
                ['curriculum_id', 'require', '学科不能为空'],
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
                $dorm_log_edit = $this->dorm_log_model->update_data($log,['id' => $dorm_log_info['id']]);
            }else{
                $log['student_id'] = $id;
                $log['type'] = 1;
                $dorm_log_edit = $this->dorm_log_model->add_data($log);
            }
            if ($student_edit && $dorm_log_edit) {
                //提交
                $this->teacher_model->commit();
                $this->success('修改成功');
            } else {
                //回滚
                $this->teacher_model->rollback();
                $this->error('修改出错，请重试');
            }
        }
        //获取学生信息
        $student_info = $this->student_model->get_one_data(['id'=>$id], 'id desc', 'id,student_id,name,status,phone,english,sex,age,nationality,passport,address,arrival,flight,curriculum_id,days,dorm_id,mechanism_id,school,pay,leave,remarks,admin_id', ['admins','dorm']);
        //获取学科
        $curriculum_info = $this->curriculum_model->get_all_data(['level' => 0], '', 'id,username')?:[];
        $return_data = [];
        $return_data['curriculum_info'] = $curriculum_info;
        $return_data['student_info'] = $student_info;
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
        $start = isset($param['start'])?$param['start']:'';
        $end = isset($param['end'])?$param['end']:'';
        $sex = isset($param['sex'])?$param['sex']:'';
        if(empty($start) || empty($end) || empty($sex)){
            $this->error('请完整时间,性别！');
        }
        //查询出所有寝室床位
        $dorm = $this->dorm_model->get_all_data(['status' => 1,'type' => $sex],'','id');
        if(empty($dorm)){
            $this->error('无床位！');
        }
        foreach ($dorm as $k=>$v)
        {
            //查询寝室床位对应学生
            $student_dorm[$v['id']] = $this->student_model->get_all_data(['dorm_id' =>$v['id'],'leave' =>['>= time',$start]],'','id')?1:2;
        }
        //寝室id
        $dorm_id = array_search('2',$student_dorm);
        if(empty($dorm_id)){
            $this->error('无床位！');
        }
        $dorm = $this->dorm_model->get_one_data(['id'=>$dorm_id]);
        $this->success('有床位','',$dorm);
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
            $this->error('请勿非法访问');
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
    public function get_teacher()
    {
        $param = input('');
        //选中的第几节
        $num = $param['num'];
        //获取课程信息
        $curriculum_info = $this->curriculum_model->get_one_data(['id' =>$param['curriculum_id']],'','id,username,pid,type');
        $row = [];
        $row['cid'] = $param['curriculum_id'];
        if($curriculum_info['type'] == 2){
            //获取老师
            $teacher_info = $this->teacher_model->get_all_data('find_in_set('.$curriculum_info['pid'].',curriculum) and status = 1 and team = 2','','id,username,classroom_id');
            $row['teacher_info'] = $teacher_info;
            return $row;
        }else{
            //查询课表
            $course_info = $this->course_model->get_all_data(['curriculum_id' =>$param['curriculum_id'] , 'num' => $num],'','id,leave,teacher_id');
            //获取学生信息
            $student_info = $this->student_model->get_one_data(['id' => $param['student_id']],'','id,arrival');
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
    public function get_textbook()
    {
        $param = input('');
        //获取教室
        $teacher_info = $this->teacher_model->get_one_data(['id' => $param['teacher_id']],'','classroom_id',['classroom']);
        //获取课本
        $textbook_info = $this->curriculum_model->get_all_data(['pid' =>$param['curriculum_id']]);
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
                ['curriculum', 'require', '课程不能为空'],
                ['teacher', 'require', '老师不能为空'],
                ['textbook', 'require', '课本不能为空'],
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
                $this->success('编辑成功');
            } else {
                $this->error('编辑出错，请重试');
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
                ['curriculum', 'require', '课程不能为空'],
                ['teacher', 'require', '老师不能为空'],
                ['textbook', 'require', '课本不能为空'],
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
            //新增数据
            $ret = $this->course_model->isUpdate()->saveAll($sqlmap);
            if ($ret) {
                $this->success('编辑成功');
            } else {
                $this->error('编辑出错，请重试');
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
        $kc_one =  $this->curriculum_model->get_all_data(['pid' => $student_info['curriculum_id'],'type' => 1],'','id,username');
        //获取团体课程
        $kc_team =  $this->curriculum_model->get_all_data(['pid' => $student_info['curriculum_id'],'type' => 2],'','id,username');
        //组合数据
        $data = [];
        $one = 1;
        $team = 1;
        for ($i=1;$i<10;$i++){
            $data[$i]['student_id'] = $student_info['id'];
            $data[$i]['num'] = $i;
            $data[$i]['arrival'] = $student_info['arrival'];
            $data[$i]['leave'] = $student_info['leave'];
            if($one <= $xk_info['one']){
                //课程
                $data[$i]['curriculum_id'] = $kc_one[$i];
                //老师
                $data[$i]['teacher_id'] = 'one'.$i;
                //课本
                $data[$i]['teacher'] = 'one'.$i;
                $one++;
                continue;
            }
            if($team <= $xk_info['team']){
                //课程
                $data[$i]['curriculum_id'] = 'team'.$i;
                //老师
                $data[$i]['teacher_id'] = 'team'.$i;
                //课本
                $data[$i]['teacher'] = 'team'.$i;
                $team++;
                continue;
            }
            //课程
            $data[$i]['curriculum_id'] = 0;
            //老师
            $data[$i]['teacher_id'] = 0;
            //课本
            $data[$i]['teacher'] = [];
        }
        dump($data);exit;
    }

}