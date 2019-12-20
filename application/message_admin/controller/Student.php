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

    public function __construct()
    {
        parent::__construct();
        $this->student_model = model('Student');
        $this->curriculum_model = model('Curriculum');
        $this->dorm_model = model('Dorm');
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
        //列表信息
        $lists = $this->student_model->get_all_data_page($sqlmap, $page, $limit, 'id desc', 'id,student_id,name,status,phone,english,sex,age,nationality,passport,address,arrival,flight,curriculum_id,days,dorm_id,mechanism_id,leave,remarks,admin_id', ['admin','admins','curriculum','dorm']);
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
            //新增数据
            $ret = $this->student_model->add_data($sqlmap);
            if ($ret) {
                $this->success('添加成功');
            } else {
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
        $this->success('ok','',$dorm);
    }

}