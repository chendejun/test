<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/4
 * Time: 12:00
 */
class DcrowdController extends BasicController{
    /**
     * 初始化标签库redis
     */
    public function tagRAction(){
        $crowdModel = Helper::M("Dcrowd");
        $tagAll = $crowdModel->getTagALL();
        foreach ($tagAll as $k => $v){
            $this->redis->set('tagLabel_'.$v['c_tag_id'],$v['label_id']);
        }
        echo "初始化成功<br/>";
    }
    /**
     * 人群包列表
     */
    public function indexAction(){
        $page = Helper::clean($this->getRequest()->getParam('page' , 0));
        $crowdModel = Helper::M("Dcrowd");
        if($page > 0){
            //搜索
            $where = "cri.comp_pid=" . $this->uinfo['comp_id']." AND cri.if_show = 1 AND label_info != ''";
            $audit_status = Helper::clean($this->getRequest()->getParam('audit_status' , -1));
            if($audit_status != -1){
                $where.=" AND cri.audit_status=".$audit_status;
            }
            $start_date = Helper::clean($this->getRequest()->getParam('start_date' , ''));
            $end_date = Helper::clean($this->getRequest()->getParam('end_date' , ''));
            if(!empty($start_date) && !empty($end_date)){
                $where.=" AND cri.add_time >= ".strtotime($start_date." "."00:00:00")." AND cri.add_time <=".strtotime($end_date." "."23:59:59");
            }
            $keyword = Helper::clean($this->getRequest()->getParam('keyword' , ''));
            if(!empty($keyword)){
                $where.=" AND cri.crowd_name LIKE '%".$keyword."%'";
            }
            //分页

            $numPerPage = Helper::clean($this->getRequest()->getParam('limit' , 10));//每页显示条数
            $start = $page>0?($page-1)*$numPerPage:0;


            $list = $crowdModel->getCrowdlList($where , $start , $numPerPage);
            foreach ($list as $k=>$v){
                if($v['audit_status'] == 2){
                    $audit_info=$crowdModel->getCrowdAuditInfo($v['crowd_id']);
                    $list[$k]['audit_remark']=$audit_info['audit_remark'];
                }
            }
            $count = $crowdModel->getCrowdCount($where);
            $pages = ceil($count/$numPerPage);
            //统计数据
            $stadata=array();
            $stadata['count']=$crowdModel->getCrowdCount("cri.comp_pid=" . $this->uinfo['comp_id']." AND cri.if_show = 1 AND label_info != ''");
            $statics_data=$crowdModel->getStaticsData("comp_pid=" . $this->uinfo['comp_id']." AND if_show = 1 AND label_info != ''");
            $stadata['count_noAudit']=$stadata['count_succAudit']=$stadata['count_failAudit']=$stadata['count_succHandel']=0;
            foreach ($statics_data as $k=>$v){
                switch ($v['audit_status']){
                    case 0:
                        $stadata['count_noAudit']=$v['count'];
                        break;
                    case 1:
                        $stadata['count_succAudit']=$v['count'];
                        break;
                    case 2:
                        $stadata['count_failAudit']=$v['count'];
                        break;
                    case 3:
                        $stadata['count_succHandel']=$v['count'];
                        break;
                }
            }
            //新增限制
            $w=date('W');//本年度第几周
            $add_count=$crowdModel->getCrowdCount("WEEK(FROM_UNIXTIME(cri.add_time,'%Y-%m-%d'),3)=".$w." AND cri.comp_pid=" . $this->uinfo['comp_id']." AND audit_status in(0,1,3) AND cri.if_show = 1 AND label_info != ''");
            $if_add=$add_count >= $this->config['crowd']['dls_crowd_nums']?0:1;
            Helper::outJson(array('state'=>1,'msg'=>'','data'=>array('pages' => $pages ,'if_add' => $if_add, 'stadata' => $stadata, 'list' => $list)));
        }
    }

    /**
     * 申请人群包
     * ``
     */
    public function applyAction(){
        if(isset($_POST)&&!empty($_POST)){
            $crowdModel = Helper::M("Dcrowd");
            $data=$label_arr=$tag_arr=array();
            //1区域
            $reg_all=array();
            $region = Helper::clean($_POST['region']);
            if(!empty($region)){
                $r_ids=explode(',',$region);
                $label_arr['region']=$r_ids;
                foreach ($r_ids as $reg){
                    $reg_info=$crowdModel->getTagInfoById($reg);
                    if($reg_info['pid'] == 0){
                        $city_arr=$crowdModel->getCityTagList($reg_info['c_tag_id']);
                        //print_r($city_arr);
                        foreach ($city_arr as $ka=>$va){
                          $c_arr[$ka]['id']=$va['label_id'];
                        }
                        $reg_all=array_merge($reg_all,$c_arr);
                    }else{
                        array_push($reg_all,array('id'=>$this->redis->get('tagLabel_'.$reg)));
                    }
                }
                $tag_arr['and'][]=array('or'=>$reg_all);
            }
            //2年龄
            $age = Helper::clean($_POST['age']);
            if(!empty($age)){
                $age_arr=array();
                $age_ids=$crowdModel->getTagByW("c_tag_id in (".$age.")");
                foreach ($age_ids as $av){
                    $age_arr[]['id']=$this->redis->get('tagLabel_'.$av['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$age_arr);
                $label_arr['age']=explode(',',$age);
            }
            //3受教育水平
            $education = Helper::clean($_POST['education']);
            if(!empty($education)){
                $education_arr=array();
                $education_ids=$crowdModel->getTagByW("c_tag_id in (".$education.")");
                foreach ($education_ids as $ev){
                    $education_arr[]['id']=$this->redis->get('tagLabel_'.$ev['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$education_arr);
                $label_arr['education']=explode(',',$education);
            }
            //4从业行业
            $industry = Helper::clean($_POST['industry']);
            if(!empty($industry)){
                $industry_arr=array();
                $industry_ids=$crowdModel->getTagByW("c_tag_id in (".$industry.")");
                foreach ($industry_ids as $iv){
                    $industry_arr[]['id']=$this->redis->get('tagLabel_'.$iv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$industry_arr);
                $label_arr['industry']=explode(',',$industry);
            }
            //5房产
            $house = Helper::clean($_POST['house']);
            if(!empty($house)){
                $house_arr=array();
                $house_ids=$crowdModel->getTagByW("c_tag_id in (".$house.")");
                foreach ($house_ids as $hv){
                    $house_arr[]['id']=$this->redis->get('tagLabel_'.$hv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$house_arr);
                $label_arr['house']=explode(',',$house);
            }
            //6汽车
            $car = Helper::clean($_POST['car']);
            if(!empty($car)){
                $car_arr=array();
                $car_ids=$crowdModel->getTagByW("c_tag_id in (".$car.")");
                foreach ($car_ids as $cv){
                    $car_arr[]['id']=$this->redis->get('tagLabel_'.$cv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$car_arr);
                $label_arr['car']=explode(',',$car);
            }
            //7建材家居
            $home = Helper::clean($_POST['home']);
            if(!empty($home)){
                $home_arr=array();
                $home_ids=$crowdModel->getTagByW("c_tag_id in (".$home.")");
                foreach ($home_ids as $ov){
                    $home_arr[]['id']=$this->redis->get('tagLabel_'.$ov['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$home_arr);
                $label_arr['home']=explode(',',$home);
            }
            //8家电数码
            $digital = Helper::clean($_POST['digital']);
            if(!empty($digital)){
                $digital_arr=array();
                $digital_ids=$crowdModel->getTagByW("c_tag_id in (".$digital.")");
                foreach ($digital_ids as $dv){
                    $digital_arr[]['id']=$this->redis->get('tagLabel_'.$dv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$digital_arr);
                $label_arr['digital']=explode(',',$digital);
            }
            //9生活服务
            $service = Helper::clean($_POST['service']);
            if(!empty($service)){
                $service_arr=array();
                $service_ids=$crowdModel->getTagByW("c_tag_id in (".$service.")");
                foreach ($service_ids as $vv){
                    $service_arr[]['id']=$this->redis->get('tagLabel_'.$vv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$service_arr);
                $label_arr['service']=explode(',',$service);
            }
            //10休闲爱好
            $hobby = Helper::clean($_POST['hobby']);
            if(!empty($hobby)){
                $hobby_arr=array();
                $hobby_ids=$crowdModel->getTagByW("c_tag_id in (".$hobby.")");
                foreach ($hobby_ids as $yv){
                    $hobby_arr[]['id']=$this->redis->get('tagLabel_'.$yv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$hobby_arr);
                $label_arr['hobby']=explode(',',$hobby);
            }
            //11影视音乐
            $music = Helper::clean($_POST['music']);
            if(!empty($music)){
                $music_arr=array();
                $music_ids=$crowdModel->getTagByW("c_tag_id in (".$music.")");
                foreach ($music_ids as $mv){
                    $music_arr[]['id']=$this->redis->get('tagLabel_'.$mv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$music_arr);
                $label_arr['music']=explode(',',$music);
            }
            //12游戏
            $game = Helper::clean($_POST['game']);
            if(!empty($game)){
                $game_arr=array();
                $game_ids=$crowdModel->getTagByW("c_tag_id in (".$game.")");
                foreach ($game_ids as $gv){
                    $game_arr[]['id']=$this->redis->get('tagLabel_'.$gv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$game_arr);
                $label_arr['game']=explode(',',$game);
            }
            //13服饰鞋包
            $dress = Helper::clean($_POST['dress']);
            if(!empty($dress)){
                $dress_arr=array();
                $dress_ids=$crowdModel->getTagByW("c_tag_id in (".$dress.")");
                foreach ($dress_ids as $rv){
                    $dress_arr[]['id']=$this->redis->get('tagLabel_'.$rv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$dress_arr);
                $label_arr['dress']=explode(',',$dress);
            }
            //14医疗健康
            $health = Helper::clean($_POST['health']);
            if(!empty($health)){
                $health_arr=array();
                $health_ids=$crowdModel->getTagByW("c_tag_id in (".$health.")");
                foreach ($health_ids as $lv){
                    $health_arr[]['id']=$this->redis->get('tagLabel_'.$lv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$health_arr);
                $label_arr['health']=explode(',',$health);
            }
            //15个护美容
            $beauty = Helper::clean($_POST['beauty']);
            if(!empty($beauty)){
                $beauty_arr=array();
                $beauty_ids=$crowdModel->getTagByW("c_tag_id in (".$beauty.")");
                foreach ($beauty_ids as $uv){
                    $beauty_arr[]['id']=$this->redis->get('tagLabel_'.$uv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$beauty_arr);
                $label_arr['beauty']=explode(',',$beauty);
            }
            //16教育培训
            $train = Helper::clean($_POST['train']);
            if(!empty($train)){
                $train_arr=array();
                $train_ids=$crowdModel->getTagByW("c_tag_id in (".$train.")");
                foreach ($train_ids as $tv){
                    $train_arr[]['id']=$this->redis->get('tagLabel_'.$tv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$train_arr);
                $label_arr['train']=explode(',',$train);
            }
            //17旅游酒店
            $tourism = Helper::clean($_POST['tourism']);
            if(!empty($tourism)){
                $tourism_arr=array();
                $tourism_ids=$crowdModel->getTagByW("c_tag_id in (".$tourism.")");
                foreach ($tourism_ids as $zv){
                    $tourism_arr[]['id']=$this->redis->get('tagLabel_'.$zv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$tourism_arr);
                $label_arr['tourism']=explode(',',$tourism);
            }
            //18母婴亲子
            $baby = Helper::clean($_POST['baby']);
            if(!empty($baby)){
                $baby_arr=array();
                $baby_ids=$crowdModel->getTagByW("c_tag_id in (".$baby.")");
                foreach ($baby_ids as $bv){
                    $baby_arr[]['id']=$this->redis->get('tagLabel_'.$bv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$baby_arr);
                $label_arr['baby']=explode(',',$baby);
            }
            //19体育健身
            $sports = Helper::clean($_POST['sports']);
            if(!empty($sports)){
                $sports_arr=array();
                $sports_ids=$crowdModel->getTagByW("c_tag_id in (".$sports.")");
                foreach ($sports_ids as $pv){
                    $sports_arr[]['id']=$this->redis->get('tagLabel_'.$pv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$sports_arr);
                $label_arr['sports']=explode(',',$sports);
            }
            //20网络购物
            $shopping = Helper::clean($_POST['shopping']);
            if(!empty($shopping)){
                $shopping_arr=array();
                $shopping_ids=$crowdModel->getTagByW("c_tag_id in (".$shopping.")");
                foreach ($shopping_ids as $sv){
                    $shopping_arr[]['id']=$this->redis->get('tagLabel_'.$sv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$shopping_arr);
                $label_arr['shopping']=explode(',',$shopping);
            }
            //21性别
            $sex = Helper::clean($_POST['sex']);
            if(!empty($sex)){
                $sex_arr=explode(',',$this->redis->get('tagLabel_'.$sex));
                foreach ($sex_arr as $ks=>$vs){
                    $sex_list[$ks]['id']=$vs;
                }
                $tag_arr['and'][]=array('or'=>$sex_list);
                $label_arr['sex']=explode(',',$sex);
            }
            if(empty($label_arr)){
                Helper::outJson(array('state'=>0 , 'msg' => '请选择人群包标签！'));
            }
            $crowdName = Helper::clean($_POST['crowd_name']);
            $data['crowd_name'] = $crowdName;
            $data['comp_id'] =  0;//广告主ID
            $data['comp_pid'] =  $this->uinfo['comp_id'];//服务商ID
            $data['add_staff_id'] = $this->uinfo['staff_id'];//用户ID
            $data['add_time'] = time();
            $data['label_info'] = json_encode($label_arr);
            if(empty($data['crowd_name'])){
                Helper::outJson(array('state'=>0 , 'msg' => '人群包名称不能为空'));
            }
            //请求DMP接口获取覆盖用户数
            $this->config = Yaf_Registry::get('config');
            //获取token
            if(!$this->redis->exists('dmp_access_token'.date('Ymd'))){
                $this->setDmpToken();
            }
            //print_r($tag_arr);
            $dmp_tagConditions=json_encode($tag_arr);
            //echo $dmp_tagConditions;exit;
            $dmp_token=$this->redis->get('dmp_access_token');
            $dmp_ts=time();
            $key=$this->config['dmp']['des_secret'];
            $des = new CryptDes($key);
            $s=$des->encrypt("tagConditions=".$dmp_tagConditions."&token=".$dmp_token."&ts=".$dmp_ts);
            $users_result = DoNetwork::makeRequest($this->config['dmp']['api_host'].$this->config['dmp']['api_dir'].'getTagGroupCount.do?s=' . urlencode($s), 'POST','','','',500000 );
            //var_dump($users_result);exit;
            $users_result = json_decode($users_result , true);
            //print_r($users_result);
            if($users_result['errcode'] == 0){
                if($users_result['data'] == 0){
                    Helper::outJson(array('state'=>0 , 'msg' => '您选择的自定义人群人数为0，请重新选择用户属性!'));
                }
                $data['cover_users']=$users_result['data'];
            }else{
                Helper::outJson(array('state'=>0 , 'msg' => 'dmp请求人群覆盖数量失败'));
            }
            //print_r($data);
            //人群包信息入库
            $this->db = Yaf_Registry::get('db');
            $this->db->dsp->startTrans();
            $res_crowd=$crowdModel->add($data);
            if($res_crowd){
                if($users_result['data'] <= 10000){
                    $audit_arr=array();
                    $audit_arr['crowd_id']=$res_crowd;
                    $audit_arr['audit_status']=1;
                    $audit_arr['audit_staff_id']=0;
                    $audit_arr['audit_time']=time();
                    $audit_arr['audit_remark']='系统默认审核通过';
                    $a_res=$crowdModel->addAudit($audit_arr);
                    if($a_res){
                        //覆盖用户小于10000不用审核，直接进入运算中
                        $ap_arr=$edit_data=array();
                        $ap_arr['tagConditions']=$dmp_tagConditions;
                        $crowd_apply=DoNetwork::makeRequest('https://dev-dspservice.100msh.com/api/getCrowd', 'POST' , $ap_arr);
                        $crowd_apply=json_decode($crowd_apply , true);
                        if(!empty($crowd_apply) && $crowd_apply['errcode'] == 0){
                            $edit_data['crowd_taskid']=$crowd_apply['data'];
                        }else{
                            $this->db->dsp->rollback();
                            Helper::outJson(array('state'=>0 , 'msg' => 'dmp请求人群包失败'));
                        }
                        $edit_data['audit_status']=1;
                        $e_res=$crowdModel->edit($res_crowd,$edit_data);

                        if(!$e_res){
                            $this->db->dsp->rollback();
                            Helper::outJson(array('state'=>0 , 'msg' => '修改人群包状态失败'));
                        }

                    }else{
                        $this->db->dsp->rollback();
                        Helper::outJson(array('state'=>0 , 'msg' => '审核记录添加失败'));
                    }
                }
                $this->db->dsp->commit();
                //添加操作日志
                $log_content[] = "人群包管理 :". "人群包申请";
                $log_content_str = implode(" , ",$log_content);
                $log_obj = Helper::M('Log');
                $log_obj->create_log('人群包申请',$log_content_str,'LC005',"LOT001");
                Helper::outJson(array('state'=>1 , 'msg' => '申请人群包成功'));
            }else{
                Helper::outJson(array('state'=>0 , 'msg' => '人群包入库失败'));
            }

        }
    }
    /**
     * 获取tag地区列表
     */
    public function getTagAction(){
        $crowdModel = Helper::M("Dcrowd");
        $pid = Helper::clean($this->getRequest()->getParam('pid' , 0));
        $tagList = $crowdModel->getTag($pid,1);
        //print_r($tagList);
        Helper::outJson(array('state'=>1,'msg'=>'','data'=>$tagList));
    }
    /**
     * 获取tag其他列表
     */
    public function getLabelAction(){
        $crowdModel = Helper::M("Dcrowd");
        $age=$crowdModel->getTag(0,2);
        $age_new=array($age[0],$age[3],$age[2],$age[4],$age[5],$age[1],$age[6]);
        $tagList=array(
            'age' => $age_new,
            'education' => $crowdModel->getTag(0,3),
            'industry' => $crowdModel->getTag(0,4),
            'house' => $crowdModel->getTag(0,5),
            'car' => $crowdModel->getTag(0,6),
            'home' => $crowdModel->getTag(0,7),
            'digital' => $crowdModel->getTag(0,8),
            'service' => $crowdModel->getTag(0,9),
            'hobby' => $crowdModel->getTag(0,10),
            'music' => $crowdModel->getTag(0,11),
            'game' => $crowdModel->getTag(0,12),
            'dress' => $crowdModel->getTag(0,13),
            'health' => $crowdModel->getTag(0,14),
            'beauty' => $crowdModel->getTag(0,15),
            'train' => $crowdModel->getTag(0,16),
            'tourism' => $crowdModel->getTag(0,17),
            'baby' => $crowdModel->getTag(0,18),
            'sports' => $crowdModel->getTag(0,19),
            'shopping' => $crowdModel->getTag(0,20),
            'sex' => $crowdModel->getTag(0,21),

        );
        $pid = Helper::clean($this->getRequest()->getParam('pid' , 0));
        //$tagList = $crowdModel->getTag($pid,1);
        //print_r($tagList);
        Helper::outJson(array('state'=>1,'msg'=>'','data'=>$tagList));
    }
    /**
     * 人群包详情
     */
    public function detailAction(){
        $crowdModel = Helper::M("Dcrowd");
        $crowd_id = Helper::clean($this->getRequest()->getParam('crowd_id' , 0));
        if(empty($crowd_id)){
            Helper::outJson(array('state'=>0 , 'msg' => '参数错误'));
        }
        $crowd_info=$crowdModel->getCrowdById($crowd_id);
        $tag_list=array();
        $label_info=json_decode($crowd_info['label_info'],true);
        foreach ($label_info as $k => $v){
            $t_name=array();
            switch ($k){
                case 'region':
                $reg_ids=$label_info[$k];
                sort($reg_ids);
                foreach($reg_ids as $rv){
                    if(intval($rv)>0){
                        $t_name[] = $crowdModel->getCrowdTagN($rv);
                    }
                }
                break;
                default:
                $ids=implode(',',$label_info[$k]);
                $list=$crowdModel->getTagByW("c_tag_id in (".$ids.")");
                foreach ($list as $lv){
                    $t_name[]=$lv['tag_name'];
                }
            }
            if(!empty($t_name)){
                $tag_list[] =$this->config['tag'][$k]."：".implode(" ",$t_name);
            }
        }
        $crowd_info['add_time']=date('Y-m-d H:i',$crowd_info['add_time']);
        $crowd_info['tag_list']=$tag_list;
        //print_r($crowd_info);
        Helper::outJson(array('state'=>1,'msg'=>'','data'=>$crowd_info));
    }
    /**
     * 人群包删除
     */
    public function delAction(){
        Yaf_Dispatcher::getInstance()->disableView();
        $crowdModel = Helper::M("Dcrowd");
        $crowd_id = Helper::clean($this->getRequest()->getParam('crowd_id' , 0));
        if(empty($crowd_id)){
            Helper::outJson(array('state'=>0 , 'msg' => '参数错误'));
        }
        $res=$crowdModel->del($crowd_id);
        if($res){
            //添加操作日志
            $log_content[] = "人群包管理 :". "人群包删除";
            $log_content_str = implode(" , ",$log_content);
            $log_obj = Helper::M('Log');
            $log_obj->create_log('人群包删除',$log_content_str,'LC005',"LOT003");
            Helper::outJson(array('state'=>1,'msg'=>'删除成功'));
        }else{
            Helper::outJson(array('state'=>2,'msg'=>'删除失败'));
        }
    }

    /**
     * 人群包重新申请
     */
    public function reapplyAction(){
        if(isset($_POST)&&!empty($_POST)){
            $crowdModel = Helper::M("Dcrowd");
            $crowd_id = Helper::clean($_POST['crowd_id']);
            if(empty($crowd_id)){
                Helper::outJson(array('state'=>0 , 'msg' => '参数错误'));
            }
            $data=$label_arr=$tag_arr=array();
            //1区域
            $reg_all=array();
            $region = Helper::clean($_POST['region']);
            if(!empty($region)){
                $r_ids=explode(',',$region);
                $label_arr['region']=$r_ids;
                foreach ($r_ids as $reg){
                    $reg_info=$crowdModel->getTagInfoById($reg);
                    if($reg_info['pid'] == 0){
                        $city_arr=$crowdModel->getCityTagList($reg_info['c_tag_id']);
                        foreach ($city_arr as $ka=>$va){
                            $c_arr[$ka]['id']=$va['label_id'];
                        }
                        $reg_all=array_merge($reg_all,$c_arr);
                    }else{
                        array_push($reg_all,array('id'=>$this->redis->get('tagLabel_'.$reg)));
                    }
                }
                $tag_arr['and'][]=array('or'=>$reg_all);
            }
            //2年龄
            $age = Helper::clean($_POST['age']);
            if(!empty($age)){
                $age_arr=array();
                $age_ids=$crowdModel->getTagByW("c_tag_id in (".$age.")");
                foreach ($age_ids as $av){
                    $age_arr[]['id']=$this->redis->get('tagLabel_'.$av['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$age_arr);
                $label_arr['age']=explode(',',$age);
            }
            //3受教育水平
            $education = Helper::clean($_POST['education']);
            if(!empty($education)){
                $education_arr=array();
                $education_ids=$crowdModel->getTagByW("c_tag_id in (".$education.")");
                foreach ($education_ids as $ev){
                    $education_arr[]['id']=$this->redis->get('tagLabel_'.$ev['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$education_arr);
                $label_arr['education']=explode(',',$education);
            }
            //4从业行业
            $industry = Helper::clean($_POST['industry']);
            if(!empty($industry)){
                $industry_arr=array();
                $industry_ids=$crowdModel->getTagByW("c_tag_id in (".$industry.")");
                foreach ($industry_ids as $iv){
                    $industry_arr[]['id']=$this->redis->get('tagLabel_'.$iv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$industry_arr);
                $label_arr['industry']=explode(',',$industry);
            }
            //5房产
            $house = Helper::clean($_POST['house']);
            if(!empty($house)){
                $house_arr=array();
                $house_ids=$crowdModel->getTagByW("c_tag_id in (".$house.")");
                foreach ($house_ids as $hv){
                    $house_arr[]['id']=$this->redis->get('tagLabel_'.$hv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$house_arr);
                $label_arr['house']=explode(',',$house);
            }
            //6汽车
            $car = Helper::clean($_POST['car']);
            if(!empty($car)){
                $car_arr=array();
                $car_ids=$crowdModel->getTagByW("c_tag_id in (".$car.")");
                foreach ($car_ids as $cv){
                    $car_arr[]['id']=$this->redis->get('tagLabel_'.$cv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$car_arr);
                $label_arr['car']=explode(',',$car);
            }
            //7建材家居
            $home = Helper::clean($_POST['home']);
            if(!empty($home)){
                $home_arr=array();
                $home_ids=$crowdModel->getTagByW("c_tag_id in (".$home.")");
                foreach ($home_ids as $ov){
                    $home_arr[]['id']=$this->redis->get('tagLabel_'.$ov['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$home_arr);
                $label_arr['home']=explode(',',$home);
            }
            //8家电数码
            $digital = Helper::clean($_POST['digital']);
            if(!empty($digital)){
                $digital_arr=array();
                $digital_ids=$crowdModel->getTagByW("c_tag_id in (".$digital.")");
                foreach ($digital_ids as $dv){
                    $digital_arr[]['id']=$this->redis->get('tagLabel_'.$dv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$digital_arr);
                $label_arr['digital']=explode(',',$digital);
            }
            //9生活服务
            $service = Helper::clean($_POST['service']);
            if(!empty($service)){
                $service_arr=array();
                $service_ids=$crowdModel->getTagByW("c_tag_id in (".$service.")");
                foreach ($service_ids as $vv){
                    $service_arr[]['id']=$this->redis->get('tagLabel_'.$vv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$service_arr);
                $label_arr['service']=explode(',',$service);
            }
            //10休闲爱好
            $hobby = Helper::clean($_POST['hobby']);
            if(!empty($hobby)){
                $hobby_arr=array();
                $hobby_ids=$crowdModel->getTagByW("c_tag_id in (".$hobby.")");
                foreach ($hobby_ids as $yv){
                    $hobby_arr[]['id']=$this->redis->get('tagLabel_'.$yv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$hobby_arr);
                $label_arr['hobby']=explode(',',$hobby);
            }
            //11影视音乐
            $music = Helper::clean($_POST['music']);
            if(!empty($music)){
                $music_arr=array();
                $music_ids=$crowdModel->getTagByW("c_tag_id in (".$music.")");
                foreach ($music_ids as $mv){
                    $music_arr[]['id']=$this->redis->get('tagLabel_'.$mv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$music_arr);
                $label_arr['music']=explode(',',$music);
            }
            //12游戏
            $game = Helper::clean($_POST['game']);
            if(!empty($game)){
                $game_arr=array();
                $game_ids=$crowdModel->getTagByW("c_tag_id in (".$game.")");
                foreach ($game_ids as $gv){
                    $game_arr[]['id']=$this->redis->get('tagLabel_'.$gv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$game_arr);
                $label_arr['game']=explode(',',$game);
            }
            //13服饰鞋包
            $dress = Helper::clean($_POST['dress']);
            if(!empty($dress)){
                $dress_arr=array();
                $dress_ids=$crowdModel->getTagByW("c_tag_id in (".$dress.")");
                foreach ($dress_ids as $rv){
                    $dress_arr[]['id']=$this->redis->get('tagLabel_'.$rv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$dress_arr);
                $label_arr['dress']=explode(',',$dress);
            }
            //14医疗健康
            $health = Helper::clean($_POST['health']);
            if(!empty($health)){
                $health_arr=array();
                $health_ids=$crowdModel->getTagByW("c_tag_id in (".$health.")");
                foreach ($health_ids as $lv){
                    $health_arr[]['id']=$this->redis->get('tagLabel_'.$lv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$health_arr);
                $label_arr['health']=explode(',',$health);
            }
            //15个护美容
            $beauty = Helper::clean($_POST['beauty']);
            if(!empty($beauty)){
                $beauty_arr=array();
                $beauty_ids=$crowdModel->getTagByW("c_tag_id in (".$beauty.")");
                foreach ($beauty_ids as $uv){
                    $beauty_arr[]['id']=$this->redis->get('tagLabel_'.$uv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$beauty_arr);
                $label_arr['beauty']=explode(',',$beauty);
            }
            //16教育培训
            $train = Helper::clean($_POST['train']);
            if(!empty($train)){
                $train_arr=array();
                $train_ids=$crowdModel->getTagByW("c_tag_id in (".$train.")");
                foreach ($train_ids as $tv){
                    $train_arr[]['id']=$this->redis->get('tagLabel_'.$tv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$train_arr);
                $label_arr['train']=explode(',',$train);
            }
            //17旅游酒店
            $tourism = Helper::clean($_POST['tourism']);
            if(!empty($tourism)){
                $tourism_arr=array();
                $tourism_ids=$crowdModel->getTagByW("c_tag_id in (".$tourism.")");
                foreach ($tourism_ids as $zv){
                    $tourism_arr[]['id']=$this->redis->get('tagLabel_'.$zv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$tourism_arr);
                $label_arr['tourism']=explode(',',$tourism);
            }
            //18母婴亲子
            $baby = Helper::clean($_POST['baby']);
            if(!empty($baby)){
                $baby_arr=array();
                $baby_ids=$crowdModel->getTagByW("c_tag_id in (".$baby.")");
                foreach ($baby_ids as $bv){
                    $baby_arr[]['id']=$this->redis->get('tagLabel_'.$bv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$baby_arr);
                $label_arr['baby']=explode(',',$baby);
            }
            //19体育健身
            $sports = Helper::clean($_POST['sports']);
            if(!empty($sports)){
                $sports_arr=array();
                $sports_ids=$crowdModel->getTagByW("c_tag_id in (".$sports.")");
                foreach ($sports_ids as $pv){
                    $sports_arr[]['id']=$this->redis->get('tagLabel_'.$pv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$sports_arr);
                $label_arr['sports']=explode(',',$sports);
            }
            //20网络购物
            $shopping = Helper::clean($_POST['shopping']);
            if(!empty($shopping)){
                $shopping_arr=array();
                $shopping_ids=$crowdModel->getTagByW("c_tag_id in (".$shopping.")");
                foreach ($shopping_ids as $sv){
                    $shopping_arr[]['id']=$this->redis->get('tagLabel_'.$sv['c_tag_id']);
                }
                $tag_arr['and'][]=array('or'=>$shopping_arr);
                $label_arr['shopping']=explode(',',$shopping);
            }
            //21性别
            $sex = Helper::clean($_POST['sex']);
            if(!empty($sex)){
                $sex_arr=explode(',',$this->redis->get('tagLabel_'.$sex));
                foreach ($sex_arr as $ks=>$vs){
                    $sex_list[$ks]['id']=$vs;
                }
                $tag_arr['and'][]=array('or'=>$sex_list);
                $label_arr['sex']=explode(',',$sex);
            }
            if(empty($label_arr)){
                Helper::outJson(array('state'=>0 , 'msg' => '请选择人群包标签！'));
            }
            $data['crowd_name'] = Helper::clean($_POST['crowd_name']);;
            $data['edit_time'] = time();
            $data['finish_time'] = 0;
            $data['label_info'] = json_encode($label_arr);
            if(empty($data['crowd_name'])){
                Helper::outJson(array('state'=>0 , 'msg' => '人群包名称不能为空'));
            }
            //请求DMP接口获取覆盖用户数
            $this->config = Yaf_Registry::get('config');
            //获取token
            if(!$this->redis->exists('dmp_access_token'.date('Ymd'))){
                $this->setDmpToken();
            }
            //print_r($tag_arr);
            $dmp_tagConditions=json_encode($tag_arr);
            $dmp_token=$this->redis->get('dmp_access_token');
            $dmp_ts=time();
            $key=$this->config['dmp']['des_secret'];
            $des = new CryptDes($key);
            $s=$des->encrypt("tagConditions=".$dmp_tagConditions."&token=".$dmp_token."&ts=".$dmp_ts);
            $users_result = DoNetwork::makeRequest($this->config['dmp']['api_host'].$this->config['dmp']['api_dir'].'getTagGroupCount.do?s=' . urlencode($s), 'POST','','','',500000 );
            //var_dump($users_result);
            $users_result = json_decode($users_result , true);
            //print_r($users_result);
            if($users_result['errcode'] == 0){
                if($users_result['data'] == 0){
                    Helper::outJson(array('state'=>0 , 'msg' => '您选择的自定义人群人数为0，请重新选择用户属性!'));
                }
                $data['cover_users']=$users_result['data'];
            }else{
                Helper::outJson(array('state'=>0 , 'msg' => 'dmp请求人群覆盖数量失败'));
            }
            //print_r($data);
            //人群包信息入库
            $this->db = Yaf_Registry::get('db');
            $this->db->dsp->startTrans();
            $data['audit_status']=0;
            $res_crowd=$crowdModel->edit($crowd_id,$data);
            if($res_crowd){
                if($users_result['data'] <= 10000){
                    $audit_arr=array();
                    $audit_arr['crowd_id']=$crowd_id;
                    $audit_arr['audit_status']=1;
                    $audit_arr['audit_staff_id']=0;
                    $audit_arr['audit_time']=time();
                    $audit_arr['audit_remark']='系统默认审核通过';
                    $a_res=$crowdModel->addAudit($audit_arr);
                    if($a_res){
                        //覆盖用户小于10000不用审核，直接进入运算中
                        $ap_arr=$edit_data=array();
                        $ap_arr['tagConditions']=$dmp_tagConditions;
                        $crowd_apply=DoNetwork::makeRequest('https://dev-dspservice.100msh.com/api/getCrowd', 'POST' , $ap_arr);
                        $crowd_apply=json_decode($crowd_apply , true);
                        if(!empty($crowd_apply) && $crowd_apply['errcode'] == 0){
                            $edit_data['crowd_taskid']=$crowd_apply['data'];
                        }else{
                            $this->db->dsp->rollback();
                            Helper::outJson(array('state'=>0 , 'msg' => 'dmp请求人群包失败'));
                        }
                        $edit_data['audit_status']=1;
                        $e_res=$crowdModel->edit($crowd_id,$edit_data);

                        if(!$e_res){
                            $this->db->dsp->rollback();
                            Helper::outJson(array('state'=>0 , 'msg' => '修改人群包状态失败'));
                        }

                    }else{
                        $this->db->dsp->rollback();
                        Helper::outJson(array('state'=>0 , 'msg' => '审核记录添加失败'));
                    }
                }
                $this->db->dsp->commit();
                //添加操作日志
                $log_content[] = "人群包管理 :". "人群包重新申请";
                $log_content_str = implode(" , ",$log_content);
                $log_obj = Helper::M('Log');
                $log_obj->create_log('人群包重新申请',$log_content_str,'LC005',"LOT002");
                Helper::outJson(array('state'=>1 , 'msg' => '申请人群包成功'));
            }else{
                Helper::outJson(array('state'=>0 , 'msg' => '人群包入库失败'));
            }
        }

    }
    public function crowdInfoAction(){
        $crowdModel = Helper::M("Dcrowd");
        $crowd_id = Helper::clean($this->getRequest()->getParam('crowd_id' , 0));
        if(empty($crowd_id)){
            Helper::outJson(array('state'=>0 , 'msg' => '参数错误'));
        }
        $crowd_info=$crowdModel->getCrowdById($crowd_id);
        $crowd_info['label_info']=json_decode($crowd_info['label_info'],true);
        Helper::outJson(array('state'=>1,'msg'=>'','data'=>$crowd_info));
    }
    /**
     * 人群包下载
     */
    public function downAction(){
        $crowdModel = Helper::M("Dcrowd");
        $crowd_id = Helper::clean($this->getRequest()->getParam('crowd_id' , 0));
        if(empty($crowd_id)){
            Helper::outJson(array('state'=>0 , 'msg' => '参数错误'));
        }
        $crowd_info=$crowdModel->getCrowdById($crowd_id);
        if(empty($crowd_info) || $crowd_info['if_saved'] == 0){
            Helper::outJson(array('state'=>0 , 'msg' => '下载错误'));
        }
        if($crowd_info['download_nums'] >= 3){
            Helper::outJson(array('state'=>0 , 'msg' => '已超过下载限数，不可以再下载！'));
        }
//         /Helper::download_file($this->config['config']['ACCESSORY_URL']."dmp/".$crowd_info['crowd_taskid'].".zip");exit;
        $up_data=array('download_nums'=>$crowd_info['download_nums']+1);
        $down_data=array('crowd_id' => $crowd_id,'down_staff_id' => $this->uinfo['staff_id'],'down_time' =>time());
        $res=$crowdModel->edit($crowd_id,$up_data);
        if($res){
            $crowdModel->addDown($down_data);
            //添加操作日志
            $log_content[] = "人群包管理 :". "人群包下载";
            $log_content_str = implode(" , ",$log_content);
            $log_obj = Helper::M('Log');
            $log_obj->create_log('人群包下载',$log_content_str,'LC005',"LOT005");
            Helper::outJson(array('state'=>1 , 'msg' => '111','data'=>$this->config['config']['ACCESSORY_URL']."dmp/".$crowd_info['crowd_taskid'].".zip"));
        }else{
            Helper::outJson(array('state'=>0 , 'msg' => '下载失败'));
        }
    }
    /**
     * 上传图片
     */
    public function aaaAction(){
        var_dump($_POST);

        if($_POST){
            $file = $_FILES;
            //Helper::outJson(array('status'=>1211,'msg'=>"图片上传失败:上传的图片类型不正确！请上传类型为png,jpg,jpeg,bmp的文件！"));
            //var_dump($_FILES);exit;
            $ACCESSORY_FOLDER = $this->config['config']['ACCESSORY_FOLDER'];

            //实际上传路径
            $nowday= date("Ymd",time());//今天的日期
            $nowmonth = date("Ym",time());
            // $UpFileDir = $ACCESSORY_FOLDER ."DspAdUpFile/";
            $UpFileDir = $ACCESSORY_FOLDER."/gdt/";
            $uploadObj = new Upload();
            $fileNamge = "file";
            $size = getimagesize($_FILES['file']['tmp_name']);

            $ext = strtolower(strrchr($_FILES["file"]["name"], "."));
            $uploadObj->UpFileAttribute($fileNamge);
            $UpFileName = "gdt-img-".time();
            $UpFileName= MD5($UpFileName);//无文件后缀
            $MaxSize = '200';
            $FileType = array('.png','.jpg','.jpeg','.bmp');//后缀注意带上.
            $info = $uploadObj->Uploads($fileNamge,$UpFileDir,$UpFileName,$MaxSize,$FileType,null);
            switch ($info){
                case -1:
                    Helper::outJson(array('status'=>0,'msg'=>"图片上传失败:上传的图片类型不正确！请上传类型为png,jpg,jpeg,bmp的文件！"));
                    break;
                case -2:
                    Helper::outJson(array('status'=>0,'msg'=>"图片上传失败:上传的图片大小不符合要求！请上传小于".$MaxSize."(KB)的文件！"));
                    break;
                case 1:
                    $ACCESSORY_URL = $this->config['config']['ACCESSORY_URL'];
                    $file_path = $ACCESSORY_URL.$nowmonth."/".$nowday."/dsp/". $UpFileName.$ext;
                    Helper::outJson(array('state'=>1,'msg'=>'图片上传成功','url'=>$file_path));
                    break;
                default:
                    Helper::outJson(array('status'=>0,'msg'=>"图片上传失败:上传错误！"));
                    break;
            }
        }
    }
    function bbbAction(){
        $data=array();
        $data['file']='/data001/data/sites/100msh_upload/gdt/4a21bed14e8de0b297262cc37e4136a9.jpg';
        $res=DoNetwork::curl_upload('https://dev-dspservice.100msh.com/dcrowd/aaa', array('a'=>1) , $data);
        var_dump($res);exit;
    }
}
