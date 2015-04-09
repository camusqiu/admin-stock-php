<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “用户”控制器类。
 */ 
class UserController extends CommonController {
    /**
     * 登录。
     */
    public function login() {
        $this->display();
    }

    public function getUserInfo() {

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

        $orderby = I('param.order',-1);
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $this->condition = "1=1";
        $ctime = I('param.ctime',date('Y-m-d'));
        $ctimeend = I('param.ctimeend',date('Y-m-d'));
        $Model = M('User');	
        $ModelLogin = M('LoginStats');	
        $ModelPost = M('Post');	
        $ModelMoney = M('FundManagement'); 

		$conditionTemp = sprintf(" and ctime>'%s' and ctime<'%s'", $ctime, $ctimeend." 23:59:59");
		$this->condition = $this->condition.$conditionTemp;

		$numall = $Model->where($this->condition)->count();

        if ($orderby > 0) {
            $list = $Model->where($this->condition)->order('ctime desc')->select();
        }else{
            $list = $Model->where($this->condition)->order('ctime desc')->page($this->curpage, $this->pagenum)->select();
        }

    	$num = count($list);
        if($list){
        	$keyPost ="user_post_count_total";
        	$keyFans ="user_fans_count_total";
		    $redis = S(array('type'=>'Redis'));

        	for($i=0; $i < $num; $i++){
        		$listLogin = $ModelLogin->where("user_id='".$list[$i]['id']."'")->select();
        		if($listLogin){
        			$list[$i]['ip'] = $listLogin[0]['ip'];
        		}else{
        			$list[$i]['ip'] = "";
        		}

        		$listPost = $ModelPost->where("user_id='".$list[$i]['id']."'")->order("ctime desc")->limit(1)->select();
        		if($listPost){
        			$list[$i]['lastposttime'] = $listPost[0]['ctime'];
        		}else{
        			$list[$i]['lastposttime'] = "";
        		}

                $listMoney = $ModelMoney->where("user_id='".$list[$i]['id']."'")->order("ctime desc")->limit(1)->select();
                if($listMoney){
                    $list[$i]['balance'] = $listMoney[0]['balance'];
                }else{
                    $list[$i]['balance'] = 0.00;
                }

        		//用户粉丝数
        		$listTemp = $redis->ZSCORE($keyFans, $list[$i]['id']);
	            $list[$i]['fans'] = $listTemp ? $listTemp : 0;

	            //用户发帖总数
	            $listTemp = $redis->ZSCORE($keyPost, $list[$i]['id']);
	            $list[$i]['post'] = $listTemp ? $listTemp : 0;

        	}

        	$code = 0;
        	$msg = "suc";
        }else{
        	$code = 10001;
        	$msg = "search user fail";
        	$list = array();
        }


        if($orderby > 0){
            $this->sortUserInfo($list, $numall, $orderby, $code, $msg);
        }else{
            $this->ajaxOutput($code, $msg, array('count'=>$numall, 'list'=>$list));
        }

    }


    public function sortUserInfo($list, $numall, $value, $code, $msg){

        $listTemp = array();
        $num = count($list);
        if ($value == 1) {  //order by fans
            for ($i=0; $i < $num - 1; $i++) { 
                $max = $list[$i]['fans'];
                $listTemp = $list[$i];
                $tag = $i;
                for ($j=$i+1; $j < $num; $j++) { 
                    if ($list[$j]['fans'] > $max) {
                        $max = $list[$j]['fans'];
                        $list[$i] = $list[$j];
                        $tag = $j;
                    }
                }
                $list[$tag] = $listTemp;
            //echo "num: ".$num." i:".$i." tag:".$tag."|   ";
            //print_r($list[$i]);
            //print_r($list[$j]);
            }
        }else if($value == 2){  //order by post
            for ($i=0; $i < $num - 1; $i++) { 
                $max = $list[$i]['[post]'];
                $listTemp = $list[$i];
                $tag = $i;
                for ($j=$i+1; $j < $num; $j++) { 
                    if ($list[$j]['post'] > $max) {
                        $max = $list[$j]['post'];
                        $list[$i] = $list[$j];
                        $tag = $j;
                    }
                }
                $list[$tag] = $listTemp;

            }
        }else if($value == 3){  //order by balance
            for ($i=0; $i < $num - 1; $i++) { 
                $max = $list[$i]['[balance]'];
                $listTemp = $list[$i];
                $tag = $i;
                for ($j=$i+1; $j < $num; $j++) { 
                    if ($list[$j]['balance'] > $max) {
                        $max = $list[$j]['balance'];
                        $list[$i] = $list[$j];
                        $tag = $j;
                    }
                }
                $list[$tag] = $listTemp;

            }
        }

        $tag = $this->pagenum*($this->curpage-1);
        for ($i=0; $i < $this->pagenum && $i < $num-$tag; $i++) { 
            $listShow[$i] = $list[$tag+$i]; 
        }
        
        $this->ajaxOutput($code, $msg, array('count'=>$numall, 'list'=>$listShow));
    }

    public function updateUserInfo() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $Model = M('User');
        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $this->condition = "1=1";
        $state = I('param.state',-1);
        $id = I('param.id',-1);

        $condition = "id='".$id."'";
        $list = $Model->where($condition)->select();
        if($list){
            if($list[0]['state'] == $state){
                $data['state'] = "1";
            }else {
                $data['state'] = $state;
            }

            if($state != -1){
                $this->condition = $this->condition." state='".$state."'";
            }
            $Model = M('User'); 

            $list = $Model->where("id='".$id."'")->save($data);
            if($list){
                $code = 0;
                $msg = "suc";

                getUinfo($id, array(), 'must');
            }else{
                $code = -1;
                $msg = "updateUserInfo failed!"; 
                $list = array();
            }

        }else{
            $code = -1;
            $msg = "search user info failed!"; 
            $list = array();
        }
        
        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }    

    /**
     * 用户搜索
    **/
    public function searchUserInfo() {
        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }
        $Model = M('User');

        $this->curpage = I('param.curpage',1);
        $this->pagenum = I('param.pagenum',10);
        $title = I('param.title',-1);
        $this->condition = "1=1";
        if($title != -1){
            $this->condition = $this->condition." and name like '%".$title."%'";
        }

//        echo $this->condition;
        $list = $Model->where($this->condition)->select();
        if($list){
            $keyPost ="user_post_count_total";
            $keyFans ="user_fans_count_total";
            $redis = S(array('type'=>'Redis'));

            for($i=0; $i < $num; $i++){
                $listLogin = $ModelLogin->where("user_id='".$list[$i]['id']."'")->select();
                if($listLogin){
                    $list[$i]['ip'] = $listLogin[0]['ip'];
                }else{
                    $list[$i]['ip'] = "";
                }

                $listPost = $ModelPost->where("user_id='".$list[$i]['id']."'")->order("ctime desc")->limit(1)->select();
                if($listPost){
                    $list[$i]['lastposttime'] = $listPost[0]['ctime'];
                }else{
                    $list[$i]['lastposttime'] = "";
                }

                //用户粉丝数
                $listTemp = $redis->ZSCORE($keyFans, $list[$i]['id']);
                $list[$i]['fans'] = $listTemp ? $listTemp : 0;

                //用户发帖总数
                $listTemp = $redis->ZSCORE($keyPost, $list[$i]['id']);
                $list[$i]['post'] = $listTemp ? $listTemp : 0;

            }

            $code = 0;
            $msg = "suc";
        }else{
            $code = 10001;
            $msg = "search user fail";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('count'=>count($list), 'list'=>$list));
    }    


}
