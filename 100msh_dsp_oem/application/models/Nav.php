<?php
class Nav extends Model{
	public function getNavByGroup($group_id , $level = 0){
		$row = $this->db->dsp->table('dsp_oem_group')->field('nav_ids')->where("group_id='{$group_id}'")->find();
		if(empty($row)) return false;
		$nav_ids = json_decode($row['nav_ids'] , true);
		$list = $this->db->dsp->query("SELECT nav_id,title,icon,href,pid,spread FROM dsp_oem_nav WHERE nav_id IN(".implode(',', $nav_ids).")  AND `status`=1 ORDER BY nav_id ASC");
		$data = array();
		foreach ($list as $key => $val) {
			if($level == 3 && $val['nav_id'] == 19) continue;
			if($val['pid'] == 0){
				$data[$val['nav_id']] = $val;
			}else{
				$data[$val['pid']]['children'][] = $val;
			}
		}
		return $data;
	}
	/**
	 * 获取皮肤
	 * @param  [type] $comp_id [description]
	 * @return [type]          [description]
	 */
	public function getSkin($comp_id = 0 , $url = ''){
		$where = '';
		if($comp_id > 0){
			$where = "comp_pid={$comp_id}";
		}else{
			$list = explode('.', $url);
			$where = "domain='{$list[0]}' AND domain_desc='.{$list[1]}.{$list[2]}'";
		}
		return $this->db->dsp->table('dsp_company_site')->field('comp_pid,site_name,site_logo,site_bottom_name,site_color,domain')->where($where)->find();
	}
}