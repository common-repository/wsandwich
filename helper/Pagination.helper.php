<?php
/**
 * W샌드위치 페이지 출력 함수
 * @link http://wsandwich.com/
 * @copyright Copyright 2013 i4unetworks. All rights reserved.
 */
function ws_pagination($current_page, $total, $limit){
	foreach($_GET AS $key => $value){
		if($key != 'pageid' && $value){
			$query_strings[] = ws_htmlclear(trim($key)).'='.ws_htmlclear(trim($value));
		}
	}
	if($query_strings) $query_strings = '&' . implode('&', $query_strings);
	
	$sliding_size = 10;
	$total_page = ceil($total/$limit);
	$paging = '';
	$i = 0;
	
	if($current_page > $sliding_size){
		$i = $current_page - ($current_page % $sliding_size);
	}
	
	// offset은 윈도의 마지막 페이지 번호다.
	$offset = $i + $sliding_size;
	
	// 윈도의 시작 $i 부터, 윈도우 마지막 까지 출력한다.
	for($i; $i < $offset && $i < $total_page; $i++){
		$page_name = $i+ 1;
		// 링크는 적당히 수정
		if($current_page != $page_name){
			$paging .= "<li><a href=\"?pageid={$page_name}{$query_strings}\">{$page_name}</a></li>";
		}
		else{
			$paging .= "<li class=\"active\"><a href=\"?pageid={$page_name}{$query_strings}\">{$page_name}</a></li>";
		}
	}
	
	// 좌우 이동 화살표 «, »를 출력한다.
	// 처음과 마지막 페이지가 아니라면 링크를 걸어주면 된다.
	if($current_page != 1){
		$prev_page = $current_page - 1;
		$paging = "<li><a href=\"?pageid={$prev_page}{$query_strings}\">«</a></li>" . $paging;
	}
	if($current_page != $total_page){
		$next_page = $current_page + 1;
		$paging = $paging . "<li><a href=\"?pageid={$next_page}{$query_strings}\">»</a></li>";
	}
	
	return $total?$paging:'<li class=\"active\"><a href="#" onclick="return false;">1</a></li>';
}
?>