<?php

class ActivityAction extends CommonAction
{

    public function _initialize()
    {
        parent::_initialize();
        if ($this->_CONFIG['operation']['huodong'] == 0) {
            $this->error('此功能暂未开通');
            die;
        }
    }

    public function index()
    {
        //地区 东昌府区
        $citys = D('City')->fetchAll();
        $business = array();
        $areas = array();
        foreach ($citys as $key => $val) {
            //区域 例：东昌府区下的开发区下的区县
            $cur_areas = D('area')->where(array('city_id' => $val['city_id']))->select();
            foreach ($cur_areas as $ak => $av) {
                $cur_business = D('business')->where(array('area_id' => $av['area_id']))->select();
                if (!empty($cur_business)) {
                    $business = array_merge($business, $cur_business);
                }
                $cur_areas[$ak]['businesses'] = $cur_business;

            }
            if (!empty($cur_areas)) {
                $areas = array_merge($areas, $cur_areas);
            }

            $citys[$key]['areas'] = $cur_areas;
        }

        if($business_id = $this->_get('business_id')){
            $this->assign('business_id',$business_id);
        }
        if($cate_id = $this->_get('cate_id')){
            $this->assign('cate_id',$cate_id);
        }
        if($orderBy = $this->_get('orderBy')){
            $this->assign('orderBy',$orderBy);
        }
        if($keyword = $this->_get('keyword')){
            $this->assign('keyword',$keyword);
        }

        //活动类型
        $cates = D('Activitycate')->fetchAll();
        $this->assign('citys', $citys);
        $this->assign('areas', $areas);
        $this->assign('businesses', $business);
        $this->assign('cates', $cates);
        $this->display(); // 输出模板
    }

    /**
     * 加载更多活动信息
     */
    public function moreActivity()
    {
        $Activity = D('Activity');
        import('ORG.Util.Page');
        $map = array('closed' => 0);

        //活动地点
        if ($area_id = $this->_param('area_id')) {
            $areaInfo = explode("=", $area_id);
            $map[$areaInfo[0]] = $areaInfo[1];
        }
        //活动类型
        if ($cate_id = (int)$this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $result['cate_id'] = $cate_id;
        }
        //活动类型
        if ($shop_id = (int)$this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $result['cate_id'] = $shop_id;
        }
        //排序
        $order_str = array('activity_id' => 'desc');
        if ($order = $this->_param('order')) {
            if ($order == 1) {
                $order_str = array('bg_date' => 'ASC');
            } else if ($order == 2) {
                $order_str = array('sign_num' => 'DESC');
            }
        }
        if ($search_key = $this->_param('search_key')) {
            $map['title'] = array('LIKE', '%' . $search_key . '%');
        }
        $count = $Activity->where($map)->count();
        $Page = new Page($count, 10);
        $list = $Activity->where($map)->order($order_str)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //获取所属组织ID，然后获取组织名称
        $shop_ids = array();
        foreach ($list as $key => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $shops = D('Shop')->itemsByIds($shop_ids);
        foreach ($list as $key => $val) {
            $shop_name = $shops[$val['shop_id']]['shop_name'];
            $list[$key]['shop_name'] = $shop_name;
        }
        $result['list'] = $list;

        exit(json_encode($result));
    }

    public function detail()
    {
        $activity_id = (int)$this->_get('activity_id');
        if (empty($activity_id)) {
            $this->error('该活动信息不存在！');
            die;
        }
        if (!$detail = D('Activity')->find($activity_id)) {
            $this->error('该活动信息不存在！');
            die;
        }
        if ($detail['closed']) {
            $this->error('该活动信息不存在！');
            die;
        }
        $sign = D('Activitysign')->where(array('user_id' => $this->uid, 'activity_id' => $activity_id))->select();
        if (!empty($sign)) {
            $detail['sign'] = 1;
        } else {
            $detail['sign'] = 0;
        }
        $detail = D('Activity')->_format($detail);
        $detail['end_time'] = strtotime($detail['sign_end']) - NOW_TIME + 86400;
        $this->assign('detail', $detail);
        $shop_id = $detail['shop_id'];
        $shop = D('Shop')->find($shop_id);
        $cates = D('Activitycate')->fetchAll();

        $detail['thumb'] = unserialize($detail['thumb']);

        $this->assign('cates', $cates);
        $this->assign('shop', $shop);
        $this->display();
    }

    public function sign($activity_id)
    {
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
        }
        //$activity_id = (int) $this->_param('activity_id');
        $activity_id = (int)$activity_id;
        $detail = D('Activity')->find($activity_id);
        if (empty($detail)) {
            $this->error('报名的活动不存在');
        }
        if ($this->isPost()) {
            $data = $this->checkSign();

            $data['activity_id'] = $activity_id;
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Activitysign');
            $sign = D('Activitysign')->where(array('user_id' => $this->uid, 'activity_id' => $activity_id))->find();
            if($sign){
                $this->error('您已经报过名，请勿重复操作！',
                    U('wap/activity/detail', array('activity_id' => $activity_id)));
            }
            if ($obj->add($data)) {
                D('Activity')->updateCount($activity_id, 'sign_num');
                $this->error('恭喜您报名成功',
                    U('activity/signQRCode', array('user_id' => $this->uid, 'activity_id' => $activity_id)));
            }
            $this->error('操作失败！');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }

    public function checkSign()
    {
        $data = $this->checkFields($this->_post('data', false), array('name', 'mobile', 'num'));
        $data['user_id'] = (int)$this->uid;
        $data['name'] = rtrim($data['name']);
        if (empty($data['name'])) {
            $this->error('联系人不能为空');
        }
        $data['mobile'] = htmlspecialchars(rtrim($data['mobile']));
        if (empty($data['mobile'])) {
            $this->error('联系电话不能为空');
        }
        if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->error('联系电话格式不正确');
        }
        $data['num'] = (int)(rtrim($data['num']));
        if (empty($data['num'])) {
            $this->error('活动人数不能为空');
        }
        return $data;
    }

    /**
     * 报名志愿活动生成带有user_id和活动Id的二维码
     */
    public function signQRCode()
    {
        $activity_id = $this->_get('activity_id');
        $user_id = $this->_get('user_id');
        if (!$user = D('users')->find($user_id)) {
            $this->error('没有该用户！');
        }
        if (!$activity = D('activity')->find($activity_id)) {
            $this->error("不存在该活动！");
        }
//       Baocms\Lib\Action\Members\IndexAction.class.php
        //这里是生成的二维码中的URL，即扫描二维码跳转的链接和参数
        $url = U('/wap/activity/scanScan', array('activity_id' => $activity_id, 'user_id' => $user_id, 't' => NOW_TIME, 'sign' => md5($user_id . $activity_id . C('AUTH_KEY') . NOW_TIME)));
        //这个token只是作为二维码生成后的存放路径的一个依据 并无实际意义
        $token = 'signcode_' . $activity_id;
        $file = baoQrCode($token, $url);
        $this->assign('file', $file);
        $this->assign('user', $user);
        $this->assign('activity', $activity);
        $this->display();
    }

    /**
     * 扫描报名二维码 开始计时或者结束计时
     */
    public function scanScan(){
        $user = D('Users')->where(array('user_id' => $this->uid))->find();
        $activity_id = $this->_param('activity_id');
        $sign_user_id = $this->_param('user_id');

        if (!($activity = D('activity')->where(array('activity_id' => $activity_id))->find())) {
            $this->error('该活动不存在！');
        }
        $cur_date = date("Y-m-d");
        $start_date = $activity['bg_date'];
        $end_date = $activity['end_date'];

        //判断活动是否开始/结束
        if (strtotime($cur_date) < strtotime($start_date)) {
            $this->error('该活动尚未开始！');
        } else if (strtotime($cur_date) > strtotime($end_date)) {
            $this->error('该活动已经结束！');
        }

        //是否是该活动的管理员
        $is_manager = false; //是否是该活动管理员 管理员才具有扫码权限
        $shop = D('shop')->where(array('shop_id' => $activity['shop_id']))->find();
        $managers = D('ActivityManager')->where(array('activity_id'=>$activity_id))->select();
        foreach ($managers as $key => $val){
            if($val['user_id'] == $this->uid){
                $is_manager = true;
                break;
            }
        }
        if ($shop['user_id'] != $user['user_id'] && !$is_manager) {
            $this->error('您不是该活动的管理员，无法计时！');
        }

        $map['today_date'] = $cur_date;
        $map['activity_id'] = $activity_id;
        $map['user_id'] = $sign_user_id;
        //是否已经参见了活动，没参加活动就开始计时
        $ActivityLogs = D('ActivityLogs')->where(array('activity_id' => $activity_id, 'user_id' => $sign_user_id, 'today_date' => $cur_date))->find();
        if (!$ActivityLogs) {
            $map['manager_id'] = $user['user_id'];
            $map['start_date'] = time();
            $map['status'] = '1';
            $map['update_time'] = time();
            $map['add_time'] = time();
            if (D('ActivityLogs')->add($map)) {
                $this->success('活动' . $activity['name'] . '计时成功！', U('user/member/index'));
            } else {
                $this->error('操作失败,请重新操作', U('user/member/index'));
            }
        }
        //已经开始结束 结束活动计时
        $map['activity_log_id'] = $ActivityLogs['activity_log_id'];
        $map['end_date'] = time();
        $map['update_time'] = time();
        $map['status'] = '2';
        if (D('ActivityLogs')->save($map)) {
            $this->success('活动' . $activity['name'] . '计时结束！', U('user/member/index'));
        } else {
            $this->error('操作失败,请重新操作', U('user/member/index'));
        }
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
     * 扫秒志愿者证的二维码 展示服务信息
     */
    public function serviceInfo(){
        $result = lengthOfTime($this->uid,1);
        $user_id = $this->_param('user_id');
        $user = D('users')->where(array('user_id'=>$user_id))->find();

        $this->assign('user',$user);
        $this->assign('total_time',$result['total_time']);
        $this->assign('year_time',$result['year_time']);
        $this->assign('count',$result['count']);//参加的活动个数
        $this->display();
    }
}
