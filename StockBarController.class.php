<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “股吧”控制器类。
 */ 
class StockBarController extends CommonController {
		private $type = "";
        private $code = "";
		private $order = "";
		private $condition = "1=1";
		private $curpage = 1;
    	private $pagenum = 10;
    	private $countall = 0;
    	private $listData = array();

		public function getReqParam(){    
            $code = I('param.code',-1);
            if($code>=0){
                $this->code = $code;
            }

	        $typeTemp = I('param.type',-1);
	        if($typeTemp>=0){
	            $this->type = $typeTemp;
	        }
	        
	        $orderTemp = I('param.order',-1);
	        if($orderTemp>=0){
	            $this->order = $orderTemp;
	        }

	        $curpageTemp = I('param.curpage',$this->curpage);
	        if($curpageTemp>=0){
	            $this->curpage = $curpageTemp;
	        }

	        $pagenumTemp = I('param.pagenum',$this->pagenum);
	        if($pagenumTemp>=0){
	            $this->pagenum = $pagenumTemp;
	        }
	    }

		public function getCondition(){
			if($this->type){
	            $conditionTemp = sprintf(" and type='%s'", $this->type);
	            $this->condition = $this->condition.$conditionTemp;
	        }

            if($this->code){
                $conditionTemp = sprintf(" and code='%s'", $this->code);
                $this->condition = $this->condition.$conditionTemp;
            }

		}

        public function get(){
            $Model = M('StockBar');
            //$list = $Model->getField('id,name,subtype');
            $list = $Model->where('type=4')->select();
            $this->ajaxOutput(0, '', array('list'=>$list));
        }

        public function getbak(){
            $Model = M('StockBar');
            //$list = $Model->getField('id,name,subtype');
            $list = $Model->where('type=6')->select();
            $this->ajaxOutput(0, '', array('list'=>$list));
        }

        public function getStockCode(){

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

            $type = I('param.type',-1);
            $this->curpage = I('param.curpage',1);
            $this->pagenum = I('param.pagenum',10);

            $condition = "1=1";
            if($type != "-1"){
                $condition = $condition." and type='".$type."'";
            }

            $Model = M('StockBar');

            $allnum = $Model->where($condition)->count();
            
            $list = $Model->where($condition)->order(" id asc")->page($this->curpage, $this->pagenum)->select();

            if($list || $list == null){
              $code = 0;
              $msg = "suc";
              if($list == null){
                $list = array();
              }

              $num = count($list);
              for($i=0; $i < $num; $i++){
                $list[$i]['ucode'] =  makeBarCode($list[$i]['code'], $list[$i]['class']);
              }


            }else{
              $code = -1;
              $msg = "sql failed";
            }
            $this->ajaxOutput($code, $msg, array('count'=>$allnum, 'list'=>$list));

        } 


        public function getStockList(){
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
            

            $this->getReqParam();
            $this->getCondition();

            $Model = M('StockBar');
            $ModelTagInit = M('StockTagInit');
            $ModelTag = M('Tag');

            $num = $Model->where($this->condition)->count();

            $list = $Model->where($this->condition)->order(" id asc")->page($this->curpage, $this->pagenum)->select();
            if($list){
                for($i = 0; $i < count($list); $i++){
                    $list[$i]['ucode'] =  makeBarCode($list[$i]['code'], $list[$i]['class']);

                    $listTagInit = $ModelTagInit->where("code='".$list[$i]['ucode']."'")->select();
                    if($listTagInit || $listTagInit == null){
                        $code = 0;
                        $msg = "tag init find suc";
                        if($listTagInit == null){
                            $listTagInit = array();
                            $this->ajaxOutput(0, 'suc', array('count'=>$num, 'list'=>$listTagInit));
                        }

                        //获取所属行业名称
                        $listTagHY = $ModelTag->where("id='".$listTagInit[0]['hy_tag']."'")->select();
                        $list[$i]['hy_tag'] = $listTagHY[0]['id'];
                        $list[$i]['hy_name'] = $listTagHY[0]['name'];

                        //获取所属行业名称
                        $arrayTagGN = explode("_", $listTagInit[0]['gn_tag']);
                        for ($j=0; $j < count($arrayTagGN); $j++) { 
                             $listTagGN = $ModelTag->where("id='".$arrayTagGN[$j]."'")->select();
                             $list[$i]['gn_tag'] = $listTagGN[0]['id'];
                             $list[$i]['gn_name'] = $list[$i]['gn_name']." / ".$listTagGN[0]['name'];
                        }
                    }else{
                        $code = -1;
                        $msg = "code not find in tag init ";
                        $list = array();
                    }
                }
            }

            $this->ajaxOutput(0, 'suc', array('count'=>$num, 'list'=>$list));
        }


         public function getStock(){

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
            

        	$this->getReqParam();
        	$this->getCondition();

            $Model = M('StockBar');
            $ModelComment = M('CommentCount');
            $ModelPostTime = M('PostBar');
            // if($this->type){
            // 	$count = $Model->where("type='".$this->type."'")->count();
            // }else{
            // 	$count = $Model->count();	
            // }
            
            // if(!$count){
            // 	$this->ajaxOutput(20400, '', array('count'=>"0", 'list'=>array()));
            // }

            if($this->order != "2"){
            	//按发帖数排序
            	$this->readFile();
            	$num = count($this->listData);
            	$j = $this->pagenum*($this->curpage-1);
            	$conditionTemp = "(";
	            for($i = $j; $i < $this->pagenum + $j && $i < $num; $i++){
	            	if($i + 1 == $this->pagenum + $j || $i + 1 == $num){
	            		$conditionTemp = $conditionTemp." code='".$this->listData[$i]['code']."' and class='".$this->listData[$i]['class']."') and state='1'";
	            	}else{
	            		$conditionTemp = $conditionTemp." code='".$this->listData[$i]['code']."' and class='".$this->listData[$i]['class']."' or";
					}	            
	            }
            	$list = $Model->where($conditionTemp)->select();
            	if($list){
            		for($i = 0; $i < count($list); $i++){
                        $condition = " time>'".date('Y-m-d')."' and code='".$list[$i]['code']."'";
                        for($k = $j; $k < $this->pagenum + $j; $k++){
                            if($list[$i]['code'] == $this->listData[$k]['code']){
                                $list[$i]['todayPost'] = $this->listData[$k]['post_num'];
                            }
                        }
                        $listTemp = $ModelComment->where($condition)->select();
                        $list[$i]['todayResp'] = $listTemp[0]['resp_num'] ? $listTemp[0]['resp_num'] : 0;
                        $listTime = $ModelPostTime->where(" bar_code='".$list[$i]['code']."'")->order('ctime desc')->select();
                        $list[$i]['lastPost'] = $listTime[0]['ctime'] ? $listTime[0]['ctime'] : "00-00-00 00:00:00";
                    }

                    for ($i=0; $i < count($list) - 1; $i++) { 
                        $max = $list[$i]['todayPost'];
                        $listTempSwap = $list[$i];
                        $tag = $i;
                        for ($j=$i+1; $j < count($list); $j++) { 
                            if ($list[$j]['todayPost'] > $max) {
                                $max = $list[$j]['todayPost'];
                                $list[$i] = $list[$j];
                                $tag = $j;
                            }
                        }
                        $list[$tag] = $listTempSwap;

                    }
            	}
            }else if ($this->order == "2"){
            	//按回复数排序
            	$this->userRespNumInsert();
            	$list = $ModelComment->where("time>'".date('Y-m-d')."'")->order('resp_num desc')->page($this->curpage, $this->pagenum)->select();
            	$num = count($list);
            	if($list){
            		for($i = 0; $i < count($list); $i++){
            			$listTemp = $Model->where(" code='".$list[$i]['code']."'")->select();
            			$list[$i]['todayPost'] = $list[$i]['post_num'] ? $list[$i]['post_num'] : 0;
            			$list[$i]['todayResp'] = $list[$i]['resp_num'];
            			$list[$i]['type'] = $listTemp[0]['type'];
            			$list[$i]['name'] = $listTemp[0]['name'];
            			
            			$listTime = $ModelPostTime->where(" bar_code='".$list[$i]['code']."'")->order('ctime desc')->select();
            			$list[$i]['lastPost'] = $listTime[0]['ctime'] ? $listTime[0]['ctime'] : "00-00-00 00:00:00";
            		}
            	}
            }
            
            

            $this->ajaxOutput(0, 'suc', array('count'=>$num, 'list'=>$list));
        }

        public function saveStockBar () {
        	//$Model = M('NewsInfo');
        	$Model = M('StockBar');
        	$res_isLogin = $this->isLogin();
	        if(!$res_isLogin){
	            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
	        }

        	$id = I('param.id',-1);
        	$type = I('param.type',-1);
        	$code = I('param.code',-1);
        	$name = I('param.name',-1);
            $en_title = I('param.en_title',-1);
        	$abbrev = I('param.abbrev',-1);
        	$spell = I('param.spell',-1);
        	$state = I('param.state',-1);
            $class = I('param.classtype',-1);

        	if($id != -1){
        		$listData['id'] = $id;
        	}
        	if($type != -1){
        		$listData['type'] = $type;
        	}
        	if($code != -1){
        		$listData['code'] = $code;
        	}
        	if($name != -1){
        		$listData['name'] = $name;
        	}
            if($en_title != -1){
                $listData['eng_name'] = $en_title;
            }
        	if($abbrev != -1){
        		$listData['abbrev'] = $abbrev;
        	}
        	if($spell != -1){
        		$listData['spell'] = $spell;
        	}
        	if($state != -1){
        		$listData['state'] = $state;
        	}
            if($class != -1){
                $listData['class'] = $class;
            }

        	if($id>0){
        		$list = $Model->where("id='".$id."'")->save($listData);
        	}else {
                $list = $Model->where("code='".$code."'")->select($listData);
                if($list){
                    $this->ajaxOutput(-1, 'code exist', array('count'=>count($list), 'list'=>$list));
                }else{
                    $list = $Model->add($listData);
                    $this->reIndexStock($list[0]['id']);
                }
        	}
        	
            //print_r($list);
        	if($list || $list == null){
	            $code = 0;
	            $msg = "suc";
	            if($list == null){
	               $list = Array();
	            }
	        }else{
	            $list = Array();
	            $code = -1;
	            $msg = "no result";
        	}
            if ($state != -1 && $id>0) {
                $this->reIndexStock($id);
            }
            $this->ajaxOutput($code, 'suc', array('count'=>count($list), 'list'=>$list));
        }

        public function delStockBar () {
        	$Model = M('StockBar');
        	$res_isLogin = $this->isLogin();
	        if(!$res_isLogin){
	            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
	        }

        	$id = I('param.id',-1);
        	$listData[0]['state'] = 0;
        	$list = $Model->where("id='".$id."'")->save($listData[0]);
            //print_r($list);
        	if($list || $list == null){
	            $code = 0;
	            $msg = "suc";
	            if($list == null){
	               $list = Array();
	            }
	        }else{
	            $list = Array();
	            $code = 0;
	            $msg = "no result";
        	}

            $this->reIndexStock('0');
            $this->ajaxOutput(0, 'suc', array('count'=>count($list), 'list'=>$list));
        }

        public function reIndexStock($id){
            // 初始化一个 cURL 对象 
            $curl = curl_init(); 

            // 设置你需要抓取的URL
            if ($id > 0) {
                $url = 'http://www.richba.com/index.php?m=home&c=cmd&a=addNewStockBar&bid='.$id;
                curl_setopt($curl, CURLOPT_URL, $url); 
            }else{
                curl_setopt($curl, CURLOPT_URL, 'http://www.richba.com/index.php?m=home&c=cmd&a=reIndexStock'); 
            }
            

            // 设置header 
            curl_setopt($curl, CURLOPT_HEADER, 1); 

            // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。 
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 

            // 运行cURL，请求网页 
            $data = curl_exec($curl); 

            // 关闭URL请求 
            curl_close($curl); 
            
            
            //刷新缓存
            getBarById($id, array(), 'must');
        }

        public function readFile () {
        	$ModelComment = M('CommentCount');
        	if($this->type == 5){//全部
        		$i = 0;
        		$a = file('/data/www/admin/data/ggdata_sort'.date('Y-m-d').'.txt');
		        foreach($a as $line => $content){
		        	$array = explode(",", $content);
		        	if($array[1] != 5){
		        		$this->listData[$i]['code'] = $array[2];
			        	$this->listData[$i]['post_num'] = $array[3];
			        	if($array[3] != "0"){
			        		$list = $ModelComment->where("code='".$this->listData[$i]['code']."'")->select();
						 	if($list){
						 		$list = $ModelComment->where("code='".$this->listData[$i]['code']."'")->save($this->listData[$i]);
						 	}else{
						 		$list = $ModelComment->add($listData[$i]);
						 	}
			        	}
			        	$this->listData[$i]['class'] = $array[4];
			        	$i++;
		        	}
		        }
        	}else if($this->type == 1){//沪深
        		$i = 0;
        		$a = file('/data/www/admin/data/ggdata_sort'.date('Y-m-d').'.txt');
		        foreach($a as $line => $content){
		        	$array = explode(",", $content);
		        	if($array[1] == 1){
			        	$this->listData[$i]['code'] = $array[2];
			        	$this->listData[$i]['post_num'] = $array[3];
			        	$this->listData[$i]['class'] = $array[4];
			        	$i++;
		        	}
		        }
        	}else if($this->type == 2){//港股
        		$i = 0;
        		$a = file('/data/www/admin/data/ggdata_sort'.date('Y-m-d').'.txt');
		        foreach($a as $line => $content){
		        	$array = explode(",", $content);
		        	if($array[1] == 2){
			        	$this->listData[$i]['code'] = $array[2];
			        	$this->listData[$i]['post_num'] = $array[3];
			        	$this->listData[$i]['class'] = $array[4];
			        	$i++;
		        	}
		        }
        	}else if($this->type == 3){//港股
        		$i = 0;
        		$a = file('/data/www/admin/data/ggdata_sort'.date('Y-m-d').'.txt');
		        foreach($a as $line => $content){
		        	$array = explode(",", $content);
		        	if($array[1] == 3){
			        	$this->listData[$i]['code'] = $array[2];
			        	$this->listData[$i]['post_num'] = $array[3];
			        	$this->listData[$i]['class'] = $array[4];
			        	$i++;
		        	}
		        }
        	}else if($this->type == 4){//港股
        		$i = 0;
        		$a = file('/data/www/admin/data/ggdata_sort'.date('Y-m-d').'.txt');
		        foreach($a as $line => $content){
		        	$array = explode(",", $content);
		        	if($array[1] == "4"){
			        	$this->listData[$i]['code'] = $array[2];
			        	$this->listData[$i]['post_num'] = $array[3];
			        	$this->listData[$i]['class'] = $array[4];
			        	$i++;
		        	}
		        }
        	}

        }


        public function userRespNumInsert(){

        	$Model = M('Comment');
			$ModelBar = M('PostBar');
			$ModelComment= M('CommentCount');
			$time = date('Y-m-d');
			//$time = "2015-01-26";
			$list = $Model->distinct(true)->field('post_id')->where(" ctime>'".$time."'")->select();
			$num = count($list);
			if($list){
				for($i = 0; $i < $num; $i++){
					$listBar = $ModelBar->where(" post_id='".$list[$i]['post_id']."'")->order('ctime')->limit(1)->select();
					if($listBar){
	                    $listData[$i]['code'] = $listBar[0]['bar_code'];
	                    //$listData[$i]['class'] = $listBar[0]['bar_code'];
						$count = $Model->where(" post_id='".$list[$i]['post_id']."'")->count();
						$listData[$i]['resp_num'] = $count;
                       // $listData[$i]['lastPost'] = $listBar[0]['ctime'];
					} 
				}
			}

	        
			 $num = count($listData);
			 for($i = 0; $i < $num; $i++){
			 	$list = $ModelComment->where("code='".$listData[$i]['code']."'")->select();
			 	if($list){
			 		$list = $ModelComment->where()->save($listData[$i]);
			 	}else{
			 		$list = $ModelComment->add($listData[$i]);
			 	}
			 }

        }

        //public function 
    /**
     * 获取资讯信息
    **/
    public function search(){

        $res_isLogin = $this->isLogin();
        if(!$res_isLogin){
            $this->ajaxOutput(20401, 'login fail', array('list'=>Array()));
        }

        $value = I('param.value',-1);
        $this->type = '5';

        $this->user_id = I('param.user_id',$res_isLogin);
                
        $Model = M('StockBar');
        $ModelComment = M('CommentCount');
        $ModelPostTime = M('PostBar');
        $condition = "1=1 and (code='".$value."' or abbrev='".$value."' or spell='".$value."' or name='".$value."')";

        $list = $Model->where($condition)->select();

       // echo "con:".$condition."| count:".count($list);
 
        if($list || $list == null){
            $code = 0;
            $msg = "suc";
            if($list == null){
               $list = Array();
            }

            $this->readFile();
        	$num = count($this->listData);
        	$j = $this->pagenum*($this->curpage-1);
            
            $num = count($list);
            for($i=0; $i < $num; $i++){
                $list[$i]['ucode'] =  makeBarCode($list[$i]['code'], $list[$i]['class']);
            }
           // print_r($this->listData);
           /*
            for($i = $j; $i < $this->pagenum + $j && $i < $num; $i++){
            	if($i + 1 == $this->pagenum + $j || $i + 1 == $num){
            		//$conditionTemp = $conditionTemp." code='".$this->listData[$i]['code']."' and class='".$this->listData[$i]['class'].",)";
                    $conditionTemp = $conditionTemp." (code='".$this->listData[$i]['code']."')";
            	}else{
            		//$conditionTemp = $conditionTemp." code='".$this->listData[$i]['code']."' and class='".$this->listData[$i]['class']."') or";
                    $conditionTemp = $conditionTemp." (code='".$this->listData[$i]['code']."') or";
				}	            
            }
            */
           // echo count($list)."|".print_r($list);
        	if($list){
        		for($i = 0; $i < count($list); $i++){
                    for($j = 0; $j < count($this->listData); $j++){
                        if($list[$i]['code']== $this->listData[$j]['code']){
                            //echo "j:".$j."list:".$list[$i]['code']." listdata:".$this->listData[$j]['code'];
        		        	$list[$i]['todayPost'] = $this->listData[$j]['post_num'];
        		          	$listTemp = $ModelComment->where(" code='".$list[$i]['code']."'")->select();
        			        $list[$i]['todayResp'] = $listTemp[0]['resp_num'] ? $listTemp[0]['resp_num'] : 0;
        			        $listTime = $ModelPostTime->where(" bar_code='".$list[$i]['code']."'")->order('ctime desc')->select();
        			        $list[$i]['lastPost'] = $listTime[0]['ctime'] ? $listTime[0]['ctime'] : "00-00-00 00:00:00";
                        }
                    }
        		}
        	}

           
        }else{
            $list = Array();
            $code = 0;
            $msg = "no result";
        }

        $this->ajaxOutput(0, '', array('count'=>count($list), 'user_id'=>$res_isLogin, 'list'=>$list));
    }

}
