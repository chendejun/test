<?php
class BasicController extends Yaf_Controller_Abstract {
	protected $redis;
    protected $config;
    protected $uinfo;
	protected function init(){
		$this->redis = Yaf_Registry::get('redis');
        $this->config = Yaf_Registry::get('config');
        $this->url = Helper::getHost(true);
        $moduleName = $this->getRequest()->getModuleName();
        Yaf_Registry::set('moduleName',$moduleName);
        $version = strtoupper($moduleName);
        if($version != 'INDEX'){
            $this->getView()->setScriptPath(APP_PATH . '/application/modules/' . $moduleName . '/views/');
            $this->getView()->setCompilePath($this->config['smarty']['compile_dir'] . $moduleName);
        }
        if(in_array($version, array('INDEX' , 'V1.2'))){
            $this->checkLogin(); //OEM登录检测
        }elseif($version == 'QYWX'){ //企业微信登录检测
           
        }
        
        $this->initBmApiAccessToken(); //API access_token检测
    }
    private function checkLogin(){
        $u = Helper::M('U');
        $is_login = $u->checkLogin();
        $cn = $this->getRequest()->getControllerName();
        $an = $this->getRequest()->getActionName();
        $ex = strtolower($cn);
        $exception = array('login' , 'api');
        if($ex == 'login' && $is_login){ //登录之后不能到login页面，但可以进入api页面
            header("Location: {$this->url}"); exit;
        }
        if($is_login){ //登录了
            $this->uinfo = &$_SESSION['uinfo'];
        }elseif(!in_array($ex, $exception) && !$is_login ){ //没登陆除了login页面和api页面其它都不允许进入
            //header('Location: https://dev-dspoem.100msh.com/login/index'); exit;
            echo '<script type="text/javascript">top.location.href="'.$this->url.'/login/index";</script>';
            exit;
        }

    }
    private function initBmApiAccessToken(){
        $access_token = $this->redis->get('BmApiAccessToken');
        if(empty($access_token)){ 
            $result = BmDspApi::request('oauth/access_token' , array('app_id' => $this->config['dsp']['app_id'] , 'app_key' => $this->config['dsp']['app_key']));
            if(!empty($result) && isset($result['data'])){
                $data = $result['data'];
                if(isset($data['access_token']) && !empty($data['access_token'])){
                    $access_token = $data['access_token'];
                    $this->redis->set('BmApiAccessToken' , $access_token , 3000);
                }
            }
        }
        Yaf_Registry::set('BmApiAccessToken' , $access_token);
    }
    //设置DMPtoken
    protected function setDmpToken(){
        $this->config = Yaf_Registry::get('config');
        $token_params['appid']=$this->config['dmp']['appid'];
        $token_params['secret']=$this->config['dmp']['secret'];
        //print_r($token_params);
        $result = DoNetwork::makeRequest($this->config['dmp']['api_host']."/bmtagapi/author/getAccessToken.do?appid=".$token_params['appid']."&secret=".$token_params['secret'] , 'POST' );
        $result = json_decode($result , true);
        //print_r($result);exit;
        if($result['errcode'] == 0){
            $this->redis->set('dmp_access_token',$result['data']['token']);
            $this->redis->set('dmp_access_token'.date('Ymd'),1);
            $this->redis->del('dmp_access_token'.date('Ymd',strtotime("-1 day")));
        }else{
            Helper::outJson(array('state'=>0 , 'msg' => 'dmp请求token错误'));
        }
    }
    //设置DMPtoken
    protected function setNdmpToken(){
        $this->config = Yaf_Registry::get('config');
        $token_params['appid']=$this->config['ndmp']['appid'];
        $token_params['secret']=$this->config['ndmp']['secret'];
        //print_r($token_params);
       // echo $this->config['ndmp']['api_host']."/author/getAccessToken.do?appid=".$token_params['appid']."&secret=".$token_params['secret'];
        $result = DoNetwork::makeRequest($this->config['ndmp']['api_host']."/author/getAccessToken.do?appid=".$token_params['appid']."&secret=".$token_params['secret'] , 'GET' );
        $result = json_decode($result , true);
        //var_dump($result);exit;
        if(!empty($result) && $result['token']){
            $this->redis->set('ndmp_access_token',$result['token']);
            $this->redis->set('ndmp_access_token'.date('Ymd'),1);
            $this->redis->del('ndmp_access_token'.date('Ymd',strtotime("-1 day")));
        }else{
            Helper::outJson(array('state'=>0 , 'msg' => 'ndmp请求token错误'));
        }
    }
}