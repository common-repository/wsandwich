/**
 * @author http://www.wsandwich.com/
 */

var wsandwich = {
	callback_index:0,
	access_token:'',
	api_url:'http://wsandwich.com/api',
	init:function(app_id, access_token){
		this.app_id = app_id;
		this.access_token = access_token;
	},
	api:function(command, data, callback){
		callback_name = "_WSANDWICH_callback_" + (new Date()).getTime() + "_" + this.callback_index++;
		if(data instanceof Array || data instanceof Object){
			var _data = '';
			for(var key in data){
				_data += key +'='+ data[key] +'&';
			}
			data = _data;
		}
		if(typeof callback !== 'function') callback = function(res){};
		window[callback_name] = callback;
		js = document.createElement('script');
		js.src = this.api_url + escape(command) + '?' + data + '&callback=' + callback_name + '&access_token=' + this.access_token;
		js.type = 'text/javascript';
		document.getElementsByTagName('head')[0].appendChild(js);
	},
	getGrant:function(data, callback){
		this.api('/grant', data, callback);
	},
	getVersion:function(data, callback){
		this.api('/version', data, callback);
	},
	getLicense:function(data, callback){
		this.api('/license', data, callback);
	},
	getStatus:function(callback){
		this.api('/status', '', callback);
	},
	getScope:function(data, callback){
		this.api('/scope', data, callback);
	}
}

/*
 * 관리자 페이지에 알림 메시지를 띄운다.
 */
function ws_display_message(text){
	if(jQuery('header #setting-error-settings_updated').length){
		jQuery('header #setting-error-settings_updated p strong').text(text);
	}
	else{
		jQuery('header').append('<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>'+text+'</strong></p></div>');
	}
	jQuery('header #setting-error-settings_updated').hide();
	jQuery('header #setting-error-settings_updated').fadeIn();
}