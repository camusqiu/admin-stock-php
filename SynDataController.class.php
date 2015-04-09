<?php
namespace Home\Controller;
use Think\Controller;
import('Org.Util.Date');
class SynDataController extends CommonController {
    
    //读文件，初始化stock的概念关联标签
    public function tagInit(){

        $Model = M('Tag');
        $ModelTagInit = M('StockTagInit');

        $a = file('/data/camus/a.log');
      	$num = -1;
        $nameNum = 0;
        $codeId = 0;
        $lineTag = 0;

        //分析文本 array存code，所属概念名称；arrayName存放 概念名称和id
  	    foreach($a as $line => $content){
        	if($num == -1 || $lineTag == 1){
            if($num == -1){
                $num = 0;
            }
            $content = trim($content);
            $arrayName[$nameNum]['name'] = $content; 
            $arrayName[$nameNum++]['tagid'] = $num;
  		      $codeId = $content;
            $lineTag = 0;
        		continue;
        	}

          if(strpos($content, '------') !== false){
              $lineTag = 1;
        	}
          else{
        		$array[$num]['code'] = $content;
        		$array[$num++]['tagname'] = $codeId;

            //先清除
            // $conditionTemp = " 1=1 and code='".$array[$i]['code']."'";
            // $arraydel['gn_tag'] = "";
            //$listTemp = $ModelTagInit->where($conditionTemp)->save($arraydel);
          }
  	    }

        for($i = 0; $i <count($arrayName); $i++){
           $condition = " 1=1 and name='".$arrayName[$i]['name']."' and type='2' and subtype='1'";
           $list = $Model->where($condition)->limit(1)->select(); 
           $arrayName[$i]['id'] = $list[0]['id'];

        }

        //交叉合并，得到arrayUp数组，存放code和gn_tag
        for($i = 0; $i <count($array); $i++){
           for($j = 0; $j<count($arrayName); $j++){              
           	  if($arrayName[$j]['name'] == $array[$i]['tagname']){
              //echo "ij[".$i."][".$j."]arrayName:".$arrayName[$j]['name']." array: ".$array[$i]['tagname']." | ";
                $conditionTemp = "code='".$array[$i]['code']."'";
                $arrayUp[$i]['condition'] = $conditionTemp;
                $arrayUp[$i]['gn_tag'] = $arrayName[$j]['id'];
                $arrayUp[$i]['code'] = $array[$i]['code'];
           	  	break;
           	  }
           }
        }

        //相同code合并
        for($i = 0, $k = 0; $i <count($arrayUp); $i++){
          if($arrayUp[$i]['mark'] == '1'){
            continue;
          }
          $arrayData[$k]['code'] = $arrayUp[$i]['code'];
          $arrayData[$k]['gn_tag'] =  $arrayUp[$i]['gn_tag'];
          for($j = $i+1; $j <count($arrayUp); $j++){
            if($arrayUp[$i]['code'] == $arrayUp[$j]['code']){
              $arrayData[$k]['gn_tag'] =  $arrayData[$k]['gn_tag']."_".$arrayUp[$j]['gn_tag'];
              $arrayUp[$j]['mark'] = '1';
            }
          }
          $k++;
        }


        for($i = 0; $i <count($arrayData); $i++){
          //添加关联
         // $arrayData[$i]['code'] = str_replace(" ", "", $arrayData[$i]['code']);
          $arrayData[$i]['code']= trim($arrayData[$i]['code']);
          $conditionTemp = "code='".$arrayData[$i]['code']."'";
          $arrayGnTag['gn_tag'] = $arrayData[$i]['gn_tag'];
          $listTemp = $ModelTagInit->where($conditionTemp)->save($arrayGnTag);
          if($listTemp){
            echo $arrayData[$i]['code']."-".$arrayData[$i]['gn_tag'].", ";
           // print_r($listTemp);
          }else if($listTemp == null){
            $arrayGnTagTemp['gn_tag'] = $arrayGnTag['gn_tag'];
            $arrayGnTagTemp['code'] = $arrayData[$i]['code'];
            $listTemp = $ModelTagInit->add($arrayGnTagTemp);
          }
        }
        //print_r($arrayData);
    }


    //清空A股/港股/美股的概念关联
    public function reset(){
        $Model = M('Tag');
        $ModelStock = M('StockBar');
        $ModelTagInit = M('StockTagInit');

        $type = I('param.type',-1);
        $list = $ModelTagInit->where("1=1")->select();
        if($list){
          $num = count($list);
          for($i = 0; $i < $num; $i++){
            $conditionTemp = " code='".$list[$i]['code']."'";
            $listTemp = $ModelStock->where($conditionTemp)->select();
            if($listTemp){
              if($listTemp[0]['type'] == '1'){
                $array['gn_tag'] = "";
                //echo $listTemp[0]['code'].", ";
                $listDel= $ModelTagInit->where($conditionTemp)->save($array);
              }
            }
          }
        }
    }
}
?>
