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
class GoodsAction extends HomeAction {
	public function index(){
		//标题、关键字、描述
		$Site = D("Site");
		$site=$Site->field('keyword,remark,title,link')->where('link="'.$_SERVER['REQUEST_URI'].'"')->find();
		
		$active[$this->_get('mid')]='active';
		$this->assign('active',$active);
		switch($this->_get('mid')){
			case "buckle":
			$where="`fid`=1";
			$site['title'].="扣板";
			break;
			case "appliances":
			$where="`fid`=2";
			$site['title'].="电器";
			break;
			case "accessories":
			$where="`fid`=3";
			$site['title'].="配件";
			break;
			default:
			if($this->_get('search')){
				$where="`title` LIKE '%".$this->_get('search')."%'";
			}else{
				$where="`title`=''";
			}
			//分类商品
			if($this->_get('fid')){
				function goodslistSubclass($fid,$a=''){
					$goodslist=M('goodslist')->where(array('pid'=>$fid))->select();
					if($goodslist){
						foreach($goodslist as $g){
							$a=$a.' or fid='.goodslistSubclass($g['id'],$g['id']);
						}
					}
					return $a;
				}
				$site['title']='分类商品';
			}else{
				$site['title'].="搜索";
			}
			break;
		}
		$site['link']=1;
		$this->assign('si',$site);
		import('ORG.Util.Page');
        $count      = M('goods')->where($where)->count();
		$Page       = new Page($count,12);
        $show       = $Page->show();
		$sentiment=M('goods')->where($where)->field('id,title,zimg,price')->limit($Page->firstRow.','.$Page->listRows)->order('`recommend` DESC,`sentiment` DESC')->select();
		$this->assign('sentiment',$sentiment);
		$this->assign('page',$show);
		$this->display();
	}
	
	//详细页
	public function details(){
		if($this->_get('id')<1){
			$this->error('参数有误');
		}
		$goods=M('goods')->where('`id`="'.$this->_get('id').'"')->find();
		$goods['img']=json_decode($goods['img'], true);
		$goods['attributes']=$goods['attribute'];
		$goods['attribute']=$attribute=json_decode($goods['attribute'], true);
		$this->assign('goods',$goods);
		$sales=D('Goods')->field('id,title')->limit('6')->order('`sales` DESC')->select();
		$this->assign('sales',$sales);
		//添加访问量
		M('goods')->where('`id`="'.$this->_get('id').'"')->setInc('sentiment',1);
		
		//标题、关键字、描述
		$si['link']=1;
		$si['title']=$goods['title'];
		$si['remark']=$goods['instructions']?$goods['instructions']:$goods['title'];
		$si['keyword']=$goods['keyword']?$goods['keyword']:$goods['title'];
		$si['link']=1;
		$this->assign('si',$si);
		$this->display();
	}
	
	//切换AJAX
	public function ajaxswitch(){
		if(!$this->_post('type')){
			$this->ajaxReturn(0,'参数有误',0);
		}
		$goods=M('goods')->where('`id`="'.$this->_post('goodid').'"')->find();
		$attribute=json_decode($goods['attribute'], true);
		$scount=count($attribute['size']);//尺寸数
		$p=$this->_post('color')*$scount+$this->_post('size');
		$stock=$attribute['stock'][$p];
		$arr['price']=number_format($attribute['price'][$p],2,'.',',');
		$arr['market']=number_format($attribute['market'][$p],2,'.',',');
		$this->ajaxReturn($arr,$stock,1);
	}
	
	//购物车（商品购买）
	public function ajaxcart(){
		if(!is_numeric($this->_post('color')) || !is_numeric($this->_post('size'))){
			$this->ajaxReturn(0,'参数有误',0);
		}
		$this->homeVerify();
		$c=$_SESSION['cart'];
		$uid=$this->_session('user_uid');
		$cart=json_decode($c[$uid], true);
		$id=$this->_post('id');
		$number=$id.'-'.$this->_post('color').'-'.$this->_post('size');//唯一ID
		$attributes=json_decode($_POST['attributes'], true);
		if(array_key_exists($number,$cart)){	//更新
			if($this->_post('type')==1){	//减
				$cart[$number]['number']=$cart[$number]['number']-1;
				$cart[$number]['total']=$cart[$number]['total']-$this->_post('price');
				$cart['total']=$cart['total']-$this->_post('price');
				if($cart[$number]['number']<1){
					$cart['amount']=0;
				}
				$carts[$uid]=json_encode($cart);
				session('cart',$carts);
			}else{	//加
				$cart[$number]['number']=$cart[$number]['number']+$this->_post('number');
				$cart[$number]['total']=$cart[$number]['total']+$this->_post('price')*$this->_post('number');
				$cart['total']=$cart['total']+$this->_post('price')*$this->_post('number');
				$carts[$uid]=json_encode($cart);
				session('cart',$carts);
			}
			session('total',$cart['total']);
			session('amount',$cart['amount']);
		}else{	//新加
			$cart[$number]=array(
						'id'=>$id,	//id
						'title'=>$this->_post('title'),	//商品名
						'img'=>$this->_post('img'),	//商品图
						'price'=>$this->_post('price'),	//价格
						'color'=>$this->_post('color'),	//颜色值
						'colorname'=>$attributes['color'][$this->_post('color')],	//颜色名
						'size'=>$this->_post('size'),	//尺寸值
						'sizename'=>$attributes['size'][$this->_post('size')],	//尺寸名
						'numbers'=>$this->_post('numbers'),//编号
						'number'=>$this->_post('number'),	//数量
						'total'=>$this->_post('price')*$this->_post('number'),	//总额
					);
			$cart['total']=$cart['total']+$this->_post('price')*$this->_post('number');//购物车总额
			$cart['amount']=$cart['amount']+1;//购物车数量
			$carts[$uid]=json_encode($cart);
			session('cart',$carts);
			session('total',$cart['total']);
			session('amount',$cart['amount']);
		}
		
		$this->ajaxReturn(1,'已成功加入购物车',1);
	}
	
	//购物车
	public function carts(){
		$this->homeVerify();
		$c=$_SESSION['cart'];
		$uid=$this->_session('user_uid');
		$cart=json_decode($c[$uid], true);
		$carts['total']=$cart['total'];
		$carts['amount']=$cart['amount'];
		unset($cart['total']);
		unset($cart['amount']);
		foreach($cart as $id=>$c){	//查询最新库存
			$attribute=M('goods')->where('`id`="'.$c['id'].'"')->getField('attribute');
			$attribute=json_decode($attribute, true);//获取最新的数据
			$scount=count($attribute['size']);//尺寸数
			$p=$c['color']*$scount+$c['size'];
			if($attribute['stock'][$p]<1){	//该商品已停售
				unset($cart[$id]);
				$carts['total']=$carts['total']-$cart[$id]['total'];
				$carts['amount']=$carts['amount']-1;
				$k=1;//已有修改
			}else if($c['number']>$attribute['stock'][$p]){	//购物车商品大于库存
				$cart[$id]['number']=$attribute['stock'][$p];
				$carts['total']=$carts['total']-($c['number']-$attribute['stock'][$p])*$c['price'];
				$k=1;//已有修改
			}
			$cart[$id]['stock']=$attribute['stock'][$p];
			unset($attribute);
			unset($scount);
			unset($p);
		}
		if($k==1){
			$cart['total']=$carts['total'];
			$cart['amount']=$carts['amount'];
			$ca[$uid]=json_encode($cart);
			session('cart',$ca);
			session('total',$cart['total']);
			session('amount',$cart['amount']);
			unset($cart['total']);
			unset($cart['amount']);
		}
		
		$this->assign('cart',$cart);
		$this->assign('carts',$carts);
		$this->display();
	}
	
	//购物车计算（键入值时）
	public function cartCalculation(){
		$this->homeVerify();
		$c=$_SESSION['cart'];
		$uid=$this->_session('user_uid');
		$cart=json_decode($c[$uid], true);
		$id=$this->_post('v');//唯一ID
		$number=$this->_post('t');//数量
		if($cart[$id]){
			$arr['status']=1;
			$cart[$id]['number']=$number;
			$total=$cart[$id]['total']-$number*$cart[$id]['price'];//差额
			$cart[$id]['total']=$number*$cart[$id]['price'];
			$arr['subtotal']=number_format($cart[$id]['total'],2,'.',',');	//小计
			$arr['total']=$cart['total']=$cart['total']-$total;
			$ca[$uid]=json_encode($cart);
			session('cart',$ca);
			session('total',$cart['total']);
			session('amount',$cart['amount']);
		}else{
			$arr['status']=0;
		}
		 echo json_encode($arr);
	}
	
	//购物车计算
	public function cartCalculate(){
		$this->homeVerify();
		$c=$_SESSION['cart'];
		$uid=$this->_session('user_uid');
		$cart=json_decode($c[$uid], true);
		if($this->_post('type')==1){//加
			$arr['number']=$this->_post('number')+1;
			$arr['total']=$this->_post('total')+$cart[$this->_post('data')]['total'];
		}else{	//减
			$arr['number']=$this->_post('number')-1;
			$arr['total']=$this->_post('total')-$cart[$this->_post('data')]['total'];
		}
        echo json_encode($arr);
	}
	
	//购物车全选/全不选
	public function cartGenerations(){
		$this->homeVerify();
		$c=$_SESSION['cart'];
		$uid=$this->_session('user_uid');
		$cart=json_decode($c[$uid], true);
		if($this->_post('type')==1){//全选
			$arr['number']=$cart['amount'];
			$arr['total']=$cart['total'];
		}else{	//全不选
			$arr['number']=0;
			$arr['total']=0;
		}
        echo json_encode($arr);
	}
	
	//购物车数据处理
	public function cartDispose(){
		$this->homeVerify();
		$c=$_SESSION['cart'];
		$uid=$this->_session('user_uid');
		$cart=json_decode($c[$uid], true);
		
		if($this->_post('type')==1){	//删除单条
			$number=$this->_post('data');
			if($cart[$number]){
				if($this->_post('state')==1){	//删除的是已选择的要计算结果
					$arr['total']=$cart['total']=$cart['total']-$cart[$number]['total'];
					$arr['amount']=$cart['amount']=$cart['amount']-1;
				}else{
					$arr['status']=1;//无需修改
				}
				unset($cart[$number]);
				echo json_encode($arr);
				$carts[$uid]=json_encode($cart);
				session('cart',$carts);
				session('total',$cart['total']);
				session('amount',$cart['amount']);
			}
		}else{	//单条或多条
			$number=array_filter(explode(",",$this->_post('data')));
			foreach($number as $n){
				if($cart[$n]){
					$cart['total']=$cart['total']-$cart[$n]['total'];
					$cart['amount']=$cart['amount']-1;
					unset($cart[$n]);
				}
			}
			$arr['total']=0;
			$arr['amount']=0;
			echo json_encode($arr);
			$carts[$uid]=json_encode($cart);
			session('cart',$carts);
			session('total',$cart['total']);
			session('amount',$cart['amount']);
		}
	}
	
	//购买
	public function cartSubscribe(){
		$this->homeVerify();
		$c=$_SESSION['cart'];
		$uid=$this->_session('user_uid');
		$cart=json_decode($c[$uid], true);
		$number=array_filter(explode(",",$this->_post('data')));
		if(!$number){
			$this->ajaxReturn(0,'请选择商品',0);
		}
		foreach($number as $n){
			if($cart[$n]){
				$details[$n]=$cart[$n];
				$details['total']+=$cart[$n]['total'];
				$details['amount']+=$cart[$n]['number'];
				$cart['total']=$cart['total']-$cart[$n]['total'];
				$cart['amount']=$cart['amount']-1;
				unset($cart[$n]);
			}
		}
		$arr['uid']=$uid;
		$arr['details']=json_encode($details);
		$arr['state']=1;
		$arr['time']=time();
		$arr['cellphone']=$this->_session('user_name');
		$arr['name']=$this->_post('name');
		$arr['location']=$this->_post('address');
		$arr['number']=$this->orderNumber();
		$indent=M('indent')->add($arr);
		//清空购物车
		session('cart',null);
		session('amount',null);
		session('total',null);
		$this->ajaxReturn(1,$indent,1);
		
	}
	
	//支付页
	public function subscribe(){
		$this->homeVerify();
		$uid=$this->_session('user_uid');
		$indent=M('indent')->where('`uid`="'.$uid.'" and `id`="'.$this->_get('id').'"')->find();
		$details=json_decode($indent['details'], true);
        foreach($details as $id=>$d){
            $scount=count($d['goods']['attribute']['size']);//尺寸数
            $p=$d['cart']['color']*$scount+$d['cart']['size'];
            $details[$id]['price']=$d['goods']['attribute']['price'][$p];
            $details[$id]['colorname']=$d['goods']['attribute']['color'][$d['cart']['color']];
            $details[$id]['sizename']=$d['goods']['attribute']['size'][$d['cart']['size']];
            $indent['total']+=$details[$id]['total']=$details[$id]['price']*$d['cart']['nub'];
        }
		$this->assign('details',$details);
		$this->assign('indent',$indent);
		$delivery=D('Delivery')->where('`uid`="'.$this->_session('user_uid').'"')->order('`default` DESC')->select();
		foreach($delivery as $id=>$d){
			$delivery[$id]['information']=json_decode($d['information'], true);
		}
		$this->assign('delivery',$delivery);
		$this->assign('city',$this->city());
		$this->display();
	}
	
	//支付操作
	public function depositOperation(){
		$this->homeVerify();
		if($this->_post('id')<1){
			$this->error('误操作');
		}
		if($this->_post('default')<1){
			$this->error('收货地址必须');
		}
		$money=M('money')->field('total_money,available_funds,freeze_funds')->where('`uid`="'.$this->_session('user_uid').'"')->find();
		if($money['available_funds']>=$this->_post('deposit')){
			$delivery=M('delivery')->where('`id`="'.$this->_post('default').'"')->find();
			//销量增加
			$indent=M('indent')->where(array('id'=>$this->_post('id')))->find();
			$details=json_decode($indent['details'], true);
			foreach($details as $d){
				M('goods')->where(array('id'=>$d['id']))->setInc('sales',1);
			}
			M('indent')->where(array('id'=>$this->_post('id')))->save(array('information'=>$delivery['information'],'state'=>2));
			$models = new Model();
			$models->query("UPDATE `ds_money` SET `total_money` = `total_money`-".$this->_post('deposit').", `available_funds` = `available_funds`-".$this->_post('deposit')." WHERE `uid` =".$this->_session('user_uid'));
			
			//记录添加点
			$sendMsg=$this->silSingle(array('title'=>'支付','sid'=>$this->_session('user_uid'),'msg'=>'【订单：'.$this->_post('number').'】支付成功'));//站内信
			$this->moneyLog(array(0,'【订单：'.$this->_post('number').'】支付',$this->_post('deposit'),'平台',$money['total_money']-$this->_post('total'),$money['available_funds']-$this->_post('total'),$money['freeze_funds'],$this->_session('user_uid')),3);	//资金记录
			$this->success('支付成功','__ROOT__/Center/order.html');
		}else{
			$this->error('账户余额不足，请充值!','__ROOT__/Center/fund/mid/inject.html');
		}
	}
}