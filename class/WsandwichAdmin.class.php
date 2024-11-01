<?php
/**
 * Wsandwich 관리자 페이지
 * @link http://wsandwich.com/
 * @copyright Copyright 2013 i4unetworks. All rights reserved.
 */
class WsandwichAdmin {
	
	/**
	 * 대시보드 페이지를 생성한다.
	 */
	public function dashboard(){
		$modules = WsandwichMouldeList::getInstance();
		$module_list = $modules->getList();
		$active_list = $modules->getActiveList();
		
		// 대시보드에 표시하기 위해서 모든 모듈을 불러온다.
		foreach($module_list AS $key => $module_name){
			$module_class = ucfirst($module_name);
				
			// 모듈 클래스가 없으면 불러온다.
			if(!function_exists($module_class)){
				include_once WSANDWICH_MODULES_DIR . "/$module_name/$module_class.class.php";
			}
		}
		
		$modules_writable = '1';
		if(!is_writable(WP_CONTENT_DIR . '/plugins/wsandwich/modules')){
			$modules_writable = '';
		}
		
		$credentials = ws_get('credentials');
		if($credentials){
			include WSANDWICH_PLUGIN_DIR . "/class/WsandwichUpgrader.class.php";
			$upgrader = WsandwichUpgrader::getInstance();
			
			$module = ws_get('module');
			$version = ws_get('version');
			
			$form_url = wp_nonce_url(admin_url("/admin-ajax.php?action=wsandwich_module_upgrade&module={$module}&version={$version}"), 'ajaxModuleUpgrade');
			if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . '/plugins/wsandwich/modules')) exit;
		}
		
		include WSANDWICH_PLUGIN_DIR . '/admin/dashboard.php';
	}
	
	/**
	 * 설정 페이지를 생성한다.
	 */
	public function options(){
		$meta = WsandwichMeta::getInstance();
		$meta->setModule('wsandwich');
		
		include WSANDWICH_PLUGIN_DIR . '/admin/options.php';
	}
}
?>