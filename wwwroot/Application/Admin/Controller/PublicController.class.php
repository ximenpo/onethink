<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;
use Admin\Model\AuthRuleModel;

/**
* 后台首页控制器
* @author 麦当苗儿 <zuojiazi@vip.qq.com>
*/
class PublicController extends \Think\Controller {

    protected function _initialize(){
        /* 读取数据库中的配置 */
        $config	=	S('DB_CONFIG_DATA');
        if(!$config){
            $config	=	D('Config')->lists();
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
    }

    /**
    * 后台用户登录
    * @author 麦当苗儿 <zuojiazi@vip.qq.com>
    */
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
            /* 调用UC登录接口登录 */
            $User = new UserApi;

            switch(C('ADMIN_LOGIN_EXTRA_VERIFY', null, 1)){
                case 1:
                if(!check_verify($verify)){
                    $this->error('验证码输入错误！');
                }
                break;
                case 2:
                if(!$User->verifyOTP($username, $verify, true)){
                    $this->error('动态验证码输入错误！');
                }
                break;
                case 3:
                if(!$User->verifyGeetest()){
                    $this->error('验证码错误！');
                }
                break;
            }

            $uid = $User->login($username, $password);
            if(0 < $uid){ //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    $url_redirect   = U('Index/index');
                    if(!is_administrator($uid)){
                        eval('namespace Admin\Controller;
                        class AuthHack extends \Think\Auth{
                            public function getAuthList($uid, $type){
                                return   parent::getAuthList($uid, $type);
                            }
                        }');
                        $Auth   =   new AuthHack();
                        if(!$Auth->check(MODULE_NAME.'/Index/index', $uid, C('AUTH_CONFIG.AUTH_TYPE'))){
                            $authlist   = $Auth->getAuthList($uid, C('AUTH_CONFIG.AUTH_TYPE'));
                            if(!empty($authlist)){
                                $url_redirect   = U(str_ireplace(MODULE_NAME.'/', '', $authlist[0]));
                            }
                        }
                    }
                    $this->success('登录成功！', $url_redirect);
                } else {
                    $this->error($Member->getError());
                }

            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        } else {
            if(is_login()){
                $this->redirect('Index/index');
            }else{
                $this->display();
            }
        }
    }

    /* 退出登录 */
    public function logout(){
        if(is_login()){
            D('Member')->logout();
            session('[destroy]');
            $this->success('退出成功！', U('login'));
        } else {
            $this->redirect('login');
        }
    }

    public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }

    public function geetestInit(){
        if(C('ADMIN_LOGIN_EXTRA_VERIFY') == 3){
            exit((new UserApi)->initGeetest());
        }

		/* 返回JSON数据 */
		$this->ajaxReturn();
    }

}
