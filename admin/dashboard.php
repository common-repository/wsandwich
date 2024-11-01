<?php if(!defined('ABSPATH')) exit;?>
<div class="wsandwich-wrap">
	<header class="wsandwich-header">
		<h2 class="wsandwich-header-left"><img src="<?php echo WSANDWICH_URL_PATH?>/admin/images/wsandwich_plate.png" alt="W샌드위치"></h2>
		<div class="wsandwich-header-right">
			<a href="http://wsandwich.com/" onclick="window.open(this.href); return false;" class="wsandwich-button wsandwich-button-active"><span class="wsandwich-status">서버 연결중...</span></a>
			<a href="http://wsandwich.com/" onclick="window.open(this.href); return false;" class="wsandwich-button wsandwich-button-gray"><img src="<?php echo WSANDWICH_URL_PATH?>/admin/images/license_dot_icon.png" alt=""> <span class="wsandwich-license">...</span></a>
		</div>
	</header>
	
	<div class="wsandwich-dashboard-list">
		<ul>
			<?php foreach($module_list AS $key => $module):?>
			<li data-module="<?php echo $module?>">
				<div class="wsandwich-dashboard-list-thumbnail">
					<?php if(call_user_func(array($module, 'getVersion')) < 1):?><img src="<?php echo WSANDWICH_URL_PATH?>/admin/images/wsandwich_beta.png" alt="" class="wsandwich-dashboard-list-thumbnail-beta"><?php endif?>
					<img src="<?php echo WSANDWICH_URL_PATH?>/modules/<?php echo $module?>/thumbnail.png" alt="<?php echo call_user_func(array($module, 'getName'))?>" class="wsandwich-dashboard-list-thumbnail-image">
				</div>
				<div class="wsandwich-dashboard-list-name"><h3><?php echo call_user_func(array($module, 'getName'))?></h3></div>
				<div class="wsandwich-dashboard-list-version">버전 <span class="wsandwich-version"><?php echo call_user_func(array($module, 'getVersion'))?></span> <span class="wsandwich-latest-version">업데이트 확인중...</span></div>
				<div class="wsandwich-dashboard-list-description"><?php echo call_user_func(array($module, 'getDescription'))?></div>
				<div class="wsandwich-dashboard-list-control">
					<?php if(isset($active_list[$module])):?>
					<a href="<?php echo admin_url("/admin.php?page=wsandwich_$module")?>" class="wsandwich-button">설정</a>
					<a href="#" class="wsandwich-button" onclick="return wsandwich_module_deactivate('<?php echo $module?>');">비활성화</a>
					<?php else:?>
					<a href="#" class="wsandwich-button wsandwich-button-active" onclick="return wsandwich_module_activate('<?php echo $module?>');">활성화</a>
					<?php endif?>
				</div>
			</li>
			<?php endforeach?>
		</ul>
	</div>
</div>

<script>
var ws_server_status;
var ws_domain = '<?php echo str_replace('www.', '', $_SERVER['HTTP_HOST'])?>';
var ws_license = '';
var ws_modules_writable = '<?php echo $modules_writable?>';

/*
 * 모듈 활성화
 */
function wsandwich_module_activate(module){
	wsandwich.getGrant({'domain':ws_domain, 'module':module}, function(res){
		if(res['error_code']){
			alert(res['message']);
		}
		else if(res['grant'] == 1){
			jQuery.post('<?php echo admin_url('/admin-ajax.php')?>', {'action':'wsandwich_module_activate', 'module':module}, function(res){
				location.reload();
			});
		}
	});
	return false;
}

/*
 * 모듈 비활성화
 */
function wsandwich_module_deactivate(module){
	jQuery.post('<?php echo admin_url('/admin-ajax.php')?>', {'action':'wsandwich_module_deactivate', 'module':module}, function(res){
		location.reload();
	});
	return false;
}

/*
 * 모듈 업그레이드
 */
function wsandwich_module_upgrade(module, version){
	if(!ws_modules_writable){
		location.href = '<?php echo admin_url("/admin.php?page=wsandwich_dashboard&credentials=1")?>' + '&module='+module+'&version='+version;
		return false;
	}
	wsandwich.getGrant({'domain':ws_domain, 'module':module}, function(res){
		if(res['error_code']){
			alert(res['message']);
		}
		else if(res['grant'] == 1){
			if(confirm('W샌드위치 플러그인 파일을 먼저 백업하세요. 계속 할까요?')){
				jQuery.post('<?php echo admin_url('/admin-ajax.php')?>', {'action':'wsandwich_module_upgrade', 'module':module, 'version':version}, function(res){
					if(res.error == 1){
						alert(res.message);
					}
					else{
						jQuery('.wsandwich-latest-version', 'li[data-module="'+module+'"]').text('업그레이드 중...');
						setTimeout(function(){
							jQuery('.wsandwich-version', 'li[data-module="'+module+'"]').fadeOut(function(){
								jQuery(this).fadeIn().text(version);
							})
							jQuery('.wsandwich-latest-version', 'li[data-module="'+module+'"]').text('최신 버전입니다.');
						}, 3000);
					}
				}, 'json');
			}
		}
	});
	return false;
}

/*
 * 모듈 인스톨
 */
function wsandwich_module_install(module, version){
	if(!ws_modules_writable){
		location.href = '<?php echo admin_url("/admin.php?page=wsandwich_dashboard&credentials=1")?>' + '&module='+module+'&version='+version;
		return false;
	}
	wsandwich.getGrant({'domain':ws_domain, 'module':module}, function(res){
		if(res['error_code']){
			alert(res['message']);
		}
		else if(res['grant'] == 1){
			if(confirm('모듈이 서버에서 다운로드 됩니다. 계속 할까요?')){
				jQuery.post('<?php echo admin_url('/admin-ajax.php')?>', {'action':'wsandwich_module_upgrade', 'module':module, 'version':version}, function(res){
					if(res.error == 1){
						alert(res.message);
					}
					else{
						jQuery('.wsandwich-latest-version', 'li[data-module="'+module+'"]').text('설치중...');
						setTimeout(function(){
							jQuery('.wsandwich-version', 'li[data-module="'+module+'"]').fadeOut(function(){
								jQuery(this).fadeIn().text(version);
							});
							jQuery('.wsandwich-dashboard-list-thumbnail-image', 'li[data-module="'+module+'"]').css({'opacity':0}).attr('src', '<?php echo WSANDWICH_URL_PATH?>/modules/'+module+'/thumbnail.png').animate({'opacity':1});
							jQuery('.wsandwich-latest-version', 'li[data-module="'+module+'"]').text('최신 버전입니다.');
							
							jQuery('.wsandwich-dashboard-list-control', 'li[data-module="'+module+'"]').html('<a href="#" class="wsandwich-button wsandwich-button-active" onclick="return wsandwich_module_activate(\''+module+'\');">활성화</a>');
						}, 3000);
					}
				}, 'json');
			}
		}
	});
	return false;
}

/*
 * 모듈 레이아웃 추가
 */
function wsandwich_add_module(module, name, version, description, control){
	var li = jQuery('<li></li>').attr('data-module', module);
	var div_thumbnail = jQuery('<div></div>').addClass('wsandwich-dashboard-list-thumbnail').append(jQuery('<img src="<?php echo WSANDWICH_URL_PATH?>/admin/images/thumbnail_install.png" alt="설치하세요" class="wsandwich-dashboard-list-thumbnail-image">'));
	var div_name = jQuery('<div></div>').addClass('wsandwich-dashboard-list-name').append(jQuery('<h3></h3>').text(name));
	var div_version = jQuery('<div></div>').addClass('wsandwich-dashboard-list-version').text('버전 ').append(jQuery('<span></span>').addClass('wsandwich-version').text(version)).append(jQuery('<span></span>').addClass('wsandwich-latest-version'));
	var div_description = jQuery('<div></div>').addClass('wsandwich-dashboard-list-description').text(description);
	var div_control = jQuery('<div></div>').addClass('wsandwich-dashboard-list-control').append(jQuery('<a></a>').attr('href', '#').addClass('wsandwich-button').text('다운로드 및 설치').click(function(){
			return wsandwich_module_install(module, version);
		}));
	
	if(version < 1){
		// 버전이 낮으면 beta 이미지 추가
		div_thumbnail.prepend(jQuery('<img src="<?php echo WSANDWICH_URL_PATH?>/admin/images/wsandwich_beta.png" alt="" class="wsandwich-dashboard-list-thumbnail-beta">'))
	}
	li.append(div_thumbnail).append(div_name).append(div_version).append(div_description).append(div_control);
	
	jQuery('.wsandwich-dashboard-list ul').append(li);
	return false;
}

window.onload = function(){
	wsandwich.access_token = '<?php echo WSANDWICH_ACCESS_TOKEN?>';
	
	wsandwich.getStatus(function(res){
		if(res.message){
			ws_server_status = res.status;
			jQuery('.wsandwich-status').text(res.message);
		}
		else{
			ws_server_status = 0;
			jQuery('.wsandwich-status').text('점검중');
		}
	});
	
	wsandwich.getVersion({'domain':ws_domain}, function(res){
		jQuery('.wsandwich-dashboard-list li').each(function(){
			var module = jQuery(this).attr('data-module');
			if(typeof res[module] != 'undefined'){
				var version = parseFloat(jQuery('.wsandwich-version', this).text());
				var latest = parseFloat(res[module]['version']);
				
				if(version == latest) jQuery('.wsandwich-latest-version', this).text('최신 버전입니다.');
				else if(version < latest) jQuery('.wsandwich-latest-version', this).html('<a href="#" onclick="return wsandwich_module_upgrade(\''+module+'\', \''+res[module]['version']+'\')">버전 '+res[module]['version']+' 받기</a>');
				else jQuery('.wsandwich-latest-version', this).text('가장 최신 버전입니다.');
			}
			else{
				jQuery('.wsandwich-latest-version', this).text('');
			}
		});
		
		// 서버에서 모듈 정보 받아와서 표시한다.
		jQuery.each(res, function(key, value){
			if(!jQuery('.wsandwich-dashboard-list li[data-module="'+key+'"]').length){
				wsandwich_add_module(key, value['name'], value['version'], value['description']);
			}
		});
	});
	
	wsandwich.getLicense({'domain':ws_domain}, function(res){
		if(res['error_code']){
			jQuery('.wsandwich-license').text('라이센스 발급 신청');
		}
		else{
			ws_license = res['license'];
			jQuery('.wsandwich-license').text(res['license'] + ' 이용중');
		}
	});
};
</script>