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
class LogoAction extends HomeAction {
//----------登陆页------------
     public function login(){
		 //标题、关键字、描述
		$Site = D("Site");
		$site=$Site->field('keyword,remark,title,link')->where('link="'.$_SERVER['REQUEST_URI'].'"')->find();
		$this->assign('si',$site);
		 $this->display();
    }
	
	//登陆
	public function loging(){
		
		$user = D("User");
		$condition['username'] = $this->_post('username');
		$condition['password'] = $user->userMd5($this->_post('password'));
		$list = $user->where($condition)->select();
	   if($list){
			session('user_name',$condition['username']);  //设置session
			session('user_uid',$list[0]['id']);
			session('user_verify',MD5($condition['username'].DS_ENTERPRISE.$condition['password'].DS_EN_ENTERPRISE));
			session('verify',null); //删除验证码
			$this->userLog('会员登录',$this->_session('user_uid'));	//会员记录
			$this->success('登录成功', '__ROOT__/Center/order.html');
			exit;
		}else{
			 $this->error('用户名或密码错误');
		exit;
		}
	}
	
//----------注册页------------
	public function register($uid = 0,$gid = 0){
		//标题、关键字、描述
		$Site = D("Site");
		$site=$Site->field('keyword,remark,title,link')->where('link="'.$_SERVER['REQUEST_URI'].'"')->find();
		$this->assign('si',$site);
		$head='<link  href="__PUBLIC__/css/style.css" rel="stylesheet">';
		$this->assign('head',$head);
		if($uid){
			$where= "group_id =".$id;
			$list = D("User")->where('id="'.$uid.'"')->find();
			$this->assign('list',$list);
		}
		if($gid){

			$this->assign('gid',intval($gid));
		}		
		//上线
		$QUERY_STRING=$this->_get('_URL_');
		if($QUERY_STRING){
			$lsuid=base64_decode($QUERY_STRING[2]);
			$lsuid=explode('/',$lsuid);
			$lsuid=$lsuid[1];
			$this->assign('lsuid',$lsuid);
		}
		$this->display();  
    }
	
	//验证
	public function validation(){
		$this->homeVerify();
		$head='<link  href="__PUBLIC__/css/style.css" rel="stylesheet">';
		$this->assign('head',$head);
		$cellphone=M('user')->where('`id`="'.$this->_session('user_uid').'"')->getField('username');
		$this->assign('cellphone',$cellphone);
		$this->display();  
    }
	
//----------找回密码------------
     public function forgotpass(){
		 //标题、关键字、描述
		$Site = D("Site");
		$site=$Site->field('keyword,remark,title,link')->where('link="'.$_SERVER['REQUEST_URI'].'"')->find();
		$this->assign('si',$site);
		 $head='<link  href="__PUBLIC__/css/style.css" rel="stylesheet">';
		 $this->assign('head',$head);
		 $this->display();  
	 }
	 
	 //验证提交
	 public function addregs(){
		if($this->_post('cellpcode') && $this->_post('cellpcode')==$this->_session('cellpcode')){
			M('userinfo')->where('`uid`="'.$this->_session('user_uid').'"')->setField('cellphone_audit',2);
			session('cellpcode',null);
			$this->jumps(__ROOT__.'/Center.html');	
		}else{
			$this->error("误操作，请重新提交！");
		}
	 }
	 
	//注册
	public function addreg(){
		$systems=$this->systems();
		$model=D('User');
		$ufees=M('ufees');
		$money=M('money');
		$userinfo=M('userinfo');
		$models = new Model();
		$inf=$this->integralConf();
		if($create=$model->create()){
			 $create['time']=time();
			 $create['username']=$this->_post('cellphone');
		     $result = $model->add($create);
			if($result){
				//记录添加点
				$ufees->add(array('uid'=>$result,'total'=>$inf['mem_register'][0],'available'=>$inf['mem_register'][0]));	//会员积分
				$arr[0]=1;
				$arr[1]=$inf['mem_register'][1];
				$arr[2]=$inf['mem_register'][0];
				$arr[3]='平台';
				$arr[4]=1;
				$arr[5]=1;
				$arr[7]=$result;
				$this->moneyLog($arr);
				$money->add(array('uid'=>$result));	//资金表
				$userinfo->add(array('uid'=>$result));	//用户资料表
				$this->userLog('会员注册成功',$result);	//会员记录
				$this->silSingle(array('title'=>'会员注册成功','sid'=>$result,'msg'=>$this->_post('cellphone').'您的账号已注册成功！'));//站内信
				$this->integralAdd($arr);	//积分操作
				$user=$model->where('`id`="'.$result.'"')->find();
				session('user_name',$user['username']);  //设置session
				session('user_uid',$user['id']);
				session('user_verify',MD5($user['username'].DS_ENTERPRISE.$user['password'].DS_EN_ENTERPRISE));
				session('cellpcode',null); //删除验证码
				unset($user);
				$this->jumps(__ROOT__.'/Center.html');	
			}else{
				 $this->error("注册失败");
			}	
		}else{
			$this->error($model->getError());
			
		} 
    }
	
	//找回密码
	public function rPassword(){
		if($this->_session('verify') != md5(strtoupper($this->_post('proving')))) {
			session('verify',null);
		   $this->error('验证码错误！');
		}
		$user=D('User');
		
		$users=$user->where('username="'.$this->_post('user').'"')->find();
		if(!$users){
			$this->error("手机号不存在");
		}
		$this->assign('name',$this->_post('user'));
		$this->assign('id',$users['id']);
		$this->display(); 
	}
	
	//重置找回密码
	public function replacement(){
		if($this->_session('cellpcode')!=$this->_post('cellpcode')) {
		   $this->error('验证码错误！');
		}
		$count=M('user')->where('`id`="'.$this->_post('id').'"')->count();
		if($count>0){
			$model=D('User');
			$password=$model->userMd5($this->_post('password'));
			M('user')->where('`id`="'.$this->_post('id').'"')->save(array('password'=>$password));
			$this->success("密码重置成功","__ROOT__/Logo/login.html");
		}else{
			$this->error("账号不存在！");
		}
	}
		
	//注册AJAX验证
	public function ajaxverify(){
		if($this->_post("name")=="username"){	//验证会员名
			$user=D('User');
			$row=$user->where('username="'.$this->_post('param').'"')->count();
			if($row){
				 echo '{
					"info":"会员名已存在！",
					"status":"n"
				 }';
				}else{
			echo '{
					"info":"可以注册！",
					"status":"y"
				 }';
			}
		}
		else if($this->_post("name")=="email"){	//验证会员邮箱
			$user=D('User');
			$row=$user->where('email="'.$this->_post('param').'"')->count();
			if($row){
				echo '{
					"info":"邮箱已存在！",
					"status":"n"
				 }';
			}else{
				echo '{
					"info":"可以注册！",
					"status":"y"
				 }';
			}
		}
		else if($this->_post("name")=="emailcode"){	//邮箱验证码
			if($this->_session('emailcode')==$this->_post('param')){
				echo '{
					"info":"正确！",
					"status":"y"
				 }';
			}else{
				echo '{
					"info":"邮箱证码错误！",
					"status":"n"
				 }';
			}
		}
		else if($this->_post("name")=="cellphone"){	//验证手机
			$user=D('Userinfo');
			$row=$user->where('cellphone="'.$this->_post('param').'"')->count();
			if($row){
				echo '{
					"info":"手机号已存在！",
					"status":"n",
					"vnumber":"1"
				 }';
			}else{
				echo '{
					"info":"可以注册！",
					"status":"y",
					"vnumber":"1"
				 }';
			}
		}
		else if($this->_post("name")=="cellpcode"){	//手机验证码
			if($this->_session('cellpcode')==$this->_post('param')){
				echo '{
					"info":"正确！",
					"status":"y"
				 }';
			}else{
				echo '{
					"info":"手机验证码错误！",
					"status":"n"
				 }';
			}
		}
		else if($this->_post("name")=="user"){	//验证会员名必须存在
			$user=D('User');
			$row=$user->where('username="'.$this->_post('param').'"')->count();
			if(!$row){
				echo '{
					"info":"用户不存在！",
					"status":"n",
					"vnumber":"1"
				 }';
			}else{
				echo '{
					"info":"可以操作！",
					"status":"y",
					"vnumber":"1"
				 }';
			}
		}
	}
	
	//注册手机验证码
	public function reMessage(){
		echo $cellpcode=$this->cellpcode();	//验证码
		exit;
	}
	
	//注找回密码手机验证码
	public function reMessages(){
		echo $cellpcode=$this->cellpcode();	//验证码
		exit;
	}
}