<?php
namespace app\index\controller;
use think\Controller;
class Index extends Controller
{
    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';
    }
	public function login(){
		$url='http://www.itdiandi.com/wx9527/public/index.php/index/index/getuserinfo';
		$state='hyj9527';
		$wxurl='https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxd170f87d92c70dfe&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_userinfo&state='.$state.'#wechat_redirect';
		$this->redirect($wxurl);
	}
	public function getuserinfo(){
		$code=input('code');
		$atoken='https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxd170f87d92c70dfe&secret=a13225554ef2716c568b7003e79742cc&code='.$code.'&grant_type=authorization_code';
		$re=file_get_contents($atoken);
		$array=json_decode($re,true);
		$access_token=$array['access_token'];
		$openid=$array['openid'];
		//通过access——token+openid换取用户信息
		$userapi='https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
		$userinfo=file_get_contents($userapi);
		$userinfo=json_decode($userinfo,true);
		$content='<img src="'.$userinfo['headimgurl'].'">'."\n";
		$content.='昵称：'.$userinfo['nickname']."\n";
		$content.='性别：'.($userinfo['sex']==1?'男':'女');
		echo $content;
	}
}
