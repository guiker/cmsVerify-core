# cmsVerify-core Beta
## —— cmsVerify核心文件 ——
---

> ### 为什么会出现这个程序？
因为很多CMS程序因为WebShell漏洞被挂马以后，会被上传各种页面程序，还不止一两个，很多子目录里面还会有很多个。靠人工一个个删又不很不现实，整站重新上传代码似乎也很麻烦。更有甚者会在cms文件里面添加自己的恶意代码，这样查起来就更加麻烦了。
那么，有没有一种办法可以快速又有效的验证cms文件是否有被更改和快速的找出那些目录里面有多余的文件呢？
这个嘛......这个就是我打算开发cmsVerify的初衷。cmsVerity-core是cmsVerify的核心文件，我会在此基础上开发相应的UI。

> ### 此核心程序可否单独使用呢？
程序里已包含demo文件，可以直接使用，也可以在此基础上自行开发UI。

> ### 如何使用呢？
我们就拿demo.php文件做说明，配合使用的还有/demoFile目录下的文件。
1. #### 首先需要加注cmsVerity-core文件。
   ```php
   // 加载cmsVerity-core.php
   require_once('cmsVerify-core.php');
   ```

2. #### 需要一个过滤列表，过滤列表里的目录及文件不会不会包含在结果中。
   在实际应用当中，可以把cms的upload/等一些数据目录进行过滤，可以避免不必要的麻烦。在例子中，我们就以demoFile/目录下，带有filter字样的目录和文件夹为例。
   <p style="color:red">【注意】这里$filter数组的固定格式，'path'数组为目录路径，'file'为文件路径。</p>
   <p style="color:blue">【注意】如果读取的文件运行的demo.php文件，也就是basename(__FILE__)文件会自动过滤掉，不会包含。如果cmsVerify-core.php在读取文件的同一目录，请手动过滤</p>

   ```php
   // 过滤列表
   $filter = array(
    				'path'=>array('/demoFile/filter_1',
        	                      '/demoFile/filter_2'),
    				'file'=>array('/demoFile/filter.php',
   							'/demoFile/test_1/test_1_filter.php',));
   ```

3. #### 实例化cmsVerity()，并读取文件
   ```php
   // 读取文件
   // readFile(目录,过滤器);
   $verify->readFile(dirname(__FILE__).'/demoFile',$filter);
   ```

4. #### 获取文件数据列表
   这里获取文件数据列表有两种方法，一种是调用fileList变量，一种是file()方法。

   <p style="color:green">【注意】不建议用fileList变量，因为这是没有被过滤器过滤的列表</p>
   ```php
   // 未过滤数据
   $fileList_noFilter = $verify->fileList;
   // 已过滤的期望数据
   $fileList = $verify->file();
   ```

   ##### 我们将返回的数据打印出来
   ```php
   Array (
       [path] => Array([0] => /demoFile/test
                       [1] => /demoFile/test_1
                       [2] => /demoFile/test_2)
       [file] => Array([0] => /demoFile/test_1/test_1_1.php
                       [1] => /demoFile/test_1/test_1_2.php
                       [2] => /demoFile/test_1.php
                       [3] => /demoFile/test_2/test_2_1.php[4] => /demoFile/test_2.php) )
   ```
   可以发现，返回的是一个数组，数据是已经被过滤器过滤以后的。
   细心的童鞋应该发现了，这里没有md5数据啊，我们怎么做文件的验证呢？

   <p style="color:green">别心急，因为大量的文件进行md5还是比较消耗资源的，将列表获取和md5获取分成两个方法，可以满足不同需求。</p>

5. #### md5验证数据
   ```php
   // md5验证数据
   $md5 = $verify->md5Verify();
   ```

   ##### 我们也将返回的md5数据打印出来
   ```php
   Array (
   [/demoFile/test_1/test_1_1.php] => d2bf1c9b62ac926c59d2b6f211fa9f67
   [/demoFile/test_1/test_1_2.php] => 0e9849dcbb5a1c28f8b44577cdab3c58
   [/demoFile/test_1.php]          => 3aa46de489a581d2cc49a717c335d9f7
   [/demoFile/test_2/test_2_1.php] => b07a2ac5e9fedad5282a026e2548d371
   [/demoFile/test_2.php]          => 2f338062cea2d9b81ae8e39745b821b6 ) 
   ```
   不难看出，数据的键就是文件名，值就是文件的md5。

6. #### 生成cvf文件
   cvf全称为CMS Verify File(CMS验证文件)
   ```php
   // 生成成功将返回true，失败返回false。
   // $cvf为生成的文件名，默认目录为dirname(__FILE__)
   $verify->md5Make($cvf);
   ```

7. ### 文件验证
   哈哈，激动人心的时刻到来啦，最后一步，文件验证的环节到啦~！
   ```php
   // $cvf 为cvf文件路径
   $diff = $verify->diff($cvf)
   ```

   返回的数据依然是一个数组
   ```php
   // 数据没有改变的数组为空
   Array (
       // 被 增 删 的目录数组
       [path] => Array(
           // 增加的目录
           [plus] => Array()
           // 减少的目录
           [less] => Array())
       // 被 增 删 的文件数据
       [file] => Array(
           // 增加的文件
           [plus] => Array()
           // 减少的文件
           [less] => Array())
       // md5不匹配，被更改的文件
       [change] => Array() ) 
   ```

8. #### 生成cvf完整代码

   ```php
   <?php
   // 加载cmsVerify-core
   require_once('cmsVerify-core.php');
   // cvf文件名称
   $cvf = 'demoFile.cvf';
   // 过滤列表
   $filter = array(
       'path'=>array('/demoFile/filter_1','/demoFile/filter_2'),
   'file'=>array('/demoFile/filter.php','/demoFile/test_1/test_1_filter.php',));
   // 实例化cmsVerity
   $verify = new cmsVerity();
   // 读取数据
   $verify->readFile(dirname(__FILE__).'/demoFile',$filter);
   // 生成cvf
   $verify->cvfMake($cvf);
   ?>
   ```

9. #### 文件验证完整代码
   ```php
   <?php
   // 加载cmsVerify-core
   require_once('cmsVerify-core.php');
   // cvf文件名称
   $cvf = 'demoFile.cvf';    
   // 实例化cmsVerity，并直接传入CVF
   $verify = new cmsVerity($cvf);    
   // 文件验证
   $verify->Diff($cvf);
   ?>
   ```

10. ####  还存在的问题

    1. 代码逻辑有待改善
    2. 新的功能
> ### 日志

2018-10-08 上传 Version : 0.1.0 Beta



