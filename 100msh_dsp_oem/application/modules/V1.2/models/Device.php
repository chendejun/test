<?php
class Device extends Model
{

    public function getDeviceList($where , $start = 0 , $limit = '')
    {

        $limit_str = '';
        if (!empty($limit)) {
            $limit_str = ' limit '.$start.','.$limit;
        }

        $field = 'a.mac_id,a.comp_id,a.mac_addr,a.mac_status,a.mac_set,a.day_user_num,a.yes_user_num,a.old_user_num,b.comp_name';

        $sql = 'select '.$field.' from dsp_mac_list as a inner join dsp_company as b on a.comp_id = b.comp_id
        where a.bind_status = 1 '.$where.' group by a.mac_addr order by a.add_time desc '.$limit_str;

        $rel = $this->db->dsp->query($sql);

        return $rel;


    }


    public function addDevice($data)
    {
        $scenes = Helper::M('Scenes');
        $id = $this->db->dsp->table('dsp_mac_list')->data($data)->insert();
        $mac_str = '';
        if ($id) {
            $rel = $this->db->dsp->table('dsp_scenes')->where('comp_id='.$data['comp_id'].' and is_auto_update = 2 and scenes_state != 3')->select();
            // 存在场景，更新大数据场景推送
            if ($rel && count($rel)>0) {
                foreach ($rel as $key => $rel_value) {
                    $sql = ' select b.mac_addr from dsp_scenes_mac as a inner join dsp_mac_list as b on a.mac_id = b.mac_id
                    where a.scene_id = '.$rel_value['scene_id'];
                    $mac_rel = $this->db->dsp->query($sql);
                    $len = count($mac_rel);
                    foreach ($mac_rel as $key => $value) {
                        $mac_str .= $value['mac_addr'].',';
                    }
                    $mac_str .= $data['mac_addr'];
                    $add_secne_mac_id = $this->db->dsp->table('dsp_scenes_mac')->data(['scene_id'=>$rel_value['scene_id'],'mac_id'=>$id])->insert();
                    // 该场景不是草稿还需要更新大数据接口
                    if ($add_secne_mac_id && $rel_value['scenes_state'] == 2) {
                        $scenes->updateScenesConfig($rel_value['scene_id'],$mac_str,$rel_value['detection_radius'],$rel_value['stop_time'],$rel_value['industry_id'],$rel_value['baidu_code']);
                    }
                }
            }
        }
        return $id;
    }

    // 得到广告主配置
    public function getCompanyConfig($where)
    {
        $rel = $this->db->dsp->table('dsp_company')->where($where)->find();

        return $rel;
    }

    public function getDeviceConfig($where)
    {
        $rel = $this->db->dsp->table('dsp_mac_list')->where($where)->find();

        return $rel;

    }

    public function getAllDevice($where)
    {
        $rel = $this->db->dsp->table('dsp_mac_list')->where($where)->select();

        return $rel;

    }


    /**
     * [deviceUpdate 更新设备]
     * @param  [type] $data  [数据]
     * @param  [type] $where [条件]
     * @return [type]        [设备id]
     */
    public function deviceUpdate($data,$where)
    {
        $id = $this->db->dsp->table('dsp_mac_list')->data($data)->where($where)->update();
        return $id;

    }

    // 解绑
    public function relieve($mac_id)
    {
        $scenes = Helper::M('Scenes');
        $uinfo = $_SESSION['uinfo'];
        $data = [
            'bind_status'=>2,
            'edit_time'=>strtotime(date('Y-m-d H:i:s',time())),
            'edit_staff_id'=>$uinfo['staff_id']
        ];
        $where = ' mac_id= '.$mac_id;
        $id = $this->deviceUpdate($data,$where);
        if ($id) {
            $data = $this->getDeviceConfig('mac_id='.$mac_id);
            $rel = $this->db->dsp->table('dsp_scenes')->where('comp_id='.$data['comp_id'].' and is_auto_update = 2 and scenes_state != 3 ')->select();
            $mac_str = '';
            // 存在场景，更新大数据场景推送
            if ($rel && count($rel)>0) {
                foreach ($rel as $key => $rel_value) {
                    $sql = ' select b.mac_addr from dsp_scenes_mac as a inner join dsp_mac_list as b on a.mac_id = b.mac_id
                    where a.scene_id = '.$rel_value['scene_id'].' and b.mac_id !='.$mac_id;
                    $mac_rel = $this->db->dsp->query($sql);
                    $len = count($mac_rel);
                    foreach ($mac_rel as $key => $value) {
                        if ($key == $len-1) {
                            $mac_str .= $value['mac_addr'];
                        }else {
                            $mac_str .= $value['mac_addr'].',';
                        }
                    }
                    // 更新场景设备表
                    $add_secne_mac_id = $this->db->dsp->table('dsp_scenes_mac')->where('mac_id='.$mac_id)->delete();
                    // 该场景不是草稿还需要更新大数据接口
                    if ($add_secne_mac_id && $rel_value['scenes_state'] == 2 ) {
                        $scenes->updateScenesConfig($rel_value['scene_id'],$mac_str,$rel_value['detection_radius'],$rel_value['stop_time'],$rel_value['industry_id'],$rel_value['baidu_code']);
                    }
                }
            }
        }
        return $id;

    }

    public function getAllCompanyCount($where)
    {

        $rel = $this->db->dsp->table('dsp_company')->where($where)->count();
        return $rel;

    }

    public function getBindingCompany($where)
    {
        $sql = 'select a.comp_id as count from dsp_company as a inner join dsp_mac_list as b on a.comp_id=b.comp_id
        where b.bind_status = 1 '.$where.' group by a.comp_id ';
        $rel = $this->db->dsp->query($sql);
        return $rel;
    }


    public function getDetectionNumber($where)
    {
        $field = 'ifnull(sum(day_user_num),0) as day_user_num,
        ifnull(sum(yes_user_num),0) as yes_user_num,
        ifnull(sum(old_user_num),0) as old_user_num
        ';
        $rel = $this->db->dsp->table('dsp_mac_list')->field($field)->where($where)->find();

        return $rel;
    }

}
