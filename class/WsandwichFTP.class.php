<?php
/**
 * Wsandwich FTP
 * @link http://wsandwich.com/
 * @copyright Copyright 2013 i4unetworks. All rights reserved.
 */
final class WsandwichFTP {
	
	private static $instance;
	private $conn;
	private $meta;
	
	private function __construct(){
		$this->meta = WsandwichMeta::getInstance();
		$this->meta->setModule('wsandwich');
	}
	
	/**
	 * 인스턴스를 반환한다.
	 * @return WsandwichFTP
	 */
	static public function getInstance(){
		if(!self::$instance) self::$instance = new WsandwichFTP();
		return self::$instance;
	}
	
	public function setHost($host){
		$this->meta->ftp_host = $host;
		return $this;
	}
	
	public function getHost(){
		return $this->meta->ftp_host;
	}
	
	public function setID($id){
		$this->meta->ftp_id = $id;
		return $this;
	}
	
	public function getID(){
		return $this->meta->ftp_id;
	}
	
	public function setBase($dir){
		if(substr($dir, -1) == '/') $dir = substr($dir, 0, -1);
		$this->meta->ftp_base = $dir;
		return $this;
	}
	
	public function getBase(){
		return $this->meta->ftp_base;
	}
	
	public function connect($pw=''){
		if($pw) $_SESSION['wsandwich_ftp_pw'] = $pw;
		$url = parse_url($this->meta->ftp_host);
		if($url['host'] && $this->meta->ftp_id && $_SESSION['wsandwich_ftp_pw']){
			$this->conn = ftp_connect($url['host'], $url['port']);
			ftp_login($this->conn, $this->meta->ftp_id, $_SESSION['wsandwich_ftp_pw']);
			if(!$this->meta->ftp_base) $this->meta->ftp_base = $this->getFTPBase();
		}
		else{
			unset($this->conn);
		}
		return $this->conn;
	}
	
	public function close(){
		return ftp_close($this->conn);
	}
	
	/**
	 * FTP BASE 경로를 반환한다.
	 * @param string $path
	 * @return string
	 */
	public function getFTPBase($path=''){
		if(!$path) $path = DIRECTORY_SEPARATOR;
		else $path .= DIRECTORY_SEPARATOR;
	
		$list = ftp_nlist($this->conn, $path);
		foreach($list as $key => $value){
			if($value != 'wp-content'){
				return $this->getFTPBase($path . $value);
			}
			else{
				break;
			}
		}
		return str_replace('/index.php/', '', $path);
	}
	
	/**
	 * 권한을 변경한다.
	 * @param int $chmod
	 * @param string $file
	 * @return boolean
	 */
	public function chmod($chmod, $file){
		if($this->conn){
			if(ftp_chmod($this->conn, $chmod, $this->meta->ftp_base.$file) !== false){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 파일 및 디렉토리 업로드
	 * @param string $dir
	 * @param string $remote
	 */
	public function upload($local, $remote=''){
		if($this->conn){
			if($handle = opendir($local)){
				while(false !== ($file = readdir($handle))){
					if($file != "." && $file != ".." && $file != "..."){
						if(substr_count($file, ".") > 0){
							if(!ftp_put($this->conn, "{$this->meta->ftp_base}{$remote}/{$file}", "{$local}/{$file}", FTP_BINARY)){
								return false;
							}
						}
						else{
							if(!@ftp_chdir($this->conn, "{$this->meta->ftp_base}{$remote}/{$file}")){
								if(!ftp_mkdir($this->conn, "{$this->meta->ftp_base}{$remote}/{$file}")){
									return false;
								}
							}
							if(!$this->upload("{$local}/{$file}", "{$remote}/{$file}")){
								return false;
							}
						}
					}
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * 파일 쓰기
	 * @param string $file
	 * @param string $contents
	 * @return boolean
	 */
	public function putContents($file, $contents){
		$tempfile = wp_tempnam($file);
		$temp = fopen($tempfile, 'wb+');
		if(!$temp) return false;
		
		mbstring_binary_safe_encoding();
		
		$data_length = strlen($contents);
		$bytes_written = fwrite($temp, $contents);
		
		reset_mbstring_encoding();
		
		if($data_length !== $bytes_written){
			fclose($temp);
			unlink($tempfile);
			return false;
		}
		
		fseek($temp, 0); // Skip back to the start of the file being written to
		
		$ret = @ftp_fput($this->conn, "{$this->meta->ftp_base}{$file}", $temp, FTP_BINARY);
		
		fclose($temp);
		unlink($tempfile);
		
		return $ret;
	}
	
	/**
	 * 디렉토리 생성
	 * @param string $directory
	 */
	public function mkdir($directory){
		return ftp_mkdir($this->conn , "{$this->meta->ftp_base}{$directory}");
	}
}
?>