<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “用户”控制器类。
 */ 
class StockTagInitController extends CommonController {
    /**
     * 登录。
     */
    public function getStockTag() {
        $codes = I('codes', -1);
        if($codes){
            $arrayCode = explode("/", $codes);
            $size = count($arrayCode);

            $stockTagModel = M('StockTagInit');
            for($i = 0; $i < $size; $i++){
                if($i == 0){
                    $condition = "code='".$arrayCode[$i]."'";
                }else{
                    $condition = $condition."or code='".$arrayCode[$i]."'";    
                } 
            }

            
            $list = $stockTagModel->where($condition)->select();
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
        }
        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }



    public function updateStockHYGNTag(){
        
        $code = I('code', -1);
        $strtag_id = I('strtag_id', -1);
        $hy = "";
        $gn = "";

        if($code != -1){
            $tagModel = M('Tag');
            $stockTagModel = M('StockTagInit');
            $listTagInit = $stockTagModel->where("code='".$code."'")->select();
            
            if($listTagInit && $listTagInit != null){

                $arrayTag = explode("_", $strtag_id);
                for($i = 0; $i < count($arrayTag); $i++){

                    $listTag = $tagModel->where("id='".$arrayTag[$i]."'")->select();
                    if($listTag[0]['type'] == "1"){             //1为行业 2为概念
                        $hy = $hy.$arrayTag[$i]."_";
                    }else if($listTag[0]['type'] == "2"){   
                        $gn = $gn.$arrayTag[$i]."_";
                    }
                }

                //去掉尾部多余"_"
                $data['hy_tag'] = substr($hy, 0, -1);
                $data['gn_tag'] = substr($gn, 0, -1);

                //echo "hy:".$data['hy_tag']."| gn:".$data['gn_tag']."\n";
                if($listTagInit == null){                           //没有初始化化则新增
                    $listTagAdd = $stockTagModel->add($data);
                }else{                                              //修改初始化记录
                    $listTagAdd = $stockTagModel->where("code='".$code."'")->save($data);
                }
                if($listTagAdd || $listTagAdd == null){
                    $code = 0;
                    $msg = "add suc";
                    if($listTagAdd == null){
                        $listTagAdd = array();
                    }
                }else{
                     $code = 20401;
                     $msg = "code not found in tag";
                     $list = array();
                }
                $this->ajaxOutput($code, $msg, array('list'=>$listTagAdd));    
            }else{
                $code = 20401;
                $msg = "code not found in stocktaginit";
                $list = array();
            }
        }else{
            $code = -1;
            $msg = "code param is null";
            $list = array();
        }

        $this->ajaxOutput($code, $msg, array('list'=>$list));    
    }


    public function update1(){
        $res["error"] = "";//错误信息
        $res["msg"] = "";//提示信息
        if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'],"Users/ios1/Desktop/js/test.bmp")){
            $res["msg"] = "ok";
        }else{
            $res["error"] = "error";
        }
        echo json_encode($res);
    }

    public function update() {
        $upFilePath = "/data/www/admin/data/";
        // $ok=@move_uploaded_file($_FILES['img']['tmp_name'],$upFilePath);
        // if($ok === FALSE){
        //     $this->ajaxOutput(0, "上传失败", array('list'=>$list));   
        // }else{
        //     $this->ajaxOutput(0, "上传成功", array('list'=>$list)); 
        // }

        //echo "update";

        if ((($_FILES["houseMaps"]["type"] == "image/gif")
            || ($_FILES["houseMaps"]["type"] == "image/jpeg")
            || ($_FILES["houseMaps"]["type"] == "image/bmp")
            || ($_FILES["houseMaps"]["type"] == "image/pjpeg"))
            && ($_FILES["houseMaps"]["size"] < 1000000))
        {            //100KB
            $extend = explode(".",$_FILES["houseMaps"]["name"]);
            $key = count($extend)-1;
            $ext = ".".$extend[$key];
            $newfile = time().$ext;
         
            //if(!file_exists('upload')){mkdir('upload');}
            move_uploaded_file($_FILES["houseMaps"]["tmp_name"],$upFilePath);
            @unlink($_FILES['houseMaps']);
            //$this->ajaxOutput(0, "上传成功", array('list'=>$list)); 
        }else {
            //$this->ajaxOutput(0, "上传失败", array('list'=>$list));
        }


    }
}
