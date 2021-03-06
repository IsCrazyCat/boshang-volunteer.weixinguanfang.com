<?php 
class CloudAction extends CommonAction{
    private $create_fields = array(0 => 'title', 1 => 'shop_id', 2 => 'photo', 3 => 'city_id', 4 => 'area_id', 5 => 'price', 6 => 'join', 7 => 'max', 8 => 'settlement_price', 9 => 'intro', 10 => 'type', 11 => 'thumb', 12 => 'details',13 => 'closed');
    private $edit_fields = array(0 => 'title', 1 => 'shop_id', 2 => 'photo', 3 => 'city_id', 4 => 'area_id', 5 => 'price', 6 => 'join', 7 => 'max', 8 => 'settlement_price', 9 => 'intro', 10 => 'type', 11 => 'thumb', 12 => 'details');
    public function _initialize(){
        parent::_initialize();
		if ($this->_CONFIG['operation']['cloud'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->types = D('Cloudgoods')->getType();
        $this->assign('types', $this->types);
    }
    public function index(){
        $goods = D('Cloudgoods');
        import('ORG.Util.Page');
        $map = array('closed' => 0,'shop_id'=>$this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title|intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = d('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($type = (int) $this->_param('type')) {
            $map['type'] = $type;
            $this->assign('type', $type);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $goods->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $goods->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', d('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $thumb = $this->_param('thumb', FALSE);
            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isimage($val)) {
                    unset($thumb[$k]);
                }
            }
            $data['thumb'] = serialize($thumb);
            $obj = D('Cloudgoods');
            if ($goods_id = $obj->add($data)) {
                $this->baoSuccess('添加成功', U('cloud/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', FALSE), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('产品名称不能为空');
        }
        $data['shop_id'] = $this->shop_id;
        if (!empty($data['shop_id'])) {
            $shop = D('Shop')->find($data['shop_id']);
            if (empty($shop)) {
                $this->baoError('请选择正确的组织/团体');
            }
            $data['city_id'] = $shop['city_id'];
            $data['area_id'] = $shop['area_id'];
        } else {
            $data['city_id'] = $this->_CONFIG['site']['city_id'];
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isimage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['type'] = (int) $data['type'];
        $data['price'] = (int) $data['price'];
        $data['max'] = (int) $data['max'];
        if (empty($data['price'])) {
            $this->baoError('总需人次不能为空');
        }
        if (empty($data['max'])) {
            $this->baoError('单人最大购买数不能为空');
        }
        if ($data['type'] == 2) {
            if ($data['price'] % 5 != 0) {
                $this->baoError('总需人次必须为5的倍数');
            }
            if ($data['max'] % 5 != 0) {
                $this->baoError('单人最大购买数必须为5的倍数');
            }
        }
        if ($data['type'] == 3) {
            if ($data['price'] % 10 != 0) {
                $this->baoError('总需人次必须为10的倍数');
            }
            if ($data['max'] % 10 != 0) {
                $this->baoError('单人最大购买数必须为10的倍数');
            }
        }
        $data['settlement_price'] = (int) ($data['settlement_price'] * 100);
        if ($data['price'] * 100 <= $data['settlement_price']) {
            $this->baoError('结算价格必须小于总需人次');
        }
        $data['details'] = securityeditorhtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('商品详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('商品详情含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
		$data['closed'] = 0;
        return $data;
    }
   
    public function edit($goods_id = 0){
        if ($goods_id = (int) $goods_id) {
            $obj = D('Cloudgoods');
            if (!($detail = $obj->find($goods_id))) {
                $this->error('请选择要编辑的商品');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $thumb = $this->_param('thumb', FALSE);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isimage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['thumb'] = serialize($thumb);
                $data['goods_id'] = $goods_id;
                if (FALSE !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', u('cloud/index'));
                }
                $this->baoError('操作失败');
            } else {
                $thumb = unserialize($detail['thumb']);
                $this->assign('thumb', $thumb);
                $this->assign('shop', d('Shop')->find($detail['shop_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的商品');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', FALSE), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('产品名称不能为空');
        }
        $data['shop_id'] = $this->shop_id;
        if (!empty($data['shop_id'])) {
            $shop = D('Shop')->find($data['shop_id']);
            if (empty($shop)) {
                $this->baoError('请选择正确的组织/团体');
            }
            $data['city_id'] = $shop['city_id'];
            $data['area_id'] = $shop['area_id'];
        } else {
            $data['city_id'] = $this->_CONFIG['site']['city_id'];
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isimage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['type'] = (int) $data['type'];
        $data['price'] = (int) $data['price'];
        $data['max'] = (int) $data['max'];
        if (empty($data['price'])) {
            $this->baoError('总需人次不能为空');
        }
        if (empty($data['max'])) {
            $this->baoError('单人最大购买数不能为空');
        }
        if ($data['type'] == 2) {
            if ($data['price'] % 5 != 0) {
                $this->baoError('总需人次必须为5的倍数');
            }
            if ($data['max'] % 5 != 0) {
                $this->baoError('单人最大购买数必须为5的倍数');
            }
        }
        if ($data['type'] == 3) {
            if ($data['price'] % 10 != 0) {
                $this->baoError('总需人次必须为10的倍数');
            }
            if ($data['max'] % 10 != 0) {
                $this->baoError('单人最大购买数必须为10的倍数');
            }
        }
        $data['settlement_price'] = (int) ($data['settlement_price'] * 100);
        if ($data['price'] * 100 <= $data['settlement_price']) {
            $this->baoError('结算价格必须小于总需人次');
        }
        $data['details'] = securityeditorhtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('商品详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('商品详情含有敏感词：' . $words);
        }
        return $data;
    }
    public function delete($goods_id = 0){
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('Cloudgoods');
            $obj->save(array('goods_id' => $goods_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('cloud/index'));
        } else {
            $goods_id = $this->_post('goods_id', FALSE);
            if (is_array($goods_id)) {
                $obj = D('Cloudgoods');
                foreach ($goods_id as $id) {
                    $obj->save(array('goods_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('cloud/index'));
            }
            $this->baoError('请选择要删除的商品');
        }
    }
	
	//组织/团体中心云购数据加载
	public function order(){
        $obj = D("Cloudlogs");
        import("ORG.Util.Page");
        $map = array("shop_id" =>$this->shop_id);
        if ($keyword = $this->_param( "keyword", "htmlspecialchars" ) ){
            $map['log_id'] = array("LIKE","%".$keyword."%");
            $this->assign("keyword", $keyword);
        }
        if (isset($_GET['status']) || isset($_POST['status'])) {
            $status = (int) $this->_param('status');
            if ($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        } else {
            $this->assign('status', 999);
        }
        $count = $obj->where($map)->count();
        $Page = new Page( $count, 10 );
        $show = $Page->show();
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->where($map)->order(array("log_id" => "desc" ))->limit( $Page->firstRow.",".$Page->listRows )->select();
        $goods_ids = $user_ids = array( );
        foreach ($list as $k => $val ){
            $user_ids[$val['users_id']] = $val['users_id'];
			$goods_ids[$val['goods_id']] = $val['goods_id'];
        }
        $this->assign("users", D("Users")->itemsByIds($user_ids));
		$this->assign("cloudgoods", D("Cloudgoods")->itemsByIds($goods_ids));
        $this->assign("list", $list);
        $this->assign("page", $show);
        $this->display();
    }

	//组织/团体云购删除订单
	 public function order_delete($log_id){
        if (is_numeric($log_id) && ($log_id = (int) $log_id)) {
            $obj = D("Cloudlogs");
            if (!($detail = $obj->find($log_id))) {
                $this->baoError("云购不存在");
            }
            if ($detail['status'] != 0) {
                $this->baoError("该云购状态不允许被删除");
            }
			if ($detail['shop_id'] != $this->shop_id) {
                $this->baoError("不要操作被人的订单");
            }
            $obj->delete($log_id);
            $this->baoSuccess("删除成功！", U("cloud/order"));
        }
    }
    
}