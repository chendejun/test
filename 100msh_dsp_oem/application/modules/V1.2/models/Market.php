<?php
/**
 * restful 短信营销类
 * @author :cdj
 **/
class Market extends  Model
{

    /*
     * 获取签名
     */
    public function getSign($comp_pid)
    {
        $data = $this->db->sms->query("select * from dsp_market_sign WHERE  comp_pid=" . $comp_pid . " and sign_status=1");
        return $data;
    }

    /*
     * 添加签名
     *
     */
    public function addSign($data)
    {
        $info = $this->findSign($data);
        if($info){
            $sign_id = $info['sign_id'];
        }else{
            $sign_id = $this->db->sms->table('dsp_market_sign')->data($data)->insert();
        }

        return $sign_id;
    }
    /*
     * 查询签名是否存在
     */
    public  function findSign($par){
        $info  = $this->db->sms->table('dsp_market_sign')->where($par)->find();
        return $info;
    }

    /*
     * 获取定向包列表
     */
    public function getCrowdlist($comp_pid)
    {
        $data = $this->db->dsp->query("select crowd_id,crowd_name from dsp_crowd_info WHERE  comp_pid=" . $comp_pid . " and audit_status=3 and crowd_type=2");
        return $data;
    }


    public function getMarketeList($where, $limit = '')
    {
        $rel = $this->db->sms->table('dsp_market')->where($where)->order('add_time desc')->limit($limit)->select();
        return $rel;
    }

    /*
     * 获取指定人人群包记录
     */
    public function getCrowdRecord($where)
    {
        $data = $this->db->dsp->table('dsp_crowd_info')->where($where)->find();
        return $data;
    }

    public function getMarketAudit($where)
    {
        //var_dump($where);
        $data = $this->db->sms->table('dsp_market_audit')->where($where)->order('audit_time desc ')->find();

        //var_dump($data);exit;

        return $data;

    }

    /*
     * 获取指定附加人群包记录
     */
    public function getMarketFileInfo($where)
    {
        $data = $this->db->sms->table('dsp_market_file')->where($where)->find();
        return $data;
    }


    /*
     * 获取定向包详情
     */
    public function getCrowdinfo($crowd_id, $comp_pid)
    {
        $data = $this->db->dsp->query("select crowd_id,crowd_name,label_info,cover_users from dsp_crowd_info WHERE  comp_pid=" . $comp_pid . " and crowd_id=" . $crowd_id);
        return $data;
    }

    /*
     * 添加营销短信
     */
    public function add($data)
    {
        $market_id  = $this->db->sms->table('dsp_market')->data($data)->insert();
        return $market_id;
    }
    /*
     * 修改营销信息
     */
    public function edit($market_id,$data){
        $result =  $this->db->sms->table('dsp_market')->where(array('market_id'=>$market_id))->data($data)->update();
        return $result;
    }
    /*
     * 获取营销短信详情
     */
    public function getInfo($market_id,$comp_id){
        $data = $this->db->sms->query("select ma.*,si.sign_name FROM  dsp_market as ma LEFT JOIN dsp_market_sign as si ON ma.sign_id = si.sign_id  where ma.market_id = $market_id and ma.comp_pid=$comp_id");//table('dsp_market')->where(array('market_id'=>$market_id,'comp_id'=>$comp_id))->find();
        $aduit_info = $this->db->sms->query("select * FROM dsp_market_audit WHERE  market_id = $market_id ORDER BY market_audit_id desc limit 1");
        if($aduit_info){
            $data[0]['aduit_remark'] = $aduit_info[0]['audit_remark'];
        }else{
            $data[0]['aduit_remark'] = '';
        }

        return $data;
    }
    /*
     * 获取历史短信列表
     */
    public function  hisMarkets($comp_id){
        $data = $this->db->sms->query("select market_name,market_id,comp_pid from dsp_market WHERE  send_status=1 AND comp_pid=$comp_id");
        return $data;
    }
    /*
     * 检查当天是否使用过五条
     */
    public function getTests($comp_id){
        $add_time = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $count =  $this->db->sms->query("select count(*) as counts from dsp_market_test  WHERE  comp_pid=$comp_id AND  add_time>$add_time");
        return $count;
    }
    /*
     * 获取最近的短信
     */
    public function lastTest($comp_id){
        $count =  $this->db->sms->query("select *  from dsp_market_test  WHERE  comp_pid=$comp_id ORDER BY add_time  desc limit 1");
        return $count;
    }
    /*
     * 插入测试短信
     */
    public function addTest($par){
        $test_id  = $this->db->sms->table('dsp_market_test')->data($par)->insert();
        return $test_id;
    }
    /*
     * 删除短信
     */
    public function del($market_id){
        $result = $this->db->sms->query("DELETE FROM dsp_market WHERE market_id=".$market_id);
        return $result;
    }
    /*
     * 获取文件香型
     */
    public function getFiles($market_file_id){

        $result = $this->db->sms->query("select *  from dsp_market_file  WHERE  market_file_id=$market_file_id ");
        return $result[0];
    }
}
