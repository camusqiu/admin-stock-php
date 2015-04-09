<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “帖子标签”控制器类。
 */ 
class TagController extends CommonController {
	public function get(){
		$Model = M('Tag');
		//$list = $Model->getField('id,name,subtype');
		$list = $Model->where('1=1')->select();
		$this->ajaxOutput(0, '', array('list'=>$list));
	}


	public function getTag() {
        $HYGN = I('HYGN', -1);
        $market = I('market', -1);
        $Model = M('Tag');

        $condition = "1=1";
        if($HYGN != -1 && $HYGN != 0){
            $condition = $condition." and type='".$HYGN."'";
        }

        if($market != -1 && $market != 0){
            $condition = $condition." and subtype='".$market."'";
        }

        $list = $Model->where($condition)->select();

        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if($list == null){
                $list = Array();
            }
        }else{
            $list = Array();
            $code = 20401;
            $msg = $codes." is not set inittag";
        }
        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }

    public function addTag() {
    	$HYGN = I('HYGN', -1);
        $market = I('market', -1);
        $name = I('name', "");

        $condition = "1=1";

        if($HYGN != -1 && $HYGN != 0){
            $condition = $condition." and type='".$HYGN."'";
        }
        if($market != -1 && $market != 0){
            $condition = $condition." and subtype='".$market."'";
        }
        if($name != ""){
            $condition = $condition." and name='".$name."'";
        }

        $Model = M('Tag');
        $list = $Model->where($condition)->select();

        if($list || $list == null){
            $code = -1;
            $msg = "is exist";
            if($list == null){
                $data['type'] = $HYGN;
                $data['subtype'] = $market;
                $data['name'] = $name; 
                $listAdd = $Model->add($data);
                if($listAdd){
                	$code = 0;
            		$msg = "add suc";
                }else{
                	$listAdd = Array();
		            $code = 20401;
		            $msg = "add tag failed";
                }
                $list = $listAdd;
            }else{
            	//已经存在
            	$this->ajaxOutput($code, $msg, array('list'=>array()));
            }
        }else{
            $list = Array();
            $code = 20401;
            $msg = "add tag failed";
        }
        
        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }

    public function delTag() {
        $tagid = I('tagid', -1);

        $condition = "1=1";

        if($tagid != -1 && $tagid != 0){
            $condition = $condition." and id='".$tagid."'";
        }

        $Model = M('Tag');
        $list = $Model->where($condition)->delete();

        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if($list == null){
                $list = Array();
            }
        }else{
            $list = Array();
            $code = 20401;
            $msg = "del tag failed";
        }
        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }
}
