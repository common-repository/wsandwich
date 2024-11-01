<?php
/**
 * Wsandwich 업그레이더
 * @link http://wsandwich.com/
 * @copyright Copyright 2013 i4unetworks. All rights reserved.
 */
final class WsandwichUpgrader {
	
	static private $instance;
	private $server = 'http://wsandwich.com/api/module';
	var $package;
	
	private function __construct(){
		
	}
	
	/**
	 * 인스턴스를 반환한다.
	 * @return WsandwichUpgrader
	 */
	static public function getInstance(){
		if(!self::$instance) self::$instance = new WsandwichUpgrader();
		return self::$instance;
	}
	
	/**
	 * 패키지를 다운받는다.
	 * @param string $package
	 * @param string $version
	 * @param string $access_token
	 * @return string
	 */
	public function download($package, $version, $access_token){
		//로컬에 있는 파일인지 확인한다.
		if(!preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package)){
			return $package;
		}
		
		$download_file = download_url("{$this->server}/{$package}/{$version}?access_token={$access_token}&domain=".str_replace('www.', '', $_SERVER['HTTP_HOST']));
		if(is_wp_error($download_file)){
			unlink($download_file);
			die('<script>alert("다운로드에 실패 했습니다. 다시 시도해 주세요.");history.go(-1);</script>');
		}
		
		$this->package = $download_file;
		return $this->package;
	}
	
	/**
	 * 패키지 파일의 압축을 풀고 설치한다.
	 * @param string $package
	 * @return string
	 */
	public function install($package=''){
		$package = $package?$package:$this->package;
		
		require_once WSANDWICH_PLUGIN_DIR . '/class/WsandwichFileHandler.class.php';
		$module_dir = WP_CONTENT_DIR . '/plugins/wsandwich/modules';
		
		// See #15789 - PclZip uses string functions on binary data, If it's overloaded with Multibyte safe functions the results are incorrect.
		if(ini_get('mbstring.func_overload') && function_exists('mb_internal_encoding')){
			$previous_encoding = mb_internal_encoding();
			mb_internal_encoding('ISO-8859-1');
		}
		require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
		
		$archive = new PclZip($package);
		$archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING);
		
		if(!$archive_files){
			$result = json_decode(file_get_contents($package));
			unlink($package);
			
			if($result->error_code){
				return "서버거부: $result->message (error code: $result->error_code)";
			}
			else{
				return '다운로드된 패키지 파일('.$package.')의 압축을 풀지 못했습니다. 서버 관리자에게 문의하시기 바랍니다.';
			}
		}
		else{
			unlink($package);
			$extract_result = true;
			
			if(is_writable($module_dir)){
				$file_handler = new WsandwichFileHandler();
				foreach($archive_files AS $file){
					if($file['folder']){
						$extract_result = $file_handler->mkPath($module_dir . '/' . $file['filename']);
					}
					else{
						$extract_result = $file_handler->putContents($module_dir . '/' . $file['filename'], $file['content']);
					}
					if(!$extract_result) break;
				}
				
				if(!$extract_result){
					$file_handler->delete($module_dir);
					return "$module_dir 디렉토리에 쓰기권한이 필요합니다. 서버 관리자에게 문의하시기 바랍니다.";
				}
			}
			else{
				global $wp_filesystem;
				$target_dir = trailingslashit($wp_filesystem->find_folder($module_dir));
				foreach($archive_files AS $file){
					if($file['folder']){
						if($wp_filesystem->is_dir($target_dir . $file['filename'])) continue;
						else $extract_result = $wp_filesystem->mkdir($target_dir . $file['filename'], FS_CHMOD_DIR);
					}
					else{
						$extract_result = $wp_filesystem->put_contents($target_dir . $file['filename'], $file['content'], FS_CHMOD_FILE);
					}
					if(!$extract_result) break;
				}
				if(!$extract_result){
					return 'FTP로 파일 쓰기에 실패했습니다. 서버 관리자에게 문의하시기 바랍니다.';
				}
			}
		}
		return '';
	}
	
	/**
	 * 워드프레스 Filesystem을 초기화 한다.
	 * @param string $form_url
	 * @param string $path
	 * @param string $method
	 * @param string $fields
	 * @return boolean
	 */
	function credentials($form_url, $path, $method='', $fields=null){
		global $wp_filesystem;
		
		if(is_writable($path)){
			return true;
		}
		if(false === ($creds = request_filesystem_credentials($form_url, $method, false, $path, $fields))){
			return false;
		}
		if(!WP_Filesystem($creds)){
			request_filesystem_credentials($form_url, $method, true, $path);
			return false;
		}
		return true;
	}
}
?>