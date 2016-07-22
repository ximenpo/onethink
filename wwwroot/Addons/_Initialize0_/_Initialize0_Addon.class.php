<?php

namespace Addons\_Initialize0_;
use Common\Controller\Addon;

/**
* simple-onethink 环境初始化插件
* @author ximenpo <ximenpo@jiandan.ren>
*/
class _Initialize0_Addon extends Addon{

    public $info = array(
        'name'          => '_Initialize0_',
        'title'         => 'simple-onethink 环境初始化',
        'description'   => '初始化 simple-onethink 用到的数据及环境',
        'status'        => 1,
        'author'        => 'ximenpo <ximenpo@jiandan.ren>',
        'version'       => '0.1'
    );

    public function install(){
        if(!install_execute_sqlfile(__DIR__ . '/install.sql')){
            return  false;
        }

        $Member = D('Admin/Member');
        $Member->login(1);
        if(!is_login() || !is_administrator()){
            return  false;
        }
        $Member->logout();
        return  true;
    }

    public function uninstall(){
        return  true;
    }
}
