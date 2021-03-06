<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “股吧”控制器类。
 */ 
class VUserApplyController extends CommonController {

	public function get(){  
		$id = I('param.vid',"");
		if (!isset($id)) {
			$this->ajaxOutput(-1, "id参数错误", array('count'=>0, 'list'=>array()));
		}

		$list = M('VUserApply')->where("id='".$id."'")->limit(1)->select();
		if ($list) {
			$this->ajaxOutput(0, "suc", array('count'=>1, 'list'=>$list));
		}else{
			$this->ajaxOutput(-1, "没有查到该用户", array('count'=>0, 'list'=>array()));
		}
	}

	public function update(){
		$id = I('param.id',"");  
		$vid = I('param.vid',"");
		$state = I('param.state',"");
		if (!isset($vid)) {
			$this->ajaxOutput(-1, "id参数错误", array('count'=>0, 'list'=>array()));
		}
		if (!isset($state)) {
			$this->ajaxOutput(-1, "state参数错误", array('count'=>0, 'list'=>array()));
		}

		if ($state==1) {
			$v_state = M('User')->where("id='".$id."'")->getField('v_state');
			if ($v_state == 1) {
				$this->ajaxOutput(-1, "该用户是系统账号, 不可认证", array('count'=>0, 'list'=>array()));
			}else if ($v_state == 2){
				$this->ajaxOutput(-1, "该用户是官方账号, 不可认证", array('count'=>0, 'list'=>array()));
			}
		}

		$data['state'] = $state;
		$list = M('VUserApply')->where("id='".$vid."' and state='0'")->save($data);
		if ($list) {
			if ($state==1) {
				$VData['v_state'] = 3;
				$UList = M('User')->where("id='".$id."'")->save($VData);
				if (!$UList) {
					$this->ajaxOutput(-1, "审核通过, 但用户状态未能修改成功, 请联系管理员", array('count'=>0, 'list'=>array()));
				}
			}
			
			$this->ajaxOutput(0, "审核成功", array('count'=>1, 'list'=>$list));
		}else{
			$this->ajaxOutput(-1, "没有查到该用户", array('count'=>0, 'list'=>array()));
		}
	}  

}
