<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class FundManagementController extends CommonController {
    
    //根据提款记录的状态筛选
    public function getFundList(){
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $type = I('param.type',-1);
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        //$ctime = I('param.ctime',date('Y-m-d'));
        //$ctimeend = I('param.ctimeend',date('Y-m-d'));

        $Model = M('FundManagement');
        $ModelUser = M('User');
        
        $pageName = I('param.pagename',-1);
        if($pageName != "-1"){
            $is_allow = pageAuthority($pageName, $res_isLogin);
        }


        $condition = "1=1";
        if($type != "-1"){
          $condition = $condition." and type='".$type."'";
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

          $num = count($list);
          for($i=0; $i<$num; $i++){
            $condition = " id=".$list[$i]['user_id'];
            $listTemp = $ModelUser->where($condition)->select();
            if($listTemp){
              $list[$i]['user_name'] = $listTemp[0]['name'];
            }
          }

        }else{
          $code = -1;
          $msg = "sql failed";
        }

        if($is_allow == "1"){
            $this->ajaxOutput(0, $msg, array('count'=>$allnum, 'list'=>$list));
        }else{
            $this->ajaxOutput(20402, 'user limited', array('count'=>$allnum, 'list'=>Array()));
        }
    }

    //搜索提款记录
    public function searchUserFundInfo() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        $Model = M('FundManagement');
        $ModelUser = M('User');

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $title = I('param.title',-1);
        $this->condition = "1=1";
        if($title != -1){
            $this->condition = $this->condition." and name like '%".$title."%'";
        }

//        echo $this->condition;
        $list = $ModelUser->where($this->condition)->select();
        if($list){
            $code = 0;
            $msg = "suc";
            $num = count($list);
            $tag = 0;
            for($i=0; $i<$num; $i++){
              $this->condition = " user_id='".$list[$i]['id']."'";
              $listTemp = $Model->where($this->condition)->select();
              for($j=0; $j<count($listTemp); $j++){
                $listTemp[$j]['user_name'] = $list[$i]['name'];
                $listData[$tag] = $listTemp[$j];
                $tag = $tag + 1;
              }
            }
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($listData), 'list'=>$listData));
    }    

}
?>
