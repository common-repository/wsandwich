<?php
/**
 * Wsandwich 보안 함수
 * @link http://wsandwich.com/
 * @copyright Copyright 2013 i4unetworks. All rights reserved.
 */

/**
 * 모든 html을 제거한다.
 * @param object $data
 */
function ws_htmlclear($data){
	if(is_array($data)) return array_map('ws_htmlclear', $data);
	return htmlspecialchars(strip_tags($data));
}
?>