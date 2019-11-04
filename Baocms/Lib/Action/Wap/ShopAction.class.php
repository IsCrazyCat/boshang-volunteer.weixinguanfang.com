<?php
class
ShopAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        $this->lifecate = D('Lifecate')->fetchAll();
        $this->lifechannel = D('Lifecate')->getChannelMeans();
        $this->assign('lifecate', $this->lifecate);
        $this->assign('channel', $this->lifechannel);
        //统计组织/团体分类数量代码开始
        $shopcates = D('Shopcate')->fetchAll();
        foreach ($shopcates as $key => $v) {
            if ($v['cate_id']) {
                $catids = D('Shopcate')->getChildren($v['cate_id']);
                if (!empty($catids)) {
                    $count = D('Shop')->where(array('cate_id' => array('IN', $catids), 'closed' => 0, 'audit' => 1))->count();
                } else {
                    $count = D('Shop')->where(array('cate_id' => $cat, 'closed' => 0, 'audit' => 1))->count();
                }
            }
            $shopcates[$key]['count'] = $count;
        }
        $this->assign('shopcates', $shopcates);
        //结束
    }
    public function index()
    {
        $cat = (int) $this->_param('cat');
        $this->assign('cat', $cat);
        $order = (int) $this->_param('order');
        $this->assign('order', $order);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $citys = D('City')->fetchAll();
        $city = (int) $this->_param('city');
        $this->assign('city_id', $city);
        $areas = D('Area')->fetchAll();
        $area = (int) $this->_param('area');
        $this->assign('area_id', $area);
        $biz = D('Business')->fetchAll();
        $business = (int) $this->_param('business');
        $organization_id = (int) $this->_param('organization_id');
        $this->assign('business_id', $business);
        $this->assign('organization_id', $organization_id);
        $this->assign('areas', $areas);
        $this->assign('citys', $citys);
        $this->assign('biz', $biz);
        $this->assign('nextpage', LinkTo('shop/loaddata', array('cat' => $cat, 'city'=>$city , 'area' => $area, 'business' => $business, 'order' => $order,'organization_id'=>$organization_id, 't' => NOW_TIME, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
        // 输出模板
    }
    //二维码名片开始
    public function qrcode($shop_id){
        $shop_id = (int) $shop_id;
        if (empty($shop_id)) {
            $this->error('该组织/团体不存在');
        }
        $shop = D('Shop')->find($shop_id);
        $file = D('Weixin')->getCode($shop_id, 1);
        $this->assign('file', $file);
        $this->assign('shop', $shop);
        $this->display();
    }
    public function gps($shop_id)
    {
        $shop_id = (int) $shop_id;
        if (empty($shop_id)) {
            $this->error('该组织/团体不存在');
        }
        $shop = D('Shop')->find($shop_id);
        $this->assign('shop', $shop);
        $this->display();
    }
    public function main()
    {
        $this->display();
    }
    public function loaddata()
    {
        $Shop = D('Shop');
        import('ORG.Util.Page');
        $city = (int) $this->_param('city');
        if($city){
            $map = array('closed' => 0, 'audit' => 1, 'city_id' => $this->$city);
        }else{
            $map = array('closed' => 0, 'audit' => 1);
        }
        $cat = (int) $this->_param('cat');
        if ($cat) {
            $catids = D('Shopcate')->getChildren($cat);
            if (!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
            } else {
                $map['cate_id'] = $cat;
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name|addr'] = array('LIKE', '%' . $keyword . '%');
        }
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
        }
        $business = (int) $this->_param('business');
        if ($business) {
            $map['business_id'] = $business;
        }
        $organization_id = (int) $this->_param('organization_id');
        if ($organization_id) {
            $map['parent_id'] = $organization_id;
        }
        $order = (int) $this->_param('order');
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        switch ($order) {
            case 2:
                $orderby = array('orderby' => 'asc', 'ranking' => 'desc');
                break;
            default:
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
                break;
        }
        $count = $Shop->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Shop->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $shop_ids = array();
        foreach ($list as $key => $v) {
            $shop_ids[$v['shop_id']] = $v['shop_id'];
        }
        $shopdetails = D('Shopdetails')->itemsByIds($shop_ids);
        foreach ($list as $k => $val) {
            $list[$k]['price'] = $shopdetails[$val['shop_id']]['price'];
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function detail() {
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
        $organization = D('shop')->where(array('shop_id'=>$shop_id))->find();

        //获取该组织下的活动
        $activitys = D('activity')->where(array('shop_id'=>$organization['shop_id']))->select();
        //该组织下的活动个数
        $actitity_count = count($activitys);
        $organization['activity_count'] = $actitity_count;

        $sign_count = 0;//该组织下属所有活动的所有报名人数
        $join_count = 0;//该组织下属所有活动的所有参加人数
        $total_time = 0;//该组织下属所有活动的总活动时间
//        $year_time = 0;//该组织下属所有活动的今年活动时间
        $sign_users = array();
        foreach ($activitys as $akey => $aval){
            $result = service_info_organization($aval['activity_id']);
            //获取该组织下的活动总时长和今年时长
            $total_time += $result['service_time'];

            //获取该活动下的报名人数和实际参加人数
            $sign_count += $result['sign_count'];
            $join_count += $result['join_count'];
        }
        $sign_count = count(array_unique($sign_users));
        $organization['total_time'] = $total_time;
        $organization['sign_count'] = $sign_count;
        $organization['join_count'] = $join_count;

        if (!empty($this->uid)) {
            $this->assign("is_login","1");//0未登录 1已登录
            //登陆状态
            $data['user_id'] = $this->uid;
            $data['organization_id'] = $this->_param('shop_id');
            if($volunteerInfo = D('organizationVolunteer')->where($data)->find()){
                $this->assign("volunteerInfo",$volunteerInfo);//是否是该组织的志愿者 0否 1是
            }else{
                $this->assign("is_volunteer",'0');//是否是该组织的志愿者 0否 1是
            }
        }
        $organization['volunteer_count'] = D('OrganizationVolunteer')->where(array('organization_id'=>$shop_id))->count();
        $this->assign("organization",$organization);
        $this->display();
    }
    public function favorites(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
        }
        if (D('Shopfavorites')->check($shop_id, $this->uid)) {
            $this->error('您已经收藏过了！');
        }
        $data = array('shop_id' => $shop_id, 'user_id' => $this->uid, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
        if (D('Shopfavorites')->add($data)) {
			D('Shop')->updateCount($shop_id, 'fans_num');
            $this->success('恭喜您收藏成功！', U('shop/detail', array('shop_id' => $shop_id)));
        }
        $this->error('收藏失败！');
    }
    //点评
    public function dianping(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
        $this->assign('detail', $detail);
        $this->display();
    }
    public function dianpingloading(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            die('0');
        }
        if ($detail['closed']) {
            die('0');
        }
        $Shopdianping = D('Shopdianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY));
        $count = $Shopdianping->where($map)->count();
        $Page = new Page($count, 5);
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $show = $Page->show();
        $list = $Shopdianping->where($map)->order(array('dianping_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $dianping_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $dianping_ids[$val['dianping_id']] = $val['dianping_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($dianping_ids)) {
            $this->assign('pics', D('Shopdianpingpics')->where(array('dianping_id' => array('IN', $dianping_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	
	//点评详情
    public function img(){
        $dianping_id = (int) $this->_get('dianping_id');
        if (!($detail = D('Shopdianping')->where(array('dianping_id'=>$dianping_id))->find())){
            $this->error('没有该点评');
            die;
        }
        if ($detail['closed']) {
            $this->error('该点评已经被删除');
            die;
        }
        $list =  D('Shopdianpingpics')->where(array('dianping_id' =>$detail['dianping_id']))->select();
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	
    //新添加预约组织/团体开始
    public function book($shop_id){
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
        }
        $shop_id = (int) $shop_id;
        $detail = D('Shop')->find($shop_id);
        if (empty($detail)) {
            $this->error('组织/团体不存在');
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
        $sj_user = $detail['user_id'];
        $shangjia_mobile = D('Users')->find($sj_user);
        $sj_mobile = $shangjia_mobile['mobile'];
        $sj_email = $shangjia_mobile['email'];
        //获得组织/团体的邮件
        //去除组织/团体手机号结束
        if ($this->isPost()) {
            $data = $this->checkBook($shop_id);
            $obj = D('Shopyuyue');
            $data['shop_id'] = (int) $shop_id;
            $data['type'] = 0;
            $data['code'] = $obj->getCode();
            if ($obj->add($data)) {
                //通知用户
                if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                    D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_shop_yuyue_code', $data['mobile'], array('sitename' => $this->_CONFIG['site']['sitename'], 'shop_name' => $detail['shop_name'], 'shop_tel' => $detail['tel'], 'shop_addr' => $detail['addr'], 'code' => $data['code']));
                } else {
                    D('Sms')->sendSms('sms_shop_yuyue', $data['mobile'], array('shop_name' => $detail['shop_name'], 'shop_tel' => $detail['tel'], 'shop_addr' => $detail['addr'], 'code' => $data['code']));
                }
                //预约通知组织/团体功能开始
                if (!empty($sj_mobile)) {
                    if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                        D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_dytz', $sj_mobile, array('sitename' => $this->_CONFIG['site']['sitename'], 'name' => $data['name'], 'content' => $data['content'], 'yuyue_time' => $data['yuyue_time'], 'mobile' => $data['mobile'], 'number' => $data['number'], 'yuyue_date' => $data['yuyue_date']));
                    } else {
                        D('Sms')->sendSms('sms_shangjia_yuyue', $sj_mobile, array('name' => $data['name'], 'content' => $data['content'], 'yuyue_time' => $data['yuyue_time'], 'mobile' => $data['mobile'], 'number' => $data['number'], 'yuyue_date' => $data['yuyue_date']));
                    }
                }
                //预约通知组织/团体功能结束
                //邮件功能
                if (!empty($sj_email)) {
                    D('Email')->sendMail('email_yuyue', $sj_email, '邮件标题', array('name' => $data['name'], 'content' => $data['content'], 'yuyue_time' => $data['yuyue_time'], 'mobile' => $data['mobile'], 'number' => $data['number'], 'yuyue_date' => $data['yuyue_date']));
                }
                //邮件功能
                D('Shop')->updateCount($shop_id, 'yuyue_total');
                $this->fengmiMsg('预约成功！', U('user/yuyue/index'));
            }
            $this->fengmiMsg('操作失败！');
        } else {
            $this->assign('shop_id', $shop_id);
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function checkBook(){
        $data = $this->checkFields($this->_post('data', false), array('name', 'mobile', 'type', 'content', 'yuyue_date', 'yuyue_time', 'number'));
        $data['user_id'] = (int) $this->uid;
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->fengmiMsg('称呼不能为空');
        }
        $data['content'] = htmlspecialchars($data['content']);
        if (empty($data['content'])) {
            $this->fengmiMsg('留言不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->fengmiMsg('手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->fengmiMsg('手机格式不正确');
        }
        $data['yuyue_date'] = htmlspecialchars($data['yuyue_date']);
        $data['yuyue_time'] = htmlspecialchars($data['yuyue_time']);
        if (empty($data['yuyue_date']) || empty($data['yuyue_time'])) {
            $this->fengmiMsg('预定日期不能为空');
        }
        if (!isDate($data['yuyue_date'])) {
            $this->fengmiMsg('预定日期格式错误！');
        }
        $data['number'] = (int) $data['number'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    //预约组织/团体结束
    public function branch(){
        $shop_id = I('shop_id', 0, 'intval,trim');
        $this->assign('shop_id', $shop_id);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $this->assign('nextpage', LinkTo('shop/branchload', array('keyword' => $keyword, 'shop_id' => $shop_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->display();
        // 输出模板
    }
    public function branchload(){
        $shop_id = I('shop_id', 0, 'intval,trim');
        $branch_id = (int) $this->_get('branch_id');
        $shopbranch = D('Shopbranch');
        import('ORG.Util.Page');
        $map = array('shop_id' => $shop_id, 'closed' => 0, 'audit' => 1);
        $count = $shopbranch->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $shopbranch->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->display();
    }
    //下属组织重写
    public function branches(){
        $shop_id = (int) $this->_get('shop_id');
        $branch_id = (int) $this->_get('branch_id');
        import('ORG.Util.Page');
        $detail = D('Shopbranch')->find($branch_id);
        if (empty($detail) || $detail['shop_id'] != $shop_id) {
            $this->error('该下属组织不存在');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->error('该下属组织不存在');
            die;
        }
        //调用下属组织数据
        $this->assign('goods', $goods = D('Goods')->where(array('shop_id' => $shop_id, 'branch_id' => $branch_id, 'city_id' => $this->city_id, 'audit' => 1, 'closed' => 0, 'end_date' => array('EGT', TODAY)))->order('goods_id desc')->limit(0, 12)->select());
        $this->assign('tuan', $tuan = D('Tuan')->where(array('shop_id' => $shop_id, 'branch_id' => $branch_id, 'city_id' => $this->city_id, 'audit' => 1, 'closed' => 0, 'end_date' => array('EGT', TODAY)))->order('tuan_id desc ')->limit(0, 10)->select());
        //调用总店数据
        $list = D('Shopbranch')->where(array('shop_id' => $shop_id, 'closed' => 0, 'audit' => 1))->select();
        $shopdetail = D('Shop')->find($shop_id);
        array_unshift($list, $shopdetail);
        foreach ($list as $k => $val) {
            if ($val['branch_id'] == $branch_id) {
                unset($list[$k]);
            }
        }
        D('Shopbranch')->updateCount($branch_id, 'view');
        $this->assign('detail', $detail);
        $this->display();
    }
    //增加团购
    public function tuan(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
        $this->assign('detail', $detail);
        $this->assign('nextpage', LinkTo('shop/tuanload', array('shop_id' => $shop_id, 't' => NOW_TIME, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
        // 输出模板
    }
    public function tuanload(){
        $shop_id = (int) $this->_get('shop_id');
        $tuanload = D('Tuan');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY));
        $count = $tuanload->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $tuanload->where($map)->order(array('tuan_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->display();
        // 输出模板
    }
	
		
	 //增加商城
    public function goods() {
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
		$map = array('shop_id' => $shop_id);
		$Goodsshopcate = D('Goodsshopcate')->where($map)->select();
		$this->assign('goodsshopcate', $Goodsshopcate); 
        $this->assign('detail', $detail);
		
		//商品
		$Goods = D('Goods');
        $goods_map = array('shop_id' => $shop_id,'closed' => 0,'audit' => 1, 'end_date' => array('EGT', TODAY));
        $count = $Goods->where($goods_map)->count();
        $list = $Goods->where($goods_map)->order(array('create_time' => 'desc'))->select();
        $this->assign('list', $list);
		
        $this->display();
    }
    //增加优惠劵
    public function coupon()
    {
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
        $this->assign('detail', $detail);
        $this->assign('nextpage', LinkTo('shop/couponload', array('shop_id' => $shop_id, 't' => NOW_TIME, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
        // 输出模板
    }
    public function couponload(){
        $shop_id = (int) $this->_get('shop_id');
        $couponload = D('Coupon');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY));
        $count = $couponload->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $couponload->where($map)->order(array('coupon_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->display();
    }
    //团购图文详情
    public function pic(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
		
		$list = D('Shoppic')->get_shop_pic_array($shop_id );//获取组织/团体全部图片结合
		$this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
    public function life() {
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
        $this->assign('nextpage', LinkTo('shop/lifeload', array('shop_id' => $shop_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function lifeload(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        $Life = D('Life');
        import('ORG.Util.Page');
        $map = array('audit' => 1, 'city_id' => $this->city_id, 'user_id' => $detail['user_id']);
        $count = $Life->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Life->where($map)->order(array('top_date' => 'desc', 'last_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function news(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
        $this->assign('nextpage', LinkTo('shop/newsload', array('shop_id' => $shop_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function newsload(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
        $Shopnews = D('Shopnews');
        import('ORG.Util.Page');
        $map = array('audit' => 1, 'city_id' => $this->city_id, 'shop_id' => $shop_id);
        $count = $Shopnews->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Shopnews->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	public function news_detail($news_id = 0) {
        if ($news_id = (int) $news_id) {
            $obj = D('Shopnews');
            if (!$detail = $obj->find($news_id)) {
                $this->error('没有该文章');
            }
			if ($detail['audit'] != 1 ) {
            	$this->error('该文章不存在');
            }	
			$obj->updateCount($news_id, 'views');
            $this->assign('detail', $detail);
            $this->display();
        } else {
            $this->error('没有该文章');
        }
    }
    //增加优惠劵
    public function mall(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
        $this->assign('detail', $detail);
        $this->assign('nextpage', LinkTo('shop/mallload', array('shop_id' => $shop_id, 't' => NOW_TIME, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
    }
    public function mallonload(){
        $shop_id = (int) $this->_get('shop_id');
        $Goods = D('Goods');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => 1, 'shop_id' => $shop_id, 'end_date' => array('ELT', TODAY));
        $count = $Goods->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $list = $Goods->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->display();
    }
    public function recognition() {
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该组织/团体');
            die;
        }
        if ($detail['closed']) {
            $this->error('该组织/团体已经被删除');
            die;
        }
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('name', 'mobile', 'content'));
//            if (D('Shop')->find(array('where' => array('user_id' => $this->uid)))) {
//                $this->fengmiMsg('您已经拥有一家组织/团体了！不能认领了！', U('distributors/index/index'));
//            }
//            if (D('Shoprecognition')->where(array('user_id' => $this->uid))->find()) {
//                $this->fengmiMsg('您已经认领过一家组织/团体了，不能认领了哦！');
//            }
            $data['user_id'] = (int) $this->uid;
            $data['shop_id'] = (int) $shop_id;
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->fengmiMsg('称呼不能为空');
            }
            $data['content'] = htmlspecialchars($data['content']);
            if (empty($data['content'])) {
                $this->fengmiMsg('留言不能为空');
            }
            $data['mobile'] = htmlspecialchars($data['mobile']);
            if (empty($data['mobile'])) {
                $this->fengmiMsg('手机不能为空');
            }
            if (!isMobile($data['mobile'])) {
                $this->fengmiMsg('手机格式不正确');
            }
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Shoprecognition');
            $data['code'] = $obj->getCode();
            //保证唯一性
            if ($obj->add($data)) {
                $mobile = $this->_CONFIG['site']['config_mobile'];
                //通知用户
                if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                    D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_shop_recognition_admin', $mobile, array('shop_name' => $detail['shop_name'], 'name' => $data['name']));
                }
            }
            $this->fengmiMsg('恭喜，认领成功，等待管理员审核', U('Wap/shop/index'));
        } else {
            $this->assign('shop_id', $shop_id);
            $this->assign('detail', $detail);
            $this->display();
        }
    }
	//点餐页面
	public function ele(){
        $shop_id = (int) $this->_param('shop_id');
        if (!($detail = D('Ele')->find($shop_id))) {
            $this->error('该餐厅不存在');
        }
        if (!($shop = D('Shop')->find($shop_id))) {
            $this->error('该餐厅不存在');
        }
        $Eleproduct = D('Eleproduct');
        $map = array('closed' => 0, 'audit' => 1, 'shop_id' => $shop_id);
        $list = $Eleproduct->where($map)->order(array('sold_num' => 'desc', 'price' => 'asc'))->select();
        foreach ($list as $k => $val) {
            $list[$k]['cart_num'] = $this->cart[$val['product_id']]['cart_num'];
        }
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->assign('cates', D('Elecate')->where(array('shop_id' => $shop_id, 'closed' => 0))->select());
        $this->assign('shop', $shop);
        $this->display();
    }
    public function breaks($shop_id){
        //优惠买单
        if (!$this->uid) {
            $this->error('请登录', U('passport/login'));
        }
        $shop_id = (int) $shop_id;
        if (!$shop_id) {
            $this->error('该组织/团体没有设置买单优惠');
        } elseif (!($detail = D('Shopyouhui')->where(array('shop_id' => $shop_id, 'is_open' => 1))->find())) {
            $this->error('该组织/团体没有设置买单优惠或已关闭');
        }
        if ($detail['audit'] == 0) {
            $this->error('组织/团体优惠未通过审核');
        }
        $breaksorder = D('Breaksorder')->where(array('user_id' => $this->uid))->order(array('create_time' => 'desc'))->find();
        $breaksorder_time = NOW_TIME;
        $cha = $breaksorder_time - $breaksorder['create_time'];
        if ($cha < 30) {
            $this->success('提交太频繁！', U('shop/detail', array('shop_id' => $shop_id)));
        }
        if ($this->isPost()) {
            $amount = floatval($_POST['amount']);
            if (empty($amount)) {
                $this->fengmiMsg('消费金额不能为空');
            }
            $exception = floatval($_POST['exception']);
            $need_pay = D('Shopyouhui')->get_amount($shop_id, $amount, $exception);
            $data = array('shop_id' => $shop_id, 'user_id' => $this->uid, 'amount' => $amount, 'exception' => $exception, 'need_pay' => $need_pay, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
            if ($order_id = D('Breaksorder')->add($data)) {
                $this->fengmiMsg('创建订单成功！', U('shop/breakspay', array('order_id' => $order_id)), U('shop/breakspay', array('order_id' => $order_id)));
            } else {
                $this->fengmiMsg('创建订单失败！');
            }
        } else {
            $this->assign('detail', $detail);
            $this->mobile_title = '优惠买单';
            $this->display();
        }
    }
    public function breakspay(){
        if (empty($this->uid)) {
            $this->error('请登录', U('passport/login'));
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Breaksorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->fengmiMsg('该订单不存在');
        }
        $shop = D('Shop')->find($order['shop_id']);
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->assign('shop', $shop);
        $this->assign('order', $order);
        $this->display();
    }
    public function breakspay2(){
        if (empty($this->uid)) {
            $this->error('请登录', U('passport/login'));
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Breaksorder')->find($order_id);
        if (empty($order) || (int) $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->fengmiMsg('该订单不存在');
        }
        if (!($code = $this->_post('code'))) {
            $this->fengmiMsg('请选择支付方式！');
        }
        $logs = D('Paymentlogs')->getLogsByOrderId('breaks', $order_id);
        if (empty($logs)) {
            $logs = array('type' => 'breaks', 'user_id' => $this->uid, 'order_id' => $order_id, 'code' => $code, 'need_pay' => $order['need_pay'] * 100, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip(), 'is_paid' => 0);
            $logs['log_id'] = D('Paymentlogs')->add($logs);
        } else {
            $logs['need_pay'] = $order['need_pay'] * 100;
            $logs['code'] = $code;
            D('Paymentlogs')->save($logs);
        }
        $this->fengmiMsg('买单订单设置完毕，即将进入付款。', U('payment/payment', array('log_id' => $logs['log_id'])));
    }
}