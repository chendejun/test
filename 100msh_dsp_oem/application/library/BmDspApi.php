<?php
/**
 * 请求接口
 */
class BmDspApi{
	private static $apiUri = 'https://dev-oemapi.100msh.com/';
	private static $version = 'v1';
	private static $apiMap = array(
		'oauth/access_token',
		'fund/sendSms',
        'advertiser/add',
        'fund/applyTransfer',
        'advertiser/dlsAccInfo',
        'advertiser/upImg',
        'advertiser/getCate',
        'advertiser/getScList',
        'advertiser/view',
        'advertiser/advList',
        'fund/createPayOrder',
        'fund/applyInvoice',
        'fund/get',
        'fund/invoiceList',
		'fund/payment',
        'consume/countData',
        'consume/tbDateList',
        'advertiser/recharge',
        'advertiser/openGdt',
        'advertiser/transfer',
        'advertiser/edit',
        'advertiser/getZkBalance',
        'fund/crontab',
        'advertiser/getGdtStatus',
        'fund/freezeAmount',
        'advertiser/refreshZkBalance',
        'advertiser/dlsStatInfo',
        'advertiser/indexStatInfo',
        'advertiser/info',
        'advertiser/advRecharge',
        'advertiser/operate',
        'advertiser/setOperate',
        'advertiser/advRecharList',
        'sms/send',
        'agent/advList',
        'agent/add',
        'agent/edit',
        'agent/stop',
        'agent/recover',
        'agent/view',
        'agent/figure',
		'Scene/getAllServerSceneResource',
		'Scene/getTransferResourceRecord',
		'Scene/getServerSceneUseInfo',
		'Scene/transferServerSceneResource',
        'Scene/getServerResourceTransferMonthRecord',
        'Scene/getServerSmsInfo',
        'Scene/getAdvertiserSceneInfo',
        'advertiser/aptList',
        'BhDeliveryAchieved/advertisedList',
        'BhDeliveryAchieved/getAdvertAreaInfo',
		'bhad/getCampaignList',
		'bhad/getDeliveryList',
		'bhad/getDeliveryInfo',
		'bhad/getCompList',
        'wechat/getOperator',
        'wechat/getCompList',
        'wechat/view',
	);
	static public function request($fuc , $args = [] , $method = 'GET' , $header=[] , $cookie=''){
		if(!in_array($fuc, self::$apiMap)) exit('找不到该方法！');
		$url = self::$apiUri . self::$version . '/' . $fuc;
		if($fuc != 'oauth/access_token'){
			$args['access_token'] = Yaf_Registry::get('BmApiAccessToken');
		}
		$result = DoNetwork::makeRequest($url , $method , $args , $header , $cookie);
        if($fuc == 'wechat/getCompList') {
           //var_dump($result);
        }
        if($fuc == 'fund/crontab'){
            var_dump($result);
        }
		if(!empty($result)) return json_decode($result , true);
		return false;
	}

}
