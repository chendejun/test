<?php
class WechatController extends BasicController {
	public function indexAction(){
        $this->getView();
	}
    public function listAction(){
        $data=array();
        $data['comp_pid']=$this->uinfo['comp_id'];
        $data['agent_level']=$this->uinfo['agent_level'];
        $data['page']=Helper::clean($this->getRequest()->getParam('page' , 1));
        $data['limit']=Helper::clean($this->getRequest()->getParam('limit' , 15));
        $data['kw']=Helper::clean($this->getRequest()->getParam('kw' , ''));
        $result = BmDspApi::request('agent/advList',$data,'GET');
        if(empty($result)){
            Helper::outJson(array('state'=>0 , 'msg' =>'请求获取代理商列表接口失败'));
        }
        if($result['api_code'] == 0){
            Helper::outJson(array('state'=>1,'msg'=>'操作成功','data'=>$result['data']));
        }else{
            Helper::outJson(array('state'=>0 , 'msg' =>$result['error']));
        }
    }
    /**
     * 获取投放操作员
     * @return [type] [description]
     */
    public function getOperatorAction(){
        $data=array();
        $data['comp_pid']=$this->uinfo['comp_id'];
        if($this->uinfo['user_group'] == ROLE_OPERATED){
            $data['staff_id']=$this->uinfo['staff_id'];
        }else{
            $data['staff_id']=0;
        }
        //var_dump($data);
        $result = BmDspApi::request('wechat/getOperator',$data,'GET');
        if(empty($result)){
            Helper::outJson(array('state'=>0 , 'msg' =>'请求获取投放操作员接口失败'));
        }
        if($result['api_code'] == 0){
            Helper::outJson(array('state'=>1,'msg'=>'操作成功','data'=>$result['data']));
        }else{
            Helper::outJson(array('state'=>0 , 'msg' =>$result['error']));
        }
    }
    /**
     * 获取绑定广告主列表
     * @return [type] [description]
     */
    public function getCompListAction(){
        $data=array();
        $data['comp_pid']=$this->uinfo['comp_id'];
        //var_dump($data);
        $result = BmDspApi::request('wechat/getCompList',$data,'GET');
        if(empty($result)){
            Helper::outJson(array('state'=>0 , 'msg' =>'请求获取绑定广告主接口失败'));
        }
        if($result['api_code'] == 0){
            Helper::outJson(array('state'=>1,'msg'=>'操作成功','data'=>$result['data']));
        }else{
            Helper::outJson(array('state'=>0 , 'msg' =>$result['error']));
        }
    }
    public function addAction(){
        if(isset($_POST)&&!empty($_POST)) {
            $data['comp_pid'] = $this->uinfo['comp_id'];
            $data['staff_id'] = $this->uinfo['staff_id'];
            $data['agent_level']=$this->uinfo['agent_level'];
            if($data['agent_level'] >= 3){
                Helper::outJson(array('state'=>0 , 'msg' =>'此代理商不可以再新增下级代理！'));
            }
            $data['comp_name'] = Helper::clean($this->getRequest()->getPost('comp_name'));
            $data['contacts'] = Helper::clean($this->getRequest()->getPost('contacts'));
            $data['mobile_phone'] = Helper::clean($this->getRequest()->getPost('mobile_phone'));
            $data['sc_pro'] = intval($this->getRequest()->getPost('sc_pro'));
            $data['sc_pid'] = intval($this->getRequest()->getPost('sc_pid'));
            $data['sc_id'] = intval($this->getRequest()->getPost('sc_id'));
            $data['staff_acc'] = Helper::clean($this->getRequest()->getPost('staff_acc'));
            if(empty(trim($data['comp_name']))){
                Helper::outJson(array('state'=>0 , 'msg' =>'代理商名称不可为空'));
            }
            if(empty($data['contacts'])){
                Helper::outJson(array('state'=>0 , 'msg' =>'联系人不可为空'));
            }
            $result = BmDspApi::request('agent/add',$data,'POST');
            //var_dump($result);
            if(empty($result)){
                Helper::outJson(array('state'=>0 , 'msg' =>'请求api新增代理商接口失败'));
            }
            if($result['api_code'] == 0){
                //添加操作日志
                $log_content[] = "下级代理商管理 :". "代理商添加";
                $log_content_str = implode(" , ",$log_content);
                $log_obj = Helper::M('Log');
                $log_obj->create_log('代理商添加',$log_content_str,'LC008',"LOT001");
                Helper::outJson(array('state' => 1, 'msg' => '添加成功'));
            }else{
                Helper::outJson(array('state'=>0 , 'msg' =>$result['error']));
            }
        }

    }
    public function editAction(){
        $comp_id=$this->getRequest()->getParam('comp_id')?Helper::clean($this->getRequest()->getParam('comp_id')):0;
        if(!$comp_id){
            Helper::outJson(array('state'=>0 , 'msg' =>'请求参数错误'));
        }
        if(isset($_POST)&&!empty($_POST)) {
            $data['comp_id'] = $comp_id;
            $data['staff_id'] = $this->uinfo['staff_id'];
            $data['comp_name'] = Helper::clean($this->getRequest()->getPost('comp_name'));
            $data['contacts'] = Helper::clean($this->getRequest()->getPost('contacts'));
            $data['mobile_phone'] = Helper::clean($this->getRequest()->getPost('mobile_phone'));
            $data['sc_pro'] = intval($this->getRequest()->getPost('sc_pro'));
            $data['sc_pid'] = intval($this->getRequest()->getPost('sc_pid'));
            $data['sc_id'] = intval($this->getRequest()->getPost('sc_id'));
            $data['status'] = intval($this->getRequest()->getPost('status'));
            $data['staff_acc'] = Helper::clean($this->getRequest()->getPost('staff_acc'));
            if(empty(trim($data['comp_name']))){
                Helper::outJson(array('state'=>0 , 'msg' =>'代理商名称不可为空'));
            }
            if(empty($data['contacts'])){
                Helper::outJson(array('state'=>0 , 'msg' =>'联系人不可为空'));
            }
            $result = BmDspApi::request('agent/edit',$data,'POST');
            if(empty($result)){
                Helper::outJson(array('state'=>0 , 'msg' =>'请求api编辑代理商接口失败'));
            }
            if($result['api_code'] == 0){
                if($result['data']['if_edit'] == '1'){
                    $mb_orig=$result['data']['mb_orig'];
                    $mb_now=$result['data']['mb_now'];
                    $one_name=$result['data']['one_name'];
                    $comp_name=$result['data']['comp_name'];
                    $content1 = "【".$one_name."】尊敬的用户，你的账号已撤消<".$comp_name.">管理员资格";
                    $result1 = BmDspApi::request('sms/send' , array('content' => $content1 ,'mobile_phone'=>$mb_orig)); //发送短信验证码
                    $content2 = "【".$one_name."】尊敬的用户，你的账号已被创建为<".$comp_name.">管理员，默认密码为账号后6位，服务商网址请联系对接人员获取。";
                    $result2 = BmDspApi::request('sms/send' , array('content' => $content2 ,'mobile_phone'=>$mb_now)); //发送短信验证码
                }
                $str="";
                //添加操作日志
                $log_content[] = "下级代理商管理 :". "代理商修改";
                $log_content[]="操作修改的员工ID：".$data['staff_id'];
                foreach($result['data'] as $k=>$v){
                    $str.=$k."-".$v."|";
                }
                $log_content[]="修改的字段信息：".trim($str,'|');
                $log_content_str = implode(" , ",$log_content);
                $log_obj = Helper::M('Log');
                $log_obj->create_log('代理商修改',$log_content_str,'LC008',"LOT002");
                Helper::outJson(array('state' => 1, 'msg' => '修改成功'));
            }else{
                Helper::outJson(array('state'=>0 , 'msg' =>$result['error']));
            }
        }
    }
    public function viewAction()
    {
        $wechat_bind_id = $this->getRequest()->getParam('wechat_bind_id') ? Helper::clean($this->getRequest()->getParam('wechat_bind_id')) : 0;
        if (!$wechat_bind_id) {
            Helper::outJson(array('state' => 0, 'msg' => '请求参数错误'));
        }
        $data=array();
        $data['wechat_bind_id']=$wechat_bind_id;
        $result = BmDspApi::request('wechat/view',$data,'GET');
        if(empty($result)){
            Helper::outJson(array('state'=>0 , 'msg' =>'请求微信绑定信息接口失败'));
        }
        if($result['api_code'] == 0){
            Helper::outJson(array('state'=>1,'msg'=>'操作成功','data'=>$result['data']));
        }else{
            Helper::outJson(array('state'=>0 , 'msg' =>$result['error']));
        }
    }
}