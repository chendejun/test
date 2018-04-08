<?php
class DeviceController extends BasicController {

	// 查询路由设备在线详情
	protected $routeOnlineDetail = '/route/routeOnlineDetail.do';
	// 查看设备某日期段的探测详情
	protected $routeMacsDetail = '/route/routeMacsDetail.do';
	protected $macLastSniffed = '/route/macLastSniffed.do';

	/**
	 * [listAction 设备列表]
	 * @return [type] [description]
	 */
	public function listAction(){

		$page = Helper::clean($this->getRequest()->getParam('page' , 1));
		$keyword = Helper::clean($this->getRequest()->getParam('keyword',''));
        $online_status = Helper::clean($this->getRequest()->getParam('online_status' , ''));
        $limit = Helper::clean($this->getRequest()->getParam('limit' , 15));
        $start = $page>0?($page-1)*$limit:0;
		$uinfo = $_SESSION['uinfo'];
        $where = ' and a.comp_pid='. $uinfo['comp_id'];
		if (!empty($keyword)) {
			// 分解mac
			$mac_str = $this->restructureMac($keyword);
			$where .= ' and (b.comp_name like "%'.$keyword.'%"';
			$where .= ' or a.mac_addr like "%'.$mac_str.'%" )';
		}
        if($online_status != ''){
            $where .= " and a.mac_status = ".$online_status;
        }
		$accountModel = Helper::M('Account');
     	$account_ids=$accountModel->getComp_ids();
		if(!empty($account_ids) && $account_ids != '-100'){
            $where .=  ' and a.comp_id in('.$account_ids.')';
        }
        $Device = Helper::M('Device');
		//var_dump($where);
        $list = $Device->getDeviceList($where , $start , $limit);
		$count = count($Device->getDeviceList($where));
		$pages = ceil($count/$limit); //总页数
        Helper::outJson(array('state'=>1 , 'msg'=>'','list'=>$list,'totalPages'=>$pages,'page'=>$page ));
	}


	public function indexAction()
	{
	}

	public function testAction(){}

	public function deviceDetailAction(){}

	protected function restructureMac($mac_addr)
	{
		// 分解MAC
		$mac_arr = explode(':',$mac_addr);
		$mac_str = '';
		if (is_array($mac_arr)) {
			foreach ($mac_arr as $value) {
				$mac_str .= $value;
			}
		}else {
			$mac_str = $mac_arr;
		}

		return $mac_str;
	}

	/**
	 * [createAction 添加设备]
	 * @return [type] [description]
	 */
	public function createAction(){
        $device = Helper::M('Device');
        if(isset($_POST)&&!empty($_POST)) {

			$uinfo = $_SESSION['uinfo'];
            $data=array();
            $data['comp_pid'] = $uinfo['comp_id'];
            $data['comp_id'] = intval($this->getRequest()->getPost('comp_id'));
            $mac_addr = Helper::clean($this->getRequest()->getPost('device_mac'));
			$mac_addr = strtoupper($mac_addr);
            $data['mac_set'] = Helper::clean($this->getRequest()->getPost('position_des'));
            $data['add_time'] = strtotime(date('Y-m-d H:i:s',time()));
            // 验证
            if(!$data['comp_id']){
                Helper::outJson(array('state' => 0, 'msg' => '绑定的广告主不可为空！'));
            }
            if(!$mac_addr){
                Helper::outJson(array('state' => 0, 'msg' => '设备MAC地址不能为空！'));
            }
			if($data['mac_set'] === 0 || $data['mac_set'] === '0' ){
				Helper::outJson(array('state' => 0, 'msg' => '设备放置位置不能为0！'));
			}

			if(!$data['mac_set']){
                Helper::outJson(array('state' => 0, 'msg' => '设备放置位置不能为空！'));
            }

            // 分解MAC
			$mac_str = $this->restructureMac($mac_addr);

			$mac_arr = explode(',',$mac_str);
			if (!is_array($mac_arr)) {
				$mac_arr =  [$mac_arr];
			}

			foreach ($mac_arr as $key => $value) {
				$mac_str = $value;
				// 验证mac是否存在
	            preg_match("/^[0-9a-fA-F]+$/", $mac_str, $mac_match);
				if (empty($mac_match) || strlen($mac_str) != 12) {
					Helper::outJson(array('state' => 0, 'msg' => 'mac地址不合法！'));
				}

				$data['mac_addr'] = $mac_str;
				$data['mac_status'] = 0;
				$accountModel = Helper::M('Account');
		     	$account_ids=$accountModel->getComp_ids();
				$where = 'comp_id='.$data['comp_id'].' and comp_type = 1 and comp_pid='.$uinfo['comp_id'];
				if(!empty($account_ids) && $account_ids != '-100'){
		            $where .=  ' and comp_id in('.$account_ids.')';
		        }
	            // 验证广告主是否存在
	            if (!$device->getCompanyConfig($where)) {
	            	Helper::outJson(array('state' => 0, 'msg' => '广告主不存在'));
	            }

	            // 验证设备是否已近绑定
	            if ($device->getDeviceConfig('mac_addr="'.$mac_str.'"'.' and bind_status = 1')) {
	            	Helper::outJson(array('state' => 0, 'msg' => '设备已经绑定'));
	            }

	            // 验证位置
	            if (strlen($data['mac_set'])>45) {
	            	Helper::outJson(array('state' => 0, 'msg' => '位置不能超过15个汉字！'));
	            }
				$data['add_staff_id'] = $uinfo['staff_id'];
	            // 添加
	            $dev_id = $device->addDevice($data);
			}

			if ($dev_id) {
				Helper::outJson(array('state' => 1, 'msg' => '添加成功'));
			}else {
				Helper::outJson(array('state' => 0, 'msg' => '添加失败'));
			}


        }else {
			Helper::outJson(array('state' => 0, 'msg' => '非法请求'));
		}
	}

	/**
	 * [getDeviceRelieveInfoAction 得到设备解绑详情]
	 * @return [type] [description]
	 */
	public function getDeviceRelieveInfoAction()
	{

		$mac_id = Helper::clean($this->getRequest()->getParam('mac_id'));
		if (empty($mac_id)) {
			Helper::outJson(array('state' => 0, 'msg' => '请设置mac_id'));
		}
		$device_info_data = $this->getDeviceInfo($mac_id);
		$route_id = $device_info_data['mac_addr'];
		// 查询路由设备在线详情
		$data_arr_data = $this->getRouteOnlineDetail($route_id);
		$data = array_merge($data_arr_data,$device_info_data);

		unset($data['mac_status']);
		unset($data['day_user_num']);
		unset($data['yes_user_num']);
		unset($data['old_user_num']);
		Helper::outJson(array('state' => 1, 'data'=>$data));
	}

	/**
	 * [getUserMacToRouterMac 查询用户mac被最新探到的路由设备详情]
	 * 1 通过广告主得到所有路由设备
	 * 2 通过路由设备，和用户mac查询大数据接口，得到最新路由设备
	 * 3 根据最新路由设备得到放置位置
	 * 4 根据服务商得到余额
	 * 5 拼接数据
	 * @return [type] [description]
	 */
	public function getRouterMacToUserMacAction()
	{
		$comp_id = Helper::clean($this->getRequest()->getParam('comp_id'));
		$user_mac = Helper::clean($this->getRequest()->getParam('user_mac'));
		$accountModel = Helper::M('Account');
		$device = Helper::M('Device');
		$this->checkParam('用户mac',$user_mac);
		$uinfo = $_SESSION['uinfo'];
		$route_id_str = '';
		if (!empty($comp_id)) {
			// 验证广告主的合法性
			$account_ids=$accountModel->getComp_ids();
			$where = 'comp_id='.$comp_id.' and comp_type = 1 and comp_pid='.$uinfo['comp_id'];
			if(!empty($account_ids) && $account_ids != '-100'){
				$where .=  ' and comp_id in('.$account_ids.')';
			}
			// 验证广告主是否存在
			if (!$device->getCompanyConfig($where)) {
				Helper::outJson(array('state' => 0, 'msg' => '广告主不存在'));
			}
			// 获取广告主所有路由mac
			$where = 'comp_id='.$comp_id.' and bind_status = 1';
			$rel = $device->getAllDevice($where);
			$route_id_str = '';
			foreach ($rel as $key => $value) {
				if ($key == 0) {
					$route_id_str = $value['mac_addr'];
				}else {
					$route_id_str =  $route_id_str.','.$value['mac_addr'];
				}

			}

			$route_id_str = strtoupper($route_id_str);
		}

		$user_mac = strtoupper($user_mac);
		// 调用大数据接口，得到最新路由设备
		$data = $this->getMacLastSniffed($user_mac,$route_id_str);
		if (empty($data)) {
			Helper::outJson(array('state' => 0, 'msg'=>'大数据找不到相应mac'));
		}
		// 获取最新设备配置信息
		$rel = $device->getDeviceConfig('mac_addr="'.$data['route_id'].'"');
		// 获取可用余额
		$result = BmDspApi::request('Scene/getAllServerSceneResource', array('comp_id' => $uinfo['comp_id']));

		if (!empty($result) && $result['api_code'] == 0 && isset($result['data'])) {
			$new_data = [
				'mac_set'=>$rel['mac_set'],
				'one_num'=>$result['data'][$uinfo['comp_id']]['one_num']
			];
        }

		Helper::outJson(array('state' => 1, 'data'=>$new_data));
	}



	/**
	 * [getMacLastSniffed 得到最新的路由设备]
	 * @param  [type] $user_mac     [description]
	 * @param  [type] $route_id_str [description]
	 * @return [type]               [description]
	 */
	protected function getMacLastSniffed($user_mac,$route_id_str)
	{
		$scenes = Helper::M('Scenes');
		$data_arr = [];
		// 获取token
		$token = $scenes->getToken();
		$config = Yaf_Registry::get("config");
		// 域名
		$host = $config['dsp.host'];
		$url = $host.$this->macLastSniffed.'?token='.$token.'&route_id='.$route_id_str.
		'&mac='.$user_mac;
		//var_dump($url);exit;
		$data = DoNetwork::makeRequest($url,'GET');
        $data_arr = json_decode($data,true);
		if (is_array($data_arr) && array_key_exists('code',$data_arr) && $data_arr['code'] == 10000 ) {
			$this->redis->set('dsp_'.$this->macLastSniffed.'_'.$route_id_str.'_'.$user_mac.'_'.date('YmdH',time()),$data_arr,60*60);
			$data_arr_data = $data_arr['data'];
		}else {
			$redis_rel = $this->redis->get('dsp_'.$this->macLastSniffed.'_'.$route_id_str.'_'.$user_mac.'_'.date('YmdH',time()));
			$data_arr_data = $redis_rel['data'];
		}

		return $data_arr_data;

	}

	/**
	 * [getRouteMacsDetailAction 查看设备某日期段的探测详情]
	 * @return [type] [description]
	 */
	public function getRouteMacsDetailAction()
	{
		$stat_day = Helper::clean($this->getRequest()->getParam('stat_day'));
		$page_num = Helper::clean($this->getRequest()->getParam('page_num'));
		$page_size = Helper::clean($this->getRequest()->getParam('page_size'));
		$mac_id = Helper::clean($this->getRequest()->getParam('mac_id'));
		$mac = Helper::clean($this->getRequest()->getParam('user_mac'));

		$this->checkParam('时间段',$stat_day);
		$this->checkParam('mac_id',$mac_id);
		$this->checkParam('页码',$page_num);
		// 查找mac地址
		$device_info_data = $this->getDeviceInfo($mac_id);
		if (empty($device_info_data) && !array_key_exists('mac_addr',$device_info_data)) {
			Helper::outJson(array('state' => 0, 'msg'=>'不存在该路由设备'));
		}

		$route_id = $device_info_data['mac_addr'];
		// 转化为大写
		$route_id = strtoupper($route_id);
		$mac = strtoupper($mac);
		$scenes = Helper::M('Scenes');
		$data_arr = [];
		// 获取token
		$token = $scenes->getToken();
		$config = Yaf_Registry::get("config");
		// 域名
		$host = $config['dsp.host'];
		$url = $host.$this->routeMacsDetail.'?token='.$token.'&route_id='.$route_id.
		'&stat_day='.$stat_day.'&page_num='.$page_num.'&page_size='.$page_size.'&mac='.$mac;
		$data = DoNetwork::makeRequest($url,'GET');
        $data_arr = json_decode($data,true);
		if (is_array($data_arr) && array_key_exists('code',$data_arr) && $data_arr['code'] == 10000 ) {
			$this->redis->set('dsp_'.$this->routeMacsDetail.'_'.$route_id.'_'.$stat_day.'_'.$page_num.'_'.$page_size.'_'.$mac.'_'.date('YmdH',time()),$data_arr,60*60);
			$data_arr_data = $data_arr['data'];
		}else {
			$redis_rel = $this->redis->get('dsp_'.$this->routeMacsDetail.'_'.$route_id.'_'.$stat_day.'_'.$page_num.'_'.$page_size.'_'.$mac.'_'.date('YmdH',time()));
			$data_arr_data = $redis_rel['data'];
		}
		Helper::outJson(array('state' => 1, 'data'=>$data_arr_data));

	}

	/**
	 * [getRouteOnlineDetail 查询路由设备在线详情]
	 * @return [type] [description]
	 */
	protected function getRouteOnlineDetail($route_id)
	{

		// 调用大数据接口得到探测信息
		$scenes = Helper::M('Scenes');
		$token = $scenes->getToken();
		$config = Yaf_Registry::get("config");
		$host = $config['dsp.host'];
		$data_arr = [];
		$url = $host.$this->routeOnlineDetail.'?token='.$token.'&route_id='.$route_id;
		$data = DoNetwork::makeRequest($url,'GET');
        $data_arr = json_decode($data,true);
		if (is_array($data_arr) && array_key_exists('code',$data_arr) && $data_arr['code'] == 10000  ) {
			$this->redis->set('dsp_'.$this->routeOnlineDetail.'_'.$route_id.'_'.date('YmdH',time()),$data_arr,60*60);
			$data_arr_data = $data_arr['data'];
		}else {
			$redis_rel = $this->redis->get('dsp_'.$this->routeOnlineDetail.'_'.$route_id.'_'.date('YmdH',time()));
			$data_arr_data = $redis_rel['data'];
		}

		return $data_arr_data;
	}

	/**
	 * [getDeviceInfo 得到设备绑定详情]
	 * @param  [type] $mac_id [设备id]
	 * @return [type]         [description]
	 */
	protected function getDeviceInfo($mac_id)
	{
		if (empty($mac_id)) {
			Helper::outJson(array('state' => 0, 'msg' => '请设置mac_id'));
		}
		$uinfo = $_SESSION['uinfo'];
		$device = Helper::M('Device');
		$where = ' and a.comp_pid='. $uinfo['comp_id'].' and a.mac_id = '.$mac_id.' and a.bind_status=1';
		$data = $device->getDeviceList($where,0,1);
		if (!$data) {
			Helper::outJson(array('state' => 0, 'msg'=>'设备不存在'));
		}
		return $data[0];
	}


    // 设备解绑
	public function deviceRelieveAction()
	{
		if (isset($_POST) && !empty($_POST)) {
			$mac_id = Helper::clean($this->getRequest()->getPost('mac_id'));
			if (empty($mac_id)) {
				Helper::outJson(array('state' => 0, 'msg' => '请设置mac_id'));
			}
			// 验证设备是否存在
			if (!$this->getDeviceInfo($mac_id)) {
				Helper::outJson(array('state' => 0, 'msg' => '设备不存在'));
			}
			// 解绑
			$device = Helper::M('Device');
			$id = $device->relieve($mac_id);

			if ($id) {
				Helper::outJson(array('state' => 1, 'msg' => '解绑成功'));
			}else {
				Helper::outJson(array('state' => 0, 'msg' => '解绑失败'));
			}
		}else {
			Helper::outJson(array('state' => 0, 'msg' => '非法请求'));
		}

	}

	public function getDeviceSumAction()
	{

		$data = [
			'deviceStatistics'=>$this->getDeviceStatistics(),
			'advertiserStatistics'=>$this->getAdvertiserStatistics(),
			'detectionNumber'=>$this->getDetectionNumber(),
		];

		Helper::outJson(array('state' => 1, 'data' => $data));



	}

	/**
	 * [getDeviceStatistics 设备统计]
	 * @return [type] [description]
	 */
	public function getDeviceStatistics()
	{

		$uinfo = $_SESSION['uinfo'];
		$device = Helper::M('Device');
		$accountModel = Helper::M('Account');
		$account_ids=$accountModel->getComp_ids();

        // 在线数量
		$where = ' and a.comp_pid='.$uinfo['comp_id'].' and a.mac_status=1';
		if(!empty($account_ids) && $account_ids != '-100'){
			$where .=  ' and a.comp_id in('.$account_ids.')';
		}
		$on_line_num = count($device->getDeviceList($where));

		// 总数量
		$where = ' and a.comp_pid='.$uinfo['comp_id'];
		if(!empty($account_ids) && $account_ids != '-100'){
			$where .=  ' and a.comp_id in('.$account_ids.')';
		}
		$total_num = count($device->getDeviceList($where));

		// 离线数量
		$off_line_num = (int)$total_num - (int)$on_line_num;

		$rel_data = [
			'on_line_num'=>$on_line_num,
			'off_line_num'=>$off_line_num,
			'total_num'=>$total_num
		];

		return $rel_data;
	}

	/**
	 * [getDeviceStatistics 广告主统计]
	 * @return [type] [description]
	 */
	public function getAdvertiserStatistics()
	{
		$uinfo = $_SESSION['uinfo'];

		$device = Helper::M('Device');
		$accountModel = Helper::M('Account');
		$account_ids=$accountModel->getComp_ids();
        // 所有广告主
		$where = ' comp_pid = '.$uinfo['comp_id'].' and comp_type=1 ';
		if(!empty($account_ids) && $account_ids != '-100'){
			$where .=  ' and comp_id in('.$account_ids.')';
		}

		$total_num = $device->getAllCompanyCount($where);

		// 已绑定设备广告主
		$where = ' and a.comp_pid = '.$uinfo['comp_id'].' and a.comp_type=1 ';
		if(!empty($account_ids) && $account_ids != '-100'){
			$where .=  ' and a.comp_id in('.$account_ids.')';
		}
		$binding_num = count($device->getBindingCompany($where));

        // 未绑定设备
    	$unbound_num = (int)$total_num - (int)$binding_num;

		$rel_data = [
			'binding_num'=>$binding_num,
			'unbound_num'=>$unbound_num,
			'total_num'=>$total_num
		];

		return $rel_data;

	}

	/**
	 * [getDeviceStatistics 探测人数]
	 * @return [type] [description]
	 */
	public function getDetectionNumber()
	{

		$uinfo = $_SESSION['uinfo'];

		$device = Helper::M('Device');
		$accountModel = Helper::M('Account');
		$account_ids=$accountModel->getComp_ids();
		// 今日
		$where = ' bind_status=1 and comp_pid= '.$uinfo['comp_id'];
		if(!empty($account_ids) && $account_ids != '-100'){
			$where .=  ' and comp_id in('.$account_ids.')';
		}
		$total_num = $device->getDetectionNumber($where);

		if ($total_num['day_user_num'] == 0 ) {
			$rate = 0;

		}

		$total_num['rate'] = 0;
		$total_num['increase'] = 1;
		if ($total_num['yes_user_num'] != 0 ) {
			$total_num['rate'] = round(abs($total_num['day_user_num']-$total_num['yes_user_num'])/$total_num['yes_user_num'],4);
			$total_num['rate'] = sprintf("%.4f",$total_num['rate']);
			// $total_num['rate'] = 2.71388888888888888888889;
		}

		if ($total_num['day_user_num']>=$total_num['yes_user_num']) {
			$total_num['increase'] = 1;
		}elseif ($total_num['day_user_num']<$total_num['yes_user_num']) {
			$total_num['increase'] = 2;
		}

		return $total_num;

	}

	public function setMacAction()
	{
		if (isset($_POST) && !empty($_POST)) {
			$uinfo = $_SESSION['uinfo'];
			$device = Helper::M('Device');
			$mac_id = Helper::clean($this->getRequest()->getPost('mac_id'));
			$position_des = strtolower(Helper::clean($this->getRequest()->getPost('position_des')));
			if (empty($mac_id)) {
				Helper::outJson(array('state' => 0, 'msg' => '请设置mac_id'));
			}
			if($position_des === 0 || $position_des === '0' ){
				Helper::outJson(array('state' => 0, 'msg' => '设备放置位置不能为0！'));
			}
			if (empty($position_des)) {
				Helper::outJson(array('state' => 0, 'msg' => '请输入放置位置'));
			}
			if (strlen($position_des)>45) {
            	Helper::outJson(array('state' => 0, 'msg' => '位置不能超过15个汉字！'));
            }


			$accountModel = Helper::M('Account');
	     	$account_ids=$accountModel->getComp_ids();
			$where = 'mac_id='.$mac_id.' and comp_pid='.$uinfo['comp_id'];
			if(!empty($account_ids) && $account_ids != '-100'){
	            $where .=  ' and comp_id in('.$account_ids.')';
	        }
			$device_info = $device->getDeviceConfig($where);

			// 验证设备是否有权限
            if (!$device_info) {
            	Helper::outJson(array('state' => 0, 'msg' => '没有操作权限'));
            }
            // 不改变放置位置直接返回成功
			if ($device_info['mac_set'] == $position_des) {
				Helper::outJson(array('state' => 1, 'msg' => '保存成功'));
			}
			$data = [
				'mac_set'=>$position_des
			];
			$id = $device->deviceUpdate($data,'mac_id='.$mac_id);
			if ($id) {
				Helper::outJson(array('state' => 1, 'msg' => '保存成功'));
			}else {
				Helper::outJson(array('state' => 0, 'msg' => '保存失败'));
			}
		}else {
			Helper::outJson(array('state' => 0, 'msg' => '非法请求'));
		}

	}


	protected function checkParam($param_column,$param_value,$param_Range = array())
    {
        if (empty($param_value)) {
            Helper::outJson(array('state'=>0,'msg'=>'请输入：'.$param_column));
        }
        if (count($param_Range)>0) {
            if (!in_array($param_value,$param_Range)) {
                Helper::outJson(array('state'=>0,'msg'=>'参数'.$param_column.'不合法'));
            }
        }
    }

}
