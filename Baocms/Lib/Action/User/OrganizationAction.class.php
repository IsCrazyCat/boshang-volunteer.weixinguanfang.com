<?php
class OrganizationAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();

    }

    /**
     * 我的团队
     * 因为数据库shop表存的组织信息，就不改了，就拿shop当做组织
     */
    public function myOrganization(){
        //获取该用户所管理的所有组织
        $organizations = D('Shop')->where(array('user_id' => $this->uid,'audit'=>'1','closed'=>0))->select();

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
            foreach ($activitys as $akey => $aval){
                $result = service_info_organization($aval['activity_id']);
                //获取该组织下的活动总时长和今年时长
                $total_time += $result['service_time'];
                //获取该活动下的报名人数和实际参加人数
                $sign_count += $result['sign_count'];
                $join_count += $result['join_count'];
            }

            $organizations[$key]['total_time'] = $total_time;
            $organizations[$key]['sign_count'] = $sign_count;
            $organizations[$key]['join_count'] = $join_count;
            $organizations[$key]['volunteer_count'] = D('OrganizationVolunteer')->where(array('organization_id'=>$val['shop_id']))->count();
        }

        $this->assign('organizations',$organizations);
        $this->display();
    }
    public function organization(){

        $organization_id = $this->_get('organization_id');
        $organization = D('shop')->where(array('shop_id'=>$organization_id,'closed'=>0))->find();

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
            $result = lengthOfTime($aval['activity_id']);
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
        $organizations['volunteer_count'] = D('OrganizationVolunteer')->where(array('organization_id'=>$organization_id))->count();

        $this->assign('organization',$organization);
        $this->display();
    }

    /**
     * 服务志愿者
     */
    public function volunteer(){
        $organization_id = $this->_get('organization_id');
        $organization = D('shop')->where(array('shop_id'=>$organization_id,'closed'=>0))->find();

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
            $result = lengthOfTime($val,1);
            $sign_users[$key]['total_time'] = $result['total_time'];
        }

        $this->assign('volunteers',$sign_users);
        $this->display();
    }
    public function organizationVolunteerSign(){
        if (empty($this->uid)) {
            $this->error('您还没有登录！', U('passport/login'));
        }

        $data['user_id'] = $this->uid;
        $data['organization_id'] = $this->_param('organization_id');
//        $data['status'] = 1;//状态 0申请中 1审核通过 2审核失败
        if($organizationVolunteer = D('organizationVolunteer')->where($data)->find()){
            if($organizationVolunteer['status'] == 1){
                $this->error('您已经是该组织的志愿者！', U('wap/shop/detail/',array('shop_id'=>$data['organization_id'])));
            }else if($organizationVolunteer['status'] == 0){
                $this->error('您已申请，请等待审核！', U('wap/shop/detail/',array('shop_id'=>$data['organization_id'])));
            }
        }
        $data['status'] = 0;//状态 0申请中 1审核通过 2审核失败
        $data['create_time'] = time();
        if(D('organizationVolunteer')->add($data)){
            $this->success('已提交申请，请等待审核！！', U('wap/shop/detail/',array('shop_id'=>$data['organization_id'])));
        }
        $this->display();
    }

    /**
     * 我加入的组织，即我申请成为志愿者的组织
     */
    public function myJoin(){
        if (empty($this->uid)) {
            $this->error('您还没有登录！', U('passport/login'));
        }
        //获取该用户所加入的所有组织
        $organizationVolunteers = D('organizationVolunteer')->where(array('user_id'=>$this->uid,'is_del'=>'0','status'=>array('IN','0,1')))->select();
        $organizations = array();
        foreach ($organizationVolunteers as $key=>$volunteer) {
            $shop = D('Shop')->where(array('shop_id' => $volunteer['organization_id'],'audit'=>'1','closed'=>0))->find();
            if(!empty($shop)){
                $organizations[$key] =$shop;
                $organizations[$key]['user'] = $volunteer;
            }
        }

        foreach ($organizations as $key => $val){
            //获取该组织下的活动
            $activitys = D('activity')->where(array('shop_id'=>$val['shop_id']))->select();
            //该组织下的活动个数
            $actitity_count = count($activitys);
            $organizations[$key]['activity_count'] = $actitity_count;

            $sign_count = 0;//该组织下属所有活动的所有报名人数
            $join_count = 0;//该组织下属所有活动的所有参加人数
            $total_time = 0;//该组织下属所有活动的总活动时间
//            $year_time = 0;//该组织下属所有活动的今年活动时间
            $sign_users = array();
            foreach ($activitys as $akey => $aval){
                $result = service_info_organization($aval['activity_id']);
                //获取该组织下的活动总时长和今年时长
                $total_time += $result['service_time'];
//                $year_time += $result['year_time'];
                //获取该活动下的报名人数和实际参加人数
                $sign_count += $result['sign_count'];
                $join_count += $result['join_count'];
            }
            $organizations[$key]['total_time'] = $total_time;
//            $organizations[$key]['year_time'] = $year_time;
            $organizations[$key]['sign_count'] = $sign_count;
            $organizations[$key]['join_count'] = $join_count;
            $organizations[$key]['volunteer_count'] = D('OrganizationVolunteer')->where(array('organization_id'=>$val['shop_id']))->count();
        }

        $this->assign('organizations',$organizations);
        $this->display();
    }
}