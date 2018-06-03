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
class IntegralAction extends HomeAction{
//-------------积分商城--------------
//首页
	public function index(){
		//标题、关键字、描述
		$Site = D("Site");
		$site=$Site->field('keyword,remark,title,link')->where('link="'.$_SERVER['REQUEST_URI'].'"')->find();
		$this->assign('si',$site);
		$active['integral']='active';
		$this->assign('active',$active);
		$user=D('User');		
		import('ORG.Util.Page');// 导入分页类
		$list=$this->integralLest('','','',1);
		$number['total']=$list['total'];
		$number['members']=$list['members'];
		$number['vip']=$list['vip'];
		$number['promote']=$list['promote'];
		unset($list['total']);
		unset($list['members']);
		unset($list['vip']);
		unset($list['promote']);
		$Page       = new Page($number['total'],16);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出
		$lists=$this->integralLest('','',$Page->firstRow.','.$Page->listRows);
		$this->assign('number',$number);
		$this->assign('list',$lists);
		$this->assign('page',$show);
		$forr=$this->top('Forrecord','','time desc',3);
		$this->assign('forr',$forr);
		
		$users=reset($user->relation(true)->where('id="'.$this->_session('user_uid').'"')->select());
		$this->assign('users',$users);
		$endjs='

//AJAX分页
$(function(){ 
	$(".pagination-centered a").click(function(){ 
		$.get($(this).attr("href"),function(data){ 
			$("body").html(data); 
		}) 
		return false; 
	}) 
}) 

//积分商城条件选择数据保存
function integral(type,value){
	var types=$("#type").val();	//积分类型
	var scopes=$("#scope").val();	//积分范围
	var classifys=$("#classify").val();	//商品分类
	if(type=="type"){
		$("#type").val(value);	
		$("#types a").removeClass("on");
		$(".shopList").load("__URL__/indexAjax", {type:value,scope:scopes,classify:classifys});
	}
	if(type=="scope"){
		$("#scope").val(value);	
		$("#scopes a").removeClass("on");
		$(".shopList").load("__URL__/indexAjax", {type:types,scope:value,classify:classifys});	
	}
	if(type=="classify"){
		$("#classify").val(value);	
		$("#classifys a").removeClass("on");
		$(".shopList").load("__URL__/indexAjax", {type:types,scope:scopes,classify:value});
	}
	
}
		';
		$this->assign('endjs',$endjs);
		$this->display();
    }
//商品AJAX显示
	public function indexAjax(){
		import('ORG.Util.Page');// 导入分页类
		$type=$this->_param('type')==0?'':"kind =".$this->_param('type');	//积分类型
		$classify=$this->_param('classify')==0?'':"category =".$this->_param('classify');	//商品分类
		$scope=explode("-",$this->_param('scope'));	//积分范围
		if($scope[1]=='*'){
			$scope=$scope[0]==0?'':"integral >=".$scope[0];
		}else{
			$scope=$scope[0]==0?'':"integral >=".$scope[0]." and integral <=".$scope[1];
		}
		if($type || $classify || $scope[0]){
			$type=$type?$type." and ":'';
			$scope=$scope?$scope." and ":'';
			$classify=$classify?$classify." and ":'';
			$where=$type.$scope.$classify;
		}
		$lists=$this->integralLest('','','','1',$where);
		$number['total']=$lists['total'];
		unset($lists['total']);
		unset($lists['members']);
		unset($lists['vip']);
		unset($lists['promote']);
		$Page       = new Page($number['total'],15);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出
		$list=$this->integralLest('','',$Page->firstRow.','.$Page->listRows,'',$where);
		if(!$list){
			echo '<div class="invest_loading"><div>暂无数据</div> </div>';
			exit;
		}
		foreach($list as $id=>$lt){
			$content.='
				<div class="shopList_box">
				   <a class="clear" href="'.__ROOT__.'/Integral/page/'.$id.'.html"><img src="/Public/uploadify/uploads/goods/'.$lt['master'].'" height="300" width="300"></a>
				   <a class="clear" href="'.__ROOT__.'/Integral/page/'.$id.'.html">'.$lt['title'].'</a>
				   <p><span class="fleft">所需积分<br><b>'.$lt['integral'].'</b> 积分</span><a class="btn_shop fright" href="'.__ROOT__.'/Integral/page/'.$id.'.html">我要兑换</a></p>
				</div>
			';
		}
		echo $content.'
		<div class="pagination pagination-centered span9">
			<ul>'.$show.'</ul>
		</div>';
		echo "
		<script>
		//AJAX分页
		$(function(){ 
			$('.pagination-centered a').click(function(){ 
				$.get($(this).attr('href'),function(data){ 
					$('.integral_subject').html(data); 
				}) 
				return false; 
			}) 
		}) 			
		</script>";
	}
	
//商品页	
	public function page(){
		$head=$this->headPortrait('./Public/FaustCplus/php/img/big_user_'.$this->_session('user_uid').'.jpg');
		$this->assign('heads',$head);
		$user=D('User');
		$list=$this->integralLest('',$this->_get('id'));
		$this->assign('list',$list);
		$hot=$this->integralLest('`convert` DESC');
		$this->assign('hot',$hot);
		$users=reset($user->relation(true)->where('id="'.$this->_session('user_uid').'"')->select());
		$this->assign('users',$users);
		$forr=$this->top('Forrecord','','time desc',20);
		$this->assign('forr',$forr);
		 //标题、关键字、描述
		$Integral = D("Integral");
		$integral=$Integral->field('title')->where('id="'.$this->_get('id').'"')->find();
		$integral['link']=1;
		$this->assign('si',$integral);
		$integral['title']=','.$integral['title'];
		$this->assign('so',$integral);
		$this->display();
	}
//兑换记录	
	public function records(){
		$this->homeVerify();
		import('ORG.Util.Page');// 导入分页类
		$user=D('User');
		$forrecord=D('Forrecord');
		$total=$forrecord->where('uid="'.$this->_session('user_uid').'"')->count();
		$Page       = new Page($total,10);
		$show       = $Page->show();// 分页显示输出
		$lists=$forrecord->relation(true)->where('uid="'.$this->_session('user_uid').'"')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$lists);
		$this->assign('page',$show);
		 //标题、关键字、描述
		$Site = D("Site");
		$site=$Site->field('keyword,remark,title,link')->where('link="'.$_SERVER['REQUEST_URI'].'"')->find();
		$this->assign('si',$site);
		$this->display();
	}
	
//快递跟踪显示
	public function deliveryAjax(){
		$this->homeVerify();
		$express=$this->expressQuery($this->_post('name'),$this->_post('number'));	//快递跟踪记录
		if(!$express['data']){
			echo '
				<tr>
					<td>无数据</td>
				</tr>
				';
		}
		foreach($express['data'] as $exp){
		$content.='
		<tr>
			<td>'.$exp['time'].'</td>
			<td>'.$exp['context'].'</td>
	  	</tr>
		';
		}
		echo $content;
		echo '<tr class="red"><td>'.$express['end']['time'].'</td><td>'.$express['end']['context'].'</td></tr>';
	}
//积分兑换
	public function pageAdd(){
		$this->homeVerify();
		if($this->_session('user_uid')){
			$user=D('User');
			$forrecord=M('forrecord');
			$forr=$forrecord->where('gid="'.$this->_post('gid').'" AND uid="'.$this->_session('user_uid').'"')->order('`time` DESC')->find();
			$count      = $forrecord->where('gid="'.$this->_post('gid').'" AND uid="'.$this->_session('user_uid').'"')->count();
			$int=D('Integral');
			$ints=reset($int->field('number,convert,days,deadline,amount')->where('id="'.$this->_post('gid').'"')->select());
			if($ints['deadline']==0){	//天
				$deadline=$ints['days'];
				$day=$ints['days']."天内";	
			}else{	//月
				$deadline=$ints['days']*30;
				$day=$ints['days']."个月内";
			}
			if(((time()-$forr['time'])/86400)<=$deadline){	//判断是否超过可兑换期限
				if($count>$ints['amount']){	//判断用户兑换次数
					$this->error("该商品".$day."只能兑换".$ints['amount']."次！",'__ROOT__/Integral.html');
				}
			}	
			$users=reset($user->relation(true)->where('id="'.$this->_session('user_uid').'"')->select());
			if($this->_post('kind')==1){
				$integral=$users['member_available'];	//会员积分
			}else if($this->_post('kind')==2){
				$integral=$users['vip_available_integral'];	//VIP积分
			}else{
				$integral=$users['promote_available_integral'];	//推广积分
			}
			if($integral>=$_POST['integral']){	//积分小于兑换积分
				$surplus=$ints['number']-$ints['convert'];
				if($surplus>0){	//剩余数量必须大于0
					$pay_password=$user->userPayMd5($this->_post('pay_password'));
					if($users['pay_password']==$pay_password){	//支付密码
						if($create=$forrecord->create()){
							$create['uid']		=$this->_session('user_uid');		//用户ID
							$create['indent']	=$this->orderNumber();	//订单号
							$create['detailed']	=$this->_post('detailed');			//转义内容
							$create['location']	=implode(" ",$create['location']);	//组合城市
							$create['time']		=time();	//兑换时间
							$create['type']		=1;			//状态
							$result = $forrecord->add($create);
							if($result){
								$int->where('id="'.$this->_post('gid').'"')->setInc('`convert`'); //已兑换数量+1
								if($this->_post('kind')==1){
									$ufees=M('ufees');
									$ufees->where('uid="'.$this->_session('user_uid').'"')->setDec('`available`',$this->_post('integral')); //会员积分
								}else if($this->_post('kind')==2){
									$vip_points=M('vip_points');
									$vip_points->where('uid="'.$this->_session('user_uid').'"')->setDec('`available`',$this->_post('integral'));	//VIP积分
								}else{
									$promote_integral=M('promote_integral');
									$promote_integral->where('uid="'.$this->_session('user_uid').'"')->setDec('`available`',$this->_post('integral'));	//推广积分
								}
								$this->success('兑换成功','__ROOT__/Integral.html');
							 }else{
								$this->error("兑换失败");
							 }
						}else{
							 $this->error($forrecord->getError());
						}
					}else{
						$this->error("支付密码错误！");
					}
				}else{
					$this->error("此标状态已发生改变，请从新提交！");
				}
			}else{
				$this->error("积分不足！");
			}
		}else{
			$this->error("请先登陆！",'__ROOT__/Logo/login');
		}
	}
}