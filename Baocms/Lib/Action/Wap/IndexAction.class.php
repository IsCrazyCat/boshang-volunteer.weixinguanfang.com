<?php


class IndexAction extends CommonAction {
    public function index() {
        //获取wap首页广告
        $ads = D('ad')->where(array('closed'=>0,'site_id'=>57))->select();

        $this->assign('ads',$ads);

        return $this->display();
    }

      public function index1() {

//        $this->assign('lifecate', D('Lifecate')->fetchAll());
//        $this->assign('channel', D('Lifecate')->getChannelMeans());
//
//		//获取用户自定坐标
//		$lat = cookie('lat_ok');
//		$lng = cookie('lng_ok');
//		if(empty($lat) || empty($lng)){
//			$lat = cookie('lat');
//			$lng = cookie('lng');
//		}
//        if (empty($lat) || empty($lng)) {
//            $lat = $this->_CONFIG['site']['lat'];
//            $lng = $this->_CONFIG['site']['lng'];
//        }
//        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
//        $shoplist = D('Shop')->where(array('city_id'=>$this->city_id, 'closed' => 0, 'audit' => 1))->order($orderby)->limit(0, 5)->select();
//		foreach ($shoplist as $k => $val) {
//            $shoplist[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
//        }
//
//
//        $news = D('Article')->where(array('city_id'=>$this->city_id, 'closed' => 0, 'audit' => 1))->order(array('create_time' => 'desc'))->limit(0, 5)->select();
//		$community = D('Community')->where(array('city_id'=>$this->city_id, 'closed' => 0, 'audit' => 1,))->order($orderby)->limit(0, 5)->select();
//		foreach ($community as $k => $val) {
//            $community[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
//        }
//
//        $this->assign('shoplist', $shoplist);
//        $this->assign('news', $news);
//		$this->assign('community', $community);
//
//		$maps = array('status' => 2,'closed'=>0);
//		$this->assign('nav',$nav = D('Navigation') ->where($maps)->order(array('orderby' => 'asc'))->select());
//		$bg_time = strtotime(TODAY);
//		$this->assign('sign_day', $sign_day = (int) D('Usersign')->where(array('user_id' => $this->uid, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count());
        $this->display();
    }
   

    public function search() {
        $keys = D('Keyword')->fetchAll();
        $keytype = D('Keyword')->getKeyType();
        $this->assign('keys',$keys);
        $this->display();
    }
	
	 public function dingwei() {
        $lat = $this->_get('lat', 'htmlspecialchars');
        $lng = $this->_get('lng', 'htmlspecialchars');
        cookie('lat', $lat);
        cookie('lng', $lng);
        echo NOW_TIME;
    }

	public function more() {
		$maps = array('status' => 2,'closed'=>0);
		$this->assign('nav',$nav = D('Navigation') ->where($maps)->order(array('orderby' => 'asc'))->select());
		$this->display();
	}

	//没实现的功能暂时先跳转到这里
	public function closed(){
        $this->error('此功能暂未开通');
        die;
    }
	public function picture(){
        $this->display();
    }
    public function help(){
        $this->display();
    }
    public function batchQrcode(){
        $users = D('users')->where(array('is_certification'=>1))->select();
        foreach ($users as $key=>$user){
            $data['user_id'] = $user['user_id'];
//            $data['is_certification'] = 1;//更改认证状态为已认证
//            $data['certification_date'] = date("Y-m-d");
            //生成二维码
            $url = U('wap/activity/serviceInfo',array('user_id'=>$user['user_id']));
            //这个token只是作为二维码生成后的存放路径的一个依据 并无实际意义
            $token = 'userRegister_' . $user['user_id'];
            $file = baoQrCode($token,$url,$user['head_url']);
            $data['qrcode_img_url'] = $file;

            $organization = D('shop')->find($user['organization_id']);
            $cardURL = $this->bornshareqrode(config_img($user['head_url']),config_img($file),$user['user_id'],$user['real_name'],$user['sex'],$organization['shop_name'],$user['certification_date'],$user['vid']);

            $data['certification_img_url'] = $cardURL;
            D('users')->save($data);
        }
        exit;
    }
    public function bornshareqrode($head_url,$qrimg,$user_id,$name,$sex,$organization,$reg_date,$vid)
    {
//        $head_url='/attachs/2019/10/29/thumb_5db7b08751f4c.jpg';
//        $name='果果';
//        $organization='归属上级组织';
//        $qrimg='/attachs/weixin/4bc/3e0/2b6/4bc3e02b65761d35599bf4dd864d478e.png';
//        $vid='37152212002';
//        $user_id='2';
//        $reg_date='2019-10-29';
//        $sex='1';


        $root_path = $_SERVER['DOCUMENT_ROOT'];
        //这是背景图片的url
        $bgimg = $root_path . '/themes/default/Wap/statics/img/qrcode/bg.png';
        $bgotherimg = $root_path . '/themes/default/Wap/statics/img/qrcode/bg_other.png';
        //这是需要插入到背景图的图片url
        $qrimg = $root_path . $qrimg;
        $head_url = $root_path . $head_url;
        if ($bgimg) {
            //这是合成后的图片保存的路径
            $upload_dir = $root_path . "/themes/default/Wap/statics/img/qrcode/";
            if (is_file($bgimg)) {
                //创建画布
                $bgimg = imagecreatefromstring(file_get_contents($bgimg));
                $bgotherimg = imagecreatefromstring(file_get_contents($bgotherimg));
                $qrimg = imagecreatefromstring(file_get_contents($qrimg));
                $head_url = imagecreatefromstring(file_get_contents($head_url));

                //写入文字
                $black = imagecolorallocate($bgimg, 0, 0, 0);
                $green = imagecolorallocate($bgimg, 11, 153, 0);

                //将$qrimg插入到$bgotherimg里
                //$dst_image 做背景的图片, $src_image 要插入的图片, $dst_x 要插入图片的位置的X坐标, $dst_y插入图片的位置的X坐标,
                // $src_x, $src_y, $dst_w 插入图片的宽度, $dst_h 插入图片的高度, $src_w, $src_h
                //imagesx 获取图像的宽度，单位是像素  imagesy获取图像的高度
                imagecopyresampled($bgimg, $head_url, 755, 160, 0, 0, 205, 255, imagesx($head_url), imagesy($head_url));
                imagecopyresampled($bgotherimg, $qrimg, 53, 390, 0, 0, 220, 220, imagesx($qrimg), imagesy($qrimg));
                //写的文字用到的字体
                $font = $root_path . "/themes/default/Wap/statics/img/qrcode/simhei.ttf";

                //在图片里插入文字($msg,$black,$grey)
                //参数1：背景画布 参数2：字体大小 参数3：字体倾斜的角度 参数4、5：文字的x、y坐标 参数6：文字的颜色 参数7：字体文件 参数8：绘制的文字
                imagettftext($bgimg, 16, 0, 307, 255, $black, $font, $name);
                imagettftext($bgimg, 16, 0, 620, 200, $black, $font, $sex == 1 ? '男' : '女');
                imagettftext($bgimg, 16, 0, 680, 200, $black, $font, $sex == 1 ? 'F' : 'M');
                imagettftext($bgimg, 16, 0, 307, 312, $black, $font, $organization);
//                imagettftext($bgimg, 22, 0, 140, 230, $black, $font, $reg_date);
                imagettftext($bgimg, 16, 0, 307, 372, $black, $font, $vid);

                //生成图片
                imagepng($bgimg, $upload_dir . 'vol' . $user_id . '.png');
                imagepng($bgotherimg, $upload_dir . 'vol' . $user_id . '_other.png');
                //生成图片名字
                $imgUrl = '/themes/default/Wap/statics/img/qrcode/vol' . $user_id . '.png';
            }
            return $imgUrl;//返回结果图片url
        } else {
            return false;
        }
    }

    /**
     * 调起扫一扫功能
     */
    public function scan(){
        if ($this->isPost()) {
            $signPackage = $this->signPackage;
            $this->assign('signPackage',$this->signPackage);
            exit(json_encode($signPackage));
        }
        $this->display();
    }
    public function excelVolunteerInfo($shop_id = 0){
        $strTable ='<table width="500" border="1">';
        $strTable .= '<tr>';
        $strTable .= '<td style="text-align:center;font-size:12px;width:120px;">姓名</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="100">性别(汉字)</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">性别(代码)</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">注册组织</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">志愿者号</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">银行卡号</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">证件号码</td>';
        $strTable .= '</tr>';
        $strTable1 = $strTable;
        $shops = D('Shop')->select();
        foreach ($shops as $key=>$shop){
            $volunteers =  D('OrganizationVolunteer')->where(array('organization_id'=>$shop['shop_id']))->select();
            foreach ($volunteers as $vkey=>$val){
                $user = D('Users')->find($val['user_id']);
                if($user['is_certification']!=1){
                    continue;
                }
                $sexcode = 'M';
                $sex= '女';
                if($user['sex']==1){
                    $sexcode = 'F';
                    $sex= '男';
                }
                $strTable .= '<tr>';
                $strTable .= '<td style="text-align:center;font-size:12px;">'.$user['real_name'].'</td>';
                $strTable .= '<td style="text-align:left;font-size:12px;">'.$sex.'</td>';
                $strTable .= '<td style="text-align:left;font-size:12px;">'.$sexcode.'</td>';
                $strTable .= '<td style="text-align:left;font-size:12px;">'.$shop['shop_name'].'</td>';
                $strTable .= '<td style="text-align:left;font-size:12px;">'.$user['vid'].'</td>';
                $strTable .= '<td style="text-align:left;font-size:12px;"></td>';
                $strTable .= '<td style="text-align:left;font-size:12px;vnd.ms-excel.numberformat:@;">'.$user['id_num'].'</td>';
                $strTable .= '</tr>';
                //二维码照片
                getImage('volunteer.weixinguanfang.com/attachs/'.$user['qrcode_img_url'],"E:/images/",'c'.$user['id_num'].'.png',1);
                //个人证件照
                getImage('volunteer.weixinguanfang.com'.$user['head_url'],"E:/images/",'p'.$user['id_num'].'.png',1);
            }
        }
        $strTable .='</table>';
        downloadExcel($strTable,' 威海志愿者卡-个人信息文件-');
        exit();
    }

}
