<?php

class DatabaseAction extends CommonAction {
	/**
     * 数据库备份/还原列表
     * @param  String $type import-还原，export-备份
     */
    public function index($type = 'export'){
        switch ($type) {
            /* 数据还原 */
            case 'import':
                //列出备份文件列表
                import("ORG.Util.Dir");
				$path = realpath(C('DATA_BACKUP_PATH'));
		
                $dir = new Dir($path, '*.sql.gz');
				$glob = $dir->toArray();
                $list = array();
                foreach ($glob as $file) {
					$name = $file['filename'];
					 if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)){
						$fileSize = $file['size'];
						$name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
						 
						$date = "{$name[0]}-{$name[1]}-{$name[2]}";
						$time = "{$name[3]}:{$name[4]}:{$name[5]}";
						$part = $name[6];
	
						if(isset($list["{$date} {$time}"])){
							$info = $list["{$date} {$time}"];
							$info['part'] = max($info['part'], $part);
							$info['size'] = $info['size'] + $fileSize;
						} else {
							$info['part'] = $part;
							$info['size'] = $fileSize;
						}
						
						$extension        = strtoupper($file['ext']);
						$info['compress'] = ($extension === 'SQL') ? '-' : $extension;
						$info['time']     = strtotime("{$date} {$time}");
	
						$list["{$date} {$time}"] = $info;
					}
                }
                break;
            
            /* 数据备份 */
            case 'export':
                $Db    = M();
                $list  = $Db->query('SHOW TABLE STATUS');
                $list  = array_map('array_change_key_case', $list);
                break;
            
            default:
                $this->error('参数错误！');
        }

        //渲染模板
        $this->assign('meta_title', $title);
        $this->assign('list', $list);
		//p($list);die;
		
        $this->display($type);
    }

    /**
     * 优化表
     * @param  String $tables 表名
     */
    public function optimize($tables = null){
        if($tables) {
            $Db   = M();
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list = $Db->query("OPTIMIZE TABLE `{$tables}`");
                
                if($list){
                    $this->success("数据表优化完成！");
                } else {
                    $this->error("数据表优化出错请重试！");
                }
            } else {
                $list = $Db->query("OPTIMIZE TABLE `{$tables}`");
                if($list){
                    $this->success("数据表'{$tables}'优化完成！");
                } else {
                    $this->error("数据表'{$tables}'优化出错请重试！");
                }
            }
        } else {
            $this->error("请指定要优化的表！");
        }
    }

    /**
     * 修复表
     * @param  String $tables 表名
     */
    public function repair($tables = null){
        if($tables) {
            $Db   = M();
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list = $Db->query("REPAIR TABLE `{$tables}`");
                
                if($list){
                    $this->success("数据表修复完成！");
                } else {
                    $this->error("数据表修复出错请重试！");
                }
            } else {
                $list = $Db->query("REPAIR TABLE `{$tables}`");
                if($list){
                    $this->success("数据表'{$tables}'修复完成！");
                } else {
                    $this->error("数据表'{$tables}'修复出错请重试！");
                }
            }
        } else {
            $this->error("请指定要修复的表！");
        }
    }

    /**
     * 删除备份文件
     * @param  Integer $time 备份时间
     */
    public function del($time = 0){
        if($time){
            $name  = date('Ymd-His', $time) . '-*.sql*';
            $path  = realpath(C('DATA_BACKUP_PATH')) . DIRECTORY_SEPARATOR . $name;
			array_map("unlink", glob($path));
            if(count(glob($path))){
                $this->success('备份文件删除失败，请检查权限！');
            } else {
                $this->success('备份文件删除成功！');
            }
        } else {
            $this->error('参数错误！');
        }
    }

    /**
     * 备份数据库
     * @param  String  $tables 表名
     * @param  Integer $id     表ID
     * @param  Integer $start  起始行数
     */
    public function export($tables = null, $id = null, $start = null){
		import('ORG.Net.Database');
		
        if(IS_POST && !empty($tables) && is_array($tables)){ //初始化
            //读取备份配置
            $config = array(
                'path'     => realpath(C('DATA_BACKUP_PATH')) . DIRECTORY_SEPARATOR,
                'part'     => C('DATA_BACKUP_PART_SIZE'),
                'compress' => C('DATA_BACKUP_COMPRESS'),
                'level'    => C('DATA_BACKUP_COMPRESS_LEVEL'),
            );
            
            //检查是否有正在执行的任务
            $lock = "{$config['path']}backup.lock";
			
            if(is_file($lock)){
                $this->error('检测到有一个备份任务正在执行，请稍后再试！');
            } else {
                //创建锁文件
                file_put_contents($lock, NOW_TIME);
            }

            //检查备份目录是否可写
            is_writeable($config['path']) || $this->error('备份目录不存在或不可写，请检查后重试！');
            session('backup_config', $config);
            
            //生成备份文件信息
            $file = array(
                'name' => date('Ymd-His', NOW_TIME),
                'part' => 1,
            );
            session('backup_file', $file);
            
            //缓存要备份的表
            session('backup_tables', $tables);

            //创建备份文件
			
            $Database = new Database($file, $config);
            if(false !== $Database->create()){
                $tab = array('id' => 0, 'start' => 0);
                $this->success('初始化成功！', '', array('tables' => $tables, 'tab' => $tab));
            } else {
                $this->error('初始化失败，备份文件创建失败！');
            }
        } elseif (IS_GET && is_numeric($id) && is_numeric($start)) { //备份数据
            $tables = session('backup_tables');
            //备份指定表
            $Database = new Database(session('backup_file'), session('backup_config'));
            $start  = $Database->backup($tables[$id], $start);
            if(false === $start){ //出错
                $this->error('备份出错！');
            } elseif (0 === $start) { //下一表
                if(isset($tables[++$id])){
                    $tab = array('id' => $id, 'start' => 0);
                    $this->success('备份完成！', '', array('tab' => $tab));
                } else { //备份完成，清空缓存
                    unlink(session('backup_config.path') . 'backup.lock');
                    session('backup_tables', null);
                    session('backup_file', null);
                    session('backup_config', null);
                    $this->success('备份完成！');
                }
            } else {
                $tab  = array('id' => $id, 'start' => $start[0]);
                $rate = floor(100 * ($start[0] / $start[1]));
                $this->success("正在备份...({$rate}%)", '', array('tab' => $tab));
            }

        } else { //出错
            $this->error('参数错误！');
        }
    }

    /**
     * 还原数据库
     */
    public function import($time = 0, $part = null, $start = null){
		import('ORG.Net.Database');
		
        if(is_numeric($time) && is_null($part) && is_null($start)){ //初始化
            //获取备份文件信息
            $name  = date('Ymd-His', $time) . '-*.sql*';
            $path  = realpath(C('DATA_BACKUP_PATH')) . DIRECTORY_SEPARATOR . $name;//返回规范化的绝对路径名说明realpath
            $files = glob($path);//函数返回匹配指定模式的文件名或目录
            $list  = array();
            foreach($files as $name){
                $basename = basename($name);//示带有文件扩展名的文件basename名
                $match    = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');//从一个字符串中读进与指定格式相符的数据sscanf
                $gz       = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
                $list[$match[6]] = array($match[6], $name, $gz);
            }
            ksort($list);//键名对关联数组进行升序排序ksort
			

            //检测文件正确性
            $last = end($list);
            if(count($list) === $last[0]){
                session('backup_list', $list); //缓存备份列表
                $this->success('初始化完成！', '', array('part' => 1, 'start' => 0));
            } else {

                $this->error('备份文件可能已经损坏，请检查！');
            }
			
        } elseif(is_numeric($part) && is_numeric($start)) {
            $list  = session('backup_list');
            $db = new Database($list[$part], array(
                'path'     => realpath(C('DATA_BACKUP_PATH')) . DIRECTORY_SEPARATOR,
                'compress' => $list[$part][2]));

            $start = $db->import($start);

            if(false === $start){
                $this->error('还原数据出错！');
            } elseif(0 === $start) { //下一卷
                if(isset($list[++$part])){
                    $data = array('part' => $part, 'start' => 0);
                    $this->success("正在还原...#{$part}", '', $data);
                } else {
                    session('backup_list', null);
                    $this->success('还原完成！');
                }
            } else {
                $data = array('part' => $part, 'start' => $start[0]);
                if($start[1]){
                    $rate = floor(100 * ($start[0] / $start[1]));
                    $this->success("正在还原...#{$part} ({$rate}%)", '', $data);
                } else {
                    $data['gz'] = 1;
                    $this->success("正在还原...#{$part}", '', $data);
                }
            }

        } else {
            $this->error('参数错误！');
        }
    }
}