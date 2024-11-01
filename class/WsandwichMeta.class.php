<?php
/**
 * Wsandwich 데이터 저장 클래스
 * @link http://wsandwich.com/
 * @copyright Copyright 2013 i4unetworks. All rights reserved.
 */
class WsandwichMeta {
	
	static $instance;
	private $module;
	private $meta;
	
	private function __construct(){
		$this->clear();
	}
	
	public function __get($name){
		global $wpdb;
		$name = addslashes($name);
		
		if($this->module){
			if(isset($this->meta->{$name})){
				return stripslashes($this->meta->{$name});
			}
			else{
				$this->meta->{$name} = $wpdb->get_var("SELECT `value` FROM `".WSANDWICH_DB_PREFIX."wsandwich_meta` WHERE `module`='$this->module' AND `name`='$name'");
				return stripslashes($this->meta->{$name});
			}
		}
		return '';
	}
	
	public function __set($name, $value){
		global $wpdb;
		$name = addslashes($name);
		$value = addslashes($value);
		
		if($this->module){
			$wpdb->query("INSERT INTO `".WSANDWICH_DB_PREFIX."wsandwich_meta` (`module`, `name`, `value`) VALUE ('$this->module', '$name', '$value') ON DUPLICATE KEY UPDATE `value`='$value'");
			$this->meta->{$name} = $value;
		}
	}
	
	/**
	 * Singleton
	 * @static
	 */
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new WsandwichMeta();
		}
		return self::$instance;
	}
	
	/**
	 * 모듈 이름을 입력받는다.
	 * @param string $module
	 */
	public function setModule($module){
		$this->meta = new stdClass();
		$this->module = ws_htmlclear($module);
	}
	
	/**
	 * 모든 빈 값들을 제거한다.
	 */
	public function clear(){
		global $wpdb;
		$wpdb->query("DELETE FROM `".WSANDWICH_DB_PREFIX."wsandwich_meta` WHERE value=''");
	}
}
?>