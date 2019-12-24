<?php
class ActivityAction extends CommonAction{
    private $create_fields = array('cate_id', 'user_ids','shop_id', 'tuan_id', 'city_id', 'area_id', 'business_id', 'title', 'intro', 'photo', 'thumb', 'details', 'price', 'bg_date', 'end_date', 'time', 'sign_end', 'addr', 'orderby', 'sign_num');
    private $edit_fields = array('cate_id', 'shop_id', 'tuan_id', 'city_id', 'area_id', 'business_id', 'title', 'intro', 'photo', 'thumb', 'details', 'price', 'bg_date', 'end_date', 'time', 'sign_end', 'addr', 'orderby', 'sign_num');
    public function index()
    {
        $Activity = D('Activity');
        import('ORG.Util.Page');
//        $map = array('closed' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('title',$map['title']);
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
    public function tuan(){
        if (IS_AJAX) {
            $shop_id = $_POST['shop_id'];
            $list = D('Tuan')->where(array('shop_id' => $shop_id))->order('tuan_id asc')->select();
            $this->ajaxReturn(array('list' => $list));
        }
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Activity');
            if ($activity_id = $obj->add($data)) {
                if(empty($activity_id)){
                    $this->baoError('管理员添加失败！');
                }
                D('ActivityManager')->where(array('activity_id'=>$activity_id))->delete();
                if(!empty($data['user_ids'])){
                    $ids = explode(",",$data['user_ids']);
                    if(is_array($ids)){
                        foreach ($ids as $key=>$val){
                            D('ActivityManager')->add(array('user_id'=>$val,'activity_id'=>$activity_id,'create_time'=>time()));
                        }
                    }else{
                        D('ActivityManager')->add(array('user_id'=>$ids,'activity_id'=>$activity_id,'create_time'=>time()));
                    }
                }
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
        $data['tuan_id'] = (int) $data['tuan_id'];
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
        $data['audit']=1;
        $data['user_ids']=htmlspecialchars($data['user_ids']);
        $data['orderby'] = (int) $data['orderby'];
        $data['create_time'] = NOW_TIME;
        $data['sign_num'] = 0;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($activity_id = 0){
        if ($activity_id = (int) $activity_id) {
            $obj = D('Activity');
            if (!($detail = $obj->find($activity_id))) {
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
                $tuan = D('Tuan')->where(array('shop_id' => $detail['shop_id']))->select();
                $this->assign('tuan', $tuan);
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
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('类型ID不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['shop_id'] = (int) $data['shop_id'];
        $data['tuan_id'] = (int) $data['tuan_id'];
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
        return $data;
    }
    public function delete($activity_id = 0){
        if (is_numeric($activity_id) && ($activity_id = (int) $activity_id)) {
            $obj = D('Activity');
            $obj->delete($activity_id);
            $this->baoSuccess('删除成功！', U('activity/index'));
        } else {
            $activity_id = $this->_post('activity_id', false);
            if (is_array($activity_id)) {
                $obj = D('Activity');
                foreach ($activity_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('activity/index'));
            }
            $this->baoError('请选择要删除的活动');
        }
    }
    public function audit($activity_id = 0){
        if (is_numeric($activity_id) && ($activity_id = (int) $activity_id)) {
            $obj = D('Activity');
            $obj->save(array('activity_id' => $activity_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('activity/index'));
        } else {
            $activity_id = $this->_post('activity_id', false);
            if (is_array($activity_id)) {
                $obj = D('Activity');
                foreach ($activity_id as $id) {
                    $obj->save(array('activity_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('activity/index'));
            }
            $this->baoError('请选择要审核的活动');
        }
    }

    /**
     * 添加活动管理者
     */
    public function addManager(){
        $ids = $this->_param("user_ids");
        $activity_id = $this->_param("activity_id");
        if(empty($activity_id)){
            $this->baoError('管理员添加失败！');
        }
        D('ActivityManager')->where(array('activity_id'=>$activity_id))->delete();
        if(is_array($ids)){
            foreach ($ids as $key=>$val){
                D('ActivityManager')->add(array('user_id'=>$val,'activity_id'=>$activity_id,'create_time'=>time()));
            }
        }else{
            D('ActivityManager')->add(array('user_id'=>$ids,'activity_id'=>$activity_id,'create_time'=>time()));
        }
        $this->baoSuccess('设置成功！');
    }
    public function addServiceTime(){
        $user_id = $this->_param('user_id');
        $activity_id = $this->_param('activity_id');
        if(!$user = D('users')->find($user_id)){
            $this->error("用户ID不能为空");
        }
        if(!$activity = D('activity')->find($activity_id)){
            $this->error("活动ID不能为空");
        }

        //获取可添加时长 不能超过活动总时长
        //获取活动总时长
        $activity_time = strtotime($activity['end_date'])-strtotime($activity['bg_date']);
        $activity_time = ceil($activity_time/3600);

        //获取活动计时时间
        $service_info = service_info_user($user_id,$activity_id);
        //该活动该用户已服务时长 = 用户参加活动的时长 + 用户被添加的时长
        $service_time = $service_info['activity_total_service_time'];
        //可添加时长 = 活动总时长 - 该活动该用户已服务时长
//        $add_time = $activity_time - $service_time;//可添加时长
        $this->assign('user',$user);
        $this->assign('activity',$activity);
        $this->assign('activity_time',$activity_time);
        $this->assign('service_time',$service_time);
        $this->assign('add_time',$activity_time );
        if ($this->isPost()) {
            $data['user_id'] = $user_id;
            $data['activity_id'] = $activity_id;

            $add_service_time = $this->_param("add_service_time");
            $add_service_msg = $this->_param("add_service_msg");
            $data['add_service_time'] = $add_service_time;
            $data['add_service_msg'] = $add_service_msg;
            $data['type'] = 2;
            $data['status'] = 2;
            $data['today_date'] = date("Y-m-d");
            $data['update_time']=time();
            $data['add_time']=time();
            if(D('ActivityLogs')->add($data)){
                exit('success');
            }else{
                exit('error');
            }
        }
        $this->display();
    }

    /**
     * 活动关闭和开启
     */
    public function closed(){
        $activity_id = $this->_param('activity_id');
        $closed=$this->_param('closed');
        if(D('Activity')->save(array('activity_id'=>$activity_id,'closed'=>$closed))){
            $this->success('更新成功！',U('activity/index'));
        }else{
            $this->error('更新失败！',U('activity/index'));
        }
    }
}