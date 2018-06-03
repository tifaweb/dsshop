<?php
// +----------------------------------------------------------------------
// | dswjcms
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.tifaweb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
// +----------------------------------------------------------------------
// | Author: 宁波市鄞州区天发网络科技有限公司 <dianshiweijin@126.com>
// +----------------------------------------------------------------------
// | Released under the GNU General Public License
// +----------------------------------------------------------------------
defined('THINK_PATH') or exit();
class HomeAction extends CommAction{
	/*
	*参数说明
	*	q		//需要操作的表
	*	n		//跳转提示语
	*	u		//跳转地址
	*	m		//存放LOG的数据并区分前后台		m[0]:1前台2后台3同时 其他为各LOG所需的数据
	*
	*/
	protected function _initialize(){	//检测标是否过期，过期就改变状态，后期可直接读缓存
		if(C('DB_PREFIX') !='ds_'){
			header("Location:install.php");
			exit;
		}
		$this->webScan();//安全检测记录
		header("Content-Type:text/html; charset=utf-8");
		$dirname = F('dirname')?F('dirname'):"Default";
		C('DEFAULT_THEME','template/'.$dirname);	//自动切换模板
		C('TMPL_ACTION_ERROR','Index/jump');	//默认错误跳转对应的模板文件
		C('TMPL_ACTION_SUCCESS','Index/jump');	//默认成功跳转对应的模板文件
		//友情链接
		$links = M('links');
		$links=$links->field('title,url,img')->where('state=0')->order('`order` ASC')->select();
		$this->assign('links',$links);
		$system=$this->systems();
		$this->assign('s',$system);
	}
	
	/**
	*
	*前台退出
	*
	*/
	public function exits(){
		session('user_uid',null);
		session('user_name',null);
		session('user_verify',null);
		$this->jumps(__ROOT__.'/Logo/login.html');
	}
	
	/**
	 *
	 * @前台更新
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */	
	public function tfUpda(){
		
		$user=M('user');
		$users=$user->field('username,password')->where('id="'.$this->_session('user_uid').'"')->find();
		if($this->_session('user_verify')==MD5($users['username'].DS_ENTERPRISE.$users['password'].DS_EN_ENTERPRISE)){
			$this->upda();
		}else{
			$this->error('非法操作，网警已介入！');
		}
	}	
	
	
}
?>