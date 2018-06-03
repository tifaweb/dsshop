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
class IndexAction extends CommAction {
    //微信openid获取
    public function getwxopenID(){
        if(I('get.code')){
            $data['appid']='wxc31e70f47087660b';    //小程序appid
            $data['secret']='5a8d778a3ca34748dcea69ea7f40a4a4'; //小程序secret
            $data['js_code']=I('get.code');
            $data['grant_type']="authorization_code";
            $Curl=$this->Curl($data,'https://api.weixin.qq.com/sns/jscode2session');

            if($Curl['errcode']){	//出错
                $this->ajaxReturn($Curl['errcode'],$Curl['errmsg'],0);
            }else{

                $add['openid']=$Curl['openid'];
                $add['session_key']=$Curl['session_key'];
                $add['time']=time();
                $add['password']=D('User')->userMd5('123456');  //生成默认登陆密码
                $user=M('user')->where(array('openid'=>$Curl['openid']))->find();
                if($user['id']>0){
                    M('user')->where(array('openid'=>$Curl['openid']))->save(array('session_key'=>$Curl['session_key'],'time'=>time()));
                    $id=$user['id'];
                }else{
                    $id=M('user')->add($add);
                    M('ufees')->add(array('uid'=>$id));
                    M('money')->add(array('uid'=>$id));	//资金表
                    M('userinfo')->add(array('uid'=>$id));	//用户资料表
                    $this->userLog('会员注册成功',$id);	//会员记录
                    $this->silSingle(array('title'=>'会员注册成功','sid'=>$id,'msg'=>'您的账号已注册成功！'));//站内信
                }
                $adds['openid']=$Curl['openid'];
                $adds['id']=$id;
                $adds['verify']=MD5($adds['openid'].DS_ENTERPRISE.$adds['id'].DS_EN_ENTERPRISE);

                $this->ajaxReturn(1,$adds,1);

            }
        }
    }

    //同步用户信息
    public function getuserinfo(){
        if(I('get.openid')){
            $user=M('user')->where(array('id'=>I('get.id')))->find();
            if(MD5($user['openid'].DS_ENTERPRISE.$user['id'].DS_EN_ENTERPRISE) !=I('get.verify')){
                $this->ajaxReturn(0,'账号不匹配',0);
            }else{
                $userInfo=json_decode(stripslashes($_GET['userInfo']), true);

                M('user')->where(array('id'=>I('get.id')))->save(array('username'=>$userInfo['nickName'],'data'=>stripslashes($_GET['userInfo'])));
                $this->ajaxReturn(1,'同步成功',1);
            }

        }
    }

    //首页轮播
    public function getShuffling(){
        $shuffling = M('shuffling')->where(array('type'=>1,'state'=>0))->order('`order` ASC,id DESC')->select();
        if($shuffling && count($shuffling)>0){
            $this->ajaxReturn(1,$shuffling,1);
        }else{
            $this->ajaxReturn(0,$data,1);
        }
    }

    //获取商品栏目列表
    public function getGoodsList(){
        $goodslist=M('goodslist')->order('pid ASC,sorting ASC,id ASC')->select();
        $list=$this->ArrayKeyValue($goodslist,0);
        $i=0;
        foreach($list as $id=>$g){

            $listarray[$i]['title']=$g['title'];

            $two=$this->ArrayKeyValue($goodslist,$g['id']);  //二级栏目
            if($two){
                foreach($two as $s=>$t){
                    $listarray[$i][$s]['title']=$t['title'];
                    $listarray[$i][$s]['data']=$this->ArrayKeyValue($goodslist,$t['id']);

                }
            }
            $i++;
        }

        $this->ajaxReturn(1,$listarray,1);
    }

    //获取多维数组中指定层级的数组
    //$data 原数组
    //$pid  对应的层级
    public function ArrayKeyValue($data,$pid){

        foreach($data as $d){
            if($d['pid']==$pid){
                $array[$d['id']]=$d;
            }

        }
        return $array;
    }

    //获取商品数据（列表）
    public function getGoods(){

        $where['state']=0;

        import('ORG.Util.Page');
        $count      = M('goods')->where($where)->count();
        $Page       = new Page($count,10);
        $goods = M('goods')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('`sentiment` DESC,id DESC')->select();
        if($goods && count($goods)>0){
            foreach($goods as $id=>$g){
                $data[$id]=array(
                    'id'=>$g['id'],	//id
                    'img'=>$g['zimg'],	//主图
                    'title'=>$g['title'],	//标题
                    'price'=>$g['price'],	//价格
                );
            }
            $this->ajaxReturn(count($goods),$data,1);
        }else{
            $this->ajaxReturn(0,$data,1);
        }
    }

    //收货地址默认展示
    public function getShippingAddressShow(){
        header("Content-Type:text/html; charset=utf-8");
        date_default_timezone_set('Asia/Shanghai');
        if(I('get.openid')){
            $delivery=M('delivery')->where(array('uid'=>I('get.uid'),'default'=>1))->find();
            if($delivery && count($delivery)>0){
                $city=M('newcity')->select();
                foreach($city as $cy){
                    $citys[$cy['id']]=$cy['city'];
                }
                $information=json_decode($delivery['information'], true);
                $delivery['information']=$information;
                $delivery['city']=$citys[$information['region'][0]].' '.$citys[$information['region'][1]].' '.$citys[$information['region'][2]];
                $this->ajaxReturn(1,$delivery,1);
            }else{
                $this->ajaxReturn(0,'',0);
            }

        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    //收货地址
    public function getShippingAddress(){
        header("Content-Type:text/html; charset=utf-8");
        date_default_timezone_set('Asia/Shanghai');
        if(I('get.openid')){
            $delivery=M('delivery')->where(array('uid'=>I('get.uid')))->select();
            if($delivery && count($delivery)>0){
                $city=M('newcity')->select();
                foreach($city as $cy){
                    $citys[$cy['id']]=$cy['city'];
                }
                foreach($delivery as $id=>$d){
                    $information=json_decode($d['information'], true);
                    $delivery[$id]['information']=$information;
                    $delivery[$id]['city']=$citys[$information['region'][0]].' '.$citys[$information['region'][1]].' '.$citys[$information['region'][2]];
                    unset($information);
                }
                $this->ajaxReturn(count($delivery),$delivery,1);
            }else{
                $this->ajaxReturn(0,$delivery,1);
            }

        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    //收货地址设为默认
    public function getDefaultAddress(){
        if(I('get.openid')){
            $user=M('user')->where(array('id'=>I('get.uid')))->find();
            if(!I('get.id')){
                $this->ajaxReturn(0,'ID有误',1);
            }
            if($user['id'] && $user['id']>0){
                M('delivery')->where(array('uid'=>I('get.uid')))->save(array('default'=>0));
                M('delivery')->where(array('uid'=>I('get.uid'),'id'=>I('get.id')))->save(array('default'=>1));
                $this->ajaxReturn(1,1,1);
            }else{
                $this->ajaxReturn(0,0,1);
            }

        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    //删除收货地址
    public function getDeleteAddress(){
        if(I('get.openid')){
            $user=M('user')->where(array('id'=>I('get.uid')))->find();
            if($user['id'] && $user['id']>0){
                if(!I('get.id')){
                    $this->ajaxReturn(0,'ID有误',1);
                }
                M('delivery')->where(array('uid'=>I('get.uid'),'id'=>I('get.id')))->delete();
                $this->ajaxReturn(1,1,1);
            }else{
                $this->ajaxReturn(0,0,1);
            }

        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    //收货地址详情
    public function getAddressDetails(){
        if(I('get.openid')){
            if(!I('get.id')){
                $this->ajaxReturn(0,'ID有误',1);
            }
            $delivery=M('delivery')->where(array('uid'=>I('get.uid'),'id'=>I('get.id')))->find();
            if($delivery['id'] && $delivery['id']>0){
                $delivery['information']=json_decode($delivery['information'], true);
                $city=M('newcity')->select();
                foreach($city as $cy){
                    $citys[$cy['id']]=$cy['city'];
                }
                $delivery['cityarray']=array($citys[$delivery['information']['region'][0]],$citys[$delivery['information']['region'][1]],$citys[$delivery['information']['region'][2]]);
                $delivery['information']['city']=$citys[$delivery['information']['region'][0]].','.$citys[$delivery['information']['region'][1]].','.$citys[$delivery['information']['region'][2]];
                $this->ajaxReturn(1,$delivery,1);
            }else{
                $this->ajaxReturn(0,'ID有误',0);
            }

        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    //添加/修改收货地址
    public function getAddAddress(){
        if(I('get.openid')){
            if(I('get.openid')){
                $user=M('user')->where(array('id'=>I('get.uid')))->find();
                if($user['id'] && $user['id']>0){

                    $add['uid']=I('get.uid');
                    $add['information']=$information=json_decode($_GET['information'], true);
                    //更新城市列表
                    $one=M('newcity')->where(array('mid'=>0,'city'=>$information['region'][0]))->find();
                    if($one['id']>0){   //判断省是否已存在
                        $oneid=$one['id'];
                    }else{
                        $oneid=M('newcity')->add(array('mid'=>0,'city'=>$information['region'][0]));
                    }
                    $two=M('newcity')->where(array('mid'=>$oneid,'city'=>$information['region'][1]))->find();
                    if($two['id']>0){   //判断市是否已存在
                        $twoid=$two['id'];
                    }else{
                        $twoid=M('newcity')->add(array('mid'=>$oneid,'city'=>$information['region'][1]));
                    }
                    $three=M('newcity')->where(array('mid'=>$twoid,'city'=>$information['region'][2]))->find();
                    if($three['id']>0){   //判断区是否已存在
                        $threeid=$three['id'];
                    }else{
                        $threeid=M('newcity')->add(array('mid'=>$twoid,'city'=>$information['region'][2]));
                    }

                    $city=M('newcity')->select();
                    foreach($city as $cy){
                        $citys[$cy['city']]=$cy['id'];
                    }
                    foreach($information['region'] as $id=>$i){
                        $add['information']['region'][$id]=$citys[$i];
                    }
                    $add['information']=json_encode($add['information']);
                    if(I('get.id')>0){  //更新
                        M('delivery')->where(array('id'=>I('get.id')))->save(array('information'=>$add['information']));
                    }else{  //添加
                        M('delivery')->add($add);
                    }

                    $this->ajaxReturn(1,1,1);
                }else{
                    $this->ajaxReturn(0,0,1);
                }

            }else{
                $this->ajaxReturn(0,'非法操作',0);
            }
        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    //生成订单
    public function getGenerateOrders(){
        if(I('get.openid')){
            $cart=json_decode($_GET['cart'], true);

            
            foreach($cart as $id=>$n){
                    $details[$id]['goods']=M('goods')->where(array('id'=>$n['id']))->find();
                    $details[$id]['goods']['img']=json_decode($details[$id]['goods']['img'], true);
                    $details[$id]['goods']['attribute']=json_decode($details[$id]['goods']['attribute'], true);
                    $details[$id]['cart']=$n;
            }
            $add['uid']=I('get.uid');
            $add['number']=$this->orderNumber();
            $add['state']=1;
            $add['details']=json_encode($details);

            $add['information']=M('delivery')->where(array('id'=>I('get.addressid')))->getField('information');
            $add['time']=time();
            M('indent')->add($add);
            $this->ajaxReturn(1,$add['number'],1);
        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    //获取商品详情
    public function getGoodsDetails(){

        if(I('get.id')>0){

            $goods = M('goods')->where(array('id'=>I('get.id')))->find();
            $preg = '/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i';
            $data['id']=$goods['id'];
            $data['zimg']=$goods['zimg'];
            $data['img']=json_decode($goods['img'], true);
            array_unshift($data['img'],$data['zimg']);
            $data['attribute']=json_decode($goods['attribute'], true);
            //设置颜色/尺寸状态
            foreach($data['attribute']['color'] as $id=> $color){
                $colorStyle=0;
                foreach($data['attribute']['size'] as $s=>$size){
                    $p=$id*count($data['attribute']['size'])+$s;
                    $colorStyle+=$data['attribute']['stock'][$p];
                    if($data['attribute']['stock'][$p]==0){
                        $data['sizestyle'][$p]=1;
                    }else{
                        $data['sizestyle'][$p]=0;
                    }

                }
                if($colorStyle==0){
                    $data['colorstyle'][$id]=1;
                }else{
                    $data['colorstyle'][$id]=0;
                }
            }

            foreach($data['attribute']['stock'] as $g){
                $data['stock']+=$g;
            }
            $data['title']=$goods['title'];
            $data['sales']=$goods['sales'];
            $data['price']=number_format($goods['price'],2,'.',',');

            preg_match_all($preg, $goods['details'], $imgArr);

            foreach($imgArr[1] as $id=>$i){

                if(is_numeric(strpos($i,'http'))){
                    $data['detailsimg'][$id]=$i;
                }else{
                    $data['detailsimg'][$id]='https://'.$_SERVER['SERVER_NAME'].$i;
                }

            }
            //更新访问次数
            M('goods')->where(array('id'=>I('get.id')))->setInc('sentiment',1);
            $this->ajaxReturn(1,$data,1);
        }else{
            $this->ajaxReturn(0,'参数有误',0);
        }
    }

    //获取购物车商品列表
    public function getCarGoods(){
        $gid=json_decode($_GET['getid'], true);

        if($gid && count($gid)>0){
            foreach($gid as $getid){
                $idarr=explode("-",$getid);
                $getids[]=$idarr[0];
                unset($idarr);
            }

            $where['id']=array('in',$getids);

            $goods = M('goods')->where($where)->select();

            foreach($goods as $id=> $g){
                $data[$g['id']]['title']=$g['title'];
                $data[$g['id']]['id']=$g['id'];
                $data[$g['id']]['zimg']=$g['zimg'];

                $data[$g['id']]['attribute']=json_decode($g['attribute'], true);
            }
            $this->ajaxReturn(1,$data,1);
        }else{
            $this->ajaxReturn(0,'参数有误',0);
        }
    }

    //商品列表/搜索
    public function getCommoditySearch(){

        //排序
        $order='recommend DESC,sales DESC,sentiment DESC,time ASC';	//综合排序
        if(I('get.order')>0){
            switch(I('get.order')){
                case 1:	//新品
                    $order='time DESC';
                    break;
                case 2:	//价格从高到低(朝下)
                    $order='price DESC';
                    break;
                case 3:	//价格从低到高(朝上)
                    $order='price ASC';
                    break;
                case 4:	//销量
                    $order='sales DESC';
                    break;
            }
        }


        //搜索

        if(I('get.type')=='1'){
            $where['title']=array('like','%'.I('get.datavalue').'%');

        }else if(I('get.type')=='2'){  //分类
            $where['fid']=I('get.datavalue');
        }else{
            $this->ajaxReturn(0,'参数有误',0);
        }

        import('ORG.Util.Page');
        $count      = M('goods')->where($where)->count();
        $Page       = new Page($count,10);
        $goods = M('goods')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order($order)->select();
        
        if($goods && count($goods)>0){
            if(($Page->firstRow+$Page->listRows) ==$count){ //当查询数量正好被每页显示数量相同时，结束查询
                $this->ajaxReturn(count($goods),$goods,2);
            }else{
                $this->ajaxReturn(count($goods),$goods,1);
            }

        }else{
            $this->ajaxReturn(0,$data,1);
        }
    }


    //订单列表
    public function getOrderList(){
        if(I('get.openid')){
            $user=M('user')->where(array('id'=>I('get.uid')))->find();
            if(MD5($user['openid'].DS_ENTERPRISE.$user['id'].DS_EN_ENTERPRISE) !=I('get.verify')){
                $this->ajaxReturn(0,'账号不匹配',0);
            }else{
                if(I('get.types')>0){	//栏目切换
                    switch(I('get.types')){
                        case '1':	//待付款
                            $where['state']=1;
                            break;
                        case '2':	//待发货
                            $where['state']=2;
                            break;
                        case '3':	//待收货
                            $where['state']=3;
                            break;
                        default:

                    }

                }else{
                    $where['state']  = array('NEQ',8);
                }
                if(I('get.starttime') !=0){	//日期筛选

                    $where['time'] =  array(array('EGT',strtotime(I('get.starttime').' 00:00:00')),array('ELT',strtotime(I('get.endtime').' 23:59:59')));

                }
                $where['uid']=I('get.uid');

                import('ORG.Util.Page');
                $count      = M('indent')->where($where)->count();

                $Page       = new Page($count,10);
                $indent = M('indent')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('time DESC')->select();
                if($indent && count($indent)>0 ){

                    foreach($indent as $id=>$o){
                        switch($o['state']){
                            case 1:
                                $indent[$id]['statename']='待付款';
                            break;
                            case 2:
                                $indent[$id]['statename']='待发货';
                                break;
                            case 3:
                                $indent[$id]['statename']='待收货';
                                break;
                            case 4:
                                $indent[$id]['statename']='交易成功';
                                break;

                        }
                        $indent[$id]['time']=date("Y-m-d",$o['time']);
                        $details=$indent[$id]['details']=json_decode($o['details'], true);
                        foreach($details as $is=>$d){
                            $scount=count($d['goods']['attribute']['size']);//尺寸数
                            $p=$d['cart']['color']*$scount+$d['cart']['size'];
                            $indent[$id]['p']=$p;
                            $indent[$id]['details'][$is]['price']=$d['goods']['attribute']['price'][$p];
                            $indent[$id]['details'][$is]['color']=$d['goods']['attribute']['color'][$d['cart']['color']];
                            $indent[$id]['details'][$is]['size']=$d['goods']['attribute']['size'][$d['cart']['size']];
                            $total+=$indent[$id]['details'][$is]['price']*$d['cart']['nub'];
                            unset($scount);
                            unset($p);
                        }
                        $indent[$id]['count']=count($details);  //商品总数
                        $indent[$id]['total']=number_format($total,2,'.',',');  //合计
                        unset($total);
                    }
                    if(($Page->firstRow+$Page->listRows) ==$count){ //当查询数量正好被每页显示数量相同时，结束查询
                        $this->ajaxReturn(count($indent),$indent,2);
                    }else{
                        $this->ajaxReturn(count($indent),$indent,1);
                    }

                }else{
                    $this->ajaxReturn(0,$data,1);
                }
            }
        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    //订单详情
    public function getOrderDetails(){

        if(!I('get.id')){
            $this->ajaxReturn(0,'参数有误',0);
        }
        if(I('get.openid')){
            $user=M('user')->where(array('id'=>I('get.uid')))->find();
            if(MD5($user['openid'].DS_ENTERPRISE.$user['id'].DS_EN_ENTERPRISE) !=I('get.verify')){
                $this->ajaxReturn(0,'账号不匹配',0);
            }else{
                $where=array('id'=>I('get.id'));
                $goods=M('indent')->where($where)->find();
                $goods['details']=json_decode($goods['details'], true);
                $goods['information']=json_decode($goods['information'], true);
                foreach($goods['details'] as $is=>$d){
                    $scount=count($d['goods']['attribute']['size']);//尺寸数
                    $p=$d['cart']['color']*$scount+$d['cart']['size'];
                    $goods['details'][$is]['p']=$p;
                    $goods['details'][$is]['price']=$price=$d['goods']['attribute']['price'][$p];
                    $goods['details'][$is]['color']=$d['goods']['attribute']['color'][$d['cart']['color']];
                    $goods['details'][$is]['size']=$d['goods']['attribute']['size'][$d['cart']['size']];
                    $goods['price']+=$price*$d['cart']['nub'];
                    unset($scount);
                    unset($p);
                }
                switch($goods['state']){
                    case 1:
                        $goods['statename']='待付款';
                        break;
                    case 2:
                        $goods['statename']='待发货';
                        break;
                    case 3:
                        $goods['statename']='待收货';
                        break;
                    case 4:
                        $goods['statename']='交易成功';
                        break;
                    case 5:
                        $goods['statename']='订单取消';
                        break;
                    case 6:
                        $goods['statename']='订单退回';
                        break;

                }
                $goods['price']=number_format($goods['price'],2,'.',',');
                if($goods['time']){
                    $goods['time']=date('Y-m-d H:i:s',$goods['time']);
                }
                if($goods['paymenttime']){
                    $goods['paymenttime']=date('Y-m-d H:i:s',$goods['paymenttime']);
                }
                if($goods['deliverytime']){
                    $goods['deliverytime']=date('Y-m-d H:i:s',$goods['deliverytime']);
                }
                if($goods['endtime']){
                    $goods['endtime']=date('Y-m-d H:i:s',$goods['endtime']);
                }
                $city=$this->city();
                $goods['city']=$city[$goods['information']['region'][0]].' '.$city[$goods['information']['region'][1]].' '.$city[$goods['information']['region'][2]];
                $this->ajaxReturn(1,$goods,1);
            }

        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }



    //资金明细
    public function getCapitalSubsidiary(){
        if(I('get.openid')){
            $user=M('user')->where(array('id'=>I('get.uid')))->find();
            if(MD5($user['openid'].DS_ENTERPRISE.$user['id'].DS_EN_ENTERPRISE) !=I('get.verify')){
                $this->ajaxReturn(0,'账号不匹配',0);
            }else{

                $where['uid']=I('get.uid');
                $where['type']=0;
                import('ORG.Util.Page');
                $count      = M('money_log')->where($where)->count();
                $Page       = new Page($count,10);
                $money_log = M('money_log')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('time DESC')->select();
                if($money_log && count($money_log)>0){
                    foreach($money_log as $id=>$m){
                        $data[$id]['id']=$m['id'];
                        switch($m['finetype']){
                            case 1:
                                $data[$id]['typename']='充值';
                                break;
                            case 2:
                                $data[$id]['typename']='提现';
                                break;
                            case 3:
                                $data[$id]['typename']='交易';
                                break;
                        }

                        $data[$id]['time']=date('Y-m-d H:i:s',$m['time']);	//时间
                        if($m['finetype']==1){	//增加状态
                            $data[$id]['operation']='+'.number_format($m['operation'],2,'.',',');
                        }else{
                            $data[$id]['operation']='-'.number_format($m['operation'],2,'.',',');
                        }
                    }
                    if(($Page->firstRow+$Page->listRows) ==$count){ //当查询数量正好被每页显示数量相同时，结束查询
                        $this->ajaxReturn(count($money_log),$data,2);
                    }else{
                        $this->ajaxReturn(count($money_log),$data,1);
                    }

                }else{
                    $this->ajaxReturn(0,$data,1);
                }
            }
        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    //资金明细详情
    public function getCapitalSubsidiaryDetails(){
        if(!I('get.id')){
            $this->ajaxReturn(0,'参数有误',0);
        }
        if(I('get.openid')){
            $user=M('user')->where(array('id'=>I('get.uid')))->find();
            if(MD5($user['openid'].DS_ENTERPRISE.$user['id'].DS_EN_ENTERPRISE) !=I('get.verify')){
                $this->ajaxReturn(0,'账号不匹配',0);
            }else{
                $where['id']=I('get.id');
                $money_log = M('money_log')->where($where)->find();

                $data['operation']=number_format($money_log['operation'],2,'.',',');	//操作金额
                $data['time']=date('Y-m-d H:i:s',$money_log['time']);	//时间
                $data['balance']=number_format($money_log['available_funds'],2,'.',',');	//余额
                $data['actionname']=$money_log['actionname'];   //操作说明
                switch($money_log['finetype']){
                    case 1:
                        $data['typename']='充值';
                        break;
                    case 2:
                        $data['typename']='提现';
                        break;
                    case 3:
                        $data['typename']='交易';
                        break;
                }
                $this->ajaxReturn(1,$data,1);
            }

        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    //文章列表
    public function getArticleLists(){
        if(!I('get.id')){
            $this->ajaxReturn(0,'参数有误',0);
        }
        $article=M('article')->where(array('catid'=>I('get.id')))->select();
        foreach($article as $id=>$a){
            $data[$id]['title']=$a['title'];	//标题
            $data[$id]['id']=$a['id'];	//	ID
        }
        $this->ajaxReturn(1,$data,1);
    }

    //文章显示
    public function content(){
        if(!I('get.id')){
            $this->ajaxReturn(0,'参数有误',0);
        }
        if(I('get.gid')==1){	//栏目内容
            $site=M('site')->where(array('id'=>I('get.id')))->find();
            $site_add=M('site_add')->where(array('id'=>$site['aid']))->find();
            $data['title']=$site['title'];	//标题
            $data['addtime']=date('Y-m-d',$site['addtime']);	//时间
            $data['introtext']=$site_add['content'];	//内容
        }else{
            $article=M('article')->where(array('id'=>I('get.id')))->find();
            $data['title']=$article['title'];	//标题
            $data['addtime']=date('Y-m-d',$article['addtime']);	//时间
            $data['introtext']=$article['introtext'];	//内容
        }
        $this->assign('countent',$data);
        $this->display();
    }

    //意见反馈
    public function setfeedback(){
        if(I('get.openid')){
            $user=M('user')->where(array('id'=>I('get.uid')))->find();
            if(MD5($user['openid'].DS_ENTERPRISE.$user['id'].DS_EN_ENTERPRISE) !=I('get.verify')){
                $this->ajaxReturn(0,'账号不匹配',0);
            }else{
                $data['uid']=I('get.uid');
                $data['note']=I('get.note');
                $data['time']=time();
                M('feedback')->add($data);
                $this->ajaxReturn(1,'成功',1);
            }

        }else{
            $this->ajaxReturn(0,'非法操作',0);
        }
    }

    /**
     * 删除订单
     */
    public function deleteOrder(){
        if(I('get.openid')){
            if(!I('get.id')){
                $this->ajaxReturn(0,'参数有误',0);
            }
            $user=M('user')->where(array('id'=>I('get.uid')))->find();
            if(MD5($user['openid'].DS_ENTERPRISE.$user['id'].DS_EN_ENTERPRISE) !=I('get.verify')){
                $this->ajaxReturn(0,'账号不匹配',0);
            }else{
                M('indent')->where(array('id'=>I('get.id')))->save(array('state'=>8));
                 $this->ajaxReturn(1,'删除成功',1);

            }

        }else{
            $this->ajaxReturn(0,0,0);
        }

    }

    /**
     * 物流展示
     */
    public function getLogistics(){
        if(I('get.openid')){
            if(!I('get.id')){
                $this->ajaxReturn(0,'参数有误',0);
            }
            $user=M('user')->where(array('id'=>I('get.uid')))->find();
            if(MD5($user['openid'].DS_ENTERPRISE.$user['id'].DS_EN_ENTERPRISE) !=I('get.verify')){
                $this->ajaxReturn(0,'账号不匹配',0);
            }else{
                $indent=M('indent')->where(array('id'=>I('get.id')))->find();
                if(!$indent['cnumber']){
                    $this->ajaxReturn(2,'暂无轨迹信息',1);
                }
                $getOrderTracesByJson=$this->getOrderTracesByJson($indent['express'],$indent['cnumber']);

                if($getOrderTracesByJson['Success']==1){
                    if($getOrderTracesByJson['State']==0){
                        $this->ajaxReturn(2,'暂无轨迹信息',1);
                    }else{
                        krsort($getOrderTracesByJson['Traces']);
                        $traces=array_values($getOrderTracesByJson['Traces']);  //快递轨迹
                        $this->ajaxReturn(1,$traces,1);
                    }

                }else{

                    $this->ajaxReturn(0,'快递订单号有误，请联系客服',0);
                }

            }

        }else{
            $this->ajaxReturn(0,0,0);
        }

    }

    /**
     *  快递鸟
     * Json方式 查询订单物流轨迹
     * $ShipperCode     快递公司编号
     * $LogisticCode    快递单号
     */
    private function getOrderTracesByJson($ShipperCode,$LogisticCode){

        $requestData= "{'OrderCode':'','ShipperCode':'".$ShipperCode."','LogisticCode':'".$LogisticCode."'}";

        $datas = array(
            'EBusinessID' => '1342451',//电商ID
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, 'c21230d1-dace-4c67-b552-d7c991190c90');  //电商加密私钥，快递鸟提供，注意保管，不要泄漏
        $result=$this->sendPost('http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx', $datas);//请求url

        //根据公司业务处理返回的信息......

        return json_decode($result, true);
    }

    /**
     *  快递鸟
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    private function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    private function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }

    /**
     * 微信支付
     */
    public function setwxPay(){
        if(!I('get.number')){
            $this->ajaxReturn(0,'订单号有误',0);
        }
        if(I('get.openid')){
            $user=M('user')->where(array('id'=>I('get.uid')))->find();
            if(MD5($user['openid'].DS_ENTERPRISE.$user['id'].DS_EN_ENTERPRISE) !=I('get.verify')){
                $this->ajaxReturn(0,'账号不匹配',0);
            }else {
                header("Content-Type:text/html; charset=utf-8");
                import('@.Plugin.wxpay.wxPayApi');
                import('@.Plugin.wxpay.wxPayNotify');
                $indent=M('indent')->where(array('number'=>I('get.number')))->find();
                if(!$indent['id']){
                    $this->ajaxReturn(0,'订单不存在',0);
                }
                $details=$indent['details']=json_decode($indent['details'], true);

                foreach($details as $is=>$d){

                    $scount=count($d['goods']['attribute']['size']);//尺寸数
                    $p=$d['cart']['color']*$scount+$d['cart']['size'];
                    $indent['p']=$p;
                    $title=$d['goods']['title'];


                    $total+=$d['goods']['attribute']['price'][$p]*$d['cart']['nub'];
                    unset($scount);
                    unset($p);
                }

                if(count($details)>1){
                    $title=$title.'等多件';
                }

                $total=$total*100;  //合计单位：分
                $WxPayApi = new WxPayApi();
                $inputObj = new WxPayUnifiedOrder();


                $data['place']['SetOut_trade_no']=$this->orderNumber();
                $inputObj->SetOut_trade_no($data['place']['SetOut_trade_no']); //商户订单号
                $data['place']['SetBody']=$title;
                $inputObj->SetBody($title); //商品描述
                //$inputObj->SetBody('测试'); //商品描述

                $data['place']['SetTotal_fee']=$total;
                $inputObj->SetTotal_fee($total); //金额（单位为分
                //$inputObj->SetTotal_fee('1'); //金额（单位为分
                $data['place']['SetTrade_type']='JSAPI';
                $inputObj->SetTrade_type('JSAPI'); //交易类型，小程序为JSAPI
                $data['place']['SetNotify_url']='https://'.$_SERVER['SERVER_NAME'] . '/Api/Index/setwxPayNotify';
                $inputObj->SetNotify_url('https://'.$_SERVER['SERVER_NAME'] . '/Api/Index/setwxPayNotify'); //异步通知地址
                $data['place']['SetTrade_type']=I('get.openid');
                $inputObj->SetOpenid(I('get.openid'));     //用户openid，小程序传过来
                $add['data']=json_encode($data);    //微信支付数据
                $add['iid']=$indent['id'];   //订单ID
                $add['number']=$data['place']['SetOut_trade_no'];   //商户订单号
                $add['time']=time();    //支付发起时间
                $add['state']=1;    //支付状态
                $add['uid']=I('get.uid');    //用户ID
                M('wxpay')->add($add);
                $unifiedOrder = $WxPayApi->unifiedOrder($inputObj);   //统一下单
                if ($unifiedOrder['result_code'] == 'SUCCESS' && $unifiedOrder['return_code'] == 'SUCCESS') { //result_code和return_code都为真是才正确
                    $jsapi = new WxPayJsApiPay();
                    $jsapi->SetAppid($unifiedOrder["appid"]);
                    $timeStamp = time();
                    $jsapi->SetTimeStamp("$timeStamp");
                    $jsapi->SetNonceStr(WxPayApi::getNonceStr());
                    $jsapi->SetPackage("prepay_id=" . $unifiedOrder['prepay_id']);
                    $jsapi->SetSignType("MD5");
                    $jsapi->SetPaySign($jsapi->MakeSign());
                    $this->ajaxReturn(1, $jsapi->GetValues(), 1);  //微信生成的预支付会话标识
                } else {
                    $this->ajaxReturn(0, $unifiedOrder['return_msg'], 0);
                }


            }

        }else{
            $this->ajaxReturn(0,0,0);
        }
    }

    /**
     * 微信支付异步通知地址
     */
    public function setwxPayNotify(){
        import('@.Plugin.wxpay.wxPayApi');
        import('@.Plugin.wxpay.PayNotifyCallBack');
        $notify = new PayNotifyCallBack();
        $notify->Handle(false);
    }



}