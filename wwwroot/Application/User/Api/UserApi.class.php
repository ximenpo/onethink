<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace User\Api;
use User\Api\Api;
use User\Model\UcenterMemberModel;

class UserApi extends Api{
    /**
     * 构造方法，实例化操作模型
     */
    protected function _init(){
        $this->model = new UcenterMemberModel();
    }

    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @param  string $email    用户邮箱
     * @param  string $mobile   用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function register($username, $password, $email, $mobile = ''){
        return $this->model->register($username, $password, $email, $mobile);
    }

    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function login($username, $password, $type = 1){
        return $this->model->login($username, $password, $type);
    }

    /**
     * 获取用户信息
     * @param  string  $uid         用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function info($uid, $is_username = false){
        return $this->model->info($uid, $is_username);
    }

    /**
     *  验证动态密码
     * @param  integer $uid 用户id
     * @param  integer $code 验证码
     * @param  boolean $is_username 是否使用用户名查询
     * @return boolean     检测结果
     * @author ximenpo <ximenpo@jiandan.ren>
     */
    public function verifyOTP($uid, $code, $is_username = false){
        return $this->model->verifyOTP($uid, $code, $is_username);
    }

    /**
     *  重置动态密码的密钥
     * @param  integer $uid 用户id
     * @return 新生成的Seed（失败为空）
     * @author ximenpo <ximenpo@jiandan.ren>
     */
    public function resetOTPSeed($uid){
        return  $this->model->resetOTPSeed($uid);
    }

    /**
     *  极验验证 初始化
     *
     * @return bool 检测结果
     *
     * @author ximenpo <ximenpo@jiandan.ren>
     */
    public function initGeetest()
    {
        vendor('Geetestlib');
        $GtSdk = new \GeetestLib(C('GEETEST_CAPTCHA_ID'), C('GEETEST_PRIVATE_KEY'));
        $_SESSION['GEETEST_server_status'] = $GtSdk->pre_process();
        return  $GtSdk->get_response_str();
    }

    /**
     *  极验验证 验证
     *
     * @return bool 检测结果
     *
     * @author ximenpo <ximenpo@jiandan.ren>
     */
    public function verifyGeetest()
    {
        vendor('Geetestlib');
        $GtSdk = new \GeetestLib(C('GEETEST_CAPTCHA_ID'), C('GEETEST_PRIVATE_KEY'));
        if ($_SESSION['GEETEST_server_status'] == 1) {
            return  $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode']);
        }else{
            return  $GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode']);
        }
    }

    /**
     * 检测用户名
     * @param  string  $field  用户名
     * @return integer         错误编号
     */
    public function checkUsername($username){
        return $this->model->checkField($username, 1);
    }

    /**
     * 检测邮箱
     * @param  string  $email  邮箱
     * @return integer         错误编号
     */
    public function checkEmail($email){
        return $this->model->checkField($email, 2);
    }

    /**
     * 检测手机
     * @param  string  $mobile  手机
     * @return integer         错误编号
     */
    public function checkMobile($mobile){
        return $this->model->checkField($mobile, 3);
    }

    /**
     * 更新用户信息
     * @param int $uid 用户id
     * @param string $password 密码，用来验证
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author huajie <banhuajie@163.com>
     */
    public function updateInfo($uid, $password, $data){
        if($this->model->updateUserFields($uid, $password, $data) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $return['info'] = $this->model->getError();
        }
        return $return;
    }

}
