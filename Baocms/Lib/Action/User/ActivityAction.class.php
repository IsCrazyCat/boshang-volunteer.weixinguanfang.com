<?php
class ActivityAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        if ($this->_CONFIG['operation']['huodong'] == 0) {
            $this->error('此功能暂未开通');
            die;
        }
    }

    public function index(){
        $result = lengthOfTime($this->uid,1);

        $this->assign('total_time',$result['total_time']);
        $this->assign('year_time',$result['year_time']);
        $this->assign('count',$result['count']);//参加的活动个数
        $this->display();
    }
    public function load(){
        $Activity = D('Activity');
        $Activitysign = D('Activitysign');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Activitysign->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Activitysign->where($map)->order(array('sign_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $activitys_ids = array();
        foreach ($list as $k => $val) {
            $activitys_ids[$val['activity_id']] = $val['activity_id'];
        }
        $this->assign('activity', $Activity->itemsByIds($activitys_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function myActivity(){
        $activitySign = D('activitySign');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $activitySign->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //获取所有的报名信息
        $list = $activitySign->where($map)->order(array('sign_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            //获取报名的活动信息
            $activity = D('activity')->where(array('activity_id'=>$val['activity_id']))->find();
            $list[$k]['activity'] = $activity;
            //报名活动的组织信息
            $shop = D('shop')->where(array('shop_id'=>$activity['shop_id']))->find();
            $list[$k]['shop'] = $shop;
            //某活动的服务时长
            $activityLogs = D('activityLogs')->where(array('user_id'=>$this->uid,'activity_id'=>$val['activity_id']))->select();
            $total_time = 0;
            foreach ($activityLogs as $logKey=>$logval){
                if(!empty($logval['start_date'])){
                    if(!empty($logval['end_date'])){
                        $total_time += ($logval['end_date']-$logval['start_date']);
                    }else{
                        $total_time += (time()-$logval['start_date']);
                    }
                }
            }
            $list[$k]['total_time']=ceil($total_time/3600);
            //该项活动的服务状态 先判断活动是否还在进行中 0未开始 1未参加 2服务中 3服务结束
            $cur_date = date("Y-m-d");
            $start_date = $activity['bg_date'];
            $end_date = $activity['end_date'];

            //判断活动是否开始/结束
            if (strtotime($cur_date) < strtotime($start_date)) {
                //该活动尚未开始！
                $list[$k]['status'] = 0;
            } else if (strtotime($cur_date) > strtotime($end_date)) {
                //活动已经结束 查看是否参加过活动
                if($activityLogs){
                    $list[$k]['status'] = 1;
                }else{
                    $list[$k]['status'] = 3;
                }

            }else{
                //活动正在进行中 是否参加了
                if(!$activityLogs){
                    $list[$k]['status'] = 0;
                }else{
                    //报名了并且参加了 查看今日是否参加了
                    $list[$k]['status'] = 3;
                    foreach ($activityLogs as $logKey=>$logval){
                        if(substr($logval['today_date'],0,4)==date('Y')){
                            if(!empty($logval['start_date'])){
                                if(!empty($logval['end_date'])){
                                    $list[$k]['status'] = 3;
                                }else{
                                    $list[$k]['status'] = 2;
                                }
                            }
                        }
                    }
                }
            }

        }
        $this->assign('user_id', $this->uid);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

}