<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 18:46
 */
Class Bhcreate extends  Model{

	/**
	 * @author	skylon
	 * @desc	添加广告投放状态修改记录
	 * @param array() $data
	 */
    public function add($data){
       return $this->db->bh->table( "bh_app" )->data( $data )->insert(  );
    }
    /*
     *
     */
    public function get($data){

        $data = $this->db->bh->query("SELECT  *    FROM `bh_size_list`  WHERE ad_size_name = '". $data['ad_size_name']."' AND ad_size_width=".$data['ad_size_width']." AND ad_size_height=".$data['ad_size_height']);
        return $data;
    }
    public function adds($data){
        return $this->db->bh->table( "bh_adxs_sizelists" )->data( $data )->insert(  );
    }



}