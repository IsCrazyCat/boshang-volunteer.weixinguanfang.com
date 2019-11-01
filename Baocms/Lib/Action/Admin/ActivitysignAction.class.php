<?php
class ActivitysignAction extends CommonAction
{
    public function index()
    {
        $Activitysign = D('Activitysign');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
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
            //某活动的服务时长
            $activityLog = D('activityLogs')->where(array('user_id'=>$val['user_id'],'activity_id'=>$val['activity_id']))->find();
            $total_time = 0;

            if(!empty($activityLog['start_date'])){

                if(!empty($activityLog['end_date'])){
                    $total_time += ($activityLog['end_date']-$activityLog['start_date']);
                }else{
                    $total_time += (time()-$activityLog['start_date']);
                }

                if($activityLog['add_service_time']){
                    $list[$key]['total_time']=ceil($total_time/3600)+$activityLog['add_service_time'].'小时';
                }else{
                    $list[$key]['total_time']=ceil($total_time/3600).'小时';
                }
            }else{
                if($activityLog['add_service_time']){
                    $list[$key]['total_time']=$activityLog['add_service_time'] .'小时';
                }else{
                    $list[$key]['total_time']='尚未参加活动';
                }
            }

            $activity_ids[$val['activity_id']] = $val['activity_id'];

            $manager = D('ActivityManager')->where(array('user_id'=>$val['user_id'],'activity_id'=>$activity_id))->find();
            if($manager){
                $list[$key]['is_manager'] = 0; //0不是该活动的管理员
            }else{
                $list[$key]['is_manager'] = 1;//是该活动的管理员
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
}