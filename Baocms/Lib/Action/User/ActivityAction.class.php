<?php
class ActivityAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        if ($this->_CONFIG['operation']['huodong'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }

    public function index(){
        $result = $this->lengthOfTime($this->uid,1);

        $this->assign('total_time',$result['total_time']);
        $this->assign('year_time',$result['year_time']);
        $this->assign('count',$result['count']);//参加的活动个数
        $this->display();
    }
    public function load(){
        $Activity = D('Activity');
        $Activitysign = D('Activitysign');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Activitysign->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Activitysign->where($map)->order(array('sign_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $activitys_ids = array();
        foreach ($list as $k => $val) {
            $activitys_ids[$val['activity_id']] = $val['activity_id'];
        }
        $this->assign('activity', $Activity->itemsByIds($activitys_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function myActivity(){
        $activitySign = D('activitySign');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $activitySign->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $activitySign->where($map)->order(array('sign_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $activity = D('activity')->where(array('activity_id'=>$val['activity_id']))->find();
            $list[$k]['activity'] = $activity;
            $shop = D('shop')->where(array('shop_id'=>$activity['shop_id']))->find();
            $list[$k]['shop'] = $shop;
            $activityLogs = D('activityLogs')->where(array('user_id'=>$this->uid,'activity_id'=>$val['activity_id']))->order(array('today_date'=>'desc'))->find();
            if(!empty($activityLogs['start_date'])){
                if(!empty($activityLogs['end_date'])){
                    $list[$k]['total_time']=ceil((strtotime($activityLogs['end_date'])-strtotime($activityLogs['start_date']))/3600);
                }else{
                    $list[$k]['total_time']=ceil((time()-strtotime($activityLogs['start_date']))/3600);
                }
            }else{
                $list[$k]['total_time'] = 0;
            }

        }
        $this->assign('user_id', $this->uid);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * 我的团队
     * 因为数据库shop表存的组织信息，就不改了，就拿shop当做组织
     */
    public function myOrganization(){
        //获取该用户所管理的所有组织
        $organizations = D('Shop')->where(array('manager_id' => $this->uid))->select();

        foreach ($organizations as $key => $val){
            //获取该组织下的活动
            $activitys = D('activity')->where(array('shop_id'=>$val['shop_id']))->select();
            //该组织下的活动个数
            $actitity_count = count($activitys);
            $organizations[$key]['activity_count'] = $actitity_count;

            $sign_count = 0;//该组织下属所有活动的所有报名人数
            $join_count = 0;//该组织下属所有活动的所有参加人数
            $total_time = 0;//该组织下属所有活动的总活动时间
            $year_time = 0;//该组织下属所有活动的今年活动时间
            $sign_users = array();
            foreach ($activitys as $akey => $aval){
                $result = $this->lengthOfTime($aval['activity_id']);
                //获取该组织下的活动总时长和今年时长
                $total_time += $result['total_time'];
                $year_time += $result['year_time'];
                //获取该活动下的报名人数和实际参加人数
                if(!empty($result['ids'])){
                    $sign_users = array_merge($sign_users,$result['ids']);
                }
                $join_count += $result['real_count'];
            }
            $sign_count = count(array_unique($sign_users));
            $organizations[$key]['total_time'] = $total_time;
            $organizations[$key]['year_time'] = $year_time;
            $organizations[$key]['sign_count'] = $sign_count;
            $organizations[$key]['join_count'] = $join_count;
        }

        $this->assign('organizations',$organizations);
        $this->display();
    }
    public function organization(){

        $organization_id = $this->_get('organization_id');
        $organization = D('shop')->where(array('shop_id'=>$organization_id))->find();

        //获取该组织下的活动
        $activitys = D('activity')->where(array('shop_id'=>$organization['shop_id']))->select();
        //该组织下的活动个数
        $actitity_count = count($activitys);
        $organization['activity_count'] = $actitity_count;
        $sign_count = 0;//该组织下属所有活动的所有报名人数
        $join_count = 0;//该组织下属所有活动的所有参加人数
        $total_time = 0;//该组织下属所有活动的总活动时间
        $year_time = 0;//该组织下属所有活动的今年活动时间
        $sign_users = array();//该组织下属所有活动的报名人ID
        foreach ($activitys as $akey => $aval){
            $result = $this->lengthOfTime($aval['activity_id']);
            //获取该组织下的活动总时长和今年时长
            $total_time += $result['total_time'];
            $year_time += $result['year_time'];
            //获取该活动下的报名人数和实际参加人数
            if(!empty($result['ids'])){
                $sign_users = array_merge($sign_users,$result['ids']);
            }

            $join_count += $result['real_count'];
        }
        $sign_count = count(array_unique($sign_users));
        $organization['total_time'] = $total_time;
        $organization['year_time'] = $year_time;
        $organization['sign_count'] = $sign_count;
        $organization['join_count'] = $join_count;

        $this->assign('organization',$organization);
        $this->display();
    }

    /**
     * 服务志愿者
     */
    public function volunteer(){
        $organization_id = $this->_get('organization_id');
        $organization = D('shop')->where(array('shop_id'=>$organization_id))->find();

        //获取该组织下的活动
        $activitys = D('activity')->where(array('shop_id'=>$organization['shop_id']))->select();
        $sign_user_ids = array();
        foreach ($activitys as $key => $val){
            $ids = D('activity_sign')
                ->where(array('activity_id'=>$val['activity_id']))
                ->field('user_id')
                ->select();
            if(!empty($ids)){
                $sign_user_ids = array_merge($sign_user_ids,$ids);
            }
        }
        $sign_user_ids = array_unique($sign_user_ids);
        foreach($sign_user_ids as $key => $val){
            $sign_user_ids[$key] = $val['user_id'];
        }
        $sign_users = D('users')->where(array('user_id'=>array('IN',$sign_user_ids)))->select();
        foreach ($sign_user_ids as $key => $val){
            $result = $this->lengthOfTime($val,1);
            $sign_users[$key]['total_time'] = $result['total_time'];
        }

        $this->assign('volunteers',$sign_users);
        $this->display();
    }

    /**
     * 服务活动列表
     */
    public function activityList(){
        $organization_id = $this->_param('organization_id');

        $this->assign('organization_id',$organization_id);
        $this->display(); // 输出模板
    }

    /**
     * 获取活动时长相关
     * @param $id 传入的用户ID或者活动ID
     * @param $type 0获取传入的活动ID所有的活动时间
     *              1获取传入的用户ID的活动时间和人数
     * @return array
     */
    public function lengthOfTime($id,$type = 0){
        $result = array();
        $where = array();
        $group = 'user_id';
        $model = 'activity_sign'; //默认查询报名表
        if($type == 0){
            $where['activity_id'] = $id;
        }else if($type == 1){
            $where['user_id'] = $id;
            $group = 'activity_id';
        }
        $activityLogs = D('activityLogs')->where($where)->select();
        if($real_count = D('activityLogs')->where($where)->group($group)->count()){
            //实际参加活动人数/实际参加活动数
            $result['real_count'] = $real_count;
        }
        //总活动时长
        $total_time = 0;
        //今年活动时长
        $year_time = 0;

        $objs = D($model)->where($where)->select();
        //个数 报名人数/参加活动个数
        $count = count($objs);
        $result['count'] = $count;
        foreach ($objs as $key =>$val){
            $result['ids'][$key] = $val[$group];
        }

        foreach ($activityLogs as $key=>$val) {
            if(empty($val['end_date'])){
                $total_time += (strtotime(date('Y-m-d H:i:s'))-strtotime($val['start_date']));
            }else{
                $total_time += (strtotime($val['end_date'])-strtotime($val['start_date']));
            }
            if(substr($val['today_date'],0,4)==date('Y')){
                $year_time += $total_time;
            }
        }
        if($total_time){
            $total_time = ceil($total_time/3600);
            $result['total_time'] = $total_time;
        }
        if($year_time){
            $year_time = ceil($year_time/3600);
            $result['year_time'] = $year_time;
        }
        return $result;
    }
}