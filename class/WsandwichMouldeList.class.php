<?php
/**
 * Wsandwich 모듈 리스트
 * @link http://wsandwich.com/
 * @copyright Copyright 2013 i4unetworks. All rights reserved.
 */
class WsandwichMouldeList {
	
	static private $instance;
	static $list;
	static $activeList;
	
	private function __construct(){
		// 활성화된 모듈 리스트를 가져온다.
		self::$activeList = get_option('wsandwich_active_modules', array());
		$active = array_keys(self::$activeList);
		
		// 활성화된 모듈 검증
		foreach($active AS $key => $module_name){
			$module_class = ucfirst($module_name);
			if(!file_exists(WSANDWICH_MODULES_DIR . "/$module_name/$module_class.class.php")){
				unset(self::$activeList[$module_name]);
				update_option('wsandwich_active_modules', self::$activeList);
			}
		}
		
		// modules 디렉토리의 목록을 가져온다.
		if ($dh = @opendir(WSANDWICH_MODULES_DIR)){
			while(($file = readdir($dh)) !== false){
				if($file == "." || $file == "..") continue;
				
				$module_class = ucfirst($file);
				if(file_exists(WSANDWICH_MODULES_DIR . "/$file/$module_class.class.php")) self::$list[] = $file;
			}
		}
		closedir($dh);
		
		// 값이 없으면 빈 배열 입력해 오류 방지
		if(!self::$list) self::$list = array();
		if(!self::$activeList) self::$activeList = array();
	}
	
	/**
	 * 인스턴스를 반환한다.
	 * @return WsandwichMouldeList
	 */
	static public function getInstance(){
		if(!self::$instance) self::$instance = new WsandwichMouldeList();
		return self::$instance;
	}
	
	/**
	 * 모든 모듈 리스트를 반환한다.
	 * @return array
	 */
	public function getList(){
		return self::$list;
	}
	
	/**
	 * 활성화 모듈 리스트를 반환한다.
	 * @return array
	 */
	public function getActiveList(){
		return self::$activeList;
	}
}
?>