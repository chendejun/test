<?php

/**
 * 场景
 */
class ScenesController extends BasicController
{

    /**
     * [SCENE_NAME_LEN 场景名称长度]
     * @var [INT]
     */
    const SCENE_NAME_LEN = 32*3;

    /**
     * [SUCCESS_STATE 请求成功状态]
     * @var integer
     */
    const SUCCESS_STATE = 1;

    /**
     * [FAIL_STATE 请求失败状态]
     * @var integer
     */
    const FAIL_STATE = 0;

    /**
     * [NOT_ENOUGH 余额不够]
     * @var integer
     */
    const NOT_ENOUGH = -1;

    /**
     * [MIN_DETECTION_RADIUS 探测最小半径]
     * @var integer
     */
    const MIN_DETECTION_RADIUS = 1;
    /**
     * [MAX_DETECTION_RADIUS 探测最大半径]
     * @var integer
     */
    const MAX_DETECTION_RADIUS = 30;
    /**
     * [MIN_STOP_TIME 停留最小时间]
     * @var integer
     */
    const MIN_STOP_TIME = 1;
    /**
     * [MAX_STOP_TIME 停留最大时间]
     * @var integer
     */
    const MAX_STOP_TIME = 30;

    /**
     * [SENIOR 高级版单价 现已替换为资源]
     * @var integer
     */
    const SENIOR = 1;

    /**
     * [APP APP分析版单价 现已替换为资源]
     * @var integer
     */
    const APP = 1;

    /**
     * [PASSENGER_FLOW_DATE 客流数据]
     * @var [int]
     */
    const PASSENGER_FLOW_DATE = 1;

    /**
     * [PASSENGER_FLOW_TREND_DATE 客流趋势数据]
     * @var integer
     */
    const PASSENGER_FLOW_TREND_DATE = 2;
    /**
     * [PASSENGER_24hours_FLOW_TREND_DATE 24小时客流趋势数据]
     * @var integer
     */
    const PASSENGER_24HOURS_FLOW_TREND_DATE = 3;

    /**
     * [STOP_TIME 获取停留时长]
     * @var integer
     */
    const STOP_TIME = 4;
    /**
     * [YEAR_ON_YEARANALYSIS 同比分析]
     * @var integer
     */
    const YEAR_ON_YEAR_ANALYSIS =5;
    /**
     * [NEW_AND_OLD 新老顾客对比]
     * @var integer
     */
    const NEW_AND_OLD =6;
    /**
     * [CUSTOMER_PROPERTY 客群属性]
     * @var integer
     */
    const CUSTOMER_PROPERTY = 7;
    /**
     * [CUSTOMER_INDUSTRY 消费偏好]
     * @var integer
     */
    const CUSTOMER_INDUSTRY = 8;
    /**
     * [CUSTOMER_DISTRIBUTION 客流来源热力图]
     * @var integer
     */
    const CUSTOMER_DISTRIBUTION =9;
    /**
     * [CUSTOMER_POI_RANKING 客流来源占比]
     * @var integer
     */
    const CUSTOMER_POI_RANKING =10;
    /**
     * [SEARCH_POIRANKING 搜索热点]
     * @var integer
     */
    const SEARCH_POIRANKING =11;
    /**
     * [MEDIA_POIRANKING APP分类排行]
     * @var integer
     */
    const MEDIA_POIRANKING =12;


    private $host = 'dmp-api-dev.100msh.com';
    // 客流数据
    private $passenger_flow_date_host = '/api/getPassengerFlowData.do';
    // 客流趋势数据
    private $passenger_flow_trend_date_host = '/api/getPassengerFlowTrend.do';
    // 获取24小时时段客流趋势数据
    private $passenger_24hours_flow_trend_date_host = '/api/getPassengerFlowTrendByHour.do';
    // 获取24小时时段客流趋势数据
    private $stop_time_host = '/api/getStayLengthAnalysis.do';
    // 同比
    private $year_on_year_analysis_host = '/api/getPassengerFlowAnalysis.do';
    // 新老
    private $new_and_old_host = '/api/getCustomerAnalysis.do';
    // 客群属性
    private $customer_property_host = '/api/getCustomerProperty.do';
    // 消费偏好
    private $customer_industry_host = '/api/getCustomerInterest.do';
    // 客流来源热力图
    private $customer_distribution_host = '/api/getCustomerDistributionHot.do';
    // 客流来源占比
    private $customer_poi_ranking_host = '/api/getCustomerDistributionPoi.do';
    // 搜索热点
    private $search_poiRanking_host = '/api/getSearchPoiRanking.do';
    // APP分类排行
    private $media_poiRanking_host = '/api/getCustomerAPP.do';




    public function passengerAction()
    {
        $type = Helper::clean($this->getRequest()->getParam('type'));
        $scene_id = Helper::clean($this->getRequest()->getParam('scene_id'));
        $time_interval = Helper::clean($this->getRequest()->getParam('time_interval',''));
        $start_date = Helper::clean($this->getRequest()->getParam('start_date',''));
        $end_date = Helper::clean($this->getRequest()->getParam('end_date',''));
        // 顾客类型
        $customer_type = Helper::clean($this->getRequest()->getParam('customer_type',''));
        $industry_id = Helper::clean($this->getRequest()->getParam('industry_id',''));
        // 一级分类id
        $category_type = Helper::clean($this->getRequest()->getParam('category_type',''));
        // 性别类型
        $gender_type = Helper::clean($this->getRequest()->getParam('gender_type',''));
        // APP分类类型
        $app_category_type = Helper::clean($this->getRequest()->getParam('app_category_type',''));
        // 常驻地类型
        $location_type = Helper::clean($this->getRequest()->getParam('location_type',''));
        // 年龄类型
        $age_type = Helper::clean($this->getRequest()->getParam('age_type',''));
        // 性别类型
        $gender_type = Helper::clean($this->getRequest()->getParam('gender_type',''));
        //数据粒度类型
        $gran_type = Helper::clean($this->getRequest()->getParam('gran_type',''));
        //矩形框选范围右上角的纬度
        $end_lat = Helper::clean($this->getRequest()->getParam('end_lat',''));
        //矩形框选范围右上角的经度
        $end_lng = Helper::clean($this->getRequest()->getParam('end_lng',''));
        //矩形框选范围左下角的纬度
        $start_lat = Helper::clean($this->getRequest()->getParam('start_lat',''));
        //矩形框选范围左下角的经度
        $start_lng = Helper::clean($this->getRequest()->getParam('start_lng',''));
        if (empty($type)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"接口编号不能为空"));
        }
        $scenes = Helper::M('Scenes');
        $token = $scenes->getToken();
        $url = '';
        $key_type = '';
        switch ($type) {
            // 客流数据
            case self::PASSENGER_FLOW_DATE:
                $url = $this->host.$this->passenger_flow_date_host.'?token='.$token.'&scene_id='.$scene_id;
                //var_dump($url);exit;
                $key_type = 'passenger_flow_date_scene_id'.$scene_id;
                break;
            // 客流趋势数据
            case self::PASSENGER_FLOW_TREND_DATE:
                $url = $this->host.$this->passenger_flow_trend_date_host.'?token='.$token.'&scene_id='.$scene_id.'&time_interval='.$time_interval;
                $key_type = 'passenger_flow_trend_date_scene_id'.$scene_id.'&time_interval'.$time_interval;
                break;
            // 获取24小时时段客流趋势数据
            case self::PASSENGER_24HOURS_FLOW_TREND_DATE:
                $url = $this->host.$this->passenger_24hours_flow_trend_date_host.'?token='.$token.'&scene_id='.$scene_id.'&time_interval='.$time_interval;
                $key_type = 'passenger_24hours_flow_trend_date_scene_id'.$scene_id.'&time_interval'.$time_interval;
                break;
            // 停留时间
            case self::STOP_TIME:
                $url = $this->host.$this->stop_time_host.'?token='.$token.'&scene_id='.$scene_id.'&time_interval='.$time_interval;
                $key_type = 'stop_time_scene_id'.$scene_id.'&time_interval'.$time_interval;
                break;
            // 同比分析
            case self::YEAR_ON_YEAR_ANALYSIS:
                $url = $this->host.$this->year_on_year_analysis_host.'?token='.$token.'&scene_id='.$scene_id.'&date1='.$start_date.'&date2='.$end_date;
                $key_type = 'year_on_year_analysis_scene_id'.$scene_id.'&date1'.$start_date.'&date2'.$end_date;
                break;
            // 新老对比
            case self::NEW_AND_OLD:
                $url = $this->host.$this->new_and_old_host.'?token='.$token.'&scene_id='.$scene_id.'&time_interval='.$time_interval;
                $key_type = 'new_and_old_scene_id'.$scene_id.'&time_interval'.$time_interval;
                break;
            // 客群属性
            case self::CUSTOMER_PROPERTY:
                $url = $this->host.$this->customer_property_host.'?token='.$token.'&scene_id='.$scene_id.'&start_date='.$start_date.'&end_date='.$end_date.'&customer_type='.$customer_type;
                $key_type = 'customer_property_scene_id'.$scene_id.'&start_date'.$start_date.'&end_date'.$end_date.'&customer_type'.$customer_type;
                break;
            // 消费偏好
            case self::CUSTOMER_INDUSTRY:
                $url = $this->host.$this->customer_industry_host.'?token='.$token.'&scene_id='.$scene_id.'&start_date='.$start_date.'&end_date='.$end_date.'&customer_type='.$customer_type.'&industry_id='.$industry_id;
                $key_type = 'customer_industry_scene_id'.$scene_id.
                '&start_date'.$start_date.
                '&end_date'.$end_date.
                '&customer_type'.$customer_type.
                '&industry_id'.$industry_id;
                break;
            // 客流来源热力图
            case self::CUSTOMER_DISTRIBUTION:
                $url = $this->host.$this->customer_distribution_host.
                '?token='.$token.
                '&scene_id='.$scene_id.
                '&start_date='.$start_date.
                '&end_date='.$end_date.
                '&customer_type='.$customer_type.
                '&gender_type='.$gender_type.
                '&age_type='.$age_type.
                '&location_type='.$location_type.
                '&start_lng='.$start_lng.
                '&start_lat='.$start_lat.
                '&end_lng='.$end_lng.
                '&end_lat='.$end_lat.
                '&gran_type='.$gran_type;
                $key_type = 'customer_distribution_scene_id'.$scene_id.
                '&start_date'.$start_date.
                '&end_date'.$end_date.
                '&customer_type'.$customer_type.
                '&gender_type'.$gender_type.
                '&age_type'.$age_type.
                '&location_type'.$location_type.
                '&start_lng'.$start_lng.
                '&start_lat'.$start_lat.
                '&end_lng'.$end_lng.
                '&end_lat'.$end_lat.
                '&gran_type'.$gran_type;
                break;
            // 客流来源占比
            case self::CUSTOMER_POI_RANKING:
                $url = $this->host.$this->customer_poi_ranking_host.'?token='.$token.
                '&scene_id='.$scene_id.
                '&start_date='.$start_date.
                '&end_date='.$end_date.
                '&customer_type='.$customer_type.
                '&gender_type='.$gender_type.
                '&age_type='.$age_type.
                '&location_type='.$location_type;
                $key_type = 'customer_poi_ranking_scene_id'.$scene_id.
                '&start_date'.$start_date.
                '&end_date'.$end_date.
                '&customer_type'.$customer_type.
                '&gender_type'.$gender_type.
                '&age_type'.$age_type.
                '&location_type'.$location_type;
                break;
            // 搜索热点
            case self::SEARCH_POIRANKING:
                $url = $this->host.$this->search_poiRanking_host.'?token='.$token.
                '&scene_id='.$scene_id.
                '&start_date='.$start_date.
                '&end_date='.$end_date.
                '&customer_type='.$customer_type.
                '&category_type='.$category_type;
                $key_type = 'search_poiRanking_scene_id'.$scene_id.
                '&start_date'.$start_date.
                '&end_date'.$end_date.
                '&customer_type'.$customer_type.
                '&category_type'.$category_type;
                break;
            // APP分类排行
            case self::MEDIA_POIRANKING:
                $url = $this->host.$this->media_poiRanking_host.'?token='.$token.
                '&scene_id='.$scene_id.
                '&start_date='.$start_date.
                '&end_date='.$end_date.
                '&customer_type='.$customer_type.
                '&app_category_type='.$app_category_type;
                $key_type = 'media_poiRanking_scene_id'.$scene_id.
                '&start_date'.$start_date.
                '&end_date'.$end_date.
                '&customer_type'.$customer_type.
                '&app_category_type'.$app_category_type;
                break;
        }

//        var_dump($url);exit;

        $data = $this->get_passenger($url,$key_type);
        Helper::outJson($data);
    }

    public function indexAction(){}
    public function cuspropetyAction(){}
    public function flowAnalysisAction(){}
    public function createanalysisAction(){}
    public function appanalysisAction(){}
    public function cusprefrenceAction(){}
    public function hotsearchAction(){}
    public function tradeanalysisAction(){}
    public function cussourceAction(){}
    public function changeanalysisAction(){}



    /**
     * [passenger_flow_trend_date 得到客流数据]
     * @return [type] [description]
     */
    protected function get_passenger($url,$key_type)
    {

        $url = $url;
        $data_array = '';

        //var_dump('ok');
        $data = DoNetwork::makeRequest($url,'','','','',5000);

        if (empty(json_decode($data,true)) || !isset(json_decode($data,true)['code']) || json_decode($data,true)['code'] != 10000) {
            $data_array = $this->redis->hGetAll($key_type);
        }else {
            $data_array = json_decode($data,true);
            $this->redis->hMset($key_type,$data_array);
        }
        return $data_array;
    }


    /**
     * [getAgentAdvertiser 得到代理商广告主]
     * @return [type] [description]
     */
    public function getAgentAdvertiserAction()
    {
        $uinfo = $_SESSION['uinfo'];
        //var_dump($uinfo);exit;
        $scenes = Helper::M('Scenes');
        $rel = $scenes->getAgentAdvertiser($uinfo['comp_id']);
        // 去掉已有场景的广告主
        $data = $this->removeAdvertiser($rel);

        Helper::outJson(array('state'=>self::SUCCESS_STATE,'data'=>$data));
    }

    /**
     * [removeAdvertiser 去除已有场景的广告主]
     * @param  [array] $advertiser_list []
     * @return [array]      [新数组]
     */
    protected function removeAdvertiser($advertiser_list)
    {
        $new_advertiser_list = array();
        foreach ($advertiser_list as $key => $value) {
            if ((int)$value['scene_id'] == 0 ) {
                unset($value['scene_id']);
                $new_advertiser_list[] = $value;
            }
        }

        return $new_advertiser_list;
    }

    /**
     * [getAdvertiserMac 得到指定广告主设备]
     * @return [type] [description]
     */
    public function getAdvertiserMacAction()
    {
        $uinfo = $_SESSION['uinfo'];
        $comp_id = Helper::clean($this->getRequest()->getParam('comp_id',0));
        if (empty($comp_id)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请设置广告主id"));
        }
        // 验证该广告主是否是该账户所有。
        $AdvertiserOwnership = $this->checkAdvertiserOwnership($comp_id);
        if ($AdvertiserOwnership != $uinfo['comp_id']) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"广告主不存在"));
        }
        // 得到设备
        $scenes = Helper::M('Scenes');
        $data = $scenes->getAdvertiserMac($comp_id);
        Helper::outJson(array('state'=>self::SUCCESS_STATE,'data'=>$data));
    }

    /**
     * [getSceneMac description]
     * @return [type] [获取场景设备]
     */
    public function getSceneMacAction()
    {
        $scene_id = Helper::clean($this->getRequest()->getParam('scene_id',0));
        if (empty($scene_id)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请设置场景id"));
        }
        $scenes = Helper::M('Scenes');
        $data = $scenes->getSceneMac($scene_id);
        Helper::outJson(array('state'=>self::SUCCESS_STATE,'data'=>$data));
    }

    /**
     * [checkAdvertiserOwnership 验证广告主的所有权]
     * @param  [type] $comp_id [description]
     * @return [type]          [description]
     */
    protected function checkAdvertiserOwnership($comp_id)
    {
        $scenes = Helper::M('Scenes');
        $where = 'comp_id = '.$comp_id.' and comp_type = 1';
        $rel = $scenes->getAdvertiserInfo($where);
        return $rel['comp_pid'];
    }

    /**
     * [checkSceneAction 验证是否能提交场景]
     * @return [array] [表单数组]
     */
    protected function checkScene()
    {

        $scenes_info['scene_name']  = Helper::clean($this->getRequest()->getPost('scene_name'));
        $scenes_info['comp_id']  = Helper::clean($this->getRequest()->getPost('comp_id'));
        $scenes_info['mac_id_array']  = $this->getRequest()->getPost('mac_id_array');
        $scenes_info['stop_time']  = Helper::clean($this->getRequest()->getPost('stop_time'));
        $scenes_info['detection_radius']  = Helper::clean($this->getRequest()->getPost('detection_radius'));
        $scenes_info['email']  = Helper::clean($this->getRequest()->getPost('email'));
        $scenes_info['business_start_time']  = Helper::clean($this->getRequest()->getPost('business_start_time'));
        $scenes_info['business_end_time']  = Helper::clean($this->getRequest()->getPost('business_end_time'));
        $scenes_info['area']  = Helper::clean($this->getRequest()->getPost('area'));
        $scenes_info['floor_number']  = Helper::clean($this->getRequest()->getPost('floor_number'));
        $scenes_info['longitude']  = Helper::clean($this->getRequest()->getPost('longitude'));
        $scenes_info['latitude']  = Helper::clean($this->getRequest()->getPost('latitude'));
        $scenes_info['term']  = Helper::clean($this->getRequest()->getPost('term'));
        $scenes_info['scenes_type']  = Helper::clean($this->getRequest()->getPost('scenes_type'));
        $scenes_info['scenes_state']  = Helper::clean($this->getRequest()->getPost('scenes_state'));
        $scenes_info['industry_id']  = Helper::clean($this->getRequest()->getPost('industry_id'));
        $scenes_info['is_auto_update']  = Helper::clean($this->getRequest()->getPost('is_auto_update',2));

        // 验证场景信息
        $this->checkScenesinfo($scenes_info);

        // 得到所需金额
        $amount = $this->getAmount($scenes_info['scenes_type'],$scenes_info['term']);

        // 不是保存到草稿且不是普通版
        if ($scenes_info['scenes_state'] != 1 && $scenes_info['scenes_type'] != 1) {
            // 得到服务商可用金额
            // $usable_amount = $this->getServerAvailableAmount();
            // 切换为可用资源
            $usable_amount = $this->getServerAvailableResources($scenes_info['scenes_type']);

            // 判断余额是否充足
            $this->checkSufficientBalance($amount,$usable_amount);
        }

        return $scenes_info;

    }

    /**
     * [checkUpdateScene 得到非草稿更新表单数据]
     * @return [type] [description]
     */
    protected function checkUpdateScene()
    {

        $scenes_info['scene_name']  = Helper::clean($this->getRequest()->getPost('scene_name'));
        $scene_id  = Helper::clean($this->getRequest()->getPost('scene_id'));
        $scenes_info['email']  = Helper::clean($this->getRequest()->getPost('email'));
        $scenes_info['scenes_type']  = Helper::clean($this->getRequest()->getPost('scenes_type'));
        $scenes_info['term']  = Helper::clean($this->getRequest()->getPost('term'));
        $scenes_info['industry_id']  = Helper::clean($this->getRequest()->getPost('industry_id'));
        $scenes_info['scenes_state'] = 2;
        // 验证广告主名称
        $this->checkSceneName($scenes_info['scene_name']);
        // 验证邮箱
        $this->checkEmail($scenes_info['email']);
        $scenes = Helper::M('Scenes');

        $scene_rel = $scenes->getSceneInfo($scene_id);

        // 普通版升级到高级版
        if ($scenes_info['scenes_type'] != 1 && $scene_rel['scenes_type'] == 1) {

            // 得到所需金额
            $amount = $this->getAmount($scenes_info['scenes_type'],$scenes_info['term']);
            // 得到服务商可用金额
            $usable_amount = $this->getServerAvailableResources($scenes_info['scenes_type']);

            // 判断余额是否充足
            $this->checkSufficientBalance($amount,$usable_amount);
        }

        return $scenes_info;
    }

    protected function checkSceneDraft()
    {
        $scenes_info['scene_name']  = Helper::clean($this->getRequest()->getPost('scene_name'));
        $scenes_info['stop_time']  = Helper::clean($this->getRequest()->getPost('stop_time'));
        $scenes_info['detection_radius']  = Helper::clean($this->getRequest()->getPost('detection_radius'));
        $scenes_info['email']  = Helper::clean($this->getRequest()->getPost('email'));
        $scenes_info['business_start_time']  = Helper::clean($this->getRequest()->getPost('business_start_time'));
        $scenes_info['business_end_time']  = Helper::clean($this->getRequest()->getPost('business_end_time'));
        $scenes_info['area']  = Helper::clean($this->getRequest()->getPost('area'));
        $scenes_info['floor_number']  = Helper::clean($this->getRequest()->getPost('floor_number'));
        $scenes_info['longitude']  = Helper::clean($this->getRequest()->getPost('longitude'));
        $scenes_info['latitude']  = Helper::clean($this->getRequest()->getPost('latitude'));
        $scenes_info['term']  = Helper::clean($this->getRequest()->getPost('term'));
        $scenes_info['scenes_type']  = Helper::clean($this->getRequest()->getPost('scenes_type'));
        $scenes_info['is_auto_update']  = Helper::clean($this->getRequest()->getPost('is_auto_update',2));
        $scenes_info['scenes_state']  = Helper::clean($this->getRequest()->getPost('scenes_state'));
        $scenes_info['industry_id']  = Helper::clean($this->getRequest()->getPost('industry_id'));


        // 验证场景名称
        $this->checkSceneName($scenes_info['scene_name']);

        // 验证人群筛选条件
        $this->checkScreeningConditions($scenes_info['detection_radius'],$scenes_info['stop_time']);

        // 验证邮箱
        $this->checkEmail($scenes_info['email']);

        // 验证营业面积
        $this->checkArea($scenes_info['area']);

        // 不是保存到草稿且普通版升级到高级版
        if ($scenes_info['scenes_state'] != 1 && $scenes_info['scenes_type'] != 1) {

            // 得到所需金额
            $amount = $this->getAmount($scenes_info['scenes_type'],$scenes_info['term']);
            // 得到服务商可用金额
            $usable_amount = $this->getServerAvailableResources($scenes_info['scenes_type']);

            // 判断余额是否充足
            $this->checkSufficientBalance($amount,$usable_amount);
        }

        return $scenes_info;

    }

    /**
     * [checkSceneApiAction 验证是否能提交场景对外接口]
     * @return [type] []
     */
    public function checkSceneApiAction()
    {

        $this->getSceneFormDate();
        Helper::outJson(array('state'=>self::SUCCESS_STATE,'msg'=>''));
    }


    /**
     * [sendShortMessageAction 发送短信]
     * @return [type] [description]
     */
    public function sendShortMessageAction()
    {
        $uinfo = $_SESSION['uinfo'];
        $code_key = $uinfo['staff_id'];
        $code = $this->getRandomCode();
        $mobile_phone = $this->getMobilePhone();
        $content = '付款验证码：'.$code.'，您正支付人群画像服务费用，请在3分钟内填写验证码完成验证。';
        $_SESSION['code_key'.$code_key] = $code;
        $result = BmDspApi::request('sms/send' , array('content' => $content ,'mobile_phone'=>$mobile_phone)); //发送短信验证码
        if(empty($result) || isset($result['error'])){
         Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>empty($result)?'短信发送异常，请稍后再试！':$result['error']));
        }else{
         Helper::outJson(array('state'=>self::SUCCESS_STATE,'msg'=> $result['msg']));
        }
    }


    protected function getSceneFormDate()
    {
        $scenes = Helper::M('Scenes');
        // 得到场景id
        $scene_id = Helper::clean($this->getRequest()->getPost('scene_id'));
        // 更新场景
        if (!empty($scene_id)) {
            // 得到场景信息
            $scene_rel = $scenes->getSceneInfo($scene_id);
            if ($scene_rel['scenes_state'] == 1) {
                // 草稿更新
                $scenes_info = $this->checkSceneDraft();
            }else {
                // 非草稿更新
                $scenes_info = $this->checkUpdateScene();
            }
            $scenes_info['comp_id'] = $scene_rel['comp_id'];
        // 新建高级版
        }else {
            // 新建
            $scenes_info = $this->checkScene();
        }

        return $scenes_info;
    }

    /**
     * [checkAmountSufficientApiAction 对外提供的检查余额是否足够的接口]
     * @return [type] [description]
     */
    public function checkAmountSufficientApiAction()
    {

        $scene_id = Helper::clean($this->getRequest()->getParam('scene_id'));
        $scene_type = Helper::clean($this->getRequest()->getParam('scene_type'));
        if (empty($scene_id)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请设置场景id"));
        }
        if (empty($scene_type)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请设置场景类型"));
        }
        $this->checkAmountSufficient($scene_id,$scene_type);
        Helper::outJson(array('state'=>self::SUCCESS_STATE,'msg'=>''));
    }

    protected function checkAmountSufficient($scene_id,$scene_type)
    {
        $scenes = Helper::M('Scenes');
        $scenes_info = $scenes->getSceneInfo($scene_id);

        $term = (int)date('Y',strtotime($scenes_info['service_end_time'])) - (int)date('Y',strtotime($scenes_info['service_start_time']));
        // 得到所需金额
        $amount = $this->getAmount($scene_type,$term);

        // 得到服务商可用金额
        $usable_amount = $this->getServerAvailableResources($scene_type);

        // 判断余额是否充足
        $this->checkSufficientBalance($amount,$usable_amount);
    }


    /**
     * [upgradeToApp 升级到app分析版]
     * @return [type] [description]
     */
    public function upgradeToAppAction()
    {

        $scene_id = Helper::clean($this->getRequest()->getPost('scene_id'));
        $code = Helper::clean($this->getRequest()->getPost('code'));
        if (empty($scene_id)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请设置场景id"));
        }
        $scenes = Helper::M('Scenes');

        $scenes_info = $scenes->getSceneInfo($scene_id);
        if ($scenes_info['scenes_type'] != 2) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"不符合升级条件"));
        }
        $term = (int)date('Y',strtotime($scenes_info['service_end_time'])) - (int)date('Y',strtotime($scenes_info['service_start_time']));
        // 得到所需金额
        $amount = $this->getAmount(3,$term);
        // 得到服务商可用金额
        $usable_amount = $this->getServerAvailableResources(3);
        // 判断余额是否充足
        $this->checkSufficientBalance($amount,$usable_amount);
        // 验证验证码是否正确
        $this->checkCode($code);
        // 插入充值记录表
        $uinfo = $_SESSION['uinfo'];
        $rel = $scenes->upgradeToApp($scene_id,$amount);
        if ($rel) {
            Helper::outJson(array('state'=>self::SUCCESS_STATE,'msg'=>'开通成功！'));
        }else{
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"开通失败！"));
        }
    }


    /**
     * [saveScenes 保存场景]
     * @param  [array] $scenes_info [场景信息数组]
     * @param  [int] $state        [场景状态,1草稿]
     * @return [type]              [description]
     */
    protected function saveScenes($scenes_info,$state,$transfer_number = 0)
    {
        $uinfo = $_SESSION['uinfo'];
        $scenes_info['comp_pid'] = $uinfo['comp_id'];
        $scenes_info['create_account'] = $uinfo['staff_id'];
        // 非高级版需要设置服务年限，高级版已经再支付接口里面设置了。
        if (!empty($scenes_info['term'])) {
            // 得到服务年限
            $service_time = $this->getServiceTime($scenes_info['term']);
            $scenes_info['service_start_time']  = $service_time['service_start_time'];
            $scenes_info['service_end_time']  = $service_time['service_end_time'];
        }
        if (isset($scenes_info['term'])) {
            unset($scenes_info['term']);
        }

        $scenes_info['scenes_state'] = $state;
        $mac_date = $scenes_info['mac_id_array'];
        unset($scenes_info['mac_id_array']);
        $scenes_info['create_time'] = date('Y-m-d H:i:s',time());
        $scene_date = $scenes_info;
        // 保存场景
        $scenes = Helper::M('Scenes');
        $rel = $scenes->saveScenes($scene_date,$mac_date,$transfer_number);
        if ($rel) {
            Helper::outJson(array('state'=>self::SUCCESS_STATE,'msg'=>'保存成功！'));
        }else{
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"保存失败！"));
        }

    }

    /**
     * [updateScenes 更新场景]
     * @param  [type] $scenes_info [场景信息]
     * @param  [type] $state       [场景状态,1草稿]
     * @param  [type] $scene_id    [场景id]
     * @return [type]              [description]
     */
    protected function updateScenes($scenes_info,$state,$scene_id,$transfer_number=0)
    {
        $scenes = Helper::M('Scenes');
        $uinfo = $_SESSION['uinfo'];
        $scenes_info['scenes_state'] = $state;

        // 得到场景信息
        $src_scenes_info = $scenes->getSceneInfo($scene_id);
        // 高级版不能变化
        if ($src_scenes_info['scenes_type'] == 2 && $src_scenes_info['scenes_state'] != 1 && $scenes_info['scenes_type'] == 1 ) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"高级版不能换成普通版"));
        }

        // app版不能变化
        if ($src_scenes_info['scenes_type'] == 3 && $scenes_info['scenes_type'] != 3 ) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"app版不可更改成其他版本"));
        }

        // 得到服务年限
        if (!empty($scenes_info['term']) && $src_scenes_info['scenes_state'] == 1 && isset($scenes_info['term'])) {
            $service_time = $this->getServiceTime($scenes_info['term']);
            $scenes_info['service_start_time']  = $service_time['service_start_time'];
            $scenes_info['service_end_time']  = $service_time['service_end_time'];
        }

        if (isset($scenes_info['term'])) {
            unset($scenes_info['term']);
        }
        // 更新时间，防止不修改场景导致受影响行数为0
        $scenes_info['update_time'] = date('Y-m-d H:i:s',time());

        // 更新场景
        $rel = $scenes->updateScenes($scenes_info,$scene_id,$transfer_number);
        if ($rel) {
            Helper::outJson(array('state'=>self::SUCCESS_STATE,'msg'=>'保存成功！'));
        }else{
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"保存失败！"));
        }
    }


    /**
     * [saveScenesApiAction 更新场景]
     * @return [type] [description]
     */
    public function updateScenesApiAction()
    {

        $scene_id = Helper::clean($this->getRequest()->getPost('scene_id'));
        if (!$scene_id) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"场景id不能为空"));
        }
        $scenes = Helper::M('Scenes');
        /**
         * [$scenes_info 得到表单数据]
         * @var [type]
         */
        $scenes_info = $this->getSceneFormDate();
        // 得到场景信息
        $scene_rel = $scenes->getSceneInfo($scene_id);

        // 由低级生高级升级到高级版，且保存非草稿
        if ($scenes_info['scenes_type'] == 2 && $scenes_info['scenes_state'] == 2 && ($scene_rel['scenes_state'] == 1 || ($scene_rel['scenes_state'] != 1 && $scene_rel['scenes_type'] == 1 ) )) {
            $this->payment($scenes_info);
        }
        // 保存
        $this->updateScenes($scenes_info,$scenes_info['scenes_state'],$scene_id);
    }

    /**
     * [getAppointSceneAction 得到指定场景]
     * @return [type] [description]
     */
    public function getAppointSceneAction()
    {
        $scene_id = Helper::clean($this->getRequest()->getParam('scene_id'));
        if (empty($scene_id)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请设置scene_id"));
        }


        $scenes = Helper::M('Scenes');
        if (empty($scenes->getSceneInfo($scene_id))) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"该场景不存在"));
        }
        $data = $scenes->getAppointScene($scene_id);
        Helper::outJson(array('state'=>self::SUCCESS_STATE,'data'=>$data));
    }

    public function sceneListAction()
    {
        $uinfo = $_SESSION['uinfo'];
        $page = Helper::clean($this->getRequest()->getParam('page',1));
        $pagecount = Helper::clean($this->getRequest()->getParam('pagecount',9));
        $scenes_state = Helper::clean($this->getRequest()->getParam('scenes_state'));
        $key_word = Helper::clean($this->getRequest()->getParam('key_word'));

        $start=($page-1)*$pagecount;
        $limit = 'limit '.$start.','.$pagecount;
        $where = ' where a.comp_pid = '.$uinfo['comp_id'];
        if (!empty($scenes_state)) {
            $where .= ' and a.scenes_state = '.$scenes_state;
        }
        if ($key_word !== '' && $key_word !== null ) {
            $where .= ' and (a.scene_name like "%'.$key_word.'%"'.' or b.comp_name like "%'.$key_word.'%" )';
        }
        $scenes = Helper::M('Scenes');
        $data = $scenes->sceneList($limit,$where);
        $count = count($scenes->sceneList('',$where));
        $total_page_count = ceil($count/$pagecount);
        Helper::outJson(array('state'=>self::SUCCESS_STATE,'data'=>$data,'page'=>$page,'count'=>$count,'total_page_count'=>$total_page_count));
    }


    /**
     * [getServiceTime 得到服务年限]
     * @param  [type] $term []
     * @return [type]       [description]
     */
    protected function getServiceTime($term)
    {
        $service_start_time  = date('Y-m-d',time());
        $term_str = '+'.$term.' year';
        $service_end_time  = date('Y-m-d',strtotime($term_str));
        return ['service_start_time'=>$service_start_time,'service_end_time'=>$service_end_time];
    }

    /**
     * [getRandomCode 得到随机码]
     * @return [type] [description]
     */
    protected function getRandomCode()
    {
         return rand(1000,9999);
    }

    /**
     * [getMobilePhone 得到手机号码]
     * @return [type] [description]
     */
    protected function getMobilePhone()
    {
        $uinfo = $_SESSION['uinfo'];
        $scenes = Helper::M('Scenes');
        $phone = $scenes->getMobilePhone($uinfo['comp_id']);
        return $phone;
    }


    /**
     * [checkSufficientBalance 判断余额是否足够]
     * @param  [type] $amount        [所需金额]
     * @param  [type] $usable_amount [可用余额]
     * @return [type]                [description]
     */
    protected function checkSufficientBalance($amount,$usable_amount)
    {
        if ($amount>$usable_amount) {
            Helper::outJson(array('state'=>self::NOT_ENOUGH,'msg'=>"人群画像次数不够！"));
        }
    }

    /**
     * [checkCode 验证验证码输入是否正确]
     * @param  [type] $code [输入的验证码]
     * @return [type]       [description]
     */
    protected function checkCode($code)
    {
        $uinfo = $_SESSION['uinfo'];
        $code_key = $uinfo['staff_id'];
        if (empty($_SESSION['code_key'.$code_key]) || !isset($_SESSION['code_key'.$code_key])) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"短信session失效！"));
        }
        if ($_SESSION['code_key'.$code_key] != $code) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"验证码输入错误！"));
        }
         $_SESSION['code_key'.$code_key] = '';
    }

    /**
     * [getAmount 得到所需金额]
     * @param  [int] $scenes_type [场景类型]
     * @param  [int] $term        [年限]
     * @param  [bool] $cent        [是否转化为分]
     * @return [type]              [description]
     */
    protected function getAmount($scenes_type,$term,$cent = true)
    {
        $amount = 0;
        switch ($scenes_type) {
            // 高级版
            case 2:
                $amount = $term*self::SENIOR;
                break;
            // app分析版
            case 3:
                $amount = $term*self::APP;
                break;
        }

        return $amount;
    }

    /**
     * [checkScenesinfo 验证场景信息是否符合要求]
     * @param  array  $scenesinfo [description]
     * @return [type]             [void]
     */
    protected  function checkScenesinfo(array $scenes_info)
    {
        // 验证场景名称
        $this->checkSceneName($scenes_info['scene_name']);
        // 验证广告主
        $this->checkAdvertiser($scenes_info['comp_id']);
        // 验证广告主是否绑定设备
        $this->checkAdvertiserMac($scenes_info['mac_id_array']);

        // 验证人群筛选条件
        $this->checkScreeningConditions($scenes_info['detection_radius'],$scenes_info['stop_time']);

        // 验证邮箱
        $this->checkEmail($scenes_info['email']);

        // 验证营业面积
        $this->checkArea($scenes_info['area']);

        // 验证场景版本
        $this->checkSceneType($scenes_info['scenes_type']);

    }

    /**
     * [checkSceneType 验证场景版本]
     * @param  [type] $scenes_type [description]
     * @return [type]              [description]
     */
    protected function checkSceneType($scenes_type)
    {
        if (empty($scenes_type) || !in_array($scenes_type,array(1,2))) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请选择正确的场景版本！"));
        }

    }

    /**
     * [checkAdvertiserMac 检查广告主是否绑定设备]
     * @param  [type] $mac_id_array [description]
     * @return [type]               [description]
     */
    protected function checkAdvertiserMac($mac_id_array)
    {
        //var_dump($mac_id_array);
        if (count($mac_id_array) <= 0 || !is_array($mac_id_array)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请先为该广告主绑定设备！"));
        }

    }

    /**
     * [checkSceneName 验证场景名称]
     * @param  [type] $scene_name [场景名称]
     * @return [type]             [void]
     */
    protected function checkSceneName($scene_name)
    {
        // 必填
        if ($scene_name === '' || $scene_name === false || $scene_name === null) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请输入场景名称！"));
        }
        //最大长度为32个汉字
        if (strlen($scene_name) > self::SCENE_NAME_LEN) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"场景名称最多32个汉字"));
        }

    }

    /**
     * [checkSceneName 验证广告主]
     * @param  [type] $comp_id [广告主id]
     * @return [type]             [void]
     */
    protected function checkAdvertiser($comp_id)
    {
        $uinfo = $_SESSION['uinfo'];
        // 必填
        if (empty($comp_id)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请选择广告主！"));
        }
        // 验证该广告主是否是该账户所有。
        $AdvertiserOwnership = $this->checkAdvertiserOwnership($comp_id);
        if ($AdvertiserOwnership != $uinfo['comp_id']) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"广告主不存在"));
        }

        // 验证广告主是否已经存在场景
        $this->checkAdvertiserScene($comp_id);

    }

    /**
     * [checkAdvertiserScene 验证广告主是否存在场景]
     * @param  [type] $comp_id [广告主id]
     * @return [type]          [void]
     */
    protected function checkAdvertiserScene($comp_id)
    {
        $scenes = Helper::M('Scenes');
        $rel = $scenes->getAdvertiserScene($comp_id);
        if (!empty($rel)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"广告主已存在场景"));
        }
    }

    /**
     * [checkScreeningConditions 验证人群筛选条件]
     * @param  [int] $detection_radius [探测半径]
     * @param  [int] $stop_time        [停留标准]
     * @return [type]                   [void]
     */
    protected function checkScreeningConditions($detection_radius,$stop_time)
    {
        // 必填
        if (empty($detection_radius) || empty($stop_time)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"筛选条件不能为空！"));
        }
        if ( $detection_radius>self::MAX_DETECTION_RADIUS || $detection_radius<self::MIN_DETECTION_RADIUS) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"探测半径不符合要求！"));
        }
        if ( $stop_time>self::MAX_STOP_TIME || $stop_time<self::MIN_STOP_TIME) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"停留标准不符合要求！"));
        }
    }

    /**
     * [checkEmail 验证邮箱]
     * @param  [string] $email [邮箱]
     * @return [type]        [description]
     */
    protected function checkEmail($email)
    {
        // 必填
        if (empty($email)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请输入邮箱！"));
        }

        if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"请输入正确格式的邮箱！"));
        }
    }

    /**
     * [checkArea 验证建筑面积]
     * @param  [int] $area [面积]
     * @return [type]       [description]
     */
    protected function checkArea($area)
    {
        if (!(is_numeric($area) && $area>0)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"建筑面积必须是大于0的数字"));
        }
        if ($area > 99999999.99) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"建筑面积超过最大值，请填写真实的建筑面积！"));
        }


    }


    /**************************************************************   人群画像v2 ****************************************/

    /**
     * [saveScenesApiAction 保存场景]
     * @return [type] [description]
     */
    public function saveScenesApiAction()
    {

        $scenes = Helper::M('Scenes');
        /**
         * [$scenes_info 得到表单数据]
         * @var [type]
         */
        $scenes_info = $this->getSceneFormDate();
        // 保存非草稿的高级版
        if ($scenes_info['scenes_type'] == 2 && $scenes_info['scenes_state'] == 2) {

            $this->payment($scenes_info);
        }
        // 保存
        $this->saveScenes($scenes_info,$scenes_info['scenes_state'],$scenes_info['term']);
    }


    /**
     * [paymentAction 支付接口]
     * @return [type] [description]
     */
    public function payment($scenes_info)
    {
        $scenes = Helper::M('Scenes');
        // 得到场景id
        $scene_id = Helper::clean($this->getRequest()->getPost('scene_id'));
        // 得到验证码
        $code = Helper::clean($this->getRequest()->getPost('code'));
        // 验证验证码是否正确
        $this->checkCode($code);
        // 插入充值记录表
        $uinfo = $_SESSION['uinfo'];
        // 更新服务年限
        // 得到服务年限
        $service_time = $this->getServiceTime($scenes_info['term']);
        $scenes_info['service_start_time']  = $service_time['service_start_time'];
        $scenes_info['service_end_time']  = $service_time['service_end_time'];
        $transfer_number = $scenes_info['term'];
        unset($scenes_info['term']);
        if ($scene_id) {
            // 更新场景
            $this->updateScenes($scenes_info,2,$scene_id,$transfer_number);
        }else {
            // 保存场景
            $this->saveScenes($scenes_info,2,$transfer_number);
        }
    }


    /**
     * [getServerAvailableAmount-》getServerAvailableResources 得到服务商可用资源]
     * @return [type] [description]
     */
    protected function getServerAvailableResources($resource_type)
    {
        $uinfo = $_SESSION['uinfo'];
        $scenes = Helper::M('Scenes');
        $data = $scenes->getServerAvailableResources($uinfo['comp_id']);

        if (empty($data)) {
            Helper::outJson(array('state'=>self::FAIL_STATE,'msg'=>"账户人群画像资源为0！"));
        }
        if ($resource_type == 2) {
            return $data['high_num'];
        }elseif ($resource_type == 3) {
            return $data['app_num'];
        }

    }

}
