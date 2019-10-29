<?php
class ShopAction extends CommonAction{
    private $edit_fields = array('user_id', 'cate_id', 'apply_id', 'grade_id', 'city_id', 'area_id', 'business_id', 'shop_name', 'mobile', 'logo', 'photo', 'addr', 'tel', 'extension', 'contact', 'tags', 'near', 'business_time', 'delivery_time', 'is_pei', 'orderby', 'lng', 'lat', 'price', 'is_ding', 'recognition', 'panorama_url', 'apiKey', 'mKey', 'partner', 'machine_code', 'service', 'service_audit', 'is_ele_print', 'is_tuan_print', 'is_goods_print', 'is_booking_print', 'is_appoint_print', 'service_audit');


    public function index(){
        $this->display();
    }
    public function logo(){
        if ($this->isPost()) {
            $logo = $this->_post('logo', 'htmlspecialchars');
            if (empty($logo)) {
                $this->baoError('请上传组织/团体LOGO');
            }
            if (!isImage($logo)) {
                $this->baoError('组织/团体LOGO格式不正确');
            }
            $data = array('shop_id' => $this->shop_id, 'logo' => $logo);
            if (D('Shop')->save($data)) {
                $this->baoSuccess('上传LOGO成功！', U('shop/logo'));
            }
            $this->baoError('更新LOGO失败');
        } else {
            $this->display();
        }
    }
    public function image(){
        if ($this->isPost()) {
            $photo = $this->_post('photo', 'htmlspecialchars');
            if (empty($photo)) {
                $this->baoError('请上传组织/团体形象照');
            }
            if (!isImage($photo)) {
                $this->baoError('组织/团体形象照格式不正确');
            }
			
			$logo = $this->_post('logo', 'htmlspecialchars');
            if (empty($logo)) {
                $this->baoError('请上传组织/团体LOGO');
            }
            if (!isImage($logo)) {
                $this->baoError('LOGO格式不正确');
            }
			
            $data = array('shop_id' => $this->shop_id, 'photo' => $photo, 'logo' => $logo);
            if (false !== D('Shop')->save($data)) {
                $this->baoSuccess('上传成功', U('shop/image'));
            }
            $this->baoError('更新形象照失败');
        } else {
            $this->display();
        }
    }
    public function about(){
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('addr', 'contact','tel','mobile', 'qq', 'business_time', 'delivery_time'));
            $data['addr'] = htmlspecialchars($data['addr']);
            if (empty($data['addr'])) {
                $this->baoError('组织/团体地址不能为空');
            }
            $data['contact'] = htmlspecialchars($data['contact']);
			$data['tel'] = htmlspecialchars($data['tel']);
			$data['mobile'] = htmlspecialchars($data['mobile']);
//            if (empty($data['mobile'])) {
//                $this->baoError('手机不能为空');
//            }
            if (!isMobile($data['mobile'])) {
                $this->baoError('手机格式不正确');
            }
            $data['qq'] = htmlspecialchars($data['qq']);
            $data['business_time'] = htmlspecialchars($data['business_time']);
            $data['shop_id'] = $this->shop_id;
            $data['delivery_time'] = (int) $data['delivery_time'];
            $details = $this->_post('details', 'SecurityEditorHtml');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('组织/团体介绍含有敏感词：' . $words);
            }
            $ex = array('details' => $details, 'near' => $data['near'], 'business_time' => $data['business_time'], 'delivery_time' => $data['delivery_time']);
            unset($data['business_time'], $data['near'], $data['delivery_time']);
            if (false !== D('Shop')->save($data)) {
                D('Shopdetails')->upDetails($this->shop_id, $ex);
                $this->baoSuccess('操作成功', U('shop/about'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('ex', D('Shopdetails')->find($this->shop_id));
            $this->display();
        }
    }
    //其他设置
    public function service(){
        $obj = D('Shop');
        if (!($detail = $obj->find($this->shop_id))) {
            $this->baoError('请选择要编辑的组织/团体');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->baoError('请不要非法操作');
        }
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('is_ele_print','is_tuan_print','is_goods_print','is_booking_print','is_appoint_print','panorama_url','apiKey', 'mKey', 'partner', 'machine_code', 'service'));
			$data['is_ele_print'] = (int) $_POST['is_ele_print'];
			$data['is_tuan_print'] = (int) $_POST['is_tuan_print'];
			$data['is_goods_print'] = (int) $_POST['is_goods_print'];
			$data['is_booking_print'] = (int) $_POST['is_booking_print'];
			$data['is_appoint_print'] = (int) $_POST['is_appoint_print'];
			$data['panorama_url'] = htmlspecialchars($data['panorama_url']);
            $data['apiKey'] = htmlspecialchars($data['apiKey']);
            $data['mKey'] = htmlspecialchars($data['mKey']);
            $data['partner'] = htmlspecialchars($data['partner']);
            $data['machine_code'] = htmlspecialchars($data['machine_code']);
            $data['service'] = $data['service'];
            $data['shop_id'] = $this->shop_id;

            if (false !== $obj->save($data)) {
                $this->baoSuccess('更新成功', U('shop/service'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    //购买短信
    public function sms() {
        $sms_shop_money = $this->_CONFIG['sms_shop']['sms_shop_money']; //单价
        $sms_shop_small = $this->_CONFIG['sms_shop']['sms_shop_small'];//最少购买多少条
        $sms_shop_big = $this->_CONFIG['sms_shop']['sms_shop_big'];//最大购买多少条
        $nums = D('Smsshop')->where(array('type' => shop, 'shop_id' => $this->shop_id))->find();
        if (IS_POST) {
            $num = (int) $_POST['num'];
            if ($num <= 0) {
                $this->baoError('购买数量不合法');
            }
            if ($num % 100 != 0) {
                $this->baoError('总需人次必须为100的倍数');
            }
            if ($num < $sms_shop_small) {
                $this->baoError('购买短信数量不得小于' . $sms_shop_small . '条');
            }
            if ($num > $sms_shop_big) {
                $this->baoError('购买短信数量不得大于' . $sms_shop_big . '条');
            }
            if ($nums['num'] >= 1000) {
                $this->baoError('您当前还有' . $nums['num'] . '条短信，用完再来买吧');
            }
            $money = $num * ($sms_shop_money * 100);
            //总金额
            if ($money > $this->member['money'] || $this->member['money'] == 0) {
                $this->baoError('你的余额不足，请先充值');
            }
            if (D('Users')->addMoney($this->uid, -$money, '商户购买短信：' . $num . '条')) {
                if (empty($nums)) {
                    //如果以前没有购买过
                    $data['user_id'] = $this->uid;
                    $data['shop_id'] = $this->shop_id;
                    $data['type'] = shop;
                    $data['num'] = $num;
                    $data['create_time'] = NOW_TIME;
                    $data['create_ip'] = get_client_ip();
                    D('Smsshop')->add($data);
                } else {
                    D('Smsshop')->where(array('log_id' => $nums['log_id']))->setInc('num', $num);
                    // 增加短信
                }
                $this->baoSuccess('购买短信成功', U('shop/sms'));
            } else {
                $this->baoError('购买错误，没有付款成功！');
            }
        } else {
            $this->assign('sms_shop_money', $sms_shop_money);
            $this->assign('sms_shop_small', $sms_shop_small);
            $this->assign('sms_shop_big', $sms_shop_big);
            $this->assign('nums', $nums);
            $this->display();
        }
    }
	
	//组织/团体等级权限
	public function grade(){
        $Shopgrade = D('Shopgrade');
        import('ORG.Util.Page');
        $map = array();
        $count = $Shopgrade->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Shopgrade->where($map)->order(array('orderby' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
            $list[$k]['shop_count'] = $Shopgrade->get_shop_count($val['grade_id']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	//组织/团体等级权限
	public function permission($grade_id = 0){
        $grade_id = (int) $grade_id;
        $obj = D('Shopgrade');
        if (!($detail = $obj->find($grade_id))) {
            $this->baoError('请选择要查看的组织/团体等级');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
	
	//购买等级权限
	public function pay_permission(){
        $grade_id = (int) $this->_param('grade_id');
		$shop_id = (int) $this->_param('shop_id');
        if (!$obj = D('Shopgradeorder')->shop_pay_grade($grade_id,$shop_id)) {
			$this->baoError(D('Shopgradeorder')->getError(), 3000, true);	
        }else{
			 $this->baoSuccess('恭喜您购买等级成功', U('shop/grade'));
		}
        $this->display();
    }
    public function edit(){

        if ($shop_id = $this->shop_id) {
            $obj = D('Shop');
            if (!($detail = $obj->find($shop_id))) {
                $this->baoError('请选择要编辑的组织/团体');
            }
            if ($this->isPost()) {
                $data = $this->editCheck($shop_id);
                $data['shop_id'] = $shop_id;
                $details = $this->_post('details', 'SecurityEditorHtml');
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->baoError('组织/团体介绍含有敏感词：' . $words);
                }
                $bank = $this->_post('bank', 'htmlspecialchars');
                $shopdetails = D('Shopdetails')->find($shop_id);
                $ex = array('details' => $details, 'bank' => $bank, 'near' => $data['near'], 'price' => $data['price'], 'business_time' => $data['business_time']);
                if (!empty($shopdetails['wei_pic'])) {
                    if (true !== strpos($shopdetails['wei_pic'], 'https://mp.weixin.qq.com/')) {
                        $wei_pic = D('Weixin')->getCode($shop_id, 1);
                        $ex['wei_pic'] = $wei_pic;
                    }
                } else {
                    $wei_pic = D('Weixin')->getCode($shop_id, 1);
                    $ex['wei_pic'] = $wei_pic;
                }
                unset($data['near'], $data['price'], $data['business_time']);
                if (false !== $obj->save($data)) {
                    D('Shopdetails')->upDetails($shop_id, $ex);
                    $this->baoSuccess('操作成功', U('shop/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('areas', D('Area')->fetchAll());
                $this->assign('cates', D('Shopcate')->fetchAll());
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('organizations', D('Shop')->fetchAll());
                $this->assign('ex', D('Shopdetails')->find($shop_id));
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的组织/团体');
        }
    }
    private function editCheck($shop_id)
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int)$data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('管理者不能为空');
        }
//        $shop = D('Shop')->find(array('where' => array('user_id' => $data['user_id'])));
//        if (!empty($shop) && $shop['shop_id'] != $shop_id) {
//            $this->baoError('该管理者已经拥有组织/团体了');
//        }
        $data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['parent_id'] = (int)$data['parent_id'];
        if (empty($data['parent_id'])) {
//            $this->baoError('归属组织不能为空');
        }
        $data['grade_id'] = (int)$data['grade_id'];
        if (empty($data['grade_id'])) {
//            $this->baoError('组织/团体等级不能为空');
        }
        $data['city_id'] = (int)$data['city_id'];
        $data['area_id'] = (int)$data['area_id'];
        if (empty($data['area_id'])) {
//            $this->baoError('所在区域不能为空');
        }
        $data['business_id'] = (int)$data['business_id'];
        if (empty($data['business_id'])) {
//            $this->baoError('所在区县不能为空');
        }
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if (empty($data['shop_name'])) {
            $this->baoError('组织/团体名称不能为空');
        }
        $data['logo'] = htmlspecialchars($data['logo']);
        if (empty($data['logo'])) {
            $this->baoError('请上传组织/团体LOGO');
        }
        if (!isImage($data['logo'])) {
            $this->baoError('组织/团体LOGO格式不正确');
        }
        $data['apply_id'] = htmlspecialchars($data['apply_id']);
        if (empty($data['apply_id'])) {
            $this->baoError('请选择组织类型');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传入驻资料');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('入驻资料格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('组织/团体地址不能为空');
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['tel']) && empty($data['mobile'])) {
            $this->baoError('组织/团体电话不能为空');
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        $data['tags'] = htmlspecialchars($data['tags']);
        $data['near'] = htmlspecialchars($data['near']);
        $data['business_time'] = htmlspecialchars($data['business_time']);
        $data['orderby'] = (int)$data['orderby'];
        $data['panorama_url'] = htmlspecialchars($data['panorama_url']);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['price'] = (int)$data['price'];
        $data['is_pei'] = (int)$data['is_pei'];
        $data['apiKey'] = htmlspecialchars($data['apiKey']);
        $data['mKey'] = htmlspecialchars($data['mKey']);
        $data['partner'] = htmlspecialchars($data['partner']);
        $data['machine_code'] = htmlspecialchars($data['machine_code']);
        $data['service'] = $data['service'];
        $data['service_audit'] = (int)$data['service_audit'];
        $data['is_ele_print'] = (int)$data['is_ele_print'];
        $data['is_tuan_print'] = (int)$data['is_tuan_print'];
        $data['is_goods_print'] = (int)$data['is_goods_print'];
        $data['is_booking_print'] = (int)$data['is_booking_print'];
        $data['is_appoint_print'] = (int)$data['is_appoint_print'];
        return $data;
    }
}