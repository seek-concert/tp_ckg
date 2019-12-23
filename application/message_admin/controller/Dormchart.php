<?php
namespace app\message_admin\controller;
use think\Db;

/**
 * 寝室列表图表
 * Class Dormchart
 * @package app\message_admin\controller
 */
class Dormchart extends Base
{
    protected $dorm_model;
    protected $student_model;
    /**
     * Dormchart constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->dorm_model = model('Dorm');
        $this->student_model = model('Student');
    }

    /**
     * 列表
     * @return \think\response\View
     */
    public function lists(){
        $data = [];
        $year =  Db::query("select distinct date_format(arrival ,'%Y') as 'years' from pq_student");
         $new_year_arr =[];
         if($year){
             foreach($year as $k=>$v){
                 $new_year_arr[] = $v['years'];
             }
         }

        $data['years_arr'] = $new_year_arr;
        $y=date('Y');
        $m=date('m');
        $data['years'] = $y;
        $data['months'] = $m;
        $data['days'] =  date("t",strtotime("$y-$m"));
        return view('',$data);
    }

    /**
     * 获取数据
     * @return array
     */
    public function get_dorm_chart(){
        //查询条件
        $param = input('');
        $page = isset($param['page'])?(int)$param['page']:1;
        $limit = isset($param['limit'])?(int)$param['limit']:10;
        $year = isset($param['year'])?(int)$param['year']:2019;
        $month = isset($param['month'])?(int)$param['month']:1;
        //条件组装
        $sqlmap = [];
        $sqlmap['status'] = 1;
        //列表信息
        $lists = $this->student_model->alias('s')
            ->field([])
            ->join('pq_dorm d ','d.id=s.dorm_id','LEFT')
            ->select();
        $return_data = [];
        $return_data['code'] = 1;
        $return_data['count'] = $this->dorm_model->get_all_count($sqlmap);
        $return_data['data'] = $lists;
        return $return_data;

    }
}