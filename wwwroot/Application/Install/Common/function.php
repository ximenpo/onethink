<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

// 检测环境是否支持可写
define('IS_WRITE', APP_MODE !== 'sae');

/**
 * 系统环境检测
 * @return array 系统环境数据
 */
function check_env()
{
    $items = array(
        'os'     => array('操作系统', '不限制', '类Unix', PHP_OS, 'success'),
        'php'    => array('PHP版本', '5.3', '5.3+', PHP_VERSION, 'success'),
        'upload' => array('附件上传', '不限制', '2M+', '未知', 'success'),
        'gd'     => array('GD库', '2.0', '2.0+', '未知', 'success'),
        'disk'   => array('磁盘空间', '5M', '不限制', '未知', 'success'),
    );

    //PHP环境检测
    if ($items['php'][3] < $items['php'][1]) {
        $items['php'][4] = 'error';
        session('error', true);
    }

    //附件上传检测
    if (@ini_get('file_uploads')) {
        $items['upload'][3] = ini_get('upload_max_filesize');
    }

    //GD库检测
    $tmp = function_exists('gd_info') ? gd_info() : array();
    if (empty($tmp['GD Version'])) {
        $items['gd'][3] = '未安装';
        $items['gd'][4] = 'error';
        session('error', true);
    } else {
        $items['gd'][3] = $tmp['GD Version'];
    }
    unset($tmp);

    //磁盘空间检测
    if (function_exists('disk_free_space')) {
        $items['disk'][3] = floor(disk_free_space(INSTALL_APP_PATH) / (1024 * 1024)) . 'M';
    }

    return $items;
}

/**
 * 目录，文件读写检测
 * @return array 检测数据
 */
function check_dirfile()
{
    $items = array(
        array('dir', '可写', 'success', './Uploads/Download'),
        array('dir', '可写', 'success', './Uploads/Picture'),
        array('dir', '可写', 'success', './Uploads/Editor'),
        array('dir', '可写', 'success', './Runtime'),
        array('dir', '可写', 'success', './Data'),
        array('dir', '可写', 'success', './Application/User/Conf'),
        array('file', '可写', 'success', './Application/Common/Conf'),

    );

    foreach ($items as &$val) {
        $item = INSTALL_APP_PATH . $val[3];
        if ('dir' == $val[0]) {
            if (!is_writable($item)) {
                if (is_dir($items)) {
                    $val[1] = '可读';
                    $val[2] = 'error';
                    session('error', true);
                } else {
                    $val[1] = '不存在';
                    $val[2] = 'error';
                    session('error', true);
                }
            }
        } else {
            if (file_exists($item)) {
                if (!is_writable($item)) {
                    $val[1] = '不可写';
                    $val[2] = 'error';
                    session('error', true);
                }
            } else {
                if (!is_writable(dirname($item))) {
                    $val[1] = '不存在';
                    $val[2] = 'error';
                    session('error', true);
                }
            }
        }
    }

    return $items;
}

/**
 * 函数检测
 * @return array 检测数据
 */
function check_func()
{
    $items = array(
        array('pdo', '支持', 'success', '类'),
        array('pdo_mysql', '支持', 'success', '模块'),
        array('file_get_contents', '支持', 'success', '函数'),
        array('mb_strlen', '支持', 'success', '函数'),
    );

    foreach ($items as &$val) {
        if (('类' == $val[3] && !class_exists($val[0]))
            || ('模块' == $val[3] && !extension_loaded($val[0]))
            || ('函数' == $val[3] && !function_exists($val[0]))
        ) {
            $val[1] = '不支持';
            $val[2] = 'error';
            session('error', true);
        }
    }

    return $items;
}

/**
 * 写入配置文件
 * @param  array $config 配置信息
 */
function write_config($config, $auth)
{
    if (is_array($config)) {
        //读取配置内容
        $conf = file_get_contents(MODULE_PATH . 'Data/conf.tpl');
        $user = file_get_contents(MODULE_PATH . 'Data/user.tpl');
        //替换配置项
        foreach ($config as $name => $value) {
            $conf = str_replace("[{$name}]", $value, $conf);
            $user = str_replace("[{$name}]", $value, $user);
        }

        $conf = str_replace('[AUTH_KEY]', $auth, $conf);
        $user = str_replace('[AUTH_KEY]', $auth, $user);

        //写入应用配置文件
        if (!IS_WRITE) {
            return '由于您的环境不可写，请复制下面的配置文件内容覆盖到相关的配置文件，然后再登录后台。<p>' . realpath(APP_PATH) . '/Common/Conf/config.php</p>
            <textarea name="" style="width:650px;height:185px">' . $conf . '</textarea>
            <p>' . realpath(APP_PATH) . '/User/Conf/config.php</p>
            <textarea name="" style="width:650px;height:125px">' . $user . '</textarea>';
        } else {
            if (file_put_contents(APP_PATH . 'Common/Conf/config.php', $conf) &&
                file_put_contents(APP_PATH . 'User/Conf/config.php', $user)) {
                show_msg('配置文件写入成功');
            } else {
                show_msg('配置文件写入失败！', 'error');
                session('error', true);
            }
            return '';
        }

    }
}

/**
 * 创建数据表
 * @param  resource $db 数据库连接资源
 */
function create_tables($db, $prefix = '')
{
    //读取SQL文件
    $sql = file_get_contents(MODULE_PATH . 'Data/install.sql');
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    //替换表前缀
    $orginal = C('ORIGINAL_TABLE_PREFIX');
    $sql     = str_replace(" `{$orginal}", " `{$prefix}", $sql);

    //开始安装
    show_msg('开始安装数据库...');
    foreach ($sql as $value) {
        $value = trim($value);
        if (empty($value)) {
            continue;
        }

        if (substr($value, 0, 12) == 'CREATE TABLE') {
            $name = preg_replace("/^CREATE TABLE `(\w+)` .*/s", "\\1", $value);
            $msg  = "创建数据表{$name}";
            if (false !== $db->execute($value)) {
                show_msg($msg . '...成功');
            } else {
                show_msg($msg . '...失败！', 'error');
                session('error', true);
            }
        } else {
            $db->execute($value);
        }

    }
}

function register_administrator($db, $prefix, $admin, $auth)
{
    show_msg('开始注册创始人帐号...');
    $sql = "INSERT INTO `[PREFIX]ucenter_member` VALUES " .
    "('1', '[NAME]', '[PASS]', '[EMAIL]', '', '[TIME]', '[IP]', 0, 0, '[TIME]', '1')";

    $password = user_md5($admin['password'], $auth);
    $sql      = str_replace(
        array('[PREFIX]', '[NAME]', '[PASS]', '[EMAIL]', '[TIME]', '[IP]'),
        array($prefix, $admin['username'], $password, $admin['email'], NOW_TIME, get_client_ip(1)),
        $sql);
    //执行sql
    $db->execute($sql);

    $sql = "INSERT INTO `[PREFIX]member` VALUES " .
    "('1', '[NAME]', '0', '0000-00-00', '', '0', '1', '0', '[TIME]', '0', '[TIME]', '1');";
    $sql = str_replace(
        array('[PREFIX]', '[NAME]', '[TIME]'),
        array($prefix, $admin['username'], NOW_TIME),
        $sql);
    $db->execute($sql);
    show_msg('创始人帐号注册完成！');
}

/**
 * 更新数据表
 * @param  resource $db 数据库连接资源
 * @author lyq <605415184@qq.com>
 */
function update_tables($db, $prefix = '')
{
    //读取SQL文件
    $sql = file_get_contents(MODULE_PATH . 'Data/update.sql');
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    //替换表前缀
    $sql = str_replace(" `onethink_", " `{$prefix}", $sql);

    //开始安装
    show_msg('开始升级数据库...');
    foreach ($sql as $value) {
        $value = trim($value);
        if (empty($value)) {
            continue;
        }

        if (substr($value, 0, 12) == 'CREATE TABLE') {
            $name = preg_replace("/^CREATE TABLE `(\w+)` .*/s", "\\1", $value);
            $msg  = "创建数据表{$name}";
            if (false !== $db->execute($value)) {
                show_msg($msg . '...成功');
            } else {
                show_msg($msg . '...失败！', 'error');
                session('error', true);
            }
        } else {
            if (substr($value, 0, 8) == 'UPDATE `') {
                $name = preg_replace("/^UPDATE `(\w+)` .*/s", "\\1", $value);
                $msg  = "更新数据表{$name}";
            } else if (substr($value, 0, 11) == 'ALTER TABLE') {
                $name = preg_replace("/^ALTER TABLE `(\w+)` .*/s", "\\1", $value);
                $msg  = "修改数据表{$name}";
            } else if (substr($value, 0, 11) == 'INSERT INTO') {
                $name = preg_replace("/^INSERT INTO `(\w+)` .*/s", "\\1", $value);
                $msg  = "写入数据表{$name}";
            }
            if (($db->execute($value)) !== false) {
                show_msg($msg . '...成功');
            } else {
                show_msg($msg . '...失败！', 'error');
                session('error', true);
            }
        }
    }
}

/**
 * 按顺序执行初始化插件(_OneThink_Initialize[0-9])
 * @return  true: succeed    false: error
 * @author ximenpo <ximenpo@jiandan.ren>
 */
function install_initialize_addons()
{
    if(empty(C('AUTOLOAD_NAMESPACE'))){
        C('AUTOLOAD_NAMESPACE', array('Addons' => ONETHINK_ADDON_PATH));
    }

    show_msg('检测初始化插件...');
    $result = true;
    spl_autoload_register('install_autoload_AdminModel');
    for($i = 0; $i < 10; ++$i){
        $addon_name  = "_Initialize{$i}_";
        $addon_class = get_addon_class($addon_name);
        if(!class_exists($addon_class)){
            continue;
        }

        $addons = new $addon_class;
        $info   = $addons->info;
        if(!$info || !$addons->checkInfo()){
            $result = false;
            show_msg("安装初始化插件{$addon_name}...失败: 信息缺失", 'error');
            break;
        }
        session('addons_install_error',null);
        if(!$addons->install()){
            $result = false;
            show_msg("安装初始化插件{$addon_name}...失败: ".session('addons_install_error'), 'error');
            break;
        }
        show_msg("安装初始化插件{$addon_name}...成功");
    }
    spl_autoload_unregister('install_autoload_AdminModel');

    if($result){
        show_msg('初始化插件安装完成！');
    }

    return  $result;
}

/**
 * 执行数据库脚本文件
 * @return  true: succeed    false: error
 * @author ximenpo <ximenpo@jiandan.ren>
 */
function install_execute_sqlfile($sqlfile)
{
    $dbcfg  = session('db_config');
    if(!$dbcfg){
        session('addons_install_error', '获取数据库配置失败');
        return  false;
    }
    $db     = Think\Db::getInstance($dbcfg);
    if(!$db){
        session('addons_install_error', '创建数据库对象失败');
        return  false;
    }
    $prefix = $dbcfg['DB_PREFIX'];

    //读取SQL文件
    $sql = file_get_contents($sqlfile);
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    //替换表前缀
    $orginal = C('ORIGINAL_TABLE_PREFIX');
    $sql     = str_replace(" `{$orginal}", " `{$prefix}", $sql);

    //开始安装
    foreach ($sql as $value) {
        $value = trim($value);
        if (empty($value)) {
            continue;
        }

        if ($db->execute($value) === false) {
            session('addons_install_error', '执行['.$value.']失败: '.($db->error()));
            return  false;
        }
    }

    return  true;
}

/**
 * 安装过程中自动加载对应的Admin模型类
 * @author ximenpo <ximenpo@jiandan.ren>
 * @return  true: succeed    false: error
 */
function    install_autoload_AdminModel($class_name){
    if(strstr($class_name, 'Install\\Model\\')){
        $class_admin    = str_replace('Install\\', 'Admin\\',   $class_name);
        $class          = str_replace('Install\\Model\\', '',   $class_name);
        $path           = APP_PATH.str_replace('\\', '/', $class_admin).'.class.php';
        if(is_file($path)){
            include_once    $path;
            return  eval("namespace Install\Model; class {$class} extends \\{$class_admin}{}");
        }
    }
}

/**
 * 及时显示提示信息
 * @param  string $msg 提示信息
 */
function show_msg($msg, $class = '')
{
    echo "<script type=\"text/javascript\">showmsg(\"{$msg}\", \"{$class}\")</script>";
    flush();
    ob_flush();
}

/**
 * 生成系统AUTH_KEY
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function build_auth_key()
{
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $chars .= '`~!@#$%^&*()_+-=[]{};:"|,.<>/?';
    $chars = str_shuffle($chars);
    return substr($chars, 0, 40);
}

/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 */
function user_md5($str, $key = '')
{
    return '' === $str ? '' : md5(sha1($str) . $key);
}
