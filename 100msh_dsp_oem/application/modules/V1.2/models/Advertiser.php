<?php
class Advertiser extends Model{
    function addAdvertiser($data){
        $comp_id  = $this->db->dsp->table("dsp_company")->data($data)->insert();
        return $comp_id;
    }
    function addCompImg($data){
        $img_id  = $this->db->dsp->table("dsp_comp_img")->data($data)->insert();
        return $img_id;
    }
    function getCompInfo($comp_id){
        $res  = $this->db->dsp->table("dsp_company")->where('comp_id='.$comp_id)->find();
        return $res;
    }
    function getCateList($cate_id){
        $res  = $this->db->dsp->table("dsp_comp_category")->where('cate_pid='.$cate_id)->select();
        return $res;
    }
    function getScList($sc_id){
        $res  = $this->db->dsp->table("dsp_statecity")->where('sc_pid='.$sc_id)->select();
        return $res;
    }
    function getAdvInfo($comp_id){
        $sql="SELECT a.*,b.gdt_account_id,b.daily_budget,b.system_status,b.account_type,c.trademark_img,c.business_img,c.front_card,c.back_card,icp_img,c.industry_img,c.apt_more FROM dsp_company a LEFT JOIN gdt_company b ON a.comp_id=b.comp_id LEFT JOIN dsp_comp_img c ON a.comp_id=c.comp_id WHERE a.comp_id=".$comp_id;
        $res  = $this->db->dsp->query($sql);
        return $res[0];
    }
    function getCateInfo($cate_id){
        $res  = $this->db->dsp->table("dsp_comp_category")->where('cate_id='.$cate_id)->find();
        return $res;
    }
    function getScInfo($sc_id){
        $res  = $this->db->dsp->table("dsp_statecity")->where('sc_id='.$sc_id)->find();
        return $res;
    }
    function getGdtDls(){
        $res  = $this->db->dsp->table("gdt_company")->where('account_type=3')->find();
        return $res;
    }
//    function getTokenInfo($gdt_account_id){
//        $res  = $this->db->dsp->table("gdt_auth_token")->where('gdt_account_id='.$gdt_account_id)->find();
//        return $res;
//    }
    function getGdtCompInfo($comp_id){
        $res  = $this->db->dsp->table("gdt_company")->where('comp_id='.$comp_id)->find();
        return $res;
    }
    /**
     * 授权信息添加
     **/
    public function addToken($data){
        return $this->db->dsp->table('gdt_auth_token')->data($data)->insert();
    }
    /**
     * 授权信息修改
     **/
    public function upToken($gdt_account_id,$data){
        return $this->db->dsp->table('gdt_auth_token')->data($data)->where('gdt_account_id ='.$gdt_account_id)->update();
    }
    /**
     * 更新广点通账户
     **/
    public function upGdtComp($gdt_account_id,$data){
        return $this->db->dsp->table('gdt_company')->data($data)->where('gdt_account_id ='.$gdt_account_id)->update();
    }
    public function getGdtComp($gdt_account_id){
        return $this->db->dsp->table('gdt_company')->where('gdt_account_id ='.$gdt_account_id)->find();
    }
    public function addGdtComp($data){
        return $this->db->dsp->table('gdt_company')->data($data)->insert();
    }
    public function getNewCompIds(){
        $ids="";
        $res = $this->db->dsp->table('gdt_company')->where('account_type =0')->select();
        if(!empty($res)){
            foreach ($res as $k=>$v){
                $ids .= $v['comp_id'].",";
            }
            $ids=trim($ids,',');
        }
        return $ids;
    }
    public function getZkCompIds(){
        $ids="";
        $res = $this->db->dsp->table('gdt_company')->where('account_type =1')->select();
        if(!empty($res)){
            foreach ($res as $k=>$v){
                $ids .= $v['comp_id'].",";
            }
            $ids=trim($ids,',');
        }
        return $ids;
    }
    /**
     * 授权信息列表
     **/
    public function getTokenList($where="1"){
        return $this->db->dsp->table('gdt_auth_token')->where($where)->select();
    }
    public function getTokenByAcc($gdt_account_id){
        return $this->db->dsp->table('gdt_auth_token')->where('gdt_account_id ='.$gdt_account_id)->find();
    }
    public function upComp($comp_id,$data){
        return $this->db->dsp->table('dsp_company')->data($data)->where('comp_id ='.$comp_id)->update();
    }
    public function upCompGdt($where,$data){
        return $this->db->dsp->table('dsp_company')->data($data)->where($where)->update();
    }
    public function getZkList(){
        return $this->db->dsp->table('gdt_company')->where('account_type =1')->select();
    }
    public function getGdtList(){
        return $this->db->dsp->table('gdt_company')->where('system_status =2')->select();
    }
    public function addGdtWxBind($data){
        return $this->db->dsp->table('gdt_wx_bind')->data($data)->insert();
    }
    public function getCompList($where){
        return $this->db->dsp->table("dsp_company")->where($where)->select();
    }
    public function getMacCompList($where){
        
        return $this->db->dsp->table("dsp_mac_list",false)->field("comp_id")->where($where)->select();
    }
    public function insetCate($sql){
        return $this->db->dsp->excute($sql);
    }
    public function getCompByN($comp_name){
        $comp_info = $this->db->dsp->query("SELECT comp_id FROM dsp_company WHERE comp_type =1 AND `comp_name` = '{$comp_name}'");
        if(!empty($comp_info)){
            return $comp_info;
        }
        return array();
    }

}
