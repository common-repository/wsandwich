<?php
/**
 * Wsandwich
 * @link http://wsandwich.com/
 * @copyright Copyright 2013 i4unetworks. All rights reserved.
 */
class Wsandwich {
	
	static $instance;
	
	/**
	 * Singleton
	 * @static
	 */
	public static function init(){
		global $wpdb;
		$wpdb->show_errors();
		
		if(!self::$instance){
			$meta = WsandwichMeta::getInstance();
			$meta->setModule('wsandwich');
			define('WSANDWICH_ACCESS_TOKEN', $meta->access_token);
			
			self::$instance = new Wsandwich();
			self::$instance->upgrade();
			self::$instance->admin();
			self::$instance->moduleLauncher();
			self::$instance->ajax();
		}
		return self::$instance;
	}
	
	/**
	 * Wsandwich 시스템 업그레이드
	 */
	public function upgrade(){
		
	}
	
	/**
	 * 관리자 기능을 실행한다.
	 */
	public function admin(){
		add_action('admin_menu', array($this, 'adminMenu'));
		add_action('admin_head', array($this, 'adminHead'));
	}
	
	/**
	 * 워드프레스 관리자 메뉴를 등록한다.
	 */
	public function adminMenu(){
		$position = 98.76;
		while(isset($GLOBALS['menu'][$position])) $position++;
		
		$menu = new WsandwichAdmin();
		add_menu_page(WSANDWICH_PAGE_TITLE, WSANDWICH_PAGE_TITLE, 'administrator', 'wsandwich_dashboard', array($menu, 'dashboard'), '', $position);
		add_submenu_page('wsandwich_dashboard', WSANDWICH_PAGE_TITLE, '알림판', 'administrator', 'wsandwich_dashboard');
		add_submenu_page('wsandwich_dashboard', WSANDWICH_PAGE_TITLE, '설정', 'administrator', 'wsandwich_options', array($menu, 'options'));
	}
	
	/**
	 * 워드프레스 관리자 헤더 내용을 작성한다.
	 */
	public function adminHead(){
		echo '<script src="'.WSANDWICH_URL_PATH.'/admin/wsnadwich.js"></script>' . "\n";
		echo '<link rel="stylesheet" href="'.WSANDWICH_URL_PATH.'/admin/wsandwich-font.css">' . "\n";
		echo '<link rel="stylesheet" href="'.WSANDWICH_URL_PATH.'/admin/wsandwich.css">' . "\n";
	}
	
	/**
	 * 활성화된 모듈을 실행한다.
	 */
	public function moduleLauncher(){
		// 모듈 리스트를 가져온다.
		$modules = WsandwichMouldeList::getInstance();
		$active_list = $modules->getActiveList();
		
		foreach($active_list AS $module_name => $value){
			$module_class = ucfirst($module_name);
			
			// 모듈 클래스가 없으면 불러온다.
			if(!class_exists($module_class) && file_exists(WSANDWICH_MODULES_DIR . "/$module_name/$module_class.class.php")){
				include_once WSANDWICH_MODULES_DIR . "/$module_name/$module_class.class.php";
			}
			
			if(class_exists($module_class)){
				// init, menu 메소드 호출
				$module = call_user_func(array($module_class, 'init'));
				add_action('admin_menu', array($module, 'menu'));
			}
		}
	}
	
	/**
	 * 활성화된 모듈의 위젯을 추가한다.
	 */
	public function widgets(){
		// 모듈 리스트를 가져온다.
		$modules = WsandwichMouldeList::getInstance();
		$active_list = $modules->getActiveList();
		
		foreach($active_list AS $module_name => $value){
			$module_class = ucfirst($module_name);
			
			// 모듈 클래스가 없으면 불러온다.
			if(!class_exists($module_class) && file_exists(WSANDWICH_MODULES_DIR . "/$module_name/$module_class.class.php")){
				include_once WSANDWICH_MODULES_DIR . "/$module_name/$module_class.class.php";
			}
			
			if(class_exists($module_class) && method_exists($module_class, 'widgets')){
				call_user_func(array($module_class, 'widgets'));
			}
		}
	}
	
	/**
	 * AJAX 등록
	 */
	public function ajax(){
		add_action('wp_ajax_wsandwich_module_activate', array('Wsandwich', 'ajaxModuleActivation'));
		add_action('wp_ajax_wsandwich_module_deactivate', array('Wsandwich', 'ajaxModuleDeactivation'));
		add_action('wp_ajax_wsandwich_module_upgrade', array('Wsandwich', 'ajaxModuleUpgrade'));
		add_action('wp_ajax_wsandwich_options_exeucte', array('Wsandwich', 'ajaxOptionsExeucte'));
	}
	
	/**
	 * 모듈을 활성화 한다.
	 */
	public static function ajaxModuleActivation(){
		$module = ws_htmlclear($_POST['module']);
		if($module){
			$modules = get_option('wsandwich_active_modules');
			$modules[$module] = ws_date('YmdHis');
			update_option('wsandwich_active_modules', $modules);
			
			// 모듈 클래스가 없으면 불러온다.
			$module_class = ucfirst($module);
			if(!function_exists($module_class)){
				include_once WSANDWICH_MODULES_DIR . "/$module/$module_class.class.php";
			}
			
			// 모듈 활성화 액션
			call_user_func(array($module_class, 'activation'));
		}
		
		die("$module 모듈이 활성화 되었습니다.");
	}
	
	/**
	 * 모듈을 비활성화 한다.
	 */
	public static function ajaxModuleDeactivation(){
		$module = ws_htmlclear($_POST['module']);
		if($module){
			$modules = get_option('wsandwich_active_modules');
			unset($modules[$module]);
			update_option('wsandwich_active_modules', $modules);
			
			// 모듈 클래스가 없으면 불러온다.
			$module_class = ucfirst($module);
			if(!function_exists($module_class)){
				include_once WSANDWICH_MODULES_DIR . "/$module/$module_class.class.php";
			}
				
			// 모듈 비활성화 액션
			call_user_func(array($module_class, 'deactivation'));
		}
		
		die("$module 모듈이 비활성화 되었습니다.");
	}
	
	/**
	 * 모듈을 비활성화 한다.
	 */
	public static function ajaxModuleUpgrade(){
		$meta = WsandwichMeta::getInstance();
		$meta->setModule('wsandwich');
		
		$is_writable = is_writable(WP_CONTENT_DIR . '/plugins/wsandwich/modules');
		$module = ws_post('module', true)?ws_post('module', true):ws_get('module');
		$version = ws_post('version', true)?ws_post('version', true):ws_get('version');
		$form_url = wp_nonce_url(admin_url("/admin-ajax.php?action=wsandwich_module_upgrade&module={$module}&version={$version}"), 'ajaxModuleUpgrade');
		
		if($module && $version){
			include WSANDWICH_PLUGIN_DIR . "/class/WsandwichUpgrader.class.php";
			$upgrader = WsandwichUpgrader::getInstance();
			if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . '/plugins/wsandwich/modules')) exit;
			$upgrader->download($module, $version, WSANDWICH_ACCESS_TOKEN);
			$error = $upgrader->install();
		}
		
		if($error){
			$result = array('error'=>1, 'message'=>$error);
		}
		else{
			// 모듈 클래스가 없으면 불러온다.
			$module_class = ucfirst($module);
			if(!function_exists($module_class)){
				include_once WSANDWICH_MODULES_DIR . "/$module/$module_class.class.php";
			}
			
			// 모듈 업그레이드 액션
			call_user_func(array($module_class, 'upgrade'));
			
			$result = array('error'=>0, 'module'=>$module, 'version'=>$version);
		}
		
		if($is_writable){
			echo json_encode($result);
		}
		else{
			echo '<script>';
			if($result['message']) echo 'alert("'.$result['message'].'");';
			echo 'location.href="'.admin_url('/admin.php?page=wsandwich_dashboard').'";';
			echo '</script>';
		}
		exit;
	}
	
	/**
	 * 설정값을 저장한다.
	 */
	public static function ajaxOptionsExeucte(){
		$meta = WsandwichMeta::getInstance();
		$meta->setModule('wsandwich');
		$meta->access_token = ws_post('access_token', true);
		die(ws_json_encode(array('message'=>'저장 되었습니다.')));
	}
	
	/**
	 * W샌드위치 플러그인 활성화
	 */
	public static function pluginActivation($networkwide){
		global $wpdb;
		if(function_exists('is_multisite') && is_multisite()){
			if($networkwide){
				$old_blog = $wpdb->blogid;
				$blogids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs");
				foreach($blogids as $blog_id){
					switch_to_blog($blog_id);
					self::_pluginActivation();
				}
				switch_to_blog($old_blog);
				return;
			}
		}
		self::_pluginActivation();
	}
	
	/**
	 * W샌드위치 플러그인 활성화 실행
	 */
	public static function _pluginActivation(){
		global $wpdb;
		$wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."wsandwich_meta` (
			`module` varchar(127) NOT NULL,
			`name` varchar(127) NOT NULL,
			`value` text NOT NULL,
			UNIQUE KEY `module` (`module`,`name`)
		) DEFAULT CHARSET=utf8");
	}
	
	/**
	 * W샌드위치 플러그인 비활성화
	 */
	public static function pluginDeactivation(){
		global $wpdb;
	}
	
	/**
	 * W샌드위치 플러그인 삭제
	 */
	public static function pluginUninstall(){
		global $wpdb;
		if(function_exists('is_multisite') && is_multisite()){
			$old_blog = $wpdb->blogid;
			$blogids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs");
			foreach($blogids as $blog_id){
				switch_to_blog($blog_id);
				self::_pluginUninstall();
			}
			switch_to_blog($old_blog);
			return;
		}
		self::_pluginUninstall();
	}
	
	/**
	 * W샌드위치 플러그인 삭제 실행
	 */
	public static function _pluginUninstall(){
		global $wpdb;
		$tables = array();
		$table_result = $wpdb->get_results('SHOW TABLES', ARRAY_N);
		foreach($table_result as $row){
			if(stristr($row[0], $wpdb->prefix.'wsandwich_')) $tables[] = $row[0];
		}
		foreach($tables as $key => $value){
			$wpdb->query("DROP TABLE `{$value}`");
		}
	}
}
?>