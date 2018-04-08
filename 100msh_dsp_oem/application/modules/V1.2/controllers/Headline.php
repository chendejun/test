<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/22
 * Time: 18:02
 */

class HeadlineController extends BasicController {




    public function indexviewAction()
    {
    }

    /*
     * 获取网站风格
     */
    public function indexAction(){

        $page = Helper::clean($this->getRequest()->getParam('page',0));
        $limit = Helper::clean($this->getRequest()->getParam('limit',10));
        $start = $page>0?($page-1)*$limit:0;
        $Headline = Helper::M('Headline');
        $comp_pid = $this->uinfo['comp_id'];
        $list = $Headline->getList($comp_pid , $start , $limit);
        $count = $Headline->getCountList($comp_pid);
        $pages = ceil($count/$limit); //总页数
        Helper::outJson(array('state'=>1 , 'msg'=>'','list'=>$list,'totalPages'=>$pages,'page'=>$page ));

    }

    /*
     * 添加页面
     */
    public function addviewAction()
    {
    }
   /*
    * 添加数据
    */
   public function addAction(){

       $par['add_staff_id'] =$this->uinfo['staff_id'];
       $par['add_time'] = time();
       $par['comp_pid'] = $this->uinfo['comp_id'];//代理商id
       $par['delivery_name'] =Helper::clean($this->getRequest()->getPost('delivery_name'));
       $par['delivery_url'] =Helper::clean($this->getRequest()->getPost('delivery_url'));
       $par['delivery_info'] =Helper::clean($this->getRequest()->getPost('delivery_info'));
       $par['money_type'] =Helper::clean($this->getRequest()->getPost('money_type'));
       $par['money'] =Helper::clean($this->getRequest()->getPost('money'));
       $par['delivery_time_type'] =Helper::clean($this->getRequest()->getPost('delivery_time_type'));
       $par['delivery_start_date'] =Helper::clean($this->getRequest()->getPost('delivery_start_date'));
       $par['delivery_end_date'] =Helper::clean($this->getRequest()->getPost('delivery_end_date'));
       $par['delivery_start_time'] =Helper::clean($this->getRequest()->getPost('delivery_start_time'));
       $par['delivery_end_time'] =Helper::clean($this->getRequest()->getPost('delivery_end_time'));
       $par['bid_type'] =Helper::clean($this->getRequest()->getPost('bid_type'));
       $par['bid_money'] =Helper::clean($this->getRequest()->getPost('bid_money'));
       $par['campaign_name'] =Helper::clean($this->getRequest()->getPost('campaign_name'));
       $par['img_type'] =Helper::clean($this->getRequest()->getPost('img_type'));
       $par['img_info'] =Helper::clean($this->getRequest()->getPost('img_info'));
       $par['img_tag'] =Helper::clean($this->getRequest()->getPost('img_tag'));
       $par['order_money'] =Helper::clean($this->getRequest()->getPost('order_money'));
       $Headline = Helper::M('Headline');
       $result = $Headline->add($par);
       if($result){
           $arr = array('api_code'=>1,'msg'=>'success');
           Helper::outJson($arr);
       }else{
           $arr = array('api_code'=>0,'msg'=>'file');
           Helper::outJson($arr);
       }
   }
   /*
    * 获取广告详情
    */
   public function getInfoAction(){
       $jr_delivery_id =Helper::clean($this->getRequest()->getPost('jr_delivery_id'));
       $Headline = Helper::M('Headline');
       $result = $Headline->getInfo($jr_delivery_id);
       $arr = array('api_code'=>1,'msg'=>'success');
       $arr['data'] = $result;
       Helper::outJson($arr);
   }
    /*
     * 详情页面
     */
    public function showviewAction()
    {
    }
    /*
     * 获取审核原因
     */
    public function  getAduitAction(){
        $jr_delivery_id =Helper::clean($this->getRequest()->getPost('jr_delivery_id'));
        $Headline = Helper::M('Headline');
        $result = $Headline->getAduit($jr_delivery_id);
        $arr = array('api_code'=>1,'msg'=>'success');
        $arr['data'] = $result;
        Helper::outJson($arr);
    }
}