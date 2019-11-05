<?php
class ActivityAction extends CommonAction {
	 public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['huodong'] == 0) {
            $this->error('此功能暂未开通');
            die;
        }
    }

    private $create_fields = array('cate_id', 'shop_id', 'tuan_id', 'city_id', 'area_id', 'business_id', 'title', 'intro', 'photo', 'thumb', 'details', 'price', 'bg_date', 'end_date', 'time', 'sign_end', 'addr', 'orderby', 'sign_num');
    private $edit_fields = array('cate_id', 'shop_id','tuan_id','city_id', 'area_id','business_id', 'title', 'intro', 'photo', 'thumb', 'details', 'price', 'bg_date', 'end_date', 'time', 'sign_end', 'addr', 'orderby', 'sign_num');

    public function index() {
        $Activity = D('Activity');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'shop_id'=>$this->shop_id);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('title', $title);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Activity->where($map)->count(); 
        $Page = new Page($count, 15);
        $show = $Page->show(); 
        $list = $Activity->where($map)->order(array('activity_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $key => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('cates', D('Activitycate')->fetchAll());
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	
    public function tuan() {
        if (IS_AJAX) {
            $shop_id = $_POST['shop_id'];
            $list = D('Tuan')->where(array('shop_id' => $shop_id))->order('tuan_id asc')->select();
            $this->ajaxReturn(array('list' => $list));
        }
    }

    public function create(){
        $this->assign("shop_id",$this->shop_id);
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Activity');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('activity/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Activitycate')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('类型ID不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        $shop = D('Shop')->find($data['shop_id']);
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('活动标题不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('活动简介不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->baoError('请上传正确的图片');
        }
        $thumb = $this->_param('thumb', false);
        foreach ($thumb as $k => $val) {
            if (empty($val)) {
                unset($thumb[$k]);
            }
            if (!isImage($val)) {
                unset($thumb[$k]);
            }
        }
        $data['thumb'] = serialize($thumb);
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('活动内容不能为空');
        }
        $data['price'] = htmlspecialchars($data['price']);
        if (empty($data['price'])) {
//            $this->baoError('价格不能为空');
        }
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->baoError('活动开始时间不能为空');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->baoError('活动结束时间不能为空');
        }
        $data['sign_end'] = htmlspecialchars($data['sign_end']);
        if (empty($data['sign_end'])) {
            $this->baoError('报名截止时间不能为空');
        }
//        $data['time'] = htmlspecialchars($data['time']);
//        if (empty($data['time'])) {
//            $this->baoError('活动具体时间不能为空');
//        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('活动地址不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('活动内容含有敏感词：' . $words);
        }
        if ($words = D('Sensitive')->checkWords($data['title'])) {
            $this->baoError('活动标题含有敏感词：' . $words);
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->baoError('活动简介含有敏感词：' . $words);
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['create_time'] = NOW_TIME;
        $data['sign_num'] = 0;
        $data['create_ip'] = get_client_ip();
        return $data;
    }



    public function edit($activity_id = 0) {
        if ($activity_id = (int) $activity_id) {
            $obj = D('Activity');
            if (!$detail = $obj->find($activity_id)) {
                $this->baoError('请选择要编辑的活动');
            }
            $managers = D('ActivityManager')->where(array('activity_id'=>$activity_id))->select();
            foreach ($managers as $key => $val){
                $manager_user = D('users')->where(array('user_id'=>$val['user_id']))->find();
                $managers[$key]['user'] = $manager_user;
            }
            $this->assign("managers",$managers);
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['activity_id'] = $activity_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('activity/index'));
                }
                $this->baoError('操作失败');
            } else {
                $thumb = unserialize($detail['thumb']);
                $tuan = D('Tuan')->where(array('shop_id'=>$detail['shop_id']))->select();
                $this->assign('tuan',$tuan);
                $this->assign('thumb', $thumb);
                $this->assign('users', D('Users')->find($detail['user_id']));
                $this->assign('cates', D('Activitycate')->fetchAll());
			    $this->assign('shops', D('Shop')->find($detail['shop_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的活动');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('类型ID不能为空');
        }
        $data['shop_id'] = $this->shop_id;
        $data['tuan_id'] = (int) $data['tuan_id'];
        $shop = D('Shop')->find($this->shop_id);
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('活动标题不能为空');
        } $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('活动简介不能为空');
        } $data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->baoError('请上传正确的图片');
        }
        $thumb = $this->_param('thumb', false);
        foreach ($thumb as $k => $val) {
            if (empty($val)) {
                unset($thumb[$k]);
            }
            if (!isImage($val)) {
                unset($thumb[$k]);
            }
        }
        $data['thumb'] = serialize($thumb);
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('活动内容不能为空');
        }
        $data['price'] = htmlspecialchars($data['price']);
        if (empty($data['price'])) {
//            $this->baoError('价格不能为空');
        } $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->baoError('活动开始时间不能为空');
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->baoError('活动结束时间不能为空');
        }$data['sign_end'] = htmlspecialchars($data['sign_end']);
        if (empty($data['sign_end'])) {
            $this->baoError('报名截止时间不能为空');
        }
        $data['time'] = htmlspecialchars($data['time']);
//        if (empty($data['time'])) {
//            $this->baoError('活动具体时间不能为空');
//        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('活动地址不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('活动内容含有敏感词：' . $words);
        }
        if ($words = D('Sensitive')->checkWords($data['title'])) {
            $this->baoError('活动标题含有敏感词：' . $words);
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->baoError('活动简介含有敏感词：' . $words);
        }
		 $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function delete($activity_id = 0) {
		$activity_id = (int) $activity_id;
        if (!empty($activity_id)) {
            $obj = D('Activity');
			if (!$detail = $obj->find($activity_id)) {
                $this->baoError('删除的活动不存在');
            }
			if ($detail['shop_id'] != $this->shop_id) {
                $this->baoError('请不要非法操作');
            }
            $obj->delete($activity_id);
            $this->baoSuccess('删除成功！', U('activity/index'));
        } else {
            $this->baoError('请选择要删除的活动');
        }
    }

	public  function sign(){
	   $activity_id = (int)$this->_param('activity_id');
	    if (empty($activity_id)) {
            $this->error('请求错误，请从正在活动》》》查看报名》》》点击进来就可以查看啦!~~');
       }
       $Activitysign = D('Activitysign');
       import('ORG.Util.Page');
       $map = array();
       $keyword = $this->_param('keyword','htmlspecialchars');
       if($keyword){
           $map['name|mobile'] = array('LIKE', '%'.$keyword.'%');
           $this->assign('keyword',$keyword);
       }
       if($activity_id){
           $map[C('DB_PREFIX').'activity_sign.activity_id'] = $activity_id;
       }
	   $join = ' inner join '.C('DB_PREFIX').'activity a on a.activity_id = '.C('DB_PREFIX').'activity_sign.activity_id';
       $count = $Activitysign->join($join)->where($map)->count();
       $Page = new Page($count,25);
       $show = $Page->show();
       $list = $Activitysign->join($join)->where($map)->order(array('sign_id'=>'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
       $activity_ids = array();
       foreach($list as  $key=>$val){
           $activity_ids[$val['activity_id']] = $val['activity_id'];
           $manager = D('ActivityManager')->where(array('user_id'=>$val['user_id'],'activity_id'=>$activity_id))->find();
           if(empty($manager)){
               $list[$key]['is_manager'] = 0; //0不是该活动的管理员
           }else{
               $list[$key]['is_manager'] = 1;//是该活动的管理员
           }
       }
       $this->assign('activity',D('Activity')->itemsByIds($activity_ids));
	   $this->assign('activity_id',$activity_id);
       $this->assign('list',$list);
       $this->assign('page',$show);
       $this->display(); 
    }
    public function select(){
        $User = D('Users');
        import('ORG.Util.Page');
        $map = array('closed' => array('IN', '0,-1'));
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        if ($nickname = $this->_param('nickname', 'htmlspecialchars')) {
            $map['nickname'] = array('LIKE', '%' . $nickname . '%');
            $this->assign('nickname', $nickname);
        }
        if ($ext0 = $this->_param('ext0', 'htmlspecialchars')) {
            $map['ext0'] = array('LIKE', '%' . $ext0 . '%');
            $this->assign('ext0', $ext0);
        }
        $count = $User->where($map)->count();
        $Page = new Page($count, 8);
        $pager = $Page->show();
        $list = $User->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        $this->display();
    }
    /**
     * 添加活动管理者
     */
    public function addManager(){
        $ids = $this->_param("user_ids");
        $activity_id = $this->_param("activity_id");
        if($flag=$this->_param('flag')){
        }else{
            D('ActivityManager')->where(array('activity_id'=>$activity_id))->delete();
        }
        if(is_array($ids)){
            foreach ($ids as $key=>$val){
                D('ActivityManager')->add(array('user_id'=>$val,'activity_id'=>$activity_id,'create_time'=>time()));
            }
        }else{
            D('ActivityManager')->add(array('user_id'=>$ids,'activity_id'=>$activity_id,'create_time'=>time()));
        }
        $this->success('操作成功', U('activity/sign',array('activity_id'=>$activity_id)));
    }
    public function delete_sign_user($sign_id = 0)
    {
        $activity_id = $this->_param('activity_id');
        if (is_numeric($sign_id) && ($sign_id = (int) $sign_id)) {
            $obj = D('Activitysign');
            $obj->delete($sign_id);
            $obj->cleanCache();
            $this->success('删除成功！', U('activity/sign',array('activity_id'=>$activity_id)));
        } else {
            $sign_id = $this->_post('sign_id', false);
            if (is_array($sign_id)) {
                $obj = D('Activitysign');
                foreach ($sign_id as $id) {
                    $obj->delete($id);
                }
                $this->success('删除成功！', U('activity/sign',array('activity_id'=>$activity_id)));
            }
            $this->error('请选择要删除的报名列表');
        }
    }

    /**
     * 停用/恢复用户报名
     * is_del字段
     */
    public function user_sign_unable($sign_id = 0){
        $sign_id = $this->_param('sign_id');
        $is_del = $this->_param('is_del'); // 0正常 1停用
        $activity_id = $this->_param('activity_id');
        $del_str = $is_del == 1 ? '停用' : '恢复';
        $obj = D('Activitysign');
        if($obj->save(array('is_del'=>$is_del,'sign_id'=>$sign_id))){
            //成功之后检查是否已经开始计时了 ，如果开始了则停止计时
            $sign_user = D('ActivitySign')->find($sign_id);
            $activity_log = D('ActivityLogs')
                ->where(array('user_id'=>$sign_user['user_id'],'activity_id'=>$activity_id,'type'=>0))
                ->find();
            if(!empty($activity_log) && empty($activity_log['end_date'])){
                D('ActivityLogs')->save(array('end_date'=>time(),'update_time'=>time(),'activity_log_id'=>$activity_log['activity_log_id']));
            }

        }
        $this->success($del_str . '成功！', U('activity/sign',array('activity_id'=>$activity_id)));
    }
}
