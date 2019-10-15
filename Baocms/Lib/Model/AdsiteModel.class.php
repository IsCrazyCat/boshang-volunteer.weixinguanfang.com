<?php
class AdsiteModel extends CommonModel {
    protected $pk = 'site_id';
    protected $tableName = 'ad_site';
    protected $token = 'ad_site';
    public function getType() {
        return array(1 => '文字广告', 2 => '图片广告', 3 => '代码广告');
    }

    public function getPlace() {
        return array(
            14 => '手机首页',
        );

    }



}

