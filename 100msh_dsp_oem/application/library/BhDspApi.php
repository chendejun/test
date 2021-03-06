<?php
/**
 * behe方法请求类
 */
class BhDspApi{
    private static $cookie_res = './cookie_res.txt'; //cookie路径
    /**
     * 设置COOKIE路劲
     * @param [type] $path [description]
     */
    static public function setCookiePath($path){
        self::$cookie_res = $path;
    }
    /**
     * 返回COOKIE路劲
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    static public function getCookiePath(){
        return self::$cookie_res;
    }
    /**
     * 登录
     * @return [type] [description]
     */
    static public function login(){
        $url = 'http://dsp.behe.com/login';
        $data = array('userName' => 'wangxuemin@100msh.com' , 'passWord' => 'harry11li' , 'VerifiCode' => ''); //登录表单信息
        $result = DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
        $result = json_decode($result , true);
        if(!empty($result) && isset($result['code']) && $result['code'] === 0) return true;
        return false;
    }
    static public function logout(){
        $url = 'http://dsp.behe.com/login/logout';
        $result = DoNetwork::makeRequest($url , 'GET' , [] , self::$cookie_res , [] , 30000 , 1);
        if(!empty($result) && isset($result['http_code']) && $result['http_code'] == 302){
            @unlink(self::$cookie_res);
            return true;
        }
        return false;
    }
    /**
     * 在线状态检测
     * @return [type] {code: 0, msg: "ok", message: "成功", reason: "", datas: []}
     */
    static public function checkAccessToken(){
        $url = 'http://dsp.behe.com/account/checkAccessToken'; //检测地址
        $result = DoNetwork::makeRequest($url , 'POST' , [] , self::$cookie_res , [] , 30000 , false);
        $result = json_decode($result , true);
        if(!empty($result) && isset($result['code']) && $result['code'] === 0 && $result['msg'] == 'ok'){
            return true;
        }
        return false;
    }
    /**
     * 获取实时余额
     * @param  [type] $userMainType  账户类型【1服务商，2广告主】
     * @param  [type] $userAccountId 账户ID
     * @return [type]                {code: 0, msg: "ok", message: "成功", reason: "", datas: {remainCash: "0.00"}}
     */
    static public function remainCash($userMainType , $userAccountId){
        $url = 'http://dsp.behe.com/account/remainCash'; //检测地址
        $data = array('userMainType' => $userMainType , 'userAccountId' => $userAccountId);
        $result = DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
        $result = json_decode($result , true);
        if(!empty($result) && isset($result['code']) && $result['code'] === 0) return $result;
        return false;
    }
    /**
     * 获取广告主广告活动列表
     * @param  [type] $userMainType  账户类型【1服务商，2广告主】
     * @param  [type] $userAccountId 账户ID
     * @return [type]                {code: 0, msg: "ok", message: "成功", reason: "", datas: {remainCash: "0.00"}}
     */
	static public function getlist(){
        $url = "http://dsp.behe.com/advertiser/".$AccountId."/campaign/list"; //检测地址
        $data = array('userMainType' => $userMainType , 'userAccountId' => $AccountId);
        $result = DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
        if(!empty($result) && isset($result['code']) && $result['code'] === 0) return true;
        return false;
    }
    /**
     * 获取行业分类一级
     * @return [type] [description]
     */
    static public function industryList(){
        $url = 'http://dsp.behe.com/account/industryList'; //检测地址
        return $result = DoNetwork::makeRequest($url , 'POST' , [] , self::$cookie_res , [] , 30000 , false);
    }
    /**
     * 获取行业分类二级
     * @return [type] [description]
     */
    static public function industryCdList($data){
        $url = 'http://dsp.behe.com/account/subIndustryList'; //检测地址
        return $result = DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
    }
    /**
     * 获取广告主资质详情页
     * @return [type] [description]
     */
    static public function qualifications($adv_id){
        $url = 'http://dsp.behe.com/advertiser/'.$adv_id.'/account_base/qualifications'; //检测地址
        return $result = DoNetwork::makeRequest($url  , 'GET' , [] , self::$cookie_res );
    }
    /**
     * 新增广告主
     * @return [type] [description]
     */
    static public function addAccount($data){
        $url = 'http://dsp.behe.com/account/addaccount'; //检测地址
        return  DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
    }
    /**
     * 图片上传
     * @return [type] [description]
     */
    static function upload($data){
        $url = 'http://dsp.behe.com/upload/upload'; //检测地址
        //$data['files[]']=new curlFile("/data001/data/sites/100msh_upload/dsp/adv/1fb5f83a16762ef720bdbd638559b9cc.jpg");
        return DoNetwork::makeRequest($url , 'POST' , $data , self::$cookie_res , [] , 30000 , false);
    }
    /**
     * 资质上传
     * @return [type] [description]
     */
    static function fileUp($data){
        $url = 'http://dsp.behe.com/account/fileUp'; //检测地址
        return DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
    }
    /**
     * 设置利润率
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    static function premium($data){
        $url = 'http://dsp.behe.com/account/premium'; //检测地址
        return DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
    }
    /**
     * 修改广告主
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    static function editInfo($data){
        $url = 'http://dsp.behe.com/advertiser/1697021015/account_base/editInfo'; //检测地址
        return DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
    }
    /**
     * 修改广告主服务商账号修改
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    static function editAccount($data){
        $url = 'http://dsp.behe.com/account/editAccount'; //检测地址
        return DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
    }
    static function getData($data)
    {
        $url = 'http://dsp.behe.com/advertiser/1697020333/report/getData'; //检测地址
        return $result = DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);

    }



    /**
     * 往广告主账户充值
     * @param  [type]  $accountId 账户ID
     * @param  [type]  $toCash    真实金额
     * @param  integer $payType   转入
     * @param  string  $content   备注
     * @return [type]             {"code":0,"msg":"ok","message":"成功","reason":"","datas":[]}
     */
    static function finaceTransfer($accountId , $toCash , $content = '' , $payType = 1){
        $data = array('accountId' => $accountId , 'payType' => $payType , 'toCash' => $toCash , 'content' => $content);
        $url = 'http://dsp.behe.com/finace/transfer';
        $result = DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
        $result = json_decode($result , true);
        if(!empty($result) && isset($result['code']) && $result['code'] === 0) return true;
        return false;
    }
    /**
     * 转出金额
     * @param  [type]  $accountId 账户ID
     * @param  [data]  data    转出金额[利润之后金额]
     * @param  integer $payType   转出
     * @param  string  $content   备注
     * @return [type]             {"code":0,"msg":"ok","message":"成功","reason":"","datas":[]}
     */
    static function finaceRefund($accountId , $toCash , $content = '' , $payType = 2){
        $data = array('accountId' => $accountId , 'payType' => $payType , 'toCash' => $toCash , 'content' => $content);
        $url = 'http://dsp.behe.com/finace/refund';
        $result = DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , self::$cookie_res , [] , 30000 , false);
        $result = json_decode($result , true);
        if(!empty($result) && isset($result['code']) && $result['code'] === 0) return true;
        return false;
    }
    /**
     * 随机17位的版本号
     * @return [type] [description]
     */
    static public function vRand() {
       list($msec, $sec) = explode(' ', microtime());
       $str = str_replace('.', '', $msec.$sec);
       return  substr($str , 0 , 17);
    }

    /**
     * 转出金额
     * @param  [type]  $accountId 账户ID
     * @param  [data]  $data   参数
     * @return [type]
     * */
    static function getAdSlotData($account_id,$data){
        $url = 'http://dsp.behe.com/advertiser/'.$account_id.'/resource/getAdSlotData'; //检测地址
        return $result = DoNetwork::makeRequest($url , 'POST' , http_build_query($data) , BhDspApi::$cookie_res , [] , 30000 , false);

    }
    /*
     * 获取所选择移动媒体的规格集合
    * @param  [type]  $accountId 账户ID
     * @param  [data]  $data   参数
     */
    static public  function getEstimateFlow($account_id,$data){
        $url  = 'http://dsp.behe.com/advertiser/'.$account_id.'/order/getEstimateFlow'; //检测地址
        return $result = DoNetwork::makeRequest($url , 'POST' ,  http_build_query($data) , BhDspApi::$cookie_res , [] , 30000 , false);
    }
    /*
     * 获取应用分类
     */
    public function  appList($account_id,$data){
        $url = 'http://dsp.behe.com/advertiser/'.$account_id.'/resource/appList';
        return $result = DoNetwork::makeRequest($url , 'POST' ,  http_build_query($data) , BhDspApi::$cookie_res , [] , 30000 , false);
    }

}
