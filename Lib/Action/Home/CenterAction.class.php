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
class CenterAction extends HomeAction {
//-------------个人中心--------------
//首页
	public function index(){
		$this->homeVerify();
		echo "<script>window.location.href='".__ROOT__."/Center/order.html';</script>";
		$this->display();
    }

//资金管理	
	public function fund(){
		$this->homeVerify();
		$active['center']='active';
		$this->assign('active',$active);
		$this->assign('mid',$this->_get('mid'));
		switch($this->_get('mid')){
			case 'fundrecord':	//资金明细
			$moneys=M('money')->where('`uid`="'.$this->_session('user_uid').'"')->find();//资金
			//待还总金额（管理费+逾期管理费+逾期罚息+原本息）
			$systems=$this->systems();
			$borrs=D('Refund')->field('interest,bid')->relation('borrowing')->where('`uid`="'.$this->_session('user_uid').'" and `type`=0')->select();
			foreach($borrs as $bo){
				$moneys['stay_still']+=$bo['interest']/($bo['rates']*0.01/12)*$systems['sys_InterestMF'];
			}
			
			import('ORG.Util.Page');// 导入分页类
			$count      = D('Money_log')->where('`type`=0 and `uid`="'.$this->_session('user_uid').'"')->count();// 查询满足要求的总记录数
			$Page       = new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			$record=D('Money_log')->relation(true)->where('`type`=0 and `uid`="'.$this->_session('user_uid').'"')->order('`time` DESC,`id` DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($record as $id => $r){
				$record[$id]['finetypename']=$this->finetypeName($r['finetype']);
			}
			$this->assign('page',$show);// 赋值分页输出
			$this->assign('record',$record);
			$this->assign('money',$moneys);
			$active['center']='active';
			$this->assign('active',$active);
			break;
			case 'bank':	//银行账户
			$available_funds=M('money')->where('`uid`="'.$this->_session('user_uid').'"')->getField('available_funds');
			$userinfos = D('Userinfo')->relation(true)->field('uid,name,bank,bank_name,bank_account')->where('`uid`="'.$this->_session('user_uid').'"')->find();	
			$userinfos['available_funds']=$available_funds;
			$this->assign('userinfos',$userinfos);
			$list=M('unite')->field('name,value')->where('`state`=0 and `pid`=1')->order('`order` asc,`id` asc')->select();
			$this->assign('list',$list);
			
			break;
			case 'draw'://账户提现
			$userinfos = D('Userinfo')->relation(true)->field('uid,name,bank,bank_name,bank_account')->where('`uid`="'.$this->_session('user_uid').'"')->find();
			$available_funds=M('money')->where('`uid`="'.$this->_session('user_uid').'"')->getField('available_funds');
			$userinfos['available_funds']=$available_funds;
			$list=M('unite')->field('name,value')->where('`state`=0 and `pid`=1')->order('`order` asc,`id` asc')->select();
			foreach($list as $lt){
				if($lt['value']==$userinfos['bank']){
					$userinfos['banks']=$lt['name'];
					break;
				}
			}
			$this->assign('list',$list);
			$this->assign('userinfos',$userinfos);
			break;
			case 'drawrecord'://提现记录
			import('ORG.Util.Page');// 导入分页类
			$count      = D('Withdrawal')->where('`uid`="'.$this->_session('user_uid').'"')->count();// 查询满足要求的总记录数
			$Page       = new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			$withuser=$this->showUser('',$this->_session('user_uid'),'',$Page->firstRow.','.$Page->listRows);
			$this->assign('withuser',$withuser);
			$this->assign('page',$show);// 赋值分页输出
			break;
			case 'inject':	//充值
			$audit=$this->offlineBank();
			$this->assign('audit',$audit);
			$online=M('online');
			$onlines=$online->field('id,name')->where('`state`=0')->order('`order` asc,`id` asc')->select();
			$this->assign('onlines',$onlines);
			break;
			case 'injectrecord':	//充值记录
			import('ORG.Util.Page');// 导入分页类
			$count      = D('Recharge')->where('`uid`="'.$this->_session('user_uid').'"')->count();// 查询满足要求的总记录数
			$Page       = new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
			$show       = $Page->show();// 分页显示输出
			$showuser=$this->rechargeUser('',$this->_session('user_uid'),'',$Page->firstRow.','.$Page->listRows);
			$this->assign('showuser',$showuser);
			$this->assign('page',$show);// 赋值分页输出
			break;
			
		}
		$this->display();
    }
	
	//收货地址
	public function shipping(){
		$this->homeVerify();
		$active['center']='active';
		$this->assign('active',$active);
		$this->assign('mid',$this->_get('mid'));
		switch($this->_get('mid')){
			case '':	//收货地址列表
			$delivery=D('Delivery')->where('`uid`="'.$this->_session('user_uid').'"')->order('`default` DESC')->select();
			foreach($delivery as $id=>$d){
				$delivery[$id]['information']=json_decode($d['information'], true);
			}
			$this->assign('delivery',$delivery);
			$this->assign('city',$this->city());
			break;
			case 'edit':	//收货地址详情
				if($this->_get('id')>0){
					$delivery=M('delivery')->where('id="'.$this->_get('id').'"')->find();
					$delivery['information']=json_decode($delivery['information'], true);
					$this->assign('delivery',$delivery);
					$this->assign('city',$this->city());
				}
			break;
		}
		$this->display();
	}
	
	//收货地址数据处理
	public function editShipping(){
		
		if(!$this->_post('address')){
			$this->error('详细地址必须');
		}
		if(!$this->_post('recipient')){
			$this->error('收货人必须');
		}
		if(!$this->_post('phone') && !$this->_post('telephone')){
			$this->error('电话号码、手机号选填一项');
		}
		$information['address']=$this->_post('address');
		$information['code']=$this->_post('code');
		$information['recipient']=$this->_post('recipient');
		$information['phone']=$this->_post('phone');
		$information['telephone']=$this->_post('telephone');
		
		if($this->_post('did')){	//更新
			
			if($this->_post('region')){
				if($_POST['region'][0]<1){
					$this->error('所在地区必须');
				}
				$information['region']=$this->_post('region');
			}else{
				$delivery=D('Delivery')->where(array('id'=>$this->_post('did')))->getField('information');
				$delivery=json_decode($delivery, true);
				$information['region']=$delivery['region'];
			}
			$create['information']=json_encode($information);
			D('Delivery')->where(array('id'=>$this->_post('did')))->save($create);
			$this->success('更新成功','__ROOT__/Center/shipping.html');
		}else{
			if(!$this->_post('region')){
				$this->error('所在地区必须');
			}
			$information['region']=$this->_post('region');
			$create['uid']=$this->_session('user_uid');
			$create['information']=json_encode($information);
			D('Delivery')->add($create);
			$this->success('添加成功','__ROOT__/Center/shipping.html');
		}
	}
	
	//收货地址删除
	public function delShinpping(){
		if($this->_get('id')<1){
			$this->error('参数有误');
		}
		M('delivery')->where(array('id'=>$this->_get('id')))->delete();
		$this->success('删除成功');
	}
	
	//收货地址默认
	public function defaultShipping(){
		if($this->_get('id')<1){
			$this->error('参数有误');
		}
		M('delivery')->where(array('uid'=>$this->_session('user_uid')))->setField('default',0);
		M('delivery')->where(array('id'=>$this->_get('id')))->setField('default',1);
		$this->success('默认地址设置成功');
	}
	
//提现申请	
	public function drawUpda(){
		$this->homeVerify();
		$withdrawal=D('Withdrawal');
		$user=D('User');
		$money=M('money');
		$userinfo=M('userinfo');
		$message=$userinfo->field('certification,bank,bank_name,bank_account')->where('`uid`="'.$this->_session('user_uid').'"')->find();//获取姓名、银行帐号信息用来判断
		if(!$message['bank'] || !$message['bank_name'] || !$message['bank_account'] ){
			$this->error("请先填写银行账户",'__ROOT__/Center/fund/mid/bank.html');
		}
		$moneys=$money->field('total_money,available_funds,freeze_funds')->where('`uid`="'.$this->_session('user_uid').'"')->find();
		$pay_password=$user->where('`id`="'.$this->_session('user_uid').'"')->getField('pay_password');
			if($this->_post('money')<=$moneys['available_funds']){	//提现金额必须小于可用余额
				if($create=$withdrawal->create()){
					$create['withdrawal_poundage']=$this->withdrawalPoundage($this->_post('money'));
					$create['account']=$this->_post('money')-$create['withdrawal_poundage'];
					$create['time']=time();
					$result = $withdrawal->add($create);
					if($result){
						$moneyarr['available_funds']=$moneys['available_funds']-$create['money'];
						$moneyarr['freeze_funds']=$moneys['freeze_funds']+$create['money'];
						$money->where(array('uid'=>$this->_session('user_uid')))->save($moneyarr);
						$this->moneyLog(array(0,'提现申请成功，冻结资金',$this->_post('money'),'平台',$moneys['total_money'],$moneyarr['available_funds'],$moneyarr['freeze_funds']),2);	//资金记录
						$this->success('提现申请成功', '__ROOT__/Center/fund/mid/drawrecord.html');
					}else{
						$this->error("提现申请失败");
					}
					
				}else{
					$this->error($withdrawal->getError());
				}
			}else{
				$this->error("提现金额需小于可提现金额");
			}
	}
//提现撤销	
	public function drawUndo(){
		$this->homeVerify();
		$id=$this->_post('id');
		$withdrawal=D('Withdrawal');
		$user=D('User');
		$money=M('money');
		$userinfo=M('userinfo');
		
		$moneys=reset($money->field('total_money,available_funds,freeze_funds')->where('`uid`="'.$this->_session('user_uid').'"')->select());
		$withdrawals=M('withdrawal')->field('money')->where('id="'.$id.'"')->find();
		if($create=$withdrawal->create()){
			$result = $withdrawal->where('id="'.$id.'"')->save();	//改变提现状态
			if($result){
				$moneyarr['available_funds']=$moneys['available_funds']+$withdrawals['money'];
				$moneyarr['freeze_funds']=$moneys['freeze_funds']-$withdrawals['money'];
				$money->where(array('uid'=>$this->_session('user_uid')))->save($moneyarr);
				$this->moneyLog(array(0,'提现撤销',$withdrawals['money'],'平台',$moneys['total_money'],$moneyarr['available_funds'],$moneyarr['freeze_funds']),5);	//资金记录
				$this->success('提现撤销成功', '__ROOT__/Center/fund/mid/drawrecord.html');
			}else{
				$this->error("提现撤销失败");
			}
			
		}else{
			$this->error($withdrawal->getError());
		}
	}
//账号充值	
	public function injectAdd(){
		$this->homeVerify();
		$recharge=D('Recharge');
		
		if($create=$recharge->create()){	
			 	$create['nid']				=$this->orderNumber();	//订单号
				$create['uid']				=$this->_session('user_uid');	//用户ID
				$create['poundage']			=$this->topUpFees($create['money']);//充值手续费
				$create['account_money']	=$create['money']-$create['poundage'];//到帐金额
				$create['time']				=time();
				$create['type']				=1;
				if($this->_post('way')==0){
					if(!$this->_post('oid')){
						$this->error("请选择充值类型");
					}
					if(!$this->_post('number')){
						$this->error("流水号必须");
					}
					$create['genre']				=0;		//线下充值
				}else{	//网上充值
			/*Dswjcmsalipay start*/
			
		if($this->_post('onid')==1){
			echo "<script>window.location.href='".__ROOT__."/Center/alipayapi?price=".$this->_post('money')."';</script>";
			exit;
		}
		
			/*Dswjcmsalipay end*/
			
					
				}
				$result = $recharge->add($create);
			if($result){
				$this->success('充值提交成功', '__ROOT__/Center/fund/mid/injectrecord.html');	
			}else{
				 $this->error("充值提交失败");
			}	
		}else{
			$this->error($recharge->getError());
			
		}
	}
				
	//站内信
	public function mails(){
		$this->homeVerify();
		$active['center']='active';
		$this->assign('active',$active);
		$this->assign('mid',$this->_get('mid'));
		$this->homeVerify();
		//标题、关键字、描述
		$active['review']='active';
		$this->assign('active',$active);
		//区分会员本人登陆还是其它人访问
		$this->homeVerify();
		$user_uid=$this->_session('user_uid');
		if($this->_get('pid')=='discuss'){
			$site['title']="发出的评论";
		}else{
			$site['title']="收到的通知";
		}
		$site['link']=1;
		$this->assign('si',$site);
		import('ORG.Util.Page');// 导入分页类
		if(isset($_GET['mid'])){
			$where=" and `state`=".$this->_get('mid');
		}else{
			$where=" and `state`<2";
		}
		$count      = M('instation')->where('`sid`="'.$this->_session('user_uid').'"'.$where)->count();
		$Page       = new Page($count,10);
		$show       = $Page->show();
		$all=$this->silReceipt($this->_session('user_uid'),$this->_get('mid'),$Page->firstRow.','.$Page->listRows);
		$this->assign('all',$all);
		$this->assign('page',$show);
		$this->display();
	}
	
	//站内信显示
	public function standLetter(){
		$this->homeVerify();
		$id=$this->_post('id');
		echo $this->singleReceipt($id);
	}
	
	//站内信删除
	public function stationexit(){
		$this->homeVerify();
		$Instation=M('instation');
		$id=$this->_get('id');
		$Instation->where('`id`="'.$id.'"')->setField('state',2);
		$this->success("删除成功");
	}
	
	//站内信还原
	public function reduction(){
		$this->homeVerify();
		$Instation=M('instation');
		$id=$this->_get('id');
		$Instation->where('`id`="'.$id.'"')->setField('state',1);
		$this->success("还原成功");
	}

//安全中心
	public function security(){
		$this->homeVerify();
		$active['center']='active';
		$this->assign('active',$active);
		$this->assign('mid',$this->_get('mid'));
		$active['center']='active';
		$this->assign('active',$active);
		$this->display();
    }
	
	//修改密码
	public function updaPass(){
		$this->homeVerify();
		$user=D('User');
		$users=$user->where('id="'.$this->_session('user_uid').'"')->find();
		if($user->create()){
			if($user->userMd5($this->_post('passwd'))==$users['password']){
				$result = $user->where(array('id'=>$this->_session('user_uid')))->save();
				if($result){
				 $this->success("密码重置成功","__ROOT__/Center/security/password.html");
				}else{
				$this->error("新密码不要和原始密码相同！");
				}		
			}else{
				$this->error("原始密码错误！");
			}
		}else{
			$this->error($user->getError());
		}

	}
	
	//个人资金明细AJAX
	public function ajaxdetail(){
			$this->homeVerify();
			import('ORG.Util.Page');
			$type=$this->_param('type');
			$times=$this->_param('times');
			$starttime=$this->_param('starttime');
			$endtime=$this->_param('endtime');
			if($type==1){	//交易
				$where.=' and (`finetype`=3 or `finetype`=7)';
			}else if($type==2){	//充值
				$where.=' and (`finetype`=1 or `finetype`=4)';
			}else if($type==3){	//提现
				$where.=' and (`finetype`=2 or `finetype`=5 or `finetype`=6)';
			}
			
			if($times>0){
				if($times==1){	//今天
					$where.=' and `time`>='.strtotime(date("Y-m-d")).' and `time`<='.time();
				}else if($times==2){	//最近一个月
					$where.=' and `time`>='.strtotime(date("Y-m-01")).' and `time`<='.time();
				}else if($times==3){	//最近3个月
					$where.=' and `time`>='.strtotime(date("Y-m-01",strtotime("-2 month"))).' and `time`<='.time();
				}else if($times==4){	//今年
					$where.=' and `time`>='.strtotime(date("Y-01-01")).' and `time`<='.time();
				}
			}else{
				if($starttime || $endtime){	//区间时间
				
					$starttime=strtotime($starttime);
					$endtime=strtotime($endtime);
					if($endtime<1){	//结束时间未设置
						$where.=' and `time`>='.$starttime.' and `time`<='.time();
					}else if($starttime<1){	//开始时间未设置
						$where.=' and `time`<='.$endtime;
					}else{
						$where.=' and `time`>='.$starttime.' and `time`<='.$endtime;
					}
				}
			}
			import('ORG.Util.Page');
			$count      = M('money_log')->where('`type`=0 and `uid`="'.$this->_session('user_uid').'"'.$where)->count();
			$Page       = new Page($count,10);
			$show       = $Page->show();
			$record=D('Money_log')->relation(true)->where('`type`=0 and `uid`="'.$this->_session('user_uid').'"'.$where)->order('`time` DESC,`id` DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			if($count<1){
				$content.='<p>没有相应记录</p>';
			}else{
				$content.='
				<table class="table table-striped" id="table">
					<thead>
						  <tr>
							<th>记录时间</th>
							<th>类型</th>
							<th>操作金额</th>
							<th>总金额</th>
							<th>可用金额</th>
							<th>冻结金额</th>
							<th>交易对方</th> 
							<th>操作说明</th>
						  </tr>
						</thead>
						<tbody>';
				
				foreach($record as $r){
					$content.='
							<tr>
								<td>'.date("Y-m-d H:i:s",$r['time']).'</td>
								<td>'.$this->finetypeName($r['finetype']).'</td>
								<td>'.number_format($r['operation'],2,'.',',').'</td>
								<td>'.number_format($r['total_money'],2,'.',',').'</td>
								<td>'.number_format($r['available_funds'],2,'.',',').'</td>
								<td>'.number_format($r['freeze_funds'],2,'.',',').'</td>
								<td>'.$r['counterparty'].'</td>
								<td>'.$r['actionname'].'</td>
							 </tr>
					';
				}
				$content.='</tbody></table>
                <div class="pagination pagination-centered">
			<ul>'.$show.'</ul>
			</div>
			<script>
			//AJAX分页
			$(function(){ 
				$(".pagination-centered a").click(function(){ 
					var loading=\'<div class="invest_loading"><div><img src="/Public/bootstrap/img/ajax-loaders/ajax-loader-1.gif"/></div><div>加载中...</div> </div>\';
					$("#table").html(loading);
					$.get($(this).attr("href"),function(data){ 
						$("#ajax").html(data); 
					}) 
					return false; 
				}) 
			}) 		
			</script>';
			}
			echo $content;
	}
	
	//订单管理
	public function order(){
		$this->homeVerify();
		$active['center']='active';
		$this->assign('active',$active);
		import('ORG.Util.Page');
        $where['state']  = array('NEQ',8);
        $where['uid']  = $this->_session('user_uid');
		$count      =M('indent')->where($where)->count();

		$Page       = new Page($count,10);
		$show       = $Page->show();
		$indent=M('indent')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('`time` DESC')->select();

		if($indent){
			foreach($indent as $i){
				$details=json_decode($i['details'], true);
				


				$s=1;
				$arr.='<tbody>';
                $count=count($details);
                foreach($details as $d){
                    $scount=count($d['goods']['attribute']['size']);//尺寸数
                    $p=$d['cart']['color']*$scount+$d['cart']['size'];
                    $price=$d['goods']['attribute']['price'][$p];
                    $total+=$price*$d['cart']['nub'];
                }
				foreach($details as $d){
					if($s==1){
					$arr.='
						<tr class="order">
						  <td colspan="6"><b>'.date("Y-m-d",$i['time']).'</b>订单号：'.$i['number'].'</td>
						</tr>';
					}
                    $scount=count($d['goods']['attribute']['size']);//尺寸数
                    $p=$d['cart']['color']*$scount+$d['cart']['size'];
                    $price=$d['goods']['attribute']['price'][$p];
                    $colorname=$d['goods']['attribute']['color'][$d['cart']['color']];
                    $sizename=$d['goods']['attribute']['size'][$d['cart']['size']];

					$arr.='
						<tr class="goods">
						  <td class="title"><a href="'.__ROOT__.'/Goods/details/id/'.$d['goods']['id'].'.html"><img src="'.__PUBLIC__.'/uploadify/uploads/commodity/'.$d['goods']['zimg'].'"/></a><div class="t_r"><a href="'.__ROOT__.'/Goods/details/id/'.$d['goods']['id'].'.html" class="t">'.$d['goods']['title'].'</a><br/><span class="t_c">颜色分类：'.$colorname.'&nbsp;尺寸：'.$sizename.'</span></div></td>	
						  <td>'.number_format($price,2,'.',',').'</td>
						  <td>'.$d['cart']['nub'].'</td>';
				   if($s==1){
					   $arr.='
							  <td rowspan="'.$count.'">'.number_format($total,2,'.',',').'</td>
							  <td rowspan="'.$count.'"><a href="'.__ROOT__.'/Center/orderdetails/id/'.$i['id'].'.html">订单详情</a><br/>';
						switch($i['state']){
							case 1:
							$arr.='<a href="'.__ROOT__.'/Goods/subscribe/id/'.$i['id'].'.html">支付</a>';
							break;
							case 2:
							$arr.='等待发货';
							break;
							case 3:
							$arr.='等待收货';
							break;
							case 4:
							$arr.='交易成功';
							
							break;
							case 5:
							$arr.='交易取消<br/>原因:'.$i['note'];
							break;
							case 6:
							$arr.='交易退回<br/>原因:'.$i['note'];
							break;
						}	  
						$arr.='</td>
							  <td rowspan="'.$count.'">';
							  
						switch($i['state']){
							case 1:
							$arr.='<a href="#screening" data-toggle="modal" onclick="$(\'#id\').val('.$i['id'].')">取消定单</a>';
							break;
							case 3:
							$arr.='<a href="#payment" data-toggle="modal"  onclick="if(confirm(\'确定收到商品并确认收货？\'))window.location.href=\''.__ROOT__.'/Center/confirmGoods/id/'.$i['id'].'\'">确认收货</a>';
							break;
						}	
				   	$arr.='</td>';
				   }
					$arr.='
						</tr>
					';
					$s++;
				}
				$arr.='</tbody>';
				unset($details);
				unset($count);
				unset($total);
				unset($s);
			}
		}else{
			$arr='<tbody><tr><td  colspan="6">暂无订单</td></tr></tbody>';
		}
		$this->assign('page',$show);
		$this->assign('arr',$arr);
		$this->display();
	}
	
	//订单详情
	public function orderdetails(){
		if($this->_get('id')<1){
			$this->error('参数有误');
		}
		$indent=D('Indent')->relation('erector')->where('`id`='.$this->_get('id'))->find();
		$details=json_decode($indent['details'], true);
		$indent['information']=json_decode($indent['information'], true);
        foreach($details as $id=>$d){
            $scount=count($d['goods']['attribute']['size']);//尺寸数
            $p=$d['cart']['color']*$scount+$d['cart']['size'];
            $details[$id]['price']=$d['goods']['attribute']['price'][$p];
            $details[$id]['colorname']=$d['goods']['attribute']['color'][$d['cart']['color']];
            $details[$id]['sizename']=$d['goods']['attribute']['size'][$d['cart']['size']];
            $indent['total']+=$details[$id]['total']=$details[$id]['price']*$d['cart']['nub'];
        }
		$this->assign('city',$this->city());

		switch($indent['state']){
			case 1:
			$indent['states']='等待支付';
			break;
			case 2:
			$indent['states']='等待配送';
			break;
			case 3:
			$indent['states']='等待验收';
			break;
			case 4:
			$indent['states']='交易成功';
			
			break;
			case 5:
			$indent['states']='交易取消&nbsp;取消原因:'.$indent['note'];
			break;
			case 6:
			$indent['states']='交易退回&nbsp;退回原因:'.$indent['note'];
			break;
		}	  
		$city=$this->city();
		$this->assign('city',$city);
		$linkageValue=$this->linkageValue(3);
		$this->assign('linkageValue',$linkageValue);
		$this->assign('details',$details);
		$this->assign('indent',$indent);
		$this->display();
	}
	
	//取消订单
	public function cancelOrder(){
		$this->homeVerify();
		if($this->_post('id')>0){
			M('indent')->where('`id`="'.$this->_post('id').'"')->save(array('state'=>5,'note'=>$this->_post('note')));
			$this->success('取消成功');
		}else{
			$this->error('操作有误！');
		}
	}
	
	//确认收货
	public function confirmGoods(){
		if($this->_get('id')<1){
			$this->_error('误操作');
		}
		M('indent')->where('`id`="'.$this->_get('id').'"')->save(array('state'=>4));
		$this->success('收货成功');
	}
	
	
			
			
			/*Dswjcmsalipay start*/
			
	//支付宝跳转页
	public function alipayapi($price){
		header("Content-Type:text/html; charset=utf-8");
		import('@.Plugin.Dswjcmsalipay.Alipay.Submit');
		$online=M('online');
		$list=$online->where('`id`=1')->find();
		$alipay_config['partner']		= $list['pid'];
		$alipay_config['key']			= $list['checking'];
		$alipay_config['sign_type']    = strtoupper('MD5');//签名方式 不需修改
		$alipay_config['input_charset']= strtolower('utf-8');//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['transport']    = 'http';//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $payment_type = "1";//支付类型
        $notify_url = "http://".$_SERVER['HTTP_HOST']."/Center/notify";//服务器异步通知页面路径 
        $return_url = "http://".$_SERVER['HTTP_HOST']."/Center/alipayreturn";//页面跳转同步通知页面路径
        $seller_email = $list['account'];//卖家支付宝帐户
        $out_trade_no = $this->orderNumber();//商户订单号
        $subject = '支付宝';//订单名称
        $quantity = "1";//商品数量
        $logistics_fee = "0.00";//物流费用
        $logistics_type = "EXPRESS";//物流类型
        $logistics_payment = "SELLER_PAY";//物流支付方式
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "trade_create_by_buyer",
				"partner" => trim($alipay_config['partner']),
				"payment_type"	=> $payment_type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"seller_email"	=> $seller_email,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"price"	=> $price,
				"quantity"	=> $quantity,
				"logistics_fee"	=> $logistics_fee,
				"logistics_type"	=> $logistics_type,
				"logistics_payment"	=> $logistics_payment,
				"body"	=> $body,
				"show_url"	=> $show_url,
				"receive_name"	=> $receive_name,
				"receive_address"	=> $receive_address,
				"receive_zip"	=> $receive_zip,
				"receive_phone"	=> $receive_phone,
				"receive_mobile"	=> $receive_mobile,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		//插入数据
		$recharge=M('recharge');
		$poundage=$this->onlineUpFees($price);//充值手续费
		$amount=$price-$poundage;	//到账金额
		$add=$recharge->add(array('uid'=>$this->_session('user_uid'),'genre'=>1,'nid'=>$out_trade_no,'money'=>$price,'time'=>time(),'type'=>1,'account_money'=>$amount,'poundage'=>$poundage));	//插入数据库
		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		echo $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
	}
	
	
	//支付宝跳转同步通知
	public function alipayreturn(){
		header("Content-Type:text/html; charset=utf-8");
		import('@.Plugin.Dswjcmsalipay.Alipay.Notify');
		$online=M('online');
		$list=$online->where('`id`=1')->find();
		$alipay_config['partner']		= $list['pid'];
		$alipay_config['key']			= $list['checking'];
		$alipay_config['sign_type']    = strtoupper('MD5');//签名方式 不需修改
		$alipay_config['input_charset']= strtolower('utf-8');//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['transport']    = 'http';//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
		//获取充值
		$recharge=M('recharge');
		$rechar=$recharge->where('nid='.$this->_get('out_trade_no'))->find();
		if($verify_result) {//验证成功
			$recharge->where('nid='.$this->_get('out_trade_no'))->save(array('type'=>2,'audittime'=>time(),'date'=>json_encode($_GET),'handlers'=>'第三方支付'));
			//获取用户资金
			$money=M('money');
			$mon=$money->field('total_money,available_funds,freeze_funds')->where(array('uid'=>$rechar['uid']))->find();
			$array['total_money']				=$mon['total_money']+$rechar['account_money'];
			$array['available_funds']			=$mon['available_funds']+$rechar['account_money'];	
			//记录添加点
			$money->where(array('uid'=>$rechar['uid']))->save($array);
			$this->silSingle(array('title'=>'充值成功','sid'=>$rechar['uid'],'msg'=>'充值成功，帐户增加'.$rechar['account_money'].'元'));//站内信
			$this->moneyLog(array(0,'充值成功',$rechar['money'],'平台',$array['total_money']+$rechar['poundage'],$array['available_funds']+$rechar['poundage'],$mon['freeze_funds'],$rechar['uid']));	//资金记录
			$this->moneyLog(array(0,'充值手续费扣除',$rechar['poundage'],'平台',$array['total_money'],$array['available_funds'],$mon['freeze_funds'],$rechar['uid']));	//资金记录
			$this->userLog('充值成功');//会员操作
			$this->success('充值成功','__ROOT__/Center/fund/injectrecord.html');
		}else{
			$recharge->where('nid='.$billno)->save(array('type'=>3,'audittime'=>time(),'date'=>json_encode($_GET),'handlers'=>'第三方支付'));	//充值失败
			//记录添加点
			$this->error('充值失败!','__ROOT__/Center/fund/injectrecord.html');
		}
	}	
		
			/*Dswjcmsalipay end*/
			//Dswjcms-tag
}