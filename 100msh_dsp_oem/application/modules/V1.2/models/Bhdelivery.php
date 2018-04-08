<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 18:46
 */
Class Bhdelivery extends  Model{

	/**
	 * @author	cdj
	 * @desc	获取广告主列表
	 * @param array() $data
	 */
    public function getcamp($comp_id){
        $data =  $this->db->bh->query('SELECT *  FROM bh_delivery_campaign WHERE comp_id= '.$comp_id);
        return $data;
    }
    /*
     * 创建广告计划
     */
    public function addcamp($data){
        return $this->db->bh->table( "bh_delivery_campaign" )->data( $data )->insert(  );
    }
    /*
     * 检查计划名称是否被使用
     */
    public function checkCam($comp_id,$bh_campaign_name){
        $data =  $this->db->bh->query("SELECT *  FROM bh_delivery_campaign WHERE  comp_id = $comp_id AND bh_campaign_name= '".$bh_campaign_name."'");
        return $data;
    }
    /*
   * 检查广告名称名称是否被使用
   */
    public function checkAdname($comp_id,$delivery_name){
        $data =  $this->db->bh->query("SELECT *  FROM bh_delivery WHERE  comp_id = $comp_id AND delivery_name= '".$delivery_name."'");
        return $data;
    }
    /*
     * 修改广告计划
     */
    public function edit($bh_campaign_id,$data){
        $result =  $this->db->bh->table('bh_delivery_campaign')->where(array('bh_campaign_id'=>$bh_campaign_id))->data($data)->update();
        return $result;
    }

    /*
     * 获取人群定向列表
     */
    public function getCrowdlist($comp_pid)
    {
        $data = $this->db->dsp->query("select crowd_id,crowd_name from dsp_crowd_info WHERE  comp_pid=" . $comp_pid . " and audit_status=3  and if_show=1 and crowd_type!=2");
        return $data;
    }
    public function getAlladx(){
        $data =  $this->db->bh->query("SELECT *  FROM bh_adx " );
        return $data;
    }
    /*
     * 获取条件下规格列表
     */
    public function getAdxs($where){
        if($where){
            $data =  $this->db->bh->query('SELECT adx_size_id as app_size_id,adx_Id,ad_size_name,ad_size_width,ad_size_height  FROM bh_adx_size WHERE '.$where);
        }else{
            $data =  $this->db->bh->query('SELECT adx_size_id as app_size_id,adx_Id,ad_size_name,ad_size_width,ad_size_height  FROM bh_adx_size ');
        }

        return $data;
    }
  /*
   * 获取媒体列表
   */
  public function getAdxlist($lists){
      $data =  $this->db->bh->query("SELECT *  FROM bh_adx WHERE adx_Id in $lists" );
      return $data;
  }
  /*
   * 获取媒体对应的app列表
   */
  public function getApp($adx_Id){
      $data =  $this->db->bh->query("SELECT *  FROM bh_app WHERE adx_Id = $adx_Id" );
      return $data;
  }
  /*
   * 获取app对应的规格
   */
  public function getAppSize($where){
      if($where){
          $data =  $this->db->bh->query('SELECT *  FROM bh_app_size WHERE '.$where);
      }else{
          $data =  $this->db->bh->query('SELECT *  FROM bh_app_size ');
      }

      return $data;
  }
  /*
   * 获取规格对应的创意
   */
  public function getCreative($data){
      $data =  $this->db->bh->query("SELECT *  FROM bh_creative WHERE ad_size_name = '".$data['ad_size_name']."' AND type = ".$data['type']." AND comp_id = ".$data['comp_id']);
      return $data;
  }
  /*
   * 获取名字对应的规格
   */
  public function getSize($ad_size_name){
      $data =  $this->db->dsp->query("SELECT *  FROM dsp_ad_size WHERE ad_size_name = '".$ad_size_name."' limit 1");
      return $data;
  }

  /*
   * 添加创意
   */
  public function addCreative($data){
      return $this->db->bh->table( "bh_creative" )->data( $data )->insert(  );
  }
  /*
   * 添加广告
   */
  public function addDelivery($data){
      return $this->db->bh->table( "bh_delivery" )->data( $data )->insert(  );
  }
  /*
   * 获取广告详情
   */
  public function getAdinfo($bh_delivery_id,$comp_id){
      $data =  $this->db->bh->query("SELECT status  FROM bh_delivery WHERE  bh_delivery_id = ".$bh_delivery_id." AND comp_id = ".$comp_id);
      return $data;
  }

 /*
  * 修改广告状态
  */
 public function updateAdStatus($bh_delivery_id,$status){
     $result = $this->db->bh->table("bh_delivery")->data(array('status'=>$status))->where(array('bh_delivery_id'=>$bh_delivery_id))->update();
     return $result;
 }
 /*
  * 修改计划状态
  */
 public function updateCampStatus($bh_campaign_id,$status,$comp_id){
     $result = $this->db->bh->table("bh_delivery_campaign")->data(array('status'=>$status))->where(array('bh_campaign_id'=>$bh_campaign_id,'comp_id'=>$comp_id))->update();
     return $result;
 }
 /*
  * 修改计划名称
  */
 public function updateCampname($where,$data){
     $result = $this->db->bh->table("bh_delivery_campaign")->data($data)->where($where)->update();
     return $result;
 }
 /*
  * 状态修改 修改对应的投放状态
  */
    public function updateAdsStatus($bh_campaign_id,$status_ad,$status,$comp_id){
        if($status_ad!=1){
            $result = $this->db->bh->table("bh_delivery")->data(array('status'=>$status))->where(array('bh_campaign_id'=>$bh_campaign_id,'comp_id'=>$comp_id,'status'=>$status_ad))->update();
        }else{
            $result = $this->db->bh->table("bh_delivery")->data(array('status'=>$status))->where(array('bh_campaign_id'=>$bh_campaign_id,'comp_id'=>$comp_id))->update();
        }

        return $result;
    }

    /*
     * 指定应用 对应选择的规格列表
     */
    public function appSizelists($where){
        if($where){
            $data =  $this->db->bh->query('SELECT *  FROM bh_app_size WHERE '.$where);
        }else{
            $data =  $this->db->bh->query('SELECT *  FROM bh_app_size ');
        }

        return $data;
    }
    /*
     * 获取应用详细信息
     */
    public function getAppinfo($ap_id){
        $data =  $this->db->bh->query("SELECT *  FROM bh_app WHERE ap_id=$ap_id");
        return $data;
    }
    /*
     *
     */
    public function getAdxsinfo($adx_lists){
        $data =  $this->db->bh->query("SELECT *  FROM bh_adx WHERE adx_Id in $adx_lists");
        return $data;
    }
}