<?php
/**
 * Wsandwich 도구 함수 모음
 * @link http://wsandwich.com/
 * @copyright Copyright 2013 i4unetworks. All rights reserved.
 */

/**
 * W샌드위치 시간 함수
 * @param string $format
 */
function ws_date($format){
	return date($format, current_time('timestamp'));
}

/**
 * W샌드위치 모바일 디바이스 체크 함수
 * @return boolean
 */
function ws_is_mobile(){
	$arr_browser = array ('iphone', 'android', 'ipod', 'iemobile', 'mobile', 'lgtelecom', 'ppc', 'symbianos', 'blackberry', 'ipad');
	$httpUserAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
	// 기본값으로 모바일 브라우저가 아닌것으로 간주함
	$mobile_browser = false;
	// 모바일브라우저에 해당하는 문자열이 있는 경우 $mobile_browser를 true로 설정
	for($indexi = 0 ; $indexi < count($arr_browser) ; $indexi++){
		if(strpos($httpUserAgent, $arr_browser[$indexi]) == true){
			$mobile_browser = true;
			break;
		}
	}
	return $mobile_browser;
}

/**
 * W샌드위치 문자열 자르기 함수
 * @param string $str
 * @param int $len
 */
function ws_strcut($str, $len=35){
	return mb_strimwidth($str, 0, $len, '...',  'UTF-8');
}

/**
 * W샌드위치 썸네일 파일 주소
 * @param unknown $post_id
 * @param unknown $size
 * @return unknown
 */
function ws_thumbnail_src($post_id, $size=array()){
	$thumbnail_id = get_post_thumbnail_id($post_id);
	$src = '';
	if($thumbnail_id > 0) list($src) = wp_get_attachment_image_src($thumbnail_id, $size);
	return $src;
}

/**
 * GET 변수를 반환한다.
 * @param string $key
 * @return string
 */
function ws_get($key){
	return isset($_GET[$key]) ? ws_htmlclear($_GET[$key]) : null;
}

/**
 * POST 변수를 반환한다.
 * @param string $key
 * @param boolean $htmlclear
 * @return string
 */
function ws_post($key, $htmlclear=false){
	if($htmlclear) return isset($_POST[$key]) ? ws_htmlclear($_POST[$key]) : null;
	else return isset($_POST[$key]) ? $_POST[$key] : null;
}

/**
 * JSON 인코더
 * @param array $val
 * @return string
 */
function ws_json_encode($val){
	if(function_exists('json_encode')){
		return json_encode($val);
	}

	/*
	 * http://kr1.php.net/json_encode#113219
	 */

	if(is_string($val)) return '"'.addslashes($val).'"';
	if(is_numeric($val)) return $val;
	if($val === null) return 'null';
	if($val === true) return 'true';
	if($val === false) return 'false';

	$assoc = false;
	$i = 0;
	foreach($val as $k=>$v){
		if($k !== $i++){
			$assoc = true;
			break;
		}
	}
	$res = array();
	foreach($val as $k=>$v){
		$v = ws_json_encode($v);
		if($assoc){
			$k = '"'.addslashes($k).'"';
			$v = $k.':'.$v;
		}
		$res[] = $v;
	}
	$res = implode(',', $res);

	return ($assoc)? '{'.$res.'}' : '['.$res.']';
}

/**
 * 특수문자를 지운 내용을 가져온다.
 * @param string $text
 * @return string
 */
function ws_get_the_excerpt($text){
	global $post;
	if(!$text) return '';
	$text = str_replace('\]\]\>', ']]&gt;', $text);
	$text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);
	$text = strip_tags($text);
	$text = str_replace(array("\r", "\n", "\t"), "", $text);
	return addslashes($text);
}

/**
 * 접속자 아이피 주소를 반환한다.
 * @return string
 */
function ws_ip(){
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		$ip=$_SERVER['HTTP_CLIENT_IP'];
	}
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else{
		$ip=$_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/**
 * 문자의 인코딩을 반환한다.
 * @param string $string
 * @return string
 */
function ws_detect_encoding($string){
	if(!$string) return '';
	$encoding_list = array('UTF-8', 'EUC-KR'); 
	
	foreach($encoding_list as $item){ 
		$sample = iconv($item, "$item//IGNORE", $string);
		if(md5($sample) == md5($string)) return $item;
	}
	return '';
}
?>