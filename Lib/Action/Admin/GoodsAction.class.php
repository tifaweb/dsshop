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
class GoodsAction extends AdminCommAction {
	//商品列表
	public function index(){
		import('ORG.Util.Page');
        $count      = M('goods')->count();
		$Page       = new Page($count,10);
        $show       = $Page->show();
		$list=D('Goods')->relation('goodslist')->limit($Page->firstRow.','.$Page->listRows)->order('`id` DESC')->select();
		
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->display();
	}
	
	//商品添加页
	public function addgoo(){
		$goodslist=M('goodslist')->where('pid=0')->order('`id` ASC')->select();
		$this->assign('arr',$goodslist);
		$this->display();
	}
	
	//商品类目AJAX
	public function ajaxGoodslist(){
		$v=$this->_post('v');
		$p=$this->_post('p');
		$goodslist=M('goodslist')->where('pid='.$v)->order('`id` ASC')->select();
		if($goodslist){
			$con.='
				<select name="fid" class="span2" data="'.$p.'">
                <option value="'.$v.'">不限</option>
			';
			foreach($goodslist as $id=>$g){
				$con.='<option  onclick="ajaxGoodslist(this,'.$g['id'].')" value="'.$g['id'].'">'.$g['title'].'</option>';
			}
			$con.='
                </select>
			';
		}
		$this->ajaxReturn(1,$con,1);
	}
	
	//商品添加
	public function addgo(){
		$Goods=D('Goods');
		if($create=$Goods->create()){
			if(!$this->_post('nameA')){
				$this->error("颜色必须");
			}
			if(!$this->_post('nameB')){
				$this->error("尺寸必须");
			}
			if(!$this->_post('i_img')){
				$this->error("商品主图必须");
			}
			if(!$this->_post('img')){
				$this->error("商品细节图必须");
			}
			if(!$this->_post('stock')){
				$this->error("商品库存必须");
			}
			if(!$this->_post('price')){
				$this->error("商品价格必须");
			}
			$arr['market']=$this->_post('market');
			$arr['price']=$this->_post('price');
			$arr['color']=$this->_post('nameA');
			$arr['size']=$this->_post('nameB');
			$arr['stock']=$this->_post('stock');
			//单价
			if(count($arr['price'])>1){
				asort($arr['price']);
				$create['price']=current($arr['price']);
				list($key, $value) = each($arr['price']);
			}else{
				$create['price']=$arr['price'][0];
			}
			//市场价
			if(count($arr['market'])>1){
				$create['market']=$arr['market'][$key];
			}else{
				$create['market']=$arr['market'][0];
			}
			$create['attribute']=json_encode ($arr); 
			$create['zimg']=$this->_post('i_img');
			//生成缩略图
			$this->addgoimg($this->_post('i_img'));
			foreach($this->_post('img') as $i){	
				$this->addgoimg($i);
			}
			$create['sentiment']=$this->_post('sentiment');
			$create['recommend']=$this->_post('recommend');
			$create['img']=json_encode ($this->_post('img')); 
			$create['instructions']=$this->_post('instructions');
			$create['time']=time(); 
			
			$result = $Goods->add($create);
			$this->Record('商品添加成功');//后台操作
			$this->success("商品添加成功");
			
		}else{
			$this->error($Goods->getError());
		}	
	}
	
	//生成对应图片
	public function addgoimg($img){
		$this->imageProcessing('./Public/uploadify/uploads/commodity/'.$img,840,995,'./Public/uploadify/uploads/commodity/'.$img);
		$this->imageProcessing('./Public/uploadify/uploads/commodity/'.$img,380,450,'./Public/uploadify/uploads/commodity/l'.$img);

	}
	//排序修改
    public function savegoo(){
		$integral=D('Goods');
		$id=$this->_post("id");
		$sort=$this->_post("sort");
		$state=$this->_post("state");
		if($integral->create()){
			  $result = $integral->where(array('id'=>$id))->save();		 			
		}else{
			 $this->error($integral->getError());
		}
    }
	
    //商品编辑页
    public function editgoo(){
            $id=$this->_get("id");
            if($id<1){
				$this->error("操作有误");
			}
			$list=M('goods')->where('`id`="'.$id.'"')->find();
			$list['img']=json_decode($list['img'], true);
			$list['attribute']=json_decode($list['attribute'], true);
			$list['scount']=$scount=count($list['attribute']['size']);//尺寸数
			$list['ccount']=$ccount=count($list['attribute']['color']);//颜色数
			foreach($list['attribute']['color'] as $id=>$col){
				$attributes.='
					<tbody class="EE C'.($id+1).'">';
					foreach($list['attribute']['size'] as $i=>$si){
						$j=$id*$scount+$i;
						$attributes.='
								<tr class="E F'.($i+1).'">';
						if($i==0){
							$attributes.='<td rowspan="'.$scount.'" class="C" id="C'.($id+1).'">'.$col.'</td>';
						}
						$attributes.='		
								  
								  <td class="G'.($i+1).'">'.$si.'</td>
								  <td><input name="market[]" type="text" value="'.$list['attribute']['market'][$j].'"/></td>
								  <td><input name="price[]" type="text" value="'.$list['attribute']['price'][$j].'"/></td>
								  <td><input name="stock[]" type="text" value="'.$list['attribute']['stock'][$j].'" /></td>
								</tr>
						';	
					}
					unset($i);
					unset($j);
				$attributes.='		
					  </tbody>
				';
			}
			$list['attributes']=$attributes;
			$this->assign('list',$list);
			//类目
			$arr=$this->superiorGoodslist($list['fid']);
			$this->assign('arr',$arr);
            $this->display();
    }
	
	//查找上级类目
	public function superiorGoodslist($id,$p=1,$arr=''){
		$list=M('goodslist')->where('id='.$id)->find();
		$goodslist=M('goodslist')->where('pid='.$list['pid'])->order('`id` ASC')->select();
		if($goodslist){
			$arrs.='
				<select name="fid" class="span2" data="'.$p.'">
                <option value="'.$list['pid'].'">不限</option>
			';
			foreach($goodslist as $id=>$g){
				if($list['id']==$g['id']){
					$arrs.='<option  onclick="ajaxGoodslist(this,'.$g['id'].')" value="'.$g['id'].'" selected>'.$g['title'].'</option>';
				}else{
					$arrs.='<option  onclick="ajaxGoodslist(this,'.$g['id'].')" value="'.$g['id'].'">'.$g['title'].'</option>';
				}
				
			}
			$arrs.='
                </select>
			';
			return $this->superiorGoodslist($list['pid'],$p+1,$arrs.$arr);
		}
		return $arr;
		
	}
	
	 //商品编辑保存
    public function editgo(){
		$Goods=D('Goods');
		if($create=$Goods->create()){
			if(!$this->_post('nameA')){
				$this->error("颜色必须");
			}
			if(!$this->_post('nameB')){
				$this->error("尺寸必须");
			}
			if(!$this->_post('i_img')){
				$this->error("商品主图必须");
			}
			if(!$this->_post('img')){
				$this->error("商品细节图必须");
			}
			if(!$this->_post('stock')){
				$this->error("商品库存必须");
			}
			if(!$this->_post('price')){
				$this->error("商品价格必须");
			}
			$arr['market']=$this->_post('market');
			$arr['price']=$this->_post('price');
			$arr['color']=$this->_post('nameA');
			$arr['size']=$this->_post('nameB');
			$arr['stock']=$this->_post('stock');
			//单价
			if(count($arr['price'])>1){
				asort($arr['price']);
				$create['price']=current($arr['price']);
				list($key, $value) = each($arr['price']);
			}else{
				$create['price']=$arr['price'][0];
			}
			
			//市场价
			if(count($arr['market'])>1){
				$create['market']=$arr['market'][$key];
			}else{
				$create['market']=$arr['market'][0];
			}
			$create['attribute']=json_encode ($arr); 
			
			$create['zimg']=$this->_post('i_img');
			//生成缩略图
			$this->addgoimg($this->_post('i_img'));
			foreach($this->_post('img') as $i){	
				$this->addgoimg($i);
			}
			$create['img']=json_encode ($this->_post('img')); 
			$create['instructions']=$this->_post('instructions');
			$create['sentiment']=$this->_post('sentiment');
			$create['recommend']=$this->_post('recommend');
			$create['time']=time();  
			
			$result = $Goods->where(array('id'=>$this->_post('id')))->save($create);
			F('cart',NULL);//删除用户购物车缓存
			$this->Record('商品更新成功');//后台操作
			$this->success("商品更新成功");
		}else{
			$this->error($Goods->getError());
		}	
	}

    //商品删除
    public function delego(){
		$id=$this->_get("id");
		$integral=M('Goods');
		$inte=$integral->field('img')->where('id="'.$id.'"')->find();
		$img=array_filter(explode(',',$inte['img']));
		foreach($img as $i){	//先删除对应的图片
			unlink('./Public/uploadify/uploads/commodity/'.$i);	//删除它
		}
		
		$result=$integral->where('id="'.$id.'"')->delete();	//再删除该条数据
		if($result){
			F('cart',NULL);//删除用户购物车缓存
			$this->success('删除成功');
		}else{
			$this->error("删除失败");
		}			
    }
	
	//类目添加
	public function addcategory(){
		if($this->_post('title')){
			M('goodslist')->add(array('title'=>$this->_post('title'),'pid'=>$this->_post('pid')));
		}
		$goodslist=M('goodslist')->order('`pid` ASC,`id` DESC')->select();
		echo '<select name="pid" class="span2">
						<option value="">顶级类目</option>
				';		
		foreach($goodslist as $g){
			echo '<option value="'.$g['id'].'">'.$g['title'].'</option>';
		}
		echo '
						</select>';
	}
	
	//类目查询AJAX
	public function ajaxcategory(){
		$goodslist=M('goodslist')->order('`pid` ASC,`id` DESC')->select();
		echo '
		<form method="post" class="form"  onsubmit="return false">
			<table class="table">
			<tbody>
			  <tr>
				<td>
					   上级类目：
				</td>
				<td>
				  		<select name="pid" class="span2">
						<option value="">顶级类目</option>
				';		
		foreach($goodslist as $g){
			echo '<option value="'.$g['id'].'">'.$g['title'].'</option>';
		}
		echo '
						</select>
				</td>
			  </tr>
			  <tr>
				<td>
					   类目名：
				</td>
				
				<td>
				  <input name="title" type="text" class="span2" placeholder="请输入联动值...">
				</td>
			  </tr>
			</tbody>      
		</table>
		<div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">关闭</a>
        <button type="submit" class="btn btn-primary" onclick="addcategory()">确认添加</button>
    </div>
    </form>
		';
	}
	
	//订单管理
	public function records(){
		import('ORG.Util.Page');
		if($this->_get('title')){
			$where.='`number`="'.$this->_get('title').'"';
		}
		
		if(is_numeric($this->_get('state'))){
			$where.=' and `state`="'.$this->_get('state').'"';
		}
		
		if($this->_get('starttime')>0){
			$starttime=strtotime($this->_get('starttime'));
			$starttime=" and `time`>='".$starttime."'";
		}
		if($this->_get('endtime')>0){
			$endtime=strtotime($this->_get('endtime'));
			$endtime=" and `time`<='".$endtime."'";
		}
		$where.=$starttime.$endtime;
		
		$where=trim($where,' and ');
		$count      =M('indent')->where($where)->count();
		$Page       = new Page($count,10);
		$show       = $Page->show();
		$indent=M('indent')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('`time` DESC')->select();
		if($indent){
			foreach($indent as $i){
				$details=json_decode($i['details'], true);

				$count=count($details);
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
							  <td rowspan="'.$count.'">';
						switch($i['state']){
							case 1:
							$arr.='等待支付';
							break;
							case 2:
							$arr.='等待配送';
							break;
							case 3:
							$arr.='等待收货';
							break;
							case 4:
							$arr.='交易成功';
							break;
							case 5:
							$arr.='交易取消<br/>取消原因:'.$i['note'];
							break;
							case 6:
							$arr.='交易退回<br/>退回原因:'.$i['note'];
							break;
                            case 8:
                                $arr.='订单删除';
                                break;
						}	  
						$arr.='</td>
							  <td rowspan="'.$count.'">
							  
							  ';
						if($i['state']==2){
							$arr.='<a href="__APP__/TIFAWEB_DSWJCMS/Goods/records_page/id/'.$i['id'].'.html" class="icon icon-color icon-sent" title="发货"></a>';
						}else{
							$arr.='<a href="__APP__/TIFAWEB_DSWJCMS/Goods/records_page/id/'.$i['id'].'.html" class="icon-search" title="查看"></a>';
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
                unset($price);
                unset($colorname);
                unset($sizename);
			}
		}else{
			$arr='<tbody><tr><td  colspan="6">暂无订单</td></tr></tbody>';
		}
		$this->assign('page',$show);
		$this->assign('arr',$arr);
		$this->display();
	}
	
	//订单详情
	public function records_page(){
		if($this->_get('id')<1){
			$this->error('参数有误');
		}
		$indent=D('Indent')->where(array('id'=>$this->_get('id')))->find();
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
		$linkageValue=$this->linkageValue(3);
		$this->assign('linkageValue',$linkageValue);

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
            case 8:
                $indent['states'].='订单删除';
                break;
		}

		$this->assign('details',$details);
		$this->assign('indent',$indent);
		$this->display();
	}
	
	//订单处理
	public function delivery(){
		if($this->_post('region')){
				if($_POST['region'][0]<1){
					$this->error('所在地区必须');
				}
				$information['region']=$this->_post('region');
		}else{
			$delivery=M('delivery')->where(array('id'=>$this->_post('id')))->getField('information');
			$delivery=json_decode($delivery, true);
			$information['region']=$delivery['region'];
		}
		$information['address']=$this->_post('address');
		$information['code']=$this->_post('code');
		$information['recipient']=$this->_post('recipient');
		$information['phone']=$this->_post('phone');
		$information['telephone']=$this->_post('telephone');
		$arr['information']=json_encode($information);
		if($this->_post('delivery')==1){	//发货
			if(!$this->_post('express')){
				$this->error('请选择快递公司');
			}
			if(!$this->_post('cnumber')){
				$this->error('请填写快递单号');
			}
			$arr['express']=$this->_post('express');
			$arr['state']=3;
			$arr['cnumber']=$this->_post('cnumber');
			M('indent')->where('`id`="'.$this->_post('id').'"')->save($arr);
			$this->Record('发货成功');//后台操作
			$this->success('已成功发货','__APP__/TIFAWEB_DSWJCMS/Goods/records.html');
		}else if($this->_post('cancel')==1){	//取消订单
			$arr['state']=5;
			M('indent')->where('`id`="'.$this->_post('id').'"')->save($arr);
			$this->Record('订单取消成功');//后台操作
			$this->success('订单取消成功','__APP__/TIFAWEB_DSWJCMS/Goods/records.html');
		}else if($this->_post('cancel')==2){	//取消订单(用户已付款)
			$arr['state']=5;
			M('indent')->where('`id`="'.$this->_post('id').'"')->save($arr);
			$this->Record('订单取消成功');//后台操作
			$this->success('订单取消成功','__APP__/TIFAWEB_DSWJCMS/Goods/records.html');
		}else if($this->_post('collection')==1){	//关闭交易
			$arr['state']=4;
			M('indent')->where('`id`="'.$this->_post('id').'"')->save($arr);
			$total=$this->_post('total');
			$models = new Model();
			$models->query("UPDATE `ds_money` SET `total_money` = `total_money`+".$total.", `available_funds` = `available_funds`+".$total." WHERE `uid` =".$this->_post('uid'));
			$money=M('money')->field('total_money,available_funds,freeze_funds')->where('`uid`="'.$this->_post('uid').'"')->find();
			//记录添加点
			$sendMsg=$this->silSingle(array('title'=>'【订单：'.$this->_post('number').'】被退回','sid'=>$this->_post('uid'),'msg'=>'【订单：'.$this->_post('number').'】被退回'));//站内信
			$this->moneyLog(array(0,'【订单：'.$this->_post('number').'】被退回，获得资金',$total,'平台',$money['total_money'],$money['available_funds'],$money['freeze_funds'],$this->_post('uid')),3);	//资金记录
			$this->Record('订单关闭成功');//后台操作
			$this->success('订单关闭成功','__APP__/TIFAWEB_DSWJCMS/Goods/records.html');
		}else{
			M('indent')->where('`id`="'.$this->_post('id').'"')->save($arr);
			$this->Record('订单修改成功');//后台操作
			$this->success('修改成功');
		}
	}
	
	//类目管理
	public function category(){
		$goodslist=M('goodslist')->order('`pid` ASC,`id` DESC')->select();
		foreach($goodslist as $id=>$g){
			if($g['pid']>0){
				$goodslist[$id]['pname']=M('goodslist')->where('`id`="'.$g['pid'].'"')->getField('title');
			}else{
				$goodslist[$id]['pname']='顶级类目';
			}
		}
		$this->assign('goodslist',$goodslist);
		$this->display();
	}
	
	//类目添加/编辑页
	public function category_page(){
		$list=M('goodslist')->where('`id`="'.$this->_get('id').'"')->find();
		$goodslist=M('goodslist')->order('`pid` ASC,`id` DESC')->select();
		foreach($goodslist as $id=>$g){
			$goodslists[$g['id']]=$g;
		}
		
		unset($g);
		unset($id);
		if($this->_get('id')>0){	//编辑		
			
			foreach($goodslists as $g){
				if(!in_array($g['id'],$dat)){
					if($g['id']==$list['pid']){
						$arr.='<option value="'.$g['id'].'" selected>'.$g['title'].'</option>';
					}else{
					$arr.='<option value="'.$g['id'].'">'.$g['title'].'</option>';
					}
					$good=M('goodslist')->where('`pid`="'.$g['id'].'"')->order('`pid` ASC,`id` DESC')->select();
					if($good){
						foreach($good as $go){
							if($go['id']==$list['pid']){
								$arr.='<option value="'.$go['id'].'" selected>&nbsp;└'.$go['title'].'</option>';
							}else{
								$arr.='<option value="'.$go['id'].'">&nbsp;└'.$go['title'].'</option>';
							}
							$dat[$go['id']]=$go['id'];
						}
						
					}
				}
			}	
		}else{//添加
			foreach($goodslists as $g){
				if(!in_array($g['id'],$dat)){
					$arr.='<option value="'.$g['id'].'">'.$g['title'].'</option>';
					$good=M('goodslist')->where('`pid`="'.$g['id'].'"')->order('`pid` ASC,`id` DESC')->select();
					if($good){
						foreach($good as $go){
							$arr.='<option value="'.$go['id'].'">&nbsp;└'.$go['title'].'</option>';
							$dat[$go['id']]=$go['id'];
						}
						
					}
				}
			}	
		}
		$this->assign('arr',$arr);
		$this->assign('list',$list);
		$this->display();
	}
	
	//类目添加/编辑
	public function categorySub(){
		$arr['pid']=$this->_post('pid');
		$arr['title']=$this->_post('title');
        $arr['img']=$this->_post('i_img');
        $arr['sorting']=$this->_post('sorting');
		if($this->_post('id')>0){//编辑
			M('goodslist')->where('`id`="'.$this->_post('id').'"')->save($arr);
			$this->Record('类目修改成功');
			$this->success('修改成功','__APP__/TIFAWEB_DSWJCMS/Goods/category.html');
		}else{//添加
			M('goodslist')->add($arr);
			$this->Record('类目添加成功');
			$this->success('添加成功','__APP__/TIFAWEB_DSWJCMS/Goods/category.html');
		}
		
	}

    //类目删除
    public function delecategory(){
        if(!$this->_get('id')){
            $this->error('参数有误');
        }
        M('goodslist')->where(array('id'=>$this->_get('id')))->delete();
        $this->Record('类目删除成功');
        $this->success('删除成功','__APP__/TIFAWEB_DSWJCMS/Goods/category.html');
    }
}
?>