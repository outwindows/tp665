<?php
namespace app\weixin\controller;
use	think\Controller;
class Common extends Controller{
	private $token='weixin9527';//私有token
	protected $fromUsername;
	protected $toUsername;
	protected $keyword;
	protected $msgtype;
	protected $event;
	protected $ctime;
	protected $eventkey;
	function __construct(){
		parent::__construct();
		$echoStr = input('echostr');
		if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
		$this->_getattr();
		$this->_menu();
	}
	protected function _getattr(){
		$postStr = file_get_contents("php://input");
		//把推送信息放到数据表
		db('xml')->insert(['xml'=>$postStr]); 
		//把推送内容转换成对象
       $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $this->fromUsername = $postObj->FromUserName;
        $this->toUsername = $postObj->ToUserName;
        $this->keyword = empty(trim($postObj->Content))?'':trim($postObj->Content);
        $this->msgtype = $postObj->MsgType;
		$this->event = empty(trim($postObj->Event))?'':trim($postObj->Event);
        $this->ctime = $postObj->CreateTime;
        $this->eventkey = empty(trim($postObj->EventKey))?'':trim($postObj->EventKey);
	}
	
	private function checkSignature()
	{
        $signature = input('signature');
        $timestamp = input('timestamp');
        $nonce = input('nonce');	
        		
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	function _menu(){
		$access_token = $this->access_token();
		if(!$access_token){die('access_token 不存在');}
		//查询是否存在默认菜单和自定义菜单
		$capi = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$access_token;
		$carr = $this->getRequest($capi);
		if(!empty($carr['menu']) || !empty($carr['conditionalmenu'])){
			$dapi = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$access_token;
			$this->getRequest($dapi);
		}	


		$menuarr = [
			'button'=>[
				[
					'type'=>'click',
					'name'=>'首页',
					'key'=>'A'
				],
				[
					'type'=>'view',
					'name'=>'搜索',
					'url'=>'http://www.baidu.com'
				],
				[
					'type'=>'click',
					'name'=>'我的',
					'sub_button'=>[
						[	
							'type'=>'click',
							'name'=>'点赞',
							'key'=>'B'
						],
						[
							'type'=>'view',
							'name'=>'登录',
							'url'=>'http://www.itdiandi.com/wx9527/public/index.php/index/index/login'
						]
					]
				]
			]
		];
		$menujson = json_encode($menuarr,JSON_UNESCAPED_UNICODE);
		db('xml')->insert(['xml'=>$menujson]); 
		$api = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
		$this->postRequest($api,$menujson);
		//			db('xml')->insert(['xml'=>$result]);

	}

	//模拟post请求
	public function postRequest($str_api,$post_data,$str_returnType='array'){
	    if(!$str_api){
	        exit('request url is empty 请求地址不正确');
	    }


	    $ch = curl_init();  //初始curl
	    curl_setopt($ch,CURLOPT_URL,$str_api);   //需要获取的 URL 地址
	    curl_setopt($ch,CURLOPT_HEADER,0);          //启用时会将头文件的信息作为数据流输出, 此处禁止输出头信息
	    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  //获取的信息以字符串返回，而不是直接输出
	    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,30); //连接超时时间
	    //curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);  //头信息
	    curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); 
	    curl_setopt($ch, CURLOPT_POST, 1);          //post请求
	    //curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false); //  PHP 5.6.0 后必须开启
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	    $res = curl_exec($ch);                      //执行curl请求
	    $response_code = curl_getinfo($ch);

	    //请求出错
	    if(curl_errno($ch)){
	        echo 'Curl error: ' . curl_error($ch)."<br>";
	        //echo $res;
	        var_dump($response_code);
	    }

	    //请求成功
	    if($response_code['http_code'] == 200){
	        if($str_returnType == 'array'){
	            return json_decode($res,true);
	        }else{
	            return $res;
	        }
	    }else{
	        $code = $response_code['http_code'];
	        switch ($code) {
	            case '404':
	                exit('请求的页面不存在');
	                break;
	            
	            default:
	                # code...
	                break;
	        }
	    }
	}


	//模拟get请求
	public function getRequest($str_api,$arr_param=array(),$str_returnType='array'){
        if(!$str_api){
            exit('request url is empty 请求地址不正确');
        }
        //url拼装
        if(is_array($arr_param) && count($arr_param)>0){
            $tmp_param = http_build_query($arr_param);
            if(strpos($str_api, '?') !== false){ $str_api .= "&".$tmp_param; }
            else{ $str_api .= "?" . $tmp_param; }
        }elseif (is_string($arr_param)){
            if(strpos($str_api, '?') !== false){ $str_api .= "&".$arr_param; }
            else{ $str_api .= "?" . $arr_param; }
        }
        //请求头
        $this_header = array("content-type: application/x-www-form-urlencoded; charset=UTF-8");

        $ch = curl_init();  //初始curl
        curl_setopt($ch,CURLOPT_URL,$str_api);      //需要获取的 URL 地址
        curl_setopt($ch,CURLOPT_HEADER,0);          //启用时会将头文件的信息作为数据流输出, 此处禁止输出头信息
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  //获取的信息以字符串返回，而不是直接输出
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,30); //连接超时时间
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);  //头信息
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); 
        $res = curl_exec($ch);                      //执行curl请求
        $response_code = curl_getinfo($ch);
        if(curl_errno($ch)){  exit('Curl error: ' . curl_error($ch)); }
        //请求成功
        if($response_code['http_code'] == 200){
            if($str_returnType == 'array'){
            	$arraytoekn = ['type'=>'get','access_token'=>$res];
            	db('atoken')->insert($arraytoekn);
             return json_decode($res,true);
            }else{ return $res; }
        }else{
            $code = $response_code['http_code'];
            switch ($code) {
                case '404':
                    exit('请求的页面不存在');
                    break;
                default:
                    # code...
                    break;
            }
        }
    }

    public function access_token(){
    	//获取access_token
		$options = ['type'=>'file','expire'=>3600,'path'=> APP_PATH.'runtime/cache/'];
		cache($options);

		$access_token = cache('access_token');
		if(!$access_token){
			$appid = 'wxd170f87d92c70dfe';
			$appsecret = 'a13225554ef2716c568b7003e79742cc';
			$api = 'https://api.weixin.qq.com/cgi-bin/token';
			$arr_param = [
				'grant_type'=>'client_credential',
				'appid'=>$appid,
				'secret'=>$appsecret
			];
			//http_build_query($arr_param)   
			//grant_type=client_credential&appid=appid&secret=appsecret
			//https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=appid&secret=appsecret
			$returnarr = $this->getRequest($api,$arr_param);
			$access_token = $returnarr['access_token'];
			cache('access_token',$access_token,3600);
		}
		return $access_token;
    }
}
?>