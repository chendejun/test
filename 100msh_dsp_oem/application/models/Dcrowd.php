<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/26
 * Time: 17:22
 */
Class Dcrowd extends Model{
    /**
     * 查询人群信息总数
     */
    public function getCrowdCount($where="1"){
        //var_dump($this->db->dsp);
        $result = $this->db->dsp->query("SELECT count(1) as num FROM `dsp_crowd_info` AS cri WHERE {$where}");
        return empty($result)?0:$result[0]['num'];
    }
    /**
     * 人群信息列表 111
     * @param $where
     * @param int $start
     * @param int $limit
     * @return mixed
     */
    public function getCrowdlList($where , $start = 0 , $limit=15){
        $result = $this->db->dsp->query("SELECT crowd_id,crowd_name,crowd_taskid,audit_status,cover_users,download_nums,if_download,staff_name,FROM_UNIXTIME(cri.add_time,'%Y-%m-%d %H:%i') as add_time,if(cri.finish_time=0,'~',FROM_UNIXTIME(cri.finish_time,'%Y-%m-%d %H:%i')) as finish_time FROM `dsp_crowd_info` AS cri
         JOIN dsp_comp_staff AS comp ON comp.staff_id = cri.add_staff_id WHERE {$where}  ORDER BY `add_time` DESC LIMIT $start,$limit");
         return $result;
    }
    /**
     * 根据人群包状态统计数据111
     */
     public function getStaticsData($where="1"){
         $result = $this->db->dsp->query("SELECT audit_status,count(audit_status) AS count FROM dsp_crowd_info WHERE {$where} GROUP BY audit_status");
         return $result;
     }
    /**
     * 根据人群id查询人群信息111
     */
    public function getCrowdById($crowdid){
        $result = $this->db->dsp->query("SELECT * FROM `dsp_crowd_info`  WHERE `crowd_id`=$crowdid");
        return $result[0];
    }
    /**
     * 查询指定人群标签列表 111
     */
    public function getTagByW($where){
        $list =  $this->db->dsp->query("SElECT * FROM `dsp_crowd_tag` WHERE $where" );
        return $list;
    }
    /**
     * 查询人群标签列表 111
     */
    public function getTag($pid=0,$type){ 
        if($type==1){
            $where="`tag_status`=1 AND `pid` = $pid AND `tag_type` = $type";
        }else{
            $where="`tag_status`=1 AND `tag_type` = $type";
        }
        $list =  $this->db->dsp->query("SElECT * FROM `dsp_crowd_tag` WHERE $where" );
        return $list;
    }
    /**
     * 查询所有人群标签列表 111
     */
    public function getTagALL(){
        $where="`tag_status`=1 AND `label_id` != ''";
        $list =  $this->db->dsp->query("SElECT c_tag_id,label_id FROM `dsp_crowd_tag` WHERE $where" );
        return $list;
    }
    /**
     * 根据标签id查询标签信息 111
     */
    public function getTagInfoById($c_tag_id){
        $res =  $this->db->dsp->query("SElECT * FROM `dsp_crowd_tag` WHERE `c_tag_id`=$c_tag_id" );
        return $res[0];
    }
    /**
     * 根据省标签id查询市区标签列表 111
     */
    public function getCityTagList($c_tag_id){
        $where="`tag_status`=1 AND `label_id` != '' AND `pid` = $c_tag_id";
        $list =  $this->db->dsp->query("SElECT label_id FROM `dsp_crowd_tag` WHERE $where" );
        return $list;
    }
    /**
     * 根据省标签id查询标签111
     */
    public function getLabelById($c_tag_id){
        $res =  $this->db->dsp->query("SElECT label_id FROM `dsp_crowd_tag` WHERE `c_tag_id`=$c_tag_id" );
        return $res[0]['label_id'];
    }
    /**
     * 添加人群信息111
     */
    public function add($data){
       $res = $this->db->dsp->table("dsp_crowd_info")->data($data)->insert();
       return $res;
    }
    /**
     * 删除人群信息111
     */
    public function del($crowdid){
      $res = $this->db->dsp->table("dsp_crowd_info")->data(array('if_show'=>0))->where("`crowd_id`= $crowdid")->update();
      return $res;
    }
    /**
     * 修改人群信息111
     */
    public function edit($crowdid,$data){
        $where['crowd_id'] = $crowdid;
        $res = $this->db->dsp->table("dsp_crowd_info")->data($data)->where($where)->update();
        return $res;
    }
    /**
     * 查询运算中的人群包taskId列表总数111
     */
    public function getCrowdDwListCount(){
        $list =  $this->db->dsp->query("SElECT count(crowd_id) as count FROM `dsp_crowd_info` WHERE audit_status = 1 AND crowd_taskid !=''" );
        return $list[0]['count'];
    }
    /**
     * 查询运算中的人群包taskId列表111
     */
    public function getCrowdDwList($start = 0 , $limit){
        $list =  $this->db->dsp->query("SElECT crowd_taskid,crowd_id FROM `dsp_crowd_info` WHERE audit_status = 1 AND crowd_taskid !='' LIMIT $start,$limit" );
        return $list;
    }
    public function addAudit($data){
        $res = $this->db->dsp->table("dsp_crowd_audit")->data($data)->insert();
        return $res;
    }
    public function getCrowdAuditInfo($crowd_id){
        $res =  $this->db->dsp->query("SElECT * FROM `dsp_crowd_audit` WHERE `crowd_id`=$crowd_id ORDER BY audit_time desc limit 0,1" );
        return $res[0];
    }
    /** @author	marie
     * @desc	通过标签id获取完整地区111
     */
    public function getCrowdTagN($c_tag_id){
        $str="";
        $res=$this->db->dsp->table("dsp_crowd_tag")->where(" c_tag_id ='$c_tag_id' ")->select();
//         var_dump($res);
        if(!empty($res)){
            $tag_arr=explode('_',$res[0]['tag_array']);
            switch (count($tag_arr)){
                case 1:
                    $c_tag=$this->db->dsp->table("dsp_crowd_tag")->where(" tag_array ='$tag_arr[0]' ")->select();
                    $str.=$c_tag[0]['tag_name'];
                    break;
                case 2:
                    $c_tag1=$this->db->dsp->table("dsp_crowd_tag")->where(" tag_array ='$tag_arr[0]' ")->select();
                    $c_tag2=$this->db->dsp->table("dsp_crowd_tag")->where(" tag_array ='".$tag_arr[0]."_".$tag_arr[1]."'")->select();
                    $str.=$c_tag1[0]['tag_name']."-".$c_tag2[0]['tag_name'];
                    break;
                case 3:
                    $c_tag1=$this->db->dsp->table("dsp_crowd_tag")->where(" tag_array ='$tag_arr[0]' ")->select();
                    $c_tag2=$this->db->dsp->table("dsp_crowd_tag")->where(" tag_array ='".$tag_arr[0]."_".$tag_arr[1]."'")->select();
                    $c_tag3=$this->db->dsp->table("dsp_crowd_tag")->where(" tag_array ='".$tag_arr[0]."_".$tag_arr[1]."_".$tag_arr[2]."'")->select();
                    $str.=$c_tag1[0]['tag_name']."-".$c_tag2[0]['tag_name']."-".$c_tag3[0]['tag_name'];
                    break;
            }
        }
        return $str;
    }
    public function addDown($data){
        $res = $this->db->dsp->table("dsp_crowd_down")->data($data)->insert();
        return $res;
    }

  //查询代理商的可投放区域
    public function getUseCity($comp_id){
        $res  = $this->db->dsp->table("dsp_comp_sc")->where('comp_id='.$comp_id)->select();
        return empty($res)?0:$res[0]['sc_id'];
    }
    //城市id转换
    public  function getCtagId($sc_id){
        $pinfo = $this->db->dsp->table("dsp_crowd_tag")->where("`tag_array` LIKE '%\_{$sc_id}'")->select();
        $c_tag_id = $pinfo[0]['c_tag_id'];
        $pname = $pinfo[0]['tag_name'];
        //查询上级的名称
       $provinceInfo =  $this->db->dsp->table("dsp_crowd_tag")->where("c_tag_id=".$pinfo[0]['pid'])->select();
       $res['provinceId'] = $provinceInfo[0]['c_tag_id'];
       $res['provinceName']= $provinceInfo[0]['tag_name'];
       $res['cityId'] = $c_tag_id;
       $res['cityName'] = $pname;
        //查询下级
        $cityidList = $this->db->dsp->table("dsp_crowd_tag")->where("pid=".$c_tag_id)->select();
        foreach($cityidList as $k=>$v){
            $res['list'][] = array('c_tag_id'=>$v['c_tag_id'],'tag_name'=>$v['tag_name']);
        }
        return $res;
    }
}