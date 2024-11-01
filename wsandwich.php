<?php
/*
Plugin Name: Wsandwich
Plugin URI: http://wsandwich.com/
Description: W샌드위치는 i4unetworks에서 제작한 플러그인들을 관리하고 실행하는 플러그인 관리 도구 입니다.
Version: 1.2
Author: i4unetworks, Inc.
Author URI: http://wsandwich.com/
*/

if(!defined('ABSPATH')) exit;
if(!session_id()) session_start();

/*
 * 사용자 상수 정의
 */
define('WSANDWICH_VERSION', '1.2');
define('WSANDWICH_PAGE_TITLE', 'W샌드위치');
define('WSANDWICH_ROOT_DIR', substr(ABSPATH, 0, -1));
define('WSANDWICH_DB_PREFIX', $wpdb->prefix);
define('WSANDWICH_PLUGIN_DIR', str_replace(DIRECTORY_SEPARATOR . 'wsandwich.php', '', __FILE__));
define('WSANDWICH_MODULES_DIR', WSANDWICH_PLUGIN_DIR . '/modules');
define('WSANDWICH_URL_PATH', plugins_url('wsandwich'));
define('WSANDWICH_MODULES_URL_PATH', WSANDWICH_URL_PATH . '/modules');

/*
 * 인터페이스 및 추상 클래스 로드
 */
require_once 'class/WsandwichModule.interface.php';
require_once 'class/WsandwichAbstractModule.class.php';

/*
 * 사용자 클래스 로드
 */
require_once 'class/Wsandwich.class.php';
require_once 'class/WsandwichAdmin.class.php';
require_once 'class/WsandwichMeta.class.php';
require_once 'class/WsandwichMouldeList.class.php';

/*
 * 도우미 파일 로드
 */
require_once 'helper/Security.helper.php';
require_once 'helper/Tool.helper.php';

/*
 * 플러그인 활성화, 비활성화, 삭제 실행
 */
register_activation_hook(__FILE__, array('Wsandwich', 'pluginActivation'));
register_deactivation_hook(__FILE__, array('Wsandwich', 'pluginDeactivation'));
register_uninstall_hook(__FILE__, array('Wsandwich', 'pluginUninstall'));

/*
 * jQuery 추가
 */
wp_enqueue_script('jquery');

/*
 * W샌드위치 실행
 */
add_action('init', array('Wsandwich', 'init'));
add_action('widgets_init', array('Wsandwich', 'widgets'));
?>