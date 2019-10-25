<?php
header('Content-Type:application/json');

define('ConfigEncrptedJs', 'encryptedJs.log');
$encryptFileCount = 0;
$encryptJs = [];
$backupJs = [];
/*********************************
 * @func  获取文件名(不带后缀名)
 * @author code lighter
 *********************************/
function GetFileName($file){
	$filename = basename($file);
	$filename = str_replace(strrchr($filename,'.'),'',$filename);
	return $filename;
}
/************************************
 * @func 扫描目录加密的JS文件
 * @author code lighter
 ************************************/
function ScanJsEncryptFile($path)
{
    global $encryptFileCount;
    global $encryptJs;
    if(!file_exists($path)){
       var_dump([ 'code'=>'1001','msg' =>'path is not exits!']);
    }elseif (!is_dir($path)){
       var_dump(['code' => '1002','msg'  => 'is not dir' ]);
    }else{
        $dir_handle = opendir($path);
        while(false !== $file=readdir($dir_handle)) {
            if ($file=='.' || $file=='..') continue;
            if(is_dir($path . '/' . $file)) {
                // file为目录时进行递归遍历
                ScanJsEncryptFile($path . '/' . $file);
            }else{
                $js = $path."/".$file;
                if(pathinfo($js,PATHINFO_EXTENSION) == 'js'){ //只检查JS文件
                	if(strstr($js,'.old') == FALSE && strstr($js,'.new') == FALSE){
                		$content = file_get_contents($js); 
	                    $content = ltrim($content);
	                    if(substr_compare($content, "eval", 0,4)==0){ //检查是否是加密的JS文件
	                        $encryptFileCount++;
	                        $encryptJs[] = $js;
	                    }
                	}
                }
            }
        }
        closedir($dir_handle);
    }
}

/**************************************
 *@func 将备份文件还原
 *@author code lighter
 **************************************/
function ScanJsBackupFile($path,$suffix=".old")
{
    global $backupJs;
    if(!file_exists($path)){
       var_dump([ 'code'=>'1001','msg' =>'path is not exits!']);
    }elseif (!is_dir($path)){
       var_dump(['code' => '1002','msg'  => 'is not dir' ]);
    }else{
        $dir_handle = opendir($path);
        while(false !== $file=readdir($dir_handle)) {
            if ($file=='.' || $file=='..') continue;
            if(is_dir($path . '/' . $file)) {
                // file为目录时进行递归遍历
                ScanJsBackupFile($path . '/' . $file,$suffix);
            }else{
                $js = $path."/".$file;
                if(pathinfo($js,PATHINFO_EXTENSION) == 'js'){ //只检查JS文件
                	if(strstr($js,$suffix) != FALSE){
	                    $backupJs[] = $js;
                	}
                }
            }
        }
        closedir($dir_handle);
    }
}

function RestoreBackupJs($path)
{
	global $backupJs;
	ScanJsBackupFile($path);
	$dump = '';
	foreach($backupJs as $k=>$v){
		$fileNew = substr($v,0,strlen($v)-7).".js";
		copy($v,$fileNew);
		$dump .= $v."\r\n".$fileNew."\r\n";
	}
	file_put_contents("restore.log", $dump);
	return count($backupJs);
}

/***************************
 *@func 功能已作废,请勿使用
 *@author code lighter
 ***************************/
function DiscardImmediateJs()
{
	global $backupJs;
	ScanJsBackupFile("D:/work_root/xiongdan",".new");
	foreach($backupJs as $k=>$v){
		unlink($v);
	}
	return count($backupJs);
}

/************************************
 * @func 扫描加密的JS文件,并保存到文件
 * @author code lighter
 ************************************/
function FindEncryptedJsFiles($dir)
{
	global $encryptJs;
	if(!is_dir($dir)){
		return false;
	}
	ScanJsEncryptFile($dir);
	$lines = "";
	foreach($encryptJs as $k=>$v){
	  $lines .=$v."\r\n";
	}
    $lines = rtrim($lines,"\r\n");
	file_put_contents(ConfigEncrptedJs, $lines);
	return true;
}

/************************************
 * @func 获取扫描后的加密JS文件
 * @author code lighter
 ************************************/
function GetEncryptedJsFiles()
{
	$js = fopen(ConfigEncrptedJs, "r") or die("Unable to open file!");
	$content =  fread($js,filesize(ConfigEncrptedJs));
	fclose($js);

	$lines = explode("\r\n", $content);
	return $lines;
}

function BeautifyJsCodeOnline($encrptedJs)
{
	$url = "http://web.chacuo.net/formatjs";
	$header = '';
	$post_args['data'] = $encrptedJs;
	$post_args['type'] = 'format';
	$post_args['arg'] = '';
	$post_args['beforeSend'] = '';
	$ch = curl_init($url);
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回数据不直接输出
    //curl_setopt($ch, CURLOPT_ENCODING, "gzip"); //指定gzip压缩
    //add header
    if(!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    //add ssl support
    if(substr($url, 0, 5) == 'https') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    //SSL 报错时使用
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    //SSL 报错时使用
    }
    //add 302 support
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //add post data support
    if(!empty($post_args)) {
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $post_args);
    }
    try {
        $content = curl_exec($ch); //执行并存储结果
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    $curlError = curl_error($ch);
    if(!empty($curlError)) {
        echo $curlError;
    }
    curl_close($ch);
    return $content;
}

if(isset($_POST["route"])){
    $route = $_POST["route"];
    switch($route){
   	 case 'generate':{
   	 	$dir = $_POST['dir'];
   	 	$result = FindEncryptedJsFiles($dir);
   	 	if(!$result){
   	 		unlink(ConfigEncrptedJs);
   	 		echo json_encode(['status'=>1,'js_count'=>0]);
   	 	}else{
   	 		$count = $encryptFileCount == 0? -1:$encryptFileCount;
   	 		echo json_encode(['status'=>0,'js_count'=>$count]);
   	 	}
   	 	break;
   	 }
   	 case 'restore':{
   	 	$dir = $_POST['dir'];
   	 	$count = RestoreBackupJs($dir);
   	 	echo json_encode(['status'=>0,'js_count'=>$count]);
   	 	break;
   	 }
     case 'encrypted_files':{
     	$lines = GetEncryptedJsFiles();
        echo json_encode($lines);
        break;
     }
     case 'encode':{
        $encode_file = $_POST['encode_file'];
        $encrypted_content = file_get_contents($encode_file);
        $encrypted_content = substr($encrypted_content, 4);
        $data['eval_str'] = $encrypted_content;
        echo json_encode($data);
        break;
     }
     case 'decode':{
        $encode_file = $_POST['encode_file'];
        $decode_content = $_POST['decode_content'];
        $number = $_POST['number'];
        $info = pathinfo($encode_file);
        $old_js = $info['dirname'].'/'.GetFileName($encode_file).'.old.'.$info['extension'];
        $new_js = $info['dirname'].'/'.GetFileName($encode_file).'.new.'.$info['extension'];

        //调用第三方网站批量格式化代码
        $beauty_js = BeautifyJsCodeOnline($decode_content);
        $code = json_decode($beauty_js,true);
        $beauty_js_content = $code["data"][0];
        //备份加密的文件
        copy($encode_file, $old_js);
        //将格式化后的js文件内容保存起来
        file_put_contents($encode_file, $beauty_js_content);
        echo json_encode(['result'=>'success','beauty_js'=>$encode_file]);
        break;
     }
     default:
        break;
   }
}


?>