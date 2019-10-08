<?php

class ActivityAction extends CommonAction {
	
	 public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['huodong'] == 0) {
				$this->error('此功能已关闭');die;
		}
    }
	

    public function index() {
        //地区 东昌府区
        $citys = D('City')->fetchAll();
        $shops = array();
        $areas = array();
        foreach ($citys as $key=>$val) {
            //区域 例：东昌府区下的开发区
            $cur_areas = D('area')->where(array('city_id'=>$val['city_id']))->select();
            foreach ($cur_areas as $ak=>$av){
                //组织或团队 例：东昌府区开发区下的残疾人社团
                $cur_shops = D('shop')->where(array('area_id'=>$av['area_id']))->select();
                if(!empty($cur_shops)){
                    $shops = array_merge($shops,$cur_shops);
                }
                $cur_areas[$ak]['shops'] = $cur_shops;

            }
            if(!empty($cur_areas)){
                $areas = array_merge($areas,$cur_areas);
            }

            $citys[$key]['areas'] = $cur_areas;
        }

        //活动类型
        $cates = D('Activitycate')->fetchAll();
        $this->assign('citys', $citys);
        $this->assign('areas', $areas);
        $this->assign('shops', $shops);
        $this->assign('cates', $cates);
        $this->display(); // 输出模板
    }

    /**
     * 加载更多活动信息
     */
    public function moreActivity(){
        $Activity = D('Activity');
        import('ORG.Util.Page');
        $map = array('closed' => 0);

        //活动发起团队或组织
        if ($shop_id =  $this->_param('shop_id')) {
            $shopInfo = explode("=",$shop_id);
            $map[$shopInfo[0]] = $shopInfo[1];
//            $shop = D('Shop')->find($shop_id);
//            $result['shop_name'] =  $shop['shop_name'];
//            $result['shop_id'] =  $shop_id;
        }
        //活动类型
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $result['cate_id'] =  $cate_id;
        }
        //排序
        $order_str = array('activity_id' => 'desc');
        if($order = $this->_param('order')){
            if($order == 1){
                $order_str = array('bg_date' => 'ASC');
            }else if($order ==2){
                $order_str =  array('sign_num' => 'DESC');
            }
        }
        if($search_key = $this->_param('search_key')){
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
        $shops =  D('Shop')->itemsByIds($shop_ids);
        foreach ($list as $key => $val) {
            $shop_name = $shops[$val['shop_id']]['shop_name'];
            $list[$key]['shop_name'] = $shop_name;
        }
        $result['list'] =  $list;

        exit(json_encode($result)) ;
    }

    public function detail() {
        $activity_id = (int) $this->_get('activity_id');
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

    public function sign($activity_id) {
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
        }
        //$activity_id = (int) $this->_param('activity_id');
        $activity_id = (int) $activity_id;
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
            if ($obj->add($data)) {
                D('Activity')->updateCount($activity_id, 'sign_num');
                $this->error('恭喜您报名成功', U('activity/index'));
            }
            $this->error('操作失败！');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }

    public function checkSign() {
        $data = $this->checkFields($this->_post('data', false), array('name', 'mobile', 'num'));
        $data['user_id'] = (int) $this->uid;
        $data['name'] = $data['name'];
        if (empty($data['name'])) {
            $this->error('联系人不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->error('联系电话不能为空');
        }
        if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->error('联系电话格式不正确');
        }
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->error('活动人数不能为空');
        }
        return $data;
    }

}
