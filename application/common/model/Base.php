<?php
/*
|--------------------------------------------------------------------------
| 模型底层
|--------------------------------------------------------------------------
*/

namespace app\common\model;

use think\Model;

class Base extends Model
{
    /**
     * 获取所有数据
     * @param array $where
     * @param string $order
     * @param string $field
     * @param array $with
     * @param int $limit
     * @return array|bool
     */
    public function get_all_data($where = [], $order = 'id desc', $field = '*', $with = [],$limit='')
    {
        if(empty($with)){
            $ret = $this->where($where)->field($field)->order($order)->limit($limit)->select();
        }else{
            $ret = $this->with($with)->where($where)->field($field)->order($order)->limit($limit)->select();
        }
        if ($ret) {
            return objToArray($ret);
        } else {
            return false;
        }
    }

    /**
     * 查询某条数据
     * @param array $where
     * @param string $order
     * @param string $field
     * @param array $with
     * @return array|bool
     */
    public function get_one_data($where=[], $order = '', $field = '*', $with = [])
    {
        if(empty($with)) {
            $ret = $this->where($where)->order($order)->field($field)->find();
        }else{
            $ret = $this->with($with)->where($where)->order($order)->field($field)->find();
        }
        if ($ret) {
            return objToArray($ret);
        } else {
            return false;
        }
    }

    /**
     * 获取某个字段值
     * @param array $where
     * @param string $value
     * @param string $order
     * @return string
     */
    public function get_one_value($where, $value = 'id', $order = 'id desc')
    {
        $ret = $this->where($where)->order($order)->value($value);
        return $ret;
    }

    /**
     * 获取某个列的值
     * @param array $where
     * @param string $value
     * @return array|bool
     */
    public function get_one_column($where, $value = 'id')
    {
        $ret = $this->where($where)->column($value);
        if ($ret) {
            return objToArray($ret);
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param array $data
     * @return int|false
     */
    public function add_data($data)
    {
        $ret = $this->isUpdate(false)->allowField(true)->save($data);
        return $ret;
    }

    /**
     * 修改数据
     * @param array $data
     * @param array $where
     * @return int|false
     */
    public function update_data($data, $where)
    {
        $ret = $this->isUpdate(true)->allowField(true)->save($data, $where);
        return $ret;
    }

    /**
     * 删除数据
     * @param array|string $where
     * @return int
     */
    public function delete_data($where)
    {
        $ret = $this->destroy($where);
        return $ret;
    }

    /**
     * 获取分页数据
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param string $order
     * @param string $field
     * @param array $with
     * @return array|false
     */
    public function get_all_data_page($where = [],$page = 1,$limit = 10,$order='id asc', $field = '*', $with = [])
    {
        if (empty($with)) {
            $ret = $this->where($where)->field($field)->order($order)->page($page, $limit)->select();
        } else {
            $ret = $this->with($with)->where($where)->field($field)->order($order)->page($page, $limit)->select();
        }
        
        if ($ret) {
            return objToArray($ret);
        } else {
            return false;
        }
    }
    
    /**
     * 获取数据总量
     * @param array $where
     * @return int|string
     */
    public function get_all_count($where = [],$value = '1')
    {
        if($value != 1){
            $ret = $this->where($where)->distinct(true)->field($value)->count($value);
        }else{
            $ret = $this->where($where)->count($value);
        }
        
        return $ret;
    }

}