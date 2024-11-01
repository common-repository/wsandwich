<?php
/**
 * Wsandwich 모듈 인터페이스
 * @link http://wsandwich.com/
 * @copyright Copyright 2013 i4unetworks. All rights reserved.
 */
interface WsandwichModule {
	
	/**
	 * 모듈을 초기화 한다.
	 * @return instance
	 */
	public static function init();
	
	/**
	 * 모듈을 활성화 한다.
	 * @return void
	 */
	public static function activation();
	
	/**
	 * 모듈을 비활성화 한다.
	 * @return void
	 */
	public static function deactivation();
	
	/**
	 * 모듈을 업그레이드 한다.
	 * @return void
	 */
	public static function upgrade();
	
	/**
	 * 모듈의 이름을 반환한다.
	 * @return string
	 */
	public static function getName();
	
	/**
	 * 모듈의 설명을 반환한다.
	 * @return string
	 */
	public static function getDescription();
	
	/**
	 * 모듈의 버전을 반환한다.
	 * @return string
	 */
	public static function getVersion();
	
	/**
	 * 워드프레스 관리자 메뉴를 생성한다.
	 * @return void
	 */
	public function menu();
	
	/**
	 * 관리자 설정 페이지를 생성한다.
	 * @return void
	 */
	public function admin();
}
?>