<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class DrawMoneyController extends CommonController {
    
    //根据提款记录的状态筛选
    public function getDrawMoneyList(){
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $pageName = I('param.pagename',-1);
        if($pageName != "-1"){
            $is_allow = pageAuthority($pageName, $res_isLogin);
            if($is_allow != "1"){
                $this->ajaxOutput(20402, "limit ", array('list'=>Array()));
            }
        }

        $state = I('param.state',-1);
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $ctime = I('param.ctime',date('Y-m-d'));
        $ctimeend = I('param.ctimeend',date('Y-m-d'));

        $Model = M('DrawMoney');

        $condition = "1=1";
        if($state != "-1"){
          $condition = $condition." and state='".$state."'";
        }

        if($ctime){
          $condition = $condition." and ctime>='".$ctime."'";
        }

        if($ctimeend){
          $condition = $condition." and ctime<='".$ctimeend." 23:59:59'";
        }

        $allnum = $Model->where($condition)->count();

        $list = $Model->where($condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
        if($list || $list == null){
          $code = 0;
          $msg = "suc";
          if($list == null){
            $list = array();
          }
        }else{
          $code = -1;
          $msg = "sql failed";
        }
        $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));
    }

    //搜索提款记录
    public function searchUserDrawMoneyInfo() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        $Model = M('DrawMoney');

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $title = I('param.title',-1);
        $this->condition = "1=1";
        if($title != -1){
            $this->condition = $this->condition." and user_name like '%".$title."%'";
        }

//        echo $this->condition;
        $list = $Model->where($this->condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }    
 
    //审核提款记录
    public function auditDrawMoneyRecord(){
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $state = I('param.state',-1);
        $uid = I('param.id',-1);
        
        if($uid){
          $condition = " id='".$uid."'";
        }

        $data['state'] = $state;
        $Model = M('DrawMoney');

        //未审核不可汇出操作
        if($state == "3"){
          $list = $Model->where($condition)->select();
          if($list && $list[0]['state'] != "1"){
            $code = 20501;      
            $msg = "no audit";
          }else{
            $code = -1;
            $msg = "audit sql failed";
          }
          $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
        }else{
          $list = $Model->where($condition)->save($data);
          if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if($list == null){
              $list = array();
            }

          }else{
            $code = -1;
            $msg = "audit sql failed";
          }
          $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
        }
    }

}
?>
