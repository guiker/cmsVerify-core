<?php
class cmsVerity{
    // 系统路径
    var $osPath = '';
    // cvfPath
    var $cvfPath = array();
    // cvf文件路径
    var $cvf = '';
    var $md5 = array();
    // 文件过滤变量
    var $filter = array('path' => array(),'file' => array());
    // 文件列表变量
    var $fileList = array('path' => array(),'file' => array());
    // 构造函数
    function __construct($cvf=''){
        // 初始化系统路径
        $this->osPath = dirname(__FILE__);
        // 加载验证文件
        if($cvf != ''){
            $this->cvf = $cvf;
        }
    }
    /* 读取文件信息*/
    public function readFile($dir='',$filter = array()){
        // 初始化过滤器配置
        if(isset($filter['path'])) $this->filter['path'] = $filter['path'];
        if(isset($filter['file'])) $this->filter['file'] = $filter['file'];
        // 开始遍历文件及文件夹
        if(!@is_dir($dir) || $dir == '') $dir = $this->osPath;
        $handle = opendir($dir);
        if($handle){
            while(($file = readdir($handle)) !== false ){
                $temp = $dir.DIRECTORY_SEPARATOR.$file;
                if($file != '.' && $file != '..'){
                    if(is_dir($temp)){
                        // 去除系统路径
                        $data = str_replace($this->osPath,'',$temp);
                        // 过滤器匹配
                        if($this->filterMatch($data,'PATH')){
                            // 写入数组
                            array_push($this->fileList['path'],$this->separator($data));
                        }
                        // 循环
                        $this->readFile($temp);
                    }else{
                        // 去除系统路径
                        $data = str_replace($this->osPath,'',$temp);
                        // 过滤器匹配，并去除本程序文件
                        if($this->filterMatch($data,'FILE') && $data != DIRECTORY_SEPARATOR.basename(__FILE__)){
                            // 写入数组
                            array_push($this->fileList['file'],$this->separator($data));
                        }
                    }
                }
            }
            
            
        }
        closedir($handle);
        
    }
    /* MD5验证 */
    public function md5Verify(){
        $fileMd5 = array();
        if($this->fileList['file'] != null){
            foreach ($this->fileList['file'] as $file){
                $md5 = md5_file(dirname(__FILE__).$file);
                $fileMd5[$file] = $md5;
            }
        }else{
            echo '~ 请载入cvf文件 ~';
        }
        $this->md5 = $fileMd5;
        return $fileMd5;
    }
    /* 调用文件列表，过滤cvf文件*/
    public function file(){
        if($this->fileList['path'] != null || $this->fileList['file'] != null){
            //
            $this->fileList['file'] = array_values(array_diff($this->fileList['file'],array_keys($this->cvfPath)));
            return $this->fileList;
        }else{
            return false;
        }
    }
    /* 调用cvf */
    public function cvf(){
        return $this->cvfPath;
    }
    /* 对比*/
    public function diff($cvfile = ''){
        $diff = array();
        if($cvfile != ''){
            $cvf = $this->cvfRead($cvfile);
            $file = $this->file();
            if($file['path'] == null && $file['file'] == null){
                $this->readFile('',$cvf['filter']);
                $this->diff($cvfile);
            }else{
                if($this->md5 == null) $this->md5Verify();
                /***************************/
                function comp($file,$cvf,$type){
                    $cvf = $cvf['info'];
                    $data = array('plus'=>array(),'less'=>array());
                    // PLUS
                    foreach ($file[$type] as $key => $path){
                        if(in_array($path,$cvf[$type]) == false){
                            array_push($data['plus'],$path);
                        }
                    }
                    foreach ($cvf[$type] as $key => $path){
                        if(in_array($path,$file[$type]) == false){
                            array_push($data['less'],$path);
                        }
                    }
                    return $data;
                }
                /***************/
                
                $diff['path'] = comp($file,$cvf,'path');
                $diff['file'] = comp($file,$cvf,'file');
                $diff['change'] = array_values(array_keys(array_diff($this->md5,$cvf['md5'])));
                // 去除因为文件增加而多出的change
                foreach ($diff['change'] as $key=>$value){
                    if(in_array($value,$diff['file']['plus'])){
                        unset($diff['change'][$key]);
                    }
                }
                return $diff;
            }
            
        }else{
            if($this->cvf != ''){
                $this->diff($this->cvf);
            }else{
                echo '~ diff()需要参数 ~';
                return false;
            }
        }
        
    }
    /* 读取cvf文件，解码并序列化*/
    public function cvfRead($cvf = ''){
        if($cvf != ''){
            $cvf = $this->separator($this->osPath).'/'.$cvf;
            if(file_exists($cvf)){
                $cvf = file_get_contents($cvf);
                return $this->cvfDecode($cvf);
            }else{
                echo '~ cvf文件不存在，请检查路径  ~';
                return false;
            }
        }else{
            echo '~ 请输入cvf路径  ~';
        }
    }
    /* 解码并序列化cvf */
    public function cvfDecode($cvfData){
        return unserialize(gzinflate(base64_decode($cvfData)));
    }
    /* 制作cvf */
    public function cvfMake($file = ''){
        $data = array('info'=>array(),'md5'=>array(),'filter' => array());
        $data['info'] = $this->file();
        $data['md5'] = $this->md5 == null ? $this->md5Verify() : $this->md5;
        $data['filter'] = $this->filter;
        $cvfEncode = base64_encode(gzdeflate(serialize($data)));
        if($file != ''){
            $file = $this->separator($this->osPath).'/'.$file;
            if(file_put_contents($file,$cvfEncode)){
                return true;
            }else{
                return false;
            }
        }else{
            return $cvfEncode;
        }
    }
    /* 过滤器 */
    private function filterMatch($data,$type){
        // 文件夹过滤
        if($type == 'PATH' && $this->filter['path'] != null){
            $fileag = true;
            foreach ($this->filter['path'] as $filter){
                $match = '{^'.$filter.'}';
                if(preg_match($match,$this->separator($data))){
                    $fileag = false;
                }
            }
            return $fileag;
            // 文件过滤
        }elseif($type == 'FILE' && $this->filter['file'] != null){
            $fileag = true;
            foreach ($this->filter['path'] as $filter){
                $match = '{^'.$filter.'}';
                if(preg_match($match,$this->separator($data))){
                    $fileag = false;
                }
            }
            return in_array($this->separator($data),$this->filter['file'])||$fileag == false ? false : true;
        }else{
            return true;
        }
    }
    // 根据windows和linux转换路径分隔符
    private function separator($path){
        return DIRECTORY_SEPARATOR == '/' ? $path : str_replace('\\','/',$path);
    }
    // 加载验证文件目录
    private function cvfLoad($path){
        $files = array();
        if (is_dir($path)){
            $handle = opendir($path);
            while ($file = readdir($handle)) {
                if($file != '.' && $file != '..'){
                    $type = strtolower(substr(strrchr($file,'.'),1));
                    if ($type == 'cvf'){
                        $files[DIRECTORY_SEPARATOR.$file] = $this->osPath.DIRECTORY_SEPARATOR.$file;
                    }
                }
            }
            closedir($handle);
            ksort($files);
        }
        return $files;
    }
    /*END*/
}
?>