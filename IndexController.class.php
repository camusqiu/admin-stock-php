<?php
namespace Home\Controller;
use Home\Controller\CommonController;

/**
 * “帖子标签”控制器类。
 */ 
class IndexController extends CommonController {
    
	public function getdata() {
        
        //当天时间
        $dateTime = date('ymd');
        $date = date('Y-m-d');
        $list['time'] = $date;

        //发表吧贴
        $ModelPost = M('Post'); 
        $postnum = $ModelPost->where("ctime>='".$dateTime."'")->count();    
        $list['post'] = $postnum;

        //回复数
        $ModelComment = M('Comment'); 
        $commentnum = $ModelComment->where("modify_time>='".$dateTime."'")->count();    
        $list['respose'] = $commentnum;

        //用户登录数
        $ModelLogin = M('LoginStats'); 
        $loginnum = $ModelLogin->where("ctime>='".$dateTime."'")->count();    
        $list['login'] = $loginnum;

        //用户举报次数
        $ModelReport = M('Report'); 
        $reportnum = $ModelReport->where("ctime>='".$dateTime."'")->count();    
        $list['report'] = $reportnum;

        //用户关注次数
        $ModelFollow = M('Follow'); 
        $follownum = $ModelFollow->where("ctime>='".$dateTime."'")->count();    
        $list['follow'] = $follownum;


        //个股新闻
        $newsnum = $ModelPost->where("type=3 and ctime>='".$dateTime."'")->count();    
        $list['news'] = $newsnum;

        //个股公告
        $announcementnum = $ModelPost->where("type=2 and ctime>='".$dateTime."'")->count();    
        $list['announcement'] = $announcementnum;

        //个股资料变动
        $ModelStockBar = M('StockBar'); 
        $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime."'")->count();    
        $list['stockbarchange'] = $stockbarnum;

        //当前在线用户
        // $ModelStockBar = M('StockBar'); 
        // $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime."'")->count();    
        $list['usernowonline'] = 0;

        //今日最高在线用户
        // $ModelStockBar = M('StockBar'); 
        // $stockbarnum = $ModelStockBar->where("modify_time>='".$dateTime."'")->count();    
        $list['usermostonline'] = 0;

        //新增用户
        $ModelUser = M('User'); 
        $usernum = $ModelUser->where("ctime>='".$dateTime."'")->count();    
        $list['useradd'] = $usernum;


        $this->ajaxOutput(0, "suc", array('list'=>$list));    
    }
}
