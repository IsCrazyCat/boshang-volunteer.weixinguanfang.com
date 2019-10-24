<?php

class MemberAction extends CommonAction
{

    public function pay()
    {
        $logs_id = (int)$this->_get('logs_id');
        if (empty($logs_id)) {
            $this->error('没有有效的支付');
        }
        if (!($detail = D('Paymentlogs')->find($logs_id))) {
            $this->error('没有有效的支付');
        }
        if ($detail['code'] != 'money') {
            $this->error('没有有效的支付');
        }
        $member = D('Users')->find($this->uid);
        if ($detail['is_paid']) {
            $this->error('没有有效的支付');
        }

        $session_pay_password = session('session_pay_password');
        if (!$session_pay_password) {
            $this->error('非法操作，付款失败');
        }

        if ($member['money'] < $detail['need_pay']) {
            $this->error('很抱歉您的账户余额不足', U('user/money/index'));
        }
        $member['money'] -= $detail['need_pay'];
        if (D('Users')->save(array('user_id' => $this->uid, 'money' => $member['money']))) {
            D('Usermoneylogs')->add(array(
                'user_id' => $this->uid,
                'money' => -$detail['need_pay'],
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'intro' => '余额支付' . $logs_id
            ));
            D('Payment')->logsPaid($logs_id);
        }
        session('session_pay_password', null); //销毁cookie
        if ($detail['type'] == 'ele') {
            $this->ele_success('恭喜您支付成功啦！', $detail);
        } elseif ($detail['type'] == 'booking') {
            $this->booking_success('恭喜您支付成功啦！', $detail);
        } elseif ($detail['type'] == 'farm') {
            $this->farm_success('恭喜您支付成功啦！', $detail);
        } elseif ($detail['type'] == 'appoint') {
            $this->appoint_success('恭喜您家政支付成功啦！', $detail);//家政
        } elseif ($detail['type'] == 'running') {
            $this->running_success('恭喜您家政跑腿成功啦！', $detail);
        } elseif ($detail['type'] == 'goods') {
            $this->goods_success('恭喜您支付成功啦！', $detail);
        } elseif ($detail['cloud'] == 'cloud') {
            $this->cloud_success('恭喜您云购支付成功啦！', $detail);
        } elseif ($detail['type'] == 'gold' || $detail['type'] == 'money') {
            $this->success('恭喜您充值成功', U('user/member/index'));
            die;
        } elseif ($detail['type'] == 'breaks') {
            $this->success('恭喜您买单成功', U('user/member/index'));
            die;
        } else {
            $this->other_success('恭喜您支付成功啦！', $detail);
        }
    }

    //跑腿支付成功
    protected function running_success($message, $detail)
    {
        $running_id = (int)$detail['order_id'];
        $running = D('Running')->find($running_id);
        $this->assign('running', $running);
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('running');
    }

    protected function ele_success($message, $detail)
    {
        $order_id = $detail['order_id'];
        $eleorder = D('Eleorder')->find($order_id);
        $detail['single_time'] = $eleorder['create_time'];
        $detail['settlement_price'] = $eleorder['settlement_price'];
        $detail['new_money'] = $eleorder['new_money'];
        $detail['fan_money'] = $eleorder['fan_money'];
        $addr_id = $eleorder['addr_id'];
        $product_ids = array();
        $ele_goods = D('Eleorderproduct')->where(array('order_id' => $order_id))->select();
        foreach ($ele_goods as $k => $val) {
            if (!empty($val['product_id'])) {
                $product_ids[$val['product_id']] = $val['product_id'];
            }
        }
        $addr = D('Useraddr')->find($addr_id);
        $this->assign('addr', $addr);
        $this->assign('ele_goods', $ele_goods);
        $this->assign('products', D('Eleproduct')->itemsByIds($product_ids));
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('ele');
    }

    protected function goods_success($message, $detail)
    {
        $order_ids = array();
        if (!empty($detail['order_id'])) {
            $order_ids[] = $detail['order_id'];
        } else {
            $order_ids = explode(',', $detail['order_ids']);
        }
        $goods = $good_ids = $addrs = array();
        foreach ($order_ids as $k => $val) {
            if (!empty($val)) {
                $order = D('Order')->find($val);
                $addr = D('Useraddr')->find($order['addr_id']);
                $ordergoods = D('Ordergoods')->where(array('order_id' => $val))->select();
                foreach ($ordergoods as $a => $v) {
                    $good_ids[$v['goods_id']] = $v['goods_id'];
                }
            }
            $goods[$k] = $ordergoods;
            $addrs[$k] = $addr;
        }
        $this->assign('addr', $addrs[0]);
        $this->assign('goods', $goods);
        $this->assign('good', D('Goods')->itemsByIds($good_ids));
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('goods');
    }

    protected function booking_success($message, $detail)
    {
        $order_id = (int)$detail['order_id'];
        $order = D('Bookingorder')->find($order_id);
        $dingmenu = D('Bookingordermenu')->where(array('order_id' => $order_id))->select();
        $menu_ids = array();
        foreach ($dingmenu as $k => $val) {
            $menu_ids[$val['menu_id']] = $val['menu_id'];
        }
        $this->assign('menus', D('Bookingmenu')->itemsByIds($menu_ids));
        $this->assign('shop', D('Booking')->find($order['shop_id']));
        $this->assign('dingmenu', $dingmenu);
        $this->assign('order', $order);
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->mobile_title = '完成支付';
        $this->display('booking');
    }

    protected function other_success($message, $detail)
    {
        $tuanorder = D('Tuanorder')->find($detail['order_id']);
        if (!empty($tuanorder['branch_id'])) {
            $branch = D('Shopbranch')->find($tuanorder['branch_id']);
            $addr = $branch['addr'];
        } else {
            $shop = D('Shop')->find($tuanorder['shop_id']);
            $addr = $shop['addr'];
        }
        $this->assign('addr', $addr);
        $tuans = D('Tuan')->find($tuanorder['tuan_id']);
        $this->assign('tuans', $tuans);
        $this->assign('tuanorder', $tuanorder);
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('other');
    }

    //家政支付成功
    protected function appoint_success($message, $detail)
    {
        $order_id = (int)$detail['order_id'];
        $order = D('Appointorder')->find($order_id);
        $Appoint = D('Appoint')->find($order['appoint_id']);//获取众筹商品
        $this->assign('order', $order);
        $this->assign('appoint', $Appoint);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('appoint');
    }

    public function detail($order_id)
    {
        $Bookingorder = D('Bookingorder');
        $Bookingyuyue = D('Bookingyuyue');
        $Bookingmenu = D('Bookingmenu');
        if (!$order = $Bookingorder->where('order_id = ' . $order_id)->find()) {
            $this->baoError('该订单不存在');
        } else if (!$yuyue = $Bookingyuyue->where('ding_id = ' . $order['ding_id'])->find()) {
            $this->baoError('该订单不存在');
        } else if ($yuyue['user_id'] != $this->uid) {
            $this->error('非法操作');
        } else {
            $arr = $Bookingorder->get_detail($this->shop_id, $order, $yuyue);
            $menu = $Bookingmenu->shop_menu($this->shop_id);
            $this->assign('yuyue', $yuyue);
            $this->assign('order', $order);
            $this->assign('order_id', $order_id);
            $this->assign('arr', $arr);
            $this->assign('menu', $menu);
            $this->display();
        }
    }

    protected function farm_success($message, $detail)
    {
        $order_id = (int)$detail['order_id'];
        $order = D('FarmOrder')->find($order_id);
        $f = D('FarmPackage')->find($order['pid']);
        $shop = D('Shop')->find($farm['shop_id']);
        $farm = D('Farm')->where(array('farm_id' => $f['farm_id']))->find();

        $this->assign('farm', $farm);
        $this->assign('order', $order);
        $this->assign('f', $f);
        $this->assign('shop', $shop);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('farm');
    }

    //云购支付成功
    protected function cloud_success($message, $detail)
    {
        $log_id = (int)$detail['order_id'];
        $cloudlogs = D('Cloudlogs')->find($log_id);
        $cloudgoods = D('Cloudgoods')->find($cloudlogs['goods_id']);//获取商品
        $this->assign('cloudlogs', $cloudlogs);
        $this->assign('cloudgoods', $cloudgoods);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('cloud');
    }

    //不知道是什么
    function diffBetweenTwoDays($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }

    public function index()
    {
        if (empty($this->uid)) {
            header('Location: ' . U('Wap/passport/login'));
            die;
        }
        //检测是否有组织/团体
        $is_shop = D('Shop')->find(array('where' => array('user_id' => $this->uid)));
        $this->assign('is_shop', $is_shop);

        $this->assign('user_id', $this->uid);
        $this->display();
    }

    public function password()
    {
        if ($this->isPost()) {
            $oldpwd = $this->_post('oldpwd', 'htmlspecialchars');
            if (empty($oldpwd)) {
                $this->error('旧密码不能为空！');
            }
            $newpwd = $this->_post('newpwd', 'htmlspecialchars');
            if (empty($newpwd)) {
                $this->error('请输入新密码');
            }
            $pwd2 = $this->_post('pwd2', 'htmlspecialchars');
            if (empty($pwd2) || $newpwd != $pwd2) {
                $this->error('两次密码输入不一致！');
            }
            if ($this->member['password'] != md5($oldpwd)) {
                $this->error('原密码不正确');
            }
            if (D('Passport')->uppwd($this->member['account'], $oldpwd, $newpwd)) {
                session('uid', null);
                $this->success('更改密码成功！', U('passport/login'));
            }
            $this->error('修改密码失败！');
        } else {
            $this->display();
        }
    }

    public function mobile()
    {
        if (!empty($this->member['mobile'])) {
            $this->success('恭喜您！您的手机已经绑定，可以正常购物！');
        }
        if ($this->isPost()) {
            $mobile = $this->_post('mobile');
            $yzm = $this->_post('yzm');
            if (empty($mobile) || empty($yzm)) {
                $this->error('请填写正确的手机及手机收到的验证码！');
            }
            $s_mobile = session('mobile');
            $s_code = session('code');
            if ($mobile != $s_mobile) {
                $this->error('手机号码和收取验证码的手机号不一致！');
            }
            if ($yzm != $s_code) {
                $this->error('验证码不正确');
            }
            $data = array('user_id' => $this->uid, 'mobile' => $mobile);
            if (D('Users')->save($data)) {
                D('Users')->integral($this->uid, 'mobile');
                $this->success('恭喜您通过手机认证', U('member/mobile'));
            }
            $this->error('更新数据失败！');
        } else {
            $this->display();
        }
    }

    public function sendsms()
    {
        $mobile = $this->_post('mobile');
        if (isMobile($mobile)) {
            session('mobile', $mobile);
            $randstring = session('code');
            if (empty($randstring)) {
                $randstring = rand_string(6, 1);
                session('code', $randstring);
            }
            //如果开启大鱼，用大鱼
            if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_yzm', $mobile, array(
                    'sitename' => $this->_CONFIG['site']['sitename'],
                    'code' => $randstring
                ));
            } else {
                D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));//短信宝
            }


        }
    }

    public function money()
    {
        $this->assign('payment', D('Payment')->getPayments());
        $this->display();
    }

    public function moneypay()
    {
        //后期优化
        $money = (int)($this->_post('money') * 100);
        $code = $this->_post('code', 'htmlspecialchars');
        if ($money <= 0) {
            $this->error('请填写正确的充值金额！');
            die;
        }
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->error('该支付方式不存在');
            die;
        }
        $logs = array('user_id' => $this->uid, 'type' => 'money', 'code' => $code, 'order_id' => 0, 'need_pay' => $money, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
        $logs['log_id'] = D('Paymentlogs')->add($logs);
        $this->assign('button', D('Payment')->getCode($logs));
        $this->assign('money', $money);
        $this->display();
    }

    public function fabu()
    {
        $this->display();
    }


    public function xiaoxizhongxin()
    {
        $msg = D('Msg');
        //用户收到的总通知
        $msg_common = $msg->where(array('is_used' => 0, 'is_fenzhan' => 0))->count();
        $msg_qita = $msg->where(array('user_id' => $this->uid, 'is_used' => 0, 'is_fenzhan' => 0))->count();
        $this->assign('msg_common', $msg_common);
        $this->assign('msg_qita', $msg_qita);
        $message = D('Message');
        $message = $message->where('user_id =' . $this->uid)->count();
        $this->assign('message', $message);
        //p($message);die;
        //统计今日新的约会数量
        $counts = array();
        $bg_time = strtotime(TODAY);
        //今日时间，需要统计其他的下面写。
        $counts['message_xiaoqu'] = (int)D('Message')->where(array('user_id' => $this->user_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $counts['mesg'] = (int)D('Msg')->where(array('user_id' => $this->user_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $this->assign('counts', $counts);
        $this->display();
    }

    public function zijinguanli()
    {
        $this->display();
    }

    public function xiaoqu()
    {
        $this->assign('community', D('Community')->where(array('user_id' => $this->uid, 'closed' => 0, 'audit' => 1))->count());//加入的小区
        $this->assign('feedback', D('Feedback')->where(array('user_id' => $this->uid, 'closed' => 0))->count());//报修数量
        $this->assign('communityorder', D('Communityorder')->where(array('user_id' => $this->uid))->count()); //账单
        $this->assign('tieba', D('Communityposts')->where(array('user_id' => $this->uid))->count());//账单
        //统计今日新的数量
        $counts = array();
        $bg_time = strtotime(TODAY);
        //今日时间，需要统计其他的下面写。
        $counts['feedback_today'] = (int)D('Feedback')->where(array('user_id' => $this->user_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $counts['communityorder_today'] = (int)D('Communityorder')->where(array('user_id' => $this->user_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $counts['tieba_today'] = (int)D('Communityposts')->where(array('user_id' => $this->user_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $this->assign('counts', $counts);
        $this->display();
    }

    public function myQRCode()
    {
        if (empty($this->uid)) {
            $this->error('您还没有登录！', U('passport/login'));
        }
        $user_id = $this->uid;
        $user = D('users')->where(array('user_id' => $user_id))->find();
        //这里是生成的二维码中的URL，即扫描二维码跳转的链接和参数
        $url = U('/user/member/index');
        //这个token只是作为二维码生成后的存放路径的一个依据 并无实际意义
        $token = 'userRegister_' . $this->uid;
        $file = baoQrCode($token, $url);
        $this->assign('user', $user);
        $this->assign('file', $file);
        $this->display();
    }

    /**
     * 我的志愿者证
     */
    public function myCard()
    {
        if (empty($this->uid)) {
            $this->error('您还没有登录！', U('passport/login'));
        }
        $user = D('users')->where(array('user_id'=>$this->uid))->find();
        //还未进行认证，跳转到认证页面
        if($user['is_certification'] == 0){
            $this->error('您还没有认证！', U('user/apply/certification'));
        }else if($user['is_certification'] == 1){
            $this->assign('cardUrl',$user['certification_img_url']);
            $this->display();
        }else{
            $data['user_id'] = $this->uid;
            $data['is_certification'] = 1;//更改认证状态为已认证

            //生成二维码
            $url = U('wap/activity/serviceInfo',array('user_id'=>$this->uid));
            //这个token只是作为二维码生成后的存放路径的一个依据 并无实际意义
            $token = 'userRegister_' . $this->uid;
            $file = baoQrCode($token, $url);

            $user = D('users')->where(array('user_id'=>$this->uid))->find();
            $organization = D('shop')->find($user['organization_id']);
            $cardURL = $this->bornshareqrode(config_img($file),$user['user_id'],$user['real_name'],$user['sex'],$organization['shop_name'],$user['certification_date'],$user['vid']);

            $data['certification_img_url'] = $cardURL;
            D('users')->save($data);
            $this->assign('cardUrl',$cardURL);
            $this->display();
        }

    }


    //图片处理函数
    function bornshareqrode($qrimg,$user_id,$name,$sex,$organization,$reg_date,$vid)
    {
        $root_path = $_SERVER['DOCUMENT_ROOT'];
        //这是背景图片的url
        $bgimg = $root_path . '/themes/default/Wap/statics/img/index/bg.png';
        //这是需要插入到背景图的图片url
        $qrimg = $root_path . $qrimg;
        if ($bgimg) {
            //这是合成后的图片保存的路径
            $upload_dir = $root_path . "/themes/default/Wap/statics/img/qrcode/";
            if (is_file($bgimg)) {
                //创建画布
                $bgimg = imagecreatefromstring(file_get_contents($bgimg));
                $qring = imagecreatefromstring(file_get_contents($qrimg));

                //写入文字
                $black = imagecolorallocate($bgimg, 0, 0, 0);
                $green = imagecolorallocate($bgimg, 11, 153, 0);

                //将$qrimg插入到$bgimg里
                //$dst_image 做背景的图片, $src_image 要插入的图片, $dst_x 要插入图片的位置的X坐标, $dst_y插入图片的位置的X坐标,
                // $src_x, $src_y, $dst_w 插入图片的宽度, $dst_h 插入图片的高度, $src_w, $src_h
                //imagesx 获取图像的宽度，单位是像素  imagesy获取图像的高度
                imagecopyresampled($bgimg, $qring, 376, 192, 0, 0, 100, 100, imagesx($qring), imagesy($qring));
                //写的文字用到的字体
                $font = $root_path . "/themes/default/Wap/statics/img/qrcode/simhei.ttf";

                //在图片里插入文字($msg,$black,$grey)
                //参数1：背景画布 参数2：字体大小 参数3：字体倾斜的角度 参数4、5：文字的x、y坐标 参数6：文字的颜色 参数7：字体文件 参数8：绘制的文字
                imagettftext($bgimg, 16, 0, 140, 128, $black, $font, $name);
                imagettftext($bgimg, 16, 0, 140, 161, $black, $font, $sex == 1 ? '男' : '女');
                imagettftext($bgimg, 16, 0, 140, 194, $black, $font, $organization);
                imagettftext($bgimg, 16, 0, 140, 230, $black, $font, $reg_date);
                imagettftext($bgimg, 16, 0, 140, 268, $black, $font, $vid);

                //生成图片
                imagepng($bgimg, $upload_dir . 'vol' . $user_id . '.png');
                //生成图片名字
                $imgUrl = '/themes/default/Wap/statics/img/qrcode/vol' . $user_id . '.png';
            }
            return $imgUrl;//返回结果图片url
        } else {
            return false;
        }
    }


    public function test()
    {
        /*
        * 图片加微信二维码，并加文字
        */
        header('Content-Type: image/png');//输出协议头
        $root_path = $_SERVER['DOCUMENT_ROOT'];
        $bigImgPath = $root_path . '/themes/default/Wap/statics/img/index/bg.png';//背景图
        $img = imagecreatefromstring(file_get_contents($bigImgPath));
        //写的文字用到的字体
        $font = $root_path . "/themes/default/Wap/statics/img/index/simhei.ttf";
        //字体颜色(RGB)
        $black = imagecolorallocate($img, 0, 0, 0);
        //字体大小
        $fontSize = 16;
        //旋转角度
        $circleSize = 0;
        //左边距
        $left = 140;
        //上边距
        $top = 125;
        $test = mb_convert_encoding('国军光', "html-entities", "utf-8"); //转成html编码
        imagefttext($img, $fontSize, $circleSize, $left, $top, $black, $font,$test );//解决乱码问题
        //左边距
        $left = 140;
        //上边距
        $top = 165;
        $test = mb_convert_encoding('国军光', "html-entities", "utf-8"); //转成html编码
        imagefttext($img, $fontSize, $circleSize, $left, $top, $black, $font,$test );//解决乱码问题
        list($bgWidth, $bgHight, $bgType) = getimagesize($bigImgPath);
        switch ($bgType) {
            case 1://gif
                header('Content-Type:image/gif');
                imagegif($img);
                break;
            case 2://jpg
                header('Content-Type:image/jpg');
                imagejpeg($img);
                break;
            case 3://jpg
                header('Content-Type:image/png');
                imagepng($img);
                break;
            default:
                break;
        }
        //销毁照片
        imagedestroy($img);
    }

    /**
     * a.合成图片信息 复制一张图片的矩形区域到另外一张图片的矩形区域
     * @param  [type] $bg_image  [目标图]
     * @param  [type] $sub_image [被添加图]
     * @param  [type] $add_x     [目标图x坐标位置]
     * @param  [type] $add_y     [目标图y坐标位置]
     * @param  [type] $add_w     [目标图宽度区域]
     * @param  [type] $add_h     [目标图高度区域]
     * @param  [type] $out_image [输出图路径]
     * @return [type]            [description]
     */

    function image_copy_image()
    {
        $root_path = $_SERVER['DOCUMENT_ROOT'];
        $bg_image = $root_path . '/themes/default/Wap/statics/img/index/bg.png';
        //这是需要插入到背景图的图片url
        $sub_image = $root_path . '/themes/default/Wap/statics/img/index/qrcode.png';
        $add_x = 20;
        $add_y = 20;
        $add_w = 50;
        $add_h = 50;
        $out_image = $root_path . '/themes/default/Wap/statics/img/index/card.png';
        if ($sub_image) {

            $bg_image_c = imagecreatefromstring(file_get_contents($bg_image));

            $sub_image_c = imagecreatefromstring(file_get_contents($sub_image));

            imagecopyresampled($bg_image_c, $sub_image_c, $add_x, $add_y, 0, 0, $add_w, $add_h, imagesx($sub_image_c), imagesy($sub_image_c));

            //保存到out_image

            imagejpeg($bg_image_c, $out_image, 80);

            imagedestroy($sub_image_c);

            imagedestroy($bg_image_c);

        }

    }

}