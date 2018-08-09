<?php
namespace app\weixin\controller;	

class Index extends Common{
	

	public function index(){
		$this->access_token();
		//获取微信推送信息
		switch($this->msgtype){
			case 'text':
				$this->textMsg();
				break;
			case 'image':
				$this->imagMsg();
				break;
			case 'event':
				$this->eventMsg();
				break;
		}
		
	}
	
	
	public function textMsg()
    {
        $time = time();
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
					</xml>";  
		$array=['搞笑','个性','美女','段子'];		           
		if(!empty( $this->keyword ))
        {
        	if(in_array($this->keyword,$array)){
        		$contentStr="明人不说暗话，你绝逼是个老司机。";
        	}elseif($this->keyword=='新闻'){
        		$this->imgtextMsg();
        	}else{
        		$contentStr="还是说点有趣的话题吧..";
        	}
      		$msgType = "text";
        	
        	$resultStr = sprintf($textTpl, $this->fromUsername, $this->toUsername, $time, $msgType, $contentStr);
        	echo $resultStr;
        }else{
        	echo "Input something...";
        }

    }
	
	
	
	public function imagMsg()
    {
        $time = time();
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
					</xml>";  
        $contentStr = "这图片有点骚喔!";
  		$msgType = "text";
    	$resultStr = sprintf($textTpl, $this->fromUsername, $this->toUsername, $time, $msgType, $contentStr);
    	echo $resultStr;
	} 
	
	//关注事件
    function eventMsg(){
    	$array = [];
    	$array['openid'] = $this->fromUsername;
    	if($this->event == 'subscribe'){
    		//关注
    		$array['ctime'] = $this->ctime;
    		db('event')->insert($array);

    		//回复信息
    		$time = time();
	        $textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						<FuncFlag>0</FuncFlag>
						</xml>";
	        $contentStr = "嘿，你终于来了！"."\n";
	        $contentStr .= "我知道，20多年过来，你的手速到了一个瓶颈。"."\n";
	        $contentStr .= "你急需一个方式突破自身。所以你还是来到了这里。现在我告诉你，你的决定是否正确。"."\n";
	        $contentStr .= "<a href='http://www.itdiandi.com'>你的答案在这</a>"."\n";
	  		$msgType = "text";
	    	$resultStr = sprintf($textTpl, $this->fromUsername, $this->toUsername, $time, $msgType, $contentStr);
	    	echo $resultStr;
    	}elseif($this->event=='CLICK'){
    		$this->_click;
    	}else{
    		//取消关注
    		db('event')->where(['openid'=>$array['openid']])->delete();
    	}
    }

    function imgtextMsg(){
    	$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>12345678</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>%d</ArticleCount>
					<Articles>%s</Articles>
					</xml>";
		$articles = "<item>
					<Title><![CDATA[%s]]></Title>
					<Description><![CDATA[%s]]></Description>
					<PicUrl><![CDATA[%s]]></PicUrl>
					<Url><![CDATA[%s]]></Url>
					</item>";
		$news = [
					[
						'Title'=>'绝地求生大型网游',
						'Description'=>'大吉大利，今晚等你吃鸡',
						'PicUrl'=>'http://www.itdiandi.com/wx9527/public/img/01.jpg',
						'Url'=>'http://pubg.qq.com/#section1',
					],
					[
						'Title'=>'lol官方新英雄解析',
						'Description'=>'这个英雄颠覆了所有玩家的常规玩法',
						'PicUrl'=>'http://www.itdiandi.com/wx9527/public/img/03.jpg',
						'Url'=>'http://lpl.qq.com/',
					]
		];

		$newstr = '';
		foreach($news as $k=>$v){
			$newstr .= sprintf($articles, $v['Title'], $v['Description'], $v['PicUrl'],$v['Url']);
		}
		$count = count($news);
    	$resultStr = sprintf($textTpl, $this->fromUsername, $this->toUsername,$count,$newstr);
    	echo $resultStr;
    }

    function _click(){
    	if($this->eventkey == 'A'){
			
    	}elseif($this->eventkey == 'B'){

    	}
    }
}
?>