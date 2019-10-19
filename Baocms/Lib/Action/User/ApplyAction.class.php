<?php
class ApplyAction extends CommonAction{
    private $create_fields = array('user_id','city_id', 'area_id', 'business_id', 'logo', 'cate_id', 'tel', 'logo', 'photo', 'shop_name', 'contact', 'details', 'business_time', 'area_id', 'addr', 'lng', 'lat', 'recognition','is_pei');
	private $delivery_create_fields = array('city_id', 'user_id','photo', 'name', 'mobile', 'addr');
    public function index(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        if (D('Shop')->find(array('where' => array('user_id' => $this->uid)))) {
            $this->error('您已经拥有一家组织/团体了！', U('Distributors/index/index'));
        }
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Shop');
            $details = $this->_post('details', 'htmlspecialchars');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->fengmiMsg('组织/团体介绍含有敏感词：' . $words);
            }
            $ex = array('details' => $details, 'near' => $data['near'], 'price' => $data['price'], 'business_time' => $data['business_time']);
            unset($data['near'], $data['price'], $data['business_time']);
            if ($shop_id = $obj->add($data)) {
                $wei_pic = D('Weixin')->getCode($shop_id, 1);
                $ex['wei_pic'] = $wei_pic;
                D('Shopdetails')->upDetails($shop_id, $ex);
                $this->fengmiMsg('恭喜您申请成功！请等待管理员审核！', U('user/member/index'));
            }
            $this->fengmiMsg('申请失败！');
        } else {
            $lat = addslashes(cookie('lat'));
            $lng = addslashes(cookie('lng'));
            if (empty($lat) || empty($lng)) {
                $lat = $this->_CONFIG['site']['lat'];
                $lng = $this->_CONFIG['site']['lng'];
            }
            if ($business_id = (int) $this->_param('business_id')) {
                $map['business_id'] = $business_id;
                $this->assign('business_id', $business_id);
            }
            $this->assign('business', D('Business')->fetchAll());
            $this->assign('lat', $lat);
            $this->assign('lng', $lng);
            $areas = D('Area')->fetchAll();
            $this->assign('cates', D('Shopcate')->fetchAll());
            $this->assign('areas', $areas);
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['user_id'] = $this->uid;
		$data['logo'] = htmlspecialchars($data['logo']);
        if (empty($data['logo'])) {
            $this->fengmiMsg('请上传组织/团体LOGO');
        }
        if (!isImage($data['logo'])) {
            $this->fengmiMsg('组织/团体LOGO格式不正确');
        }
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if (empty($data['shop_name'])) {
            $this->fengmiMsg('组织/团体名称不能为空');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->fengmiMsg('分类不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->fengmiMsg('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->fengmiMsg('地区不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->fengmiMsg('街道不能为空');
        }
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->fengmiMsg('组织/团体坐标需要设置');
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        if (empty($data['contact'])) {
            $this->fengmiMsg('联系人不能为空');
        }
        $data['apply_id'] = htmlspecialchars($data['apply_id']);
        if (empty($data['apply_id'])) {
            $this->fengmiMsg('入驻类型不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->fengmiMsg('请上传入驻申请资料');
        }
        if (!isImage($data['photo'])) {
            $this->fengmiMsg('入驻申请资料格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->fengmiMsg('地址不能为空');
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->fengmiMsg('电话不能为空');
        }
        if (!isPhone($data['tel']) && !isMobile($data['tel'])) {
            $this->fengmiMsg('电话应该为13位手机号码');
        }
        if (isMobile($data['tel'])) {
            $data['phone'] = $data['tel'];
        }
        $detail = D('Shop')->where(array('user_id' => $this->uid))->find();
        if (!empty($detail)) {
            $this->fengmiMsg('您已经是组织/团体了');
        }
        $data['recognition'] = 1;
		$data['is_pei'] = 1;
        $data['user_id'] = $this->uid;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
	public function delivery(){
        if (empty($this->uid)) {
            header("Location:" . U('wap/passport/login'));
            die;
        }
		$obj = D('Delivery');
		$user_delivery = $obj->where(array('user_id' => $this->uid))->find();
		if($user_delivery['closed'] !=0){
			$this->error('非法错误');
		}
        if ($this->isPost()) {
            $data = $this->delivery_createCheck();
            if ($id = $obj->add($data)) {
                $this->fengmiMsg('恭喜您申请成功', U('user/member/index'));
            }
            $this->fengmiMsg('申请失败！');
        } else {
			$this->assign('user_delivery', $user_delivery);
            $this->display();
        }
    }
	
	 private function delivery_createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->delivery_create_fields);
		$data['user_id'] = $this->uid;
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->fengmiMsg('请上传身份证');
        }
        if (!isImage($data['photo'])) {
            $this->fengmiMsg('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->fengmiMsg('姓名不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->fengmiMsg('电话不能为空');
        }
        if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->fengmiMsg('电话应该为13位手机号码');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->fengmiMsg('地址不能为空');
        }        
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

    /**
     * 申请志愿者证
     */
    public function volunteerCard(){
        if (empty($this->uid)) {
            $this->error('您还没有登录！', U('wap/passport/login'));
        }
        if ($this->isPost()) {
            $data['user_id']=$this->uid;
            $data['real_name'] = $this->_param('userName');
            $data['id_type'] = '1'; //1 身份证 暂时只支持身份证
            $data['id_num'] = $this->_param('idcardCode');
            $mobile = $this->_param('mobile');
            $code = $this->_param('code');
            if($code != session('code')){
                $result['status'] = 'err';
                $result['msg'] = '验证码错误';
                exit(json_encode($result));
            }
            $where = 'id_num = '.$data['id_num'] . ' AND user_id != ' . $this->uid;
            $user = D('users')->where($where)->find();
            if(!empty($user)){
                $result['status'] = 'err';
                $result['msg'] = '证件号已存在';
                exit(json_encode($result));
            }
            $data['is_certification'] = '2'; //认证中，将用户填写的信息 先录入到数据库
            $data['certification_date'] =  date("Y-m-d");
            while(true){
                $random = mt_rand(10000,99999);
                $vid = substr($data['id_num'],0,6) . $random;
                if(!D('users')->where(array('vid'=>$vid))->find()){
                    $data['vid'] = $vid;
                    break;
                }
            }
            //判断性别 奇数为男 偶数为女 15为证件号最后一位 18为身份照第17位 判断性别
            if(count($data['id_num']) == 15){
                $lastNum = substr($data['id_num'],14,1);
            }else{
                $lastNum = substr($data['id_num'],16,1);
            }
            $data['sex'] = $lastNum % 2 ;

            if(D('users')->save($data)){
                $result['status'] = 'success';
                $result['msg'] = '恭喜您，认证成功！';
            }else{
                $result['status'] = 'err';
                $result['msg'] = '认证失败，请稍后重试！';
            }

            exit(json_encode($result));
        }

        $this->display();
    }

    /**
     * 实名认证
     */
    public function certification(){
        if (empty($this->uid)) {
            $this->error('您还没有登录！', U('wap/passport/login'));
        }
        $this->display();
    }

    public function sendsms(){
        $mobile = $this->_post('mobile');
        if (isMobile($mobile)) {
            session('mobile', $mobile);
            $randstring = session('code');
            if (empty($randstring)) {
                $randstring = rand_string(6, 1);
                session('code', $randstring);
            }
            $result = false; //短信发送结果
            //大鱼短信
            if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                $result= D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_yzm', $mobile, array(
                    'sitename' => $this->_CONFIG['site']['sitename'],
                    'code' => $randstring
                ));
            } else {
                $result = D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
            }
        }
        exit($result);
    }
}