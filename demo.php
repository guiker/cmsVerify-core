<?php
require_once('cmsVerify-core.php');
$cvf = 'demoFile.cvf';
$filter = array('path'=>array('/demoFile/filter_1','/demoFile/filter_2'),
    'file'=>array('/demoFile/filter.php','/demoFile/test_1/test_1_filter.php',));
$verify = new cmsVerity();
$verify->readFile(dirname(__FILE__).'/demoFile',$filter);

if(isset($_GET['action']) && $_GET['action'] == 'diff'){
print <<<EOF
#====================</br>
#cmsVerify-core/demo</br>
#</br>
#Powered-By-guiker</br>
#</br>
#Time:2018-10-08</br>
#===================</br>
# 文件md5验证</br>
#</br>
# 如要验证文件,请手动添加、删除、修改demoFile目录下的子目录及文件</br>
#</br>
# 如要查看文件遍历结果，<a href="demo.php">【点击此处,查看文件遍历】</a></br>
#</br>
EOF;
    if(file_exists($cvf)){
        $diff = $verify->diff($cvf);
        //print_r($diff);
        //============================
        echo '=====目录=====</br>'.
             '+增加</br>';
        $num = 1;
        foreach ($diff['path']['plus'] as $plus){
            echo $num.'--'.$plus.'</br>';
            $num ++;
        }
        echo '--------------</br>'.
             '-减少</br>';
        $num = 1;
        foreach ($diff['path']['less'] as $less){
            echo $num.'--'.$less.'</br>';
            $num ++;
        }
        //=============================
        echo '=====文件=====</br>'.
            '+增加</br>';
        foreach ($diff['file']['plus'] as $plus){
            echo $num.'--'.$plus.'</br>';
            $num ++;
        }
        echo '--------------</br>'.
            '-减少</br>';
        foreach ($diff['file']['less'] as $less){
            echo $num.'--'.$less.'</br>';
            $num ++;
        }
        echo '=====变动=====</br>';
        $num = 1;
        foreach ($diff['change'] as $change){
            echo $num.'--'.$change.'</br>';
            $num ++;
        }
        echo '|</br>'.
             '|</br>';
        echo '# 如要重新生成demoFile.cvf，<a href="demo.php?action=up">'.
             '【点击此处重新生成demoFile.cvf】</a></br>';
        //============================
    }else{
        echo 'cvf文件'.$cvf.'不存在，<a href="demo.php">【点击此处生成】</a>';
    }
}elseif(isset($_GET['action']) && $_GET['action'] == 'up'){
    $result = $verify->cvfMake($cvf);
    if($result){
        echo $cvf.'保存成功~<a href="demo.php"><<<【点击返回】</a>';
    }else{
        echo $cvf.'保存失败<a href="demo.php"><<<【点击返回】</a>';
    }
}else{
print <<<EOF
#====================</br>
#cmsVerify-core/demo</br>
#</br>
#Powered-By-guiker</br>
#</br>
#Time:2018-10-08</br>
#===================</br>
# 此demo输出demoFile目录下的所有子目录、文件,已经文件md5的遍历结果</br>
#</br>
#并生成demoFile.cvf文件</br>
#</br>
# 如要验证文件,请手动添加、删除、修改demoFile目录下的子目录及文件</br>
#</br>
#  然后<a href="demo.php?action=diff">>>点击此处进行验证<<</a></br>
#</br>
EOF;
    echo '=====目录=====</br>';
    $num = 1;
    foreach ($verify->file()['path'] as $path){
        echo $num.'--'.$path.'</br>';
        $num ++;
    }
    echo '=====文件=====</br>';
    $num = 1;
    foreach ($verify->file()['file'] as $file){
        echo $num.'--'.$file.'</br>';
        $num ++;
    }
    echo '===文件md5===</br>';
    $num = 1;
    foreach ($verify->md5Verify() as $file => $md5){
        echo $num.'--'.$file.'>>>'.$md5.'</br>';
        $num ++;
    }
    echo '===cvf文件===</br>';
    if(file_exists($cvf)){
        echo 'cvf文件'.$cvf.'已生成过了<a href="demo.php?action=up">【点击此处重新生成】</a>';
    }else{
        $result = $verify->cvfMake($cvf);
        if($result){
            echo $cvf.'保存成功';
        }else{
            echo $cvf.'保存失败';
        }
    }
}
//print_r($a->md5Verity());
//print_r($a->cvt);
//$result = ['info'=>[],'md5'=>[]];
//$result['md5'] = $a->md5Verity();
//print_r($a->cvt());
//$encode = $a->cvtMake('demo.cvt');
//if($encode){
    //echo '保存成功';
//}else{
    //echo '保存失败';
//}
//print_r($a->cvtDecode($encode));
//print_r($a->cvtDiff('/1.cvt'));
?>