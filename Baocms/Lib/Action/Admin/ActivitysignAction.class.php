<?php
class ActivitysignAction extends CommonAction
{
    public function index()
    {
        $Activitysign = D('Activitysign');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
//        $map['is_del'] = 0;
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $activity_id = (int) $this->_param('activity_id');
        if ($activity_id) {
            $map['activity_id'] = $activity_id;
        }
        $count = $Activitysign->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Activitysign->where($map)->order(array('sign_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $activity_ids = array();
        foreach ($list as $key=>$val) {
            $service_info = service_info_user($val['user_id'],$val['activity_id']);
            $service_time = $service_info['activity_total_service_time'];
            $activityLog = D('activityLogs')->where(array('user_id'=>$val['user_id'],'activity_id'=>$val['activity_id']))->find();
            if(empty($activityLog)){
                $list[$key]['service_time'] = "尚未参加活动";
            }else{
                $list[$key]['service_time'] = $service_time;
            }

            $activity_ids[$val['activity_id']] = $val['activity_id'];

            $manager = D('ActivityManager')->where(array('user_id'=>$val['user_id'],'activity_id'=>$activity_id))->find();
            if(empty($manager)){
                $list[$key]['is_manager'] = 0; //0不是该活动的管理员
            }else{
                $list[$key]['is_manager'] = 1;//是该活动的管理员
            }
            $user = D('Users')->find($val['user_id']);
            if(!empty($user['real_name'])){
                $list[$key]['real_name'] = $user['real_name'];
            }
        }

        $this->assign('activity', D('Activity')->itemsByIds($activity_ids));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function delete($sign_id = 0)
    {
        if (is_numeric($sign_id) && ($sign_id = (int) $sign_id)) {
            $obj = D('Activitysign');
            $obj->delete($sign_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('activitysign/index'));
        } else {
            $sign_id = $this->_post('sign_id', false);
            if (is_array($sign_id)) {
                $obj = D('Activitysign');
                foreach ($sign_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('activitysign/index'));
            }
            $this->baoError('请选择要删除的报名列表');
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
        if (is_numeric($sign_id) && ($sign_id = (int) $sign_id)) {

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
            $this->baoSuccess($del_str . '成功！', U('activitysign/index',array('activity_id'=>$activity_id)));
        } else {
            $sign_id = $this->_post('sign_id', false);
            if (is_array($sign_id)) {
                foreach ($sign_id as $id) {
                    $obj->save(array('is_del'=>$is_del,'sign_id'=>$id));
                }
                $this->baoSuccess('批量' . $del_str . '成功！', U('activitysign/index',array('activity_id'=>$activity_id)));
            }
            $this->baoError('请选择要' .$del_str .'的报名列表');
        }
    }
}