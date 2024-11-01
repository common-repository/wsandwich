<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<header>
		<h2>설정</h2>
	</header>
	
	<form method="post" onsubmit="return wsandwich_options_exeucte(this);">
		<input type="hidden" name="action" value="wsandwich_options_exeucte">
		
		<div class="stuffbox">
			<div class="inside">
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row">허가된 라이센스</th>
							<td>
								<span class="wsandwich-license">...</span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">라이센스 키</th>
							<td>
								<input type="text" name="access_token" id="access_token" maxlength="32" size="40" value="<?php echo $meta->access_token?>">
								<p class="description">W샌드위치팀에게 발급받은 라이센스 키를 입력하세요. <a href="http://wsandwich.com/" onclick="window.open(this.href); return false;">홈페이지 방문</a></p>
							</td>
						</tr>
				    </tbody>
			    </table>
				
				<div class="submit">
					<input class="button-primary" value="변경 사항 저장" type="submit">
				</div>
			</div>
		</div>
	</form>
</div>

<script>
var ws_domain = '<?php echo str_replace('www.', '', $_SERVER['HTTP_HOST'])?>';
var ws_license = '';

/*
 * 설정값 정보 업데이트
 */
function wsandwich_options_exeucte(form){
	jQuery.post('<?php echo admin_url('/admin-ajax.php')?>', jQuery(form).serialize(), function(res){
		ws_display_message(res.message);
		if(res.ftp_base) jQuery('input[name=ftp_connect_base]').val(res.ftp_base);
		license();
	}, 'json');
	return false;
}

/*
 * 라이센스 정보
 */
function license(){
	wsandwich.access_token = jQuery('input[name="access_token"]').val();
	wsandwich.getLicense({'domain':ws_domain}, function(res){
		if(res['error_code']){
			jQuery('.wsandwich-license').text('무료 라이센스');
		}
		else{
			ws_license = res['license'];
			jQuery('.wsandwich-license').text(res['license']);
		}
	});
}

window.onload = function(){
	license();
};
</script>