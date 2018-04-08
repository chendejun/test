<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/22
 * Time: 18:02
 */

class BhcreativeController extends BasicController {

    /*
     * 获取媒体列表
     */
  public function getAdSlotDataAction(){

      $account_id = Helper::clean($this->getRequest()->getPost('account_id'));
      $data['dealType'] = 1;
      $data['creativeType'] = 1;//创意类型
      $data['mediaType'] = 1;//媒体类型
      $data['osType'] = 2;//系统类型
      $data['trafficType'] = 1;
      $data['platformType'] = 2;
      $data['behaviorType'] = 1;
      $bh = new BhDspApi();
      $result = '{"code":0,"msg":"ok","message":"成功","reason":"","datas":{"bid":"30806500","click":"74450","cost":"13705331600.0","size":{"640x100":41402,"720x1280":12108,"640x960":7531,"960x640":508},"view":"1576510","sizeTotal":61613,"sizeZb":{"640x100":67.2,"720x1280":19.65,"640x960":12.22,"960x640":0.82}}}';
      $data = json_decode($result,true);
      $arr = array();
      $lists = $data['datas']['size'];
      $Bh = Helper::M('Bhcreate');
      foreach ($lists as $key=>$val){
          $data1['ad_size_name'] = $key;
          $k_list = explode('x',$key);
          $data1['ad_size_width'] = $k_list[0];
           $data1['ad_size_height'] = $k_list[1];
           $Bh = Helper::M('Bhcreate');
           $size_list = $Bh->get($data1);
           if(empty($size_list)){
               $size_list = $Bh->add($data1);
               $arr[] = $size_list;
           }else{
               $arr[] = $size_list[0]['size_id'];
           }


      }
      $data2['adxId'] = 66;
      $data2['size_list']  =  json_encode($arr);
      $data2['osType']  = 2;
      $Bh ->adds($data2);
      exit;
      Helper::outJson($result);
   }
   /*
    * 获取媒体列表对应的规格列表
    */
   public function getEstimateFlowAction(){
       $account_id = Helper::clean($this->getRequest()->getPost('account_id'));
       $adxType = Helper::clean($this->getRequest()->getPost('adxType'));//媒体平台集合
       $data = array('randomNumber'=>'ef6580a866aaee2730ec32578d3f853',);
       $bh = new BhDspApi();
       $result = $bh->getEstimateFlow($account_id,$data);
       Helper::outJson($result);
   }
   /*
    * 获取指定应用类型
    */
   public function appListAction(){
       $account_id = Helper::clean($this->getRequest()->getPost('account_id'));
       $data['adxId'] = 8;
       $data['page'] = 1;
       $data['accountId'] =1697020333;
       $data['level'] =2;
       $data['osType'] =2;
       $data['behaviorType'] =1;
       $data['creativeType'] =1;
       $data['trafficType'] =1;
       $data['unkownAppFlag'] =0;
       $bh = new BhDspApi();
       $result = $bh->appList($account_id,$data);
       Helper::outJson($result);
   }
   /*
    *获取app列表
    */
   public function getApplistsAction(){
       $a = '{"8":[{"2073164":"优美图_ios"},{"2089284":"斗地主 "}],
           "8":[{"2073164":"优美图_ios"},{"2089284":"斗地主 "}]}';
       $arr = json_decode($a,true);
       var_dump($arr);exit;

      /*for ($x=1; $x<=12; $x++) {
                $data['adxId'] = 66;
                $data['page'] = $x;
                $data['accountId'] = 1697020333;
                $data['level'] = 2;
                $data['osType']  = 2;
                $data['behaviorType'] = 1;
                $data['creativeType'] = 1;
                $data['trafficType'] = 1;
                $data['unkownAppFlag'] = 0;
               $bh = new BhDspApi();
               $result = $bh->appList( $data['accountId'],$data);
               $result = json_decode($result,true);
               $info = $result['datas']['data'];
               foreach ($info as $key=>$val){
                   $data1['adx_Id'] = 17;
                   $data1['aid'] = $val['id'];
                   $data1['name'] = $val['name'];
                   $bh_creat = Helper::M("Bhcreate");
                   $result = $bh_creat->add($data1);
               }

       }*/
   }
   /*
    * 获取指定应用对应的有规格的媒体列表
    */
   public function getZadxsAction(){
       $where  = "";
       $osType_o = Helper::clean($this->getRequest()->getPost('osType_o'));//是否支持安卓
       if($osType_o==1){
           if($where){
               $where = $where." AND osType_o=1";
           }else{
               $where = "osType_o=1";

           }
       }
       $osType_i = Helper::clean($this->getRequest()->getPost('osType_i'));//是否支持ios
       if($osType_i==1){
           if($where){
               $where = $where." AND osType_i=1";
           }else{
               $where = "osType_i=1";
           }
       }
       $behavior_type_t = Helper::clean($this->getRequest()->getPost('behavior_type_t'));//是否支持网页跳转
       if($behavior_type_t==1){
           if($where){
               $where = $where." AND behavior_type_t=1";
           }else{
               $where = "behavior_type_t=1";
           }
       }
       $behavior_type_x = Helper::clean($this->getRequest()->getPost('behavior_type_x'));//是否支持下载应用
       if($behavior_type_x==1){
           if($where){
               $where = $where." AND behavior_type_x=1";
           }else{
               $where = "behavior_type_x=1";
           }
       }
       $action_type_h = Helper::clean($this->getRequest()->getPost('action_type_h'));//形式支持-横幅 0 不支持 1支持
       if($action_type_h==1){
           if($where){
               $where = $where." AND action_type_h=1";
           }else{
               $where = "action_type_h=1";
           }
       }
       // $action_type_s = Helper::clean($this->getRequest()->getPost('action_type_s'));//形式支持-视频贴片 0 不支持 1支持
       //$action_type_y = Helper::clean($this->getRequest()->getPost('action_type_y'));//形式支持-原生信息流 0 不支持 1支持
       $action_type_k = Helper::clean($this->getRequest()->getPost('action_type_k'));
       if($action_type_k==1){
           if($where){
               $where = $where." AND action_type_k=1";
           }else{
               $where = "action_type_k=1";
           }
       }
       $Bhdelivery = Helper::M('Bhdelivery');
       $size_info = $Bhdelivery->appSizelists($where);
       $app_list = array();
       if($size_info){
            /*
             * 获取app列表
             */
           foreach($size_info as $key=>$val){
               if(!in_array($val['ap_id'],$app_list)){
                   $app_list[] = $val['ap_id'];
               }
           }
           /*
              * 获取对应的媒体列表
              */
           $adx_list_arr = array();
           $adx_list = '(';
           foreach($app_list as $key1=>$val){
              $app_info =  $Bhdelivery->getAppinfo($val);
               if(!in_array($app_info[0]['adx_Id'],$adx_list_arr)){
                   $adx_list_arr[] = $app_info[0]['adx_Id'];
                   if($adx_list=='('){
                       $adx_list = $adx_list.$app_info[0]['adx_Id'];
                   }else{
                       $adx_list = $adx_list.",".$app_info[0]['adx_Id'];
                   }

               }
               }
           $adx_list = $adx_list.")";
           $adx_info = $Bhdelivery->getAdxsinfo($adx_list);
           foreach ($adx_info as $key2=>$val2){
                  foreach($app_list as $key3=>$val3){
                      $app_info =  $Bhdelivery->getAppinfo($val3);
                      if($app_info[0]['adx_Id']==$val2['adx_Id']){
                          $adx_info[$key2]['app_info'][] = $app_info[0];
                      }

                  }
           }
       }else{
           $arr = array('api_code' => 1, 'msg' => 'success');
           $arr['data'] = '';
           Helper::outJson($arr);
       }



   }

}