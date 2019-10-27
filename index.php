<!DOCTYPE html>
<html>
    <head>
        <title>批量解码</title>  
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta http-equiv="content-type" content="text/html;charset=utf8">
        <script type="text/javascript" src="/lib/jquery.min.js"></script>
        <style type="text/css">
            .center{
                position:absolute;
                margin-left:50%;
                width:960px;
                margin-top:20px;
                left:-50%;
            }
            .title{
                margin-top:10px;
                margin-bottom:30px;
                font-size:20px;
                font-weight:bold;
                text-align:center;
            }
            .dir{
                height:28px;
                width:280px;
            }
            .batch_decrypt,.clean,.restore{
                width:84px;
                height:28px;
                text-align: center;
                cursor:pointer;
            }
            .batch_decrypt{
                margin-left:336px;
                margin-top:40px;
            }
            .clean,.restore{
                margin-left:12px;
            }
            .xx,.log{
                margin-left:336px;
                width:1000px;
            }
            .log{height:600px;overflow-y: auto;margin-top:10px;}
            .xx{
                height: 30px;
                margin-top:30px;
                display:none;
                font-size:20px;
                font-weight:bold;
            }

        </style>
    </head>
    <body>
        <div class="center">
            <div class="title">加密代码文件批量解密</div>
            <div style="text-align:center;"><input placeholder="D:/work_root/xiongdan" type="text" name="dir" class="dir"/></div>
            <input type="button" value="批量解密" class="batch_decrypt"/>
            <input type="button" value="数据还原" class="restore"/>
            <input type="button" value="清空日志" class="clean"/>
            <div class="xx"><span>解码进度：</span><span class="count"></span></div>
            <div class="log">
            </div>
        </div>
    </body>
<script type="text/javascript">
    var encrypted_files = [];
    function GetEncyptedJsFiles()
    {
        $.post("decode.php",{route:"encrypted_files"},function(result){
            encrypted_files = result;
        },"json");
    }
    function GetEncyptedJsContent(file){
        var content = "";
        $.post("decode.php",{route:"encode",encode_file:file},function(result){
            content = result['eval_str'];
        },"json");
        return content;
    }
    function SaveDecodeJsContent(encode_file,decode_content,number){
        var decrypt_path = '';
        $.post("decode.php",{route:"decode",
                        encode_file:encode_file,
                        decode_content:decode_content,
                        number:number},function(result){
                           decrypt_path = result['beauty_js'];
        },"json");
        return decrypt_path;
    }
    function ScanEncryptedJsFile(dir){
      var file_count = 0;
      $.post("decode.php",{route:"generate",dir:dir},function(result){
             file_count = result['js_count'];
        },"json");
      return file_count;
    }
    function RestoreEncryptedJsFile(dir){
      var file_count = 0;
      $.post("decode.php",{route:"restore",dir:dir},function(result){
             file_count = result['js_count'];
        },"json");
      console.log("发送请求");
      return file_count;        
    }
    var log_count = 0;
    function echo(log,color="FF0000"){
        $('.log').append('<div style="color:#'+color+';">'+log+'</div>')
        log_count++;
    }
    function EnableControls()
    {
        $('.dir').removeAttr("disabled");
        $('.batch_decrypt').removeAttr("disabled");
    }
    function DisableControls()
    {
        $('.dir').attr("disabled","disabled");
        $('.batch_decrypt').attr("disabled","disabled");
    }
    var exec_count = 0;
    $(function(){
        $.ajaxSetup({async:false});
        $('.clean').click(function(){
            $('.log').empty();
            if(exec_count>=encrypted_files.length||exec_count==0){
                $('.xx').hide();
            }
        });
        $('.restore').click(function(){
            var dir = $('.dir').val();
            if(dir == ''){
                echo("文件夹绝对路径不能为空!","FF0000");
                return;
            }
            DisableControls();
            echo("后台加密JS文件恢复中...","055A27");            
            if(dir.substr(-1,1)=='/'){
                dir = dir.substr(0,dir.length-1);
            }
            setTimeout(function(){
                var file_count = RestoreEncryptedJsFile(dir);
                EnableControls();
                if(file_count == 0){                
                    echo("目录 "+dir+"不存在!", "FF0000");
                }else if(file_count == -1){
                    echo("目录 "+dir+"找不到加密JS文件!", "FF0000");
                }else{
                    echo("恢复加密Javascript文件总数: "+file_count, "055A27");
                }
            },1000);
        });
        $('.batch_decrypt').click(function(){
            var dir = $('.dir').val();
            if(dir == ''){
                echo("文件夹绝对路径不能为空!","FF0000");
                return;
            }
            DisableControls();
            echo("后台加密JS文件扫描中...","055A27");            
            if(dir.substr(-1,1)=='/'){
                dir = dir.substr(0,dir.length-1);
            }
            var file_count = ScanEncryptedJsFile(dir);
            if(file_count == 0){
                EnableControls();
                echo("目录 "+dir+"不存在!", "FF0000");
                return ;
            }else if(file_count == -1){
                EnableControls();
                echo("目录 "+dir+"找不到加密JS文件!", "FF0000");
                return ;
            }else{
                echo("加密Javascript文件总数: "+file_count, "055A27");
            }
            GetEncyptedJsFiles();
            $('.xx').show();
            var iTimer= setInterval(function(){
                if(exec_count>=encrypted_files.length){
                    clearInterval(iTimer);
                    EnableControls();
                }else{
                    $('.count').html(exec_count+1+"/"+encrypted_files.length);
                    var js_encrypted_content = GetEncyptedJsContent(encrypted_files[exec_count]);
                    echo("解密JS文件: "+encrypted_files[exec_count]+" 成功!",'055A27');
                    var decode = eval(js_encrypted_content);
                    var decrypt_path = SaveDecodeJsContent(encrypted_files[exec_count],decode,exec_count);
                    echo("解密后文件路径: "+decrypt_path,'9D0A46');
                    exec_count++;
                }

            },1200);
        });
        
    });
</script>
</html>