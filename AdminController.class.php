<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “用户”控制器类。
 */ 
class AdminController extends CommonController {
    /**
     * 登录。
     */
    public function login() {
        $name = I('username', -1);
        $pwd = I('password', -1);

        $adminModel = M('Admin');

        $condition = "name='".$name."' and password='".$pwd."'";
        $list = $adminModel->where($condition)->select();
        if($list){
			$code = 0;
			$msg = "suc";
			session('uname', $name);
			session('user_id', $list[0]['user_id']);
		}else{
            $list = Array();
			$code = 20401;
			$msg = $name." or ".$pwd." is wrong";
		}
        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }

    public function getUserid() {
        $user_id = $this->isLogin();
        if($user_id){
            $this->ajaxOutput(0, 'suc', array('user_id'=>$user_id, 'list'=>Array()));
        }else{
            $this->ajaxOutput(20401, 'unlogin', array('user_id'=>$user_id, 'list'=>Array()));
        }
    }

    public function getManagerid() {
        $user_id = $this->isLogin();
        if(!$user_id){
            $this->ajaxOutput(20401, 'unlogin', array('user_id'=>$user_id, 'list'=>Array()));
        }

        $adminModel = M('Admin');
        $condition = "1=1 and user_id='".$user_id."'";
        $list = $adminModel->where($condition)->select();
        if($list){
            if($list[0]['level'] > 1){
                $this->ajaxOutput(0, 'suc', array('user_id'=>$user_id, 'list'=>Array()));
            }else{
                $this->ajaxOutput(20402, 'user limited', array('user_id'=>$user_id, 'list'=>Array()));
            }
        }else{
            $list = Array();
            $code = 10001;
            $msg = "sql fail!";
            $this->ajaxOutput(20401, 'unlogin', array('user_id'=>$user_id, 'list'=>Array()));
        }
    }

    public function getUser() {
        $adminModel = M('Admin');
        $condition = "1=1 and (level='1' or level='2' or level='3')";
        $list = $adminModel->where($condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";
        }else{
            $list = Array();
            $code = 10001;
            $msg = "sql fail!";
        }
        $this->ajaxOutput($code, $msg, array('list'=>$list));
    }
}
