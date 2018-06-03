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
class SharingAction extends Action{
	/**
	 * @前台验证
     * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function homeVerify(){
		if($this->_session('user_uid')){
			$user=M('user');
			$users=D('User')->relation('userinfo')->where('`id`='.$this->_session('user_uid'))->find();
			if($this->_session('user_verify') !== MD5($users['username'].DS_ENTERPRISE.$users['password'].DS_EN_ENTERPRISE)){
				session('user_uid',null);
				session('user_name',null);
				session('user_verify',null);
				$this->error("请先重新登陆",'__ROOT__/Logo/login.html');
			}
			
		}else{
			$this->error("请先登陆",'__ROOT__/Logo/login.html');
		}
	 }
	 
	/**
	  * @返回值/错误信息
	  * @in		数组
	  *
	  */
	 protected function remote($in){
		if($in['value'] == 'NO'){
			$this->error($in['error'],$in['url']);
		}else if($in['value'] == 'accredit'){
			$this->error($in['error'],$in['url']);	
		}else{
			return $in['value'];
		}	
	 }
	 
	/**
	 * @根据id生成唯一订单号
	 * @当前时间戳+随机
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function orderNumber() {
		$order=preg_replace('/\./','',microtime(true). str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT));
		if(strlen($order)==18){
			$order=$order.'0';
		}
		return $order;
	}


	/**
	 * @(有待完善，当失败、签收状态对比、颜色变化等)
	 * @快递派送流程
	 * @name		快递公司(不支持中文)
	 * @number		快递单号
	 * @作者			shop猫
	 * @版权			宁波天发网络
	 * @官网			http://www.tifaweb.com http://www.dswjcms.com
	 * @快递公司查询：http://code.google.com/p/kuaidi-api/wiki/Open_API_API_URL
	 *
	 */
	protected function expressQuery($name,$number){
		$jsons=file_get_contents("http://www.kuaidi100.com/query?type=".$name."&postid=".$number."&id=1");
		$kuaidi=json_decode($jsons,true);	//json转数组
		sort($kuaidi['data']);	//排序
		$array['data']=$kuaidi['data'];
		$array['end']=end($kuaidi['data']);//取最新一条
		return $array;
	}

	/**
     * @后台操作记录
     * @type    记录类型
     * @id      是否开启
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
     */
    protected function Record($type,$id=0){
        if($id==0){
            $Operation = M('operation');
            $array['name']= $_SESSION['admin_name'];
            $array['page']= $_SERVER['PHP_SELF'];
            $array['type']= $type;
            $array['ip']= get_client_ip();
            $array['time']= time();
            $Operation->add($array);
        }
    }

	/**
	 *
	 * @城市
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */	
	protected function city(){
		$citys = F('city');  // 获取缓存
		if(!$citys){
			$city	=	M('newcity');
			$city=$city->select();
			foreach($city as $cy){
				$citys[$cy['id']]=$cy['city'];
			}
			F('city',$citys);	//设置缓存
		}
		return $citys;
	}
	
	/**
	 * @取前几条数据
	 * @m		传入的model
	 * @w		查询条件
	 * @o		排序
	 * @l		条数
	 * @r		是否关联查询
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function top($m,$w,$o,$l,$r=1) {
		$model=D($m);
		if($r==1){
			return $model->relation(true)->where($w)->order($o)->limit($l)->select();
		}else{
			return $model->where($w)->order($o)->limit($l)->select();
		}
		
	}

   /**
	* @积分配置
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function integralConf(){
		$system=M('integralconf');
		$system=$system->select();
		foreach($system as $s){
			$sys[$s['name']]=array($s['value'],$s['state']);
		}
		return $sys;
	}

    /**
	 * @积分添加
	 * @array	参数
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 *--------------使用说明-----------------
	 $add['member']=array(
						'uid'=>1,	//用户名
						'name'=>'mem_register',	//积分配置表中的积分名
					);
	 $this->integralAdd($add);//积分添加
	 *--------------------------------------
	 */
	protected function integralAdd($array) {
		$Model = new Model();
		$inf=$this->integralConf();
		if(isset($array['member'])){	//会员积分
		
			if(isset($inf[$array['member']['name']])){	//判断用户提交上来的积分名是否存在积分配置表中
				$upda=$Model->execute("update ds_ufees set `total`=`total`+".$inf[$array['member']['name']][0].",`available` = `available`+".$inf[$array['member']['name']][0]." where uid='".$array['member']['uid']."'");//会员积分更新
				$ufee=$Model->table('ds_ufees')->where('uid='.$array['member']['uid'])->find();
				if($upda){
					
					$arr[0]=1;
					$arr[1]=$inf[$array['member']['name']][1];
					$arr[2]=$inf[$array['member']['name']][0];
					$arr[3]='平台';
					$arr[4]=$ufee['total'];
					$arr[5]=$ufee['available'];
					$arr[6]=$ufee['freeze'];
					$arr[7]=$array['member']['uid'];
					$moneyLog=$this->moneyLog($arr);
				}
			}
		}
		if(isset($array['vip'])){	//VIP
			if(isset($inf[$array['vip']['name']])){	//判断用户提交上来的积分名是否存在积分配置表中
				$upda=$Model->execute("update ds_vip_points set `total`=`total`+".$inf[$array['vip']['name']][0].",`available` = `available`+".$inf[$array['vip']['name']][0]." where uid='".$array['vip']['uid']."'");//VIP积分更新
				$ufee=$Model->table('ds_vip_points')->where('uid='.$array['vip']['uid'])->find();
				if($upda){
					$arr[0]=2;
					$arr[1]=$inf[$array['vip']['name']][1];
					$arr[2]=$inf[$array['vip']['name']][0];
					$arr[3]='平台';
					$arr[4]=$ufee['total'];
					$arr[5]=$ufee['available'];
					$arr[6]=$ufee['freeze'];
					$arr[7]=$array['vip']['uid'];
					$moneyLog=$this->moneyLog($arr);
				}
			}
		}
		if(isset($array['promote'])){	//推广积分
			if(isset($inf[$array['promote']['name']])){	//判断用户提交上来的积分名是否存在积分配置表中
				$upda=$Model->execute("update ds_promote_integral set `total`=`total`+".$inf[$array['promote']['name']][0].",`available` = `available`+".$inf[$array['promote']['name']][0]." where uid='".$array['promote']['uid']."'");//会员积分更新
				$ufee=$Model->table('ds_promote_integral')->where('uid='.$array['promote']['uid'])->find();
				if($upda){
					$arr[0]=3;
					$arr[1]=$inf[$array['promote']['name']][1];
					$arr[2]=$inf[$array['promote']['name']][0];
					$arr[3]='平台';
					$arr[4]=$ufee['total'];
					$arr[5]=$ufee['available'];
					$arr[6]=$ufee['freeze'];
					$arr[7]=$array['promote']['uid'];
					$moneyLog=$this->moneyLog($arr);
				}
			}
		}
		return 1;
	}
	
    /**
	 *
	 * @邮件发送
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function email_send($arr){
		$this->copyright();
		import('ORG.Custom.PhpMailer');
		$mail = new PHPMailer(); 
		$smtp			=	$arr['smtp'];
		$validation		=	$arr['validation'];
		$send_email		=	$arr['send_email'];
		$password		=	$arr['password'];
		$addresser		=	$arr['addresser'];
		$receiver_email_array  =	array_filter(explode(',',$arr['receiver_email_array']));
		$receipt_email	=  	$arr['receipt_email'];
		$title			=	$arr['title'];
		$content		=	$arr['content'];
		$addattachment	=	$arr['addattachment'];
		$ishtml			=	$arr['ishtml'];
		$mail->IsSMTP(); // 使用SMTP方式发送
		$mail->CharSet='UTF-8';// 设置邮件的字符编码
		$mail->Host = "$smtp"; // 您的企业邮局域名
		$mail->SMTPAuth = $validation==1?true:false; // 启用SMTP验证功能
		$mail->Username = "$send_email"; // 邮局用户名(请填写完整的email地址)
		$mail->Password = "$password"; // 邮局密码
		$mail->From = "$send_email"; //邮件发送者email地址
		$mail->FromName = "$addresser";	//发件人
		if($receiver_email_array){	//群发
			foreach($receiver_email_array as $rea){
				$mail->AddAddress("$rea");
			}
		}else{
			$mail->AddAddress("$receipt_email");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
		}
		//$mail->AddReplyTo("", "");	//添加回复
		if($addattachment){
			$mail->AddAttachment("$addattachment"); // 添加附件
		}
		$mail->IsHTML($ishtml==1?true:false); // set email format to HTML //是否使用HTML格式
		$mail->Subject = "$title"; //邮件标题
		$mail->Body = "$content"; //邮件内容
		$mail->AltBody = "点石为金借贷"; //附加信息，可以省略
		if(!$mail->Send())
		{
			
			//echo '邮件发送失败. <p>错误原因: '. $mail->ErrorInfo;
			//exit;
			//如果不成功，就再次执行，直接成功为止
			$mail->Smtpclose();	//关闭
			$mail = new PHPMailer(); 
			$mail->IsSMTP(); // 使用SMTP方式发送
			$mail->CharSet='UTF-8';// 设置邮件的字符编码
			$mail->Host = "$smtp"; // 您的企业邮局域名
			$mail->SMTPAuth = $validation==1?true:false; // 启用SMTP验证功能
			$mail->Username = "$send_email"; // 邮局用户名(请填写完整的email地址)
			$mail->Password = "$password"; // 邮局密码
			$mail->From = "$send_email"; //邮件发送者email地址
			$mail->FromName = "$addresser";	//发件人
			if($receiver_email_array){	//群发
				foreach($receiver_email_array as $rea){
					$mail->AddAddress("$rea");
				}
			}else{
				$mail->AddAddress("$receipt_email");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
			}
			//$mail->AddReplyTo("", "");	//添加回复
			if($addattachment){
				$mail->AddAttachment("$addattachment"); // 添加附件
			}
			$mail->IsHTML($ishtml==1?true:false); // set email format to HTML //是否使用HTML格式
			$mail->Subject = "$title"; //邮件标题
			$mail->Body = "$content"; //邮件内容
			$mail->AltBody = "点石为金借贷"; //附加信息，可以省略
		}
		return true;
    }
		
   /**
	*
	* @系统配置
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function systems(){
		$this->copyright();
		$sys = F('systems');  // 获取缓存
		if(!$sys){
			$system	=	M('system');
			$system=$system->select();
			foreach($system as $s){
				$sys[$s['name']]=$s['value'];
			}
			F('systems',$sys);	//设置缓存
		}
		return $sys;
	}
	
	/**
	*
	* @会员操作记录
	* @arr		记录说明
	* @uid		用户ID
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
    protected function userLog($arr,$uid){
			$models = new Model();
            $array['uid']		= $uid?$uid:$this->_session('user_uid');
			$array['actionname']= $arr;
			$array['page']		= $_SERVER['PHP_SELF'];
            $array['ip']		= get_client_ip();
            $array['time']		= time();
			return $models->table('ds_user_log')->add($array);
    }

	/**
     * @资金/积分操作记录
     * @array   0操作类型1操作说明2操作金额3交易对方4总额5余额6冻结7用户
	 * @array	类型细分
     * @id      是否开启
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
     */
    protected function moneyLog($array,$finetype,$id=0){
        if($id==0){
			$models = new Model();
            $arrays['uid']				= $array[7]?$array[7]:$this->_session('user_uid');
            $arrays['type']				= $array[0];
			$arrays['actionname']		= $array[1];
			$arrays['total_money']		= $array[4];
			$arrays['available_funds']	= $array[5];
			$arrays['freeze_funds']		= $array[6];
			$arrays['counterparty']		= $array[3];
			$arrays['operation']		= $array[2];
			$arrays['finetype']			= $finetype?$finetype:'1';
            $arrays['time']				= time();
			$arrays['ip']				= get_client_ip();
			return $models->table('ds_money_log')->add($arrays);
        }
    }
	
   /**
	* @认证资料
	* @id	0全部1实名2视频3现场4手机
	* @q	不为0时显示认证信息
	* @limit	条数
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function audit($id,$q=0,$limit){
		if($id==1){
			$where="`certification`=1";
		}else if($id==2){
			$where="`video_audit`=1";
		}else if($id==3){
			$where="`site_audit`=1";
		}else if($id==4){
			$where="`cellphone_audit`=1";
		}else{
			$where=$id;
		}
		if($q){
			$field=",certification,email_audit,cellphone_audit,video_audit,site_audit,wechat_audit";
		}else{
			$field='';
		}
		$unite	=	M('unite');
		$userinfo	=	D('Userinfo');
		$citys=$this->city();
		$unite=$unite->field('pid,name,value')->where('`state`=0 and `pid`=13')->order('`order` asc,`id` asc')->select();
		foreach($unite as $ue){
			$unites[$ue['value']]=$ue['name'];
		}
		
		$userinfo=$userinfo->field('id,uid,name,gender,national,born,idcard,idcard_img,cellphone,native_place'.$field)->relation(true)->where($where)->order('`id` DESC')->limit($limit)->select();
		foreach($userinfo as $id=>$ufo){
			$idcard_img=explode(",",$ufo['idcard_img']);
			$native_place=explode(" ",$ufo['native_place']);
			$native_place=$citys[$native_place[0]]." ".$citys[$native_place[1]]." ".$citys[$native_place[2]];
			$userinfo[$id]['native_place']=$native_place;
			$userinfo[$id]['idcard_img']=$idcard_img;
			$userinfo[$id]['national']=$unites[$ufo['national']];
			$userinfo[$id]['gender']=$ufo['gender']?"女":"男";
			$userinfo[$id]['cellphone']=$ufo['cellphone'];
			unset($userinfo[$id]['join_date']);
		}	
		return $userinfo;
    }
	
   /**
	*
	* @线下银行
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function offlineBank(){
		$unite=M('unite');
		$offline=M('offline');
		$list=$unite->field('name,value')->where('`state`=0 and `pid`=1')->order('`order` asc,`id` asc')->select();
		$audit=$offline->order('`id` DESC')->select();
		foreach($list as $lt){
			$userinfos[$lt['value']]=$lt['name'];
		}
		foreach($audit as $id=>$au){
			$audit[$id]['type_name']=$userinfos[$au['type']];
		}
		return $audit;
	}	
	
   /**
	* @提现手续费
	* @m	提现金额
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function withdrawalPoundage($m=0){
		$systems=$this->systems();
		if($m>0){
			if($m<=$systems['sys_wFPoundage']){	//小于免费提现额度
				$wfp=0;
			}else{	//提现手续费=（提现金额-免费额度）*提现手续费率
				$wfp=round(($m-$systems['sys_wFPoundage'])*$systems['sys_withdrawalPoundage'],2);
			}
		}
		return $wfp;
	}
	
  /**	
	* @提现用户详细
	* @id		查询id
	* @uid		用户id
	* @where	条件
	* @limit	条数
	* @order	排序
	*
	*/
	protected function showUser($id=0,$uid=0,$where,$limit,$order){
		$order=$order?$order:'`time` DESC,`id` DESC';
		$withdrawal=D('Withdrawal');
		if($id){	//单记录
			$withdrawals=reset($withdrawal->relation('user')->where('id='.$id)->order($order)->limit($limit)->select());
		}else{
			if($uid>0){	//单个用户
				$withdrawals=$withdrawal->relation('user')->where('uid='.$uid)->order($order)->limit($limit)->select();
			}else{	//所有用户信息
				$withdrawals=$withdrawal->relation('user')->where($where)->order($order)->limit($limit)->select();
			}
		}
		return $withdrawals;
    }
	
   /**
	* @充值用户详细
	* @id		查询id
	* @uid		用户id
	* @where	条件
	* @limit	条数
	* @order	排序
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com	
	*
	*/
	protected function rechargeUser($id=0,$uid=0,$where,$limit,$order){
		$order=$order?$order:'`time` DESC,`id` DESC';
		$recharge=D('Recharge');
		$unite=M('unite');
		$list=$unite->field('pid,name,value')->where('(`pid` = 1 or `pid` = 2 ) and `state`=0')->order('`order` asc,`id` asc')->select();
		foreach($list as $lt){
			if($lt['pid']==2){
				$online[$lt['value']]=$lt['name'];	//网上
			}else{
				$unites[$lt['value']]=$lt['name'];	//线下
			}
		}
		unset($list);
		$offline=$this->offlineBank();
		foreach($offline as $of){
			$offlin[$of['id']]=$of;
		}
		if($id){	//单记录
			$recharges=reset($recharge->relation(true)->where('id='.$id)->order($order)->select());
			$recharges['genre_name']			=	$online[$recharges['genre']];
			$recharges['oid_array']				=	$offlin[$recharges['oid']];
		}else{
			if($uid>0){	//单个用户
					$recharges=$recharge->relation(true)->where('uid='.$uid)->order($order)->limit($limit)->select();
				foreach($recharges as $id=>$ws){
					$recharges[$id]['genre_name']			=	$online[$ws['genre']];
				}
			}else{	//所有用户信息
				$recharges=$recharge->relation(true)->where($where)->order($order)->limit($limit)->select();
				foreach($recharges as $id=>$ws){
					$recharges[$id]['genre_name']			=	$online[$ws['genre']];
					$recharges[$id]['oid_name']				=	$offlin[$ws['oid']]['bank'];
				}
			}
		}
		return $recharges;
    }
   /**
    * @充值手续费
	* @m	充值金额
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function topUpFees($m=0){
		$systems=$this->systems();
		if($m>0){
			if($systems['sys_topUFC']==0){	//大于免费额度收取手续费
				if($m<=$systems['sys_rechargeFA']){	//小于免费提现额度
					$wfp=0;
				}else{	//充值手续费=（充值金额-免费额度）*充值手续费
					$wfp=round(($m-$systems['sys_rechargeFA'])*$systems['sys_topUpFees'],2);
				}
			}else if($systems['sys_topUFC']==1){	//小于免费额度收取手续费
				if($m<=$systems['sys_rechargeFA']){	//小于免费提现额度
					//充值手续费=（充值金额-免费额度）*充值手续费率
					$wfp=round($m*$systems['sys_topUpFees'],2);
				}else{
					$wfp=0;
				}
			}
		}
		return $wfp;
	}
	
	/**
    * @线上充值手续费
	* @m	充值金额
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function onlineUpFees($m=0){
		$systems=$this->systems();
		if($m>0){
			//充值手续费=充值金额*充值手续费
			$wfp=round($m*$systems['sys_onlinePoundage'],2);
		}
		return $wfp;
	}
	
   /**
	*
	* @资金表
	* @field		需要的字段
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function moneys($field){
		$money=M('money');
		$money=$money->field($field)->select();

		if(!$money){
			$this->error("请提交正确的field，如没有可为空！");
		}
		foreach($money as $my){
			$moneys[$my['uid']]=$my;
		}
		return $moneys;
	}
	
   /**
	*
	* @资金单条记录
	* @uid		用户id
	* @field	需要的字段
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function moneySingle($uid,$field){
		$money=$this->moneys($field);
		return $money[$uid];
	}
	
	/**
	 *
	 * @资金记录
	 * @uid		用户ID
	 * @l		条数
	 * @order	排序
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 *
	 */
	protected function moneyRecord($uid,$l,$order){
		$order=$order?$order:'time DESC,id DESC ';
		$uids=$uid?' and uid='.$uid:'';
		$money_log=D('Money_log');
		
		$list=$money_log->relation(true)->where('`type`=0'.$uids)->order($order)->limit($l)->select();	//资金使用记录
		return $list;
		
	}
	
	/**
	*
	* @excel列转换
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function letter() {
		return $array=array(0=>'A',1=>'B',2=>'C',3=>'D',4=>'E',5=>'F',6=>'G',7=>'H',8=>'I',9=>'J',10=>'K',11=>'L',12=>'M',13=>'N',14=>'O',15=>'P',16=>'Q',17=>'R',18=>'S',19=>'T',20=>'U',21=>'V',22=>'W',23=>'X',24=>'Y',25=>'Z');
	}
	
	/**
	*
	* @excel导出
	* @作者			天发网络科技
	* @版权			http://www.tifaweb.com
	* @$array		数据数组
	* @-moder			所采用的模板 默认为template
	* @-title			标题
	* @-name			小标题（数组）
	* @--n					字段名
	* @--u					字段英文名
	* @--t					字段类型
	* @-content			数据(数组)
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*
	*/
	protected function excelExport($array) {
		Vendor ( 'Excel.PHPExcel' );
		$letter=$this->letter();//引入列换算
		$mode=$array['moder']?$array['moder']:'t1.xls';	//获取模板
		$mode='Public/excel/'.$mode;
		//创建一个读Excel模版的对象
		$objReader = PHPExcel_IOFactory::createReader ( 'Excel5' );
		$objPHPExcel = $objReader->load ($mode);
		//获取当前活动的表
		$objActSheet = $objPHPExcel->getActiveSheet ();
		$objActSheet->setTitle ($array['title']);
		$baseRow = 2; //数据从N-1行开始往下输出  这里是避免头信息被覆盖
		//我现在就开始输出列头了
		foreach($array['name'] as $id=>$name){
			$objActSheet->setCellValue ($letter[$id].'1',$name['n']);
			foreach ( $array['content'] as $r => $dataRow ) {
				$row = $baseRow + $r;
				//将数据填充到相对应的位置
				$objPHPExcel->getActiveSheet ()->setCellValue ( $letter[$id] . $row,$dataRow [$name['u']]);
			}
		}
		//导出
		$filename = time ();
		
		header ( 'Content-Type: application/vnd.ms-excel' );
		header ( 'Content-Disposition: attachment;filename="' . $filename . '.xls"' ); //"'.$filename.'.xls"
		header ( 'Cache-Control: max-age=0' );
		
		$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel5' ); //在内存中准备一个excel2003文件
		$objWriter->save ( 'php://output' );
		return true;
	}
	
	/**
	 * @后台总数据统计
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function statistical(){
		$recharge=M('recharge');
		$withdrawal=M('withdrawal');
		$user=M('user');
		$money=M('money');
		$certification=M('certification');
		$array['recharge']=$recharge->where('type=1 and genre=0')->count();	//充值申请
		$array['withdrawal']=$withdrawal->where('type=1')->count();	//提现申请
		$array['distribution']=M('indent')->where('`state`=2')->count();	//待配货
		//总
		$array['metotal']=$user->count();	//会员总数
		$array['mototals']=$money->sum('total_money');	//平台总资金
		$array['mototal']=number_format($array['mototals'],2,'.',',');
		$array['frtotal']=$money->sum('freeze_funds');	//冻结总资金
		$array['frtotal']=number_format($array['frtotal'],2,'.',',');
		$array['wmototals']=$withdrawal->where('type=2')->sum('money');	//提现总资金
		$array['wmototal']=number_format($array['wmototals'],2,'.',',');
		$array['rmototals']=$recharge->where('type=2')->sum('money');	//充值总资金
		$array['rmototal']=number_format($array['rmototals'],2,'.',',');
		//今天
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$where='time >='.$beginToday.' and time <='.$endToday;
		$wheres='audittime >='.$beginToday.' and audittime <='.$endToday;
		$array['menow']=$user->where($where)->count();	//会员数
		$array['wmonow']=$withdrawal->where('type=2 and '.$wheres)->sum('money');	//提现总资金
		$array['wmonow']=number_format($array['wmonow'],2,'.',',');
		$array['rmonow']=$recharge->where('type=2 and '.$wheres)->sum('money');	//充值总资金
		$array['rmonow']=number_format($array['rmonow'],2,'.',',');		
		//本周
		$time = time();
		//判断当天是星期几，0表星期天，1表星期一，6表星期六
		$w_day=date("w",$time);
 		//php处理当前星期时间点上，根据当天是否为星期一区别对待
	  	if($w_day=='1'){
			$cflag = '+0';
			$lflag = '-1';
	   	}
	  	else {
			  $cflag = '-1';
			  $lflag = '-2';
	   	}
		//本周一零点的时间戳
		$beginLastweek = strtotime(date('Y-m-d',strtotime("$cflag week Monday", $time)));        
		//本周末零点的时间戳
		$endLastweek = strtotime(date('Y-m-d',strtotime("$cflag week Monday", $time)))+7*24*3600;
		$where='time >='.$beginLastweek.' and time <='.$endLastweek;
		$wheres='audittime >='.$beginLastweek.' and audittime <='.$endLastweek;
		$array['meweeks']=$user->where($where)->count();	//会员数
		$array['wmoweeks']=$withdrawal->where('type=2 and '.$wheres)->sum('money');	//提现总资金
		$array['wmoweeks']=number_format($array['wmoweeks'],2,'.',',');
		$array['rmoweeks']=$recharge->where('type=2 and '.$wheres)->sum('money');	//充值总资金
		$array['rmoweeks']=number_format($array['rmoweeks'],2,'.',',');		
		//本月
		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y')); 
		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
		$where='time >='.$beginThismonth.' and time <='.$endThismonth;
		$wheres='audittime >='.$beginThismonth.' and audittime <='.$endThismonth;
		$array['memonth']=$user->where($where)->count();	//会员数
		$array['wmomonth']=$withdrawal->where('type=2 and '.$wheres)->sum('money');	//提现总资金
		$array['wmomonth']=number_format($array['wmomonth'],2,'.',',');
		$array['rmomonth']=$recharge->where('type=2 and '.$wheres)->sum('money');	//充值总资金
		$array['rmomonth']=number_format($array['rmomonth'],2,'.',',');		
		return $array;
	}
	
	/**
	 * @查看头像是否存在
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function headPortrait($img){
		if(file_exists($img)){	//存在图片
			return 1;
		}
	}
	
	/**
	 * @获取某个类目下的文章
	 * @id			//栏目ID
	 * @limt		//显示条数
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function someArticle($id,$limt){
		$mod = D("Article");
		$list = $mod->field('id,title,addtime')->where("published=1 and catid=".$id)->limit($limt)->order('`order` desc,`addtime` desc')->select();
		return $list;
	}

	/**
	 * @版权管理
	 * @请不要做修改或删除，因多处调用此方法，如因自行修改造成的资金错误、软件不能正常使用后果自行承担
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function copyright($tf=0){
		if($tf){
			$systems=$this->systems();
			$curlPost = "dswjw=".$_SERVER['SERVER_NAME']."&dswjn=".DS_NUMBER."&dswji=".$_SERVER["REMOTE_ADDR"]."&dswje=".$systems['sys_email']."&dswjc=".$systems['sys_cellphone']."&dswjp=".$systems['sys_phone']."&dswja=".$systems['sys_address']."&dswjco=".$systems['sys_company'];
			$url='http://www.tifaweb.com/Api/Core/counter';  
			$in=$this->Curl($curlPost,$url);
			if($in['state']=='yes'){
				echo "已授权";
			}else{
				echo "未授权 授权免费，地址：http://www.tifaweb.com/Index/counter.html";
			}
		}
	}
	
	/**
	 * @短信发送
	 */
	protected function sendMessage($cellphone,$contents){
		
	}
	
	/**
	*
	* @curl数据传输get
	* @curlGet	传输数据
	* @url		地址
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	* @curlPost	传输数据
	* @url		传输地址
	*/
	public function getCurl($curlGet,$url){
		$u=$url.'?'.$curlGet;	//组合URL和参数
		$ch = curl_init($u) ;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
		$output=curl_exec($ch);
		$output=simplexml_load_string($output);	//将XML里面的标签以数组形式获取
		$data = json_decode(json_encode($output),TRUE);	//xml转json再转数组
		return $data ;
	}
	
	/**
	*
	* @curl数据传输
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	* @curlPost	传输数据
	* @url		传输地址
	*/
	public function Curl($curlPost,$url){
		//$curlPost = "user=$username&pass=$password";
		//$url='http://xp.dswjjd.cn/index.php/Api/Index/login';  
		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_POST, 1);  
		curl_setopt($ch, CURLOPT_URL,$url);  
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);	// https请求 不验证证书和hosts
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		ob_start();  
		curl_exec($ch);  
		$json = ob_get_contents() ;  
		ob_end_clean();
		$login=json_decode($json,true);	
		return $login;
	}

	/**
	*
	* @数据库自动备份
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function automaticBackup(){
		$system=$this->systems();
		import('ORG.Custom.backupsql');
		$db = new DBManage ( C('DB_HOST'),C('DB_USER'), C('DB_PWD'), C('DB_NAME'), 'utf8' );
		$smtp=M('smtp');
		$stmpArr=$smtp->find();
		$backup=$db->backup();
		if($backup){
			$stmpArr['receipt_email']	=$system['sys_autoemail'];
			$stmpArr['title']			="数据库备份".time();
			$stmpArr['content']			='<div>
												备份时间:'.date('Y/m/d H:i:s').'
											</div>';
			$stmpArr['addattachment']	=$backup;
			$this->email_send($stmpArr);//发送邮件
		}
	}
	/**
	*
	* @显示指定目录文件
	* @dirname	要遍历的目录名字	
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function directory($dirname){
	   $num=0;    //用来记录目录下的文件个数
	   $dir_handle=opendir($dirname);
	   while($file=readdir($dir_handle))
	   {
		 if($file!="."&&$file!="..")
		 {
			$dirFile=$dirname."/".$file;
			$num++;
			$array[]=$file;
		 }
	   }
	   closedir($dir_handle);
	   $array['num']=$num;
	   return $array;
	}
	
	
	/**
	*
	* @模板数据获取
	* @dirname	要遍历的目录名字
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function templateData($dirname){
		$template=$this->directory($dirname);
		$array['num']=$template['num'];
		unset($template['num']);
		foreach($template as $id=>$te){
			$fp = file_get_contents($dirname."/".$te."/state.tf",'r'); 
			$array[$id] = explode("\r\n",$fp);
			$array[$id][3]=$te;
			fclose($fp); //关闭文件 
		}
		return $array;
	}
	
	/**
	*
	* @导出Word
	* @name		自定义名称(不支持中文)
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function exportWord($name){
		$dir_teaname = './Public/Word/';  //要创建的文件夹名称   Word
		//判断目录是否存在，存在就删除
		if(!is_dir($dir_teaname)){
		   //创建目录
			$mk = mkdir( $dir_teaname );
			if( !$mk )
			{
			 echo "创建目录失败！";
			 exit;
			}
		}
		//生成word文档
		import("ORG.Custom.Word"); 
		$savePath = $dir_teaname;
		$word = new word();	  
		$word->start();
		$this->display();
		$wordname = $name.'_'.time().'.doc'; //生成的word名称
		$wordname=iconv("utf-8","gb2312",$wordname);  //编码转换
		$word->save($savePath.$wordname);
		echo "<script>window.location.href='".__ROOT__."/Public/Word/".$wordname."';</script>";	
	}
	
	/**
	*
	* @删除指定文件
	* @path		路径
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function pathExit(){
		$path=$this->_post('img');
		if(file_exists($path)){	//存在图片
			unlink($path);	//删除它
		}
    }
	
	/**
	*
	* @联动取值
	* @pid		类目
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function linkageValue($pid){
		$unite=M("unite");
		$industry=$unite->field('value,name')->where('pid='.$pid)->order('`id` ASC')->select();
		foreach($industry as $i){
			$ind[$i['value']]=$i['name'];
		}
		return $ind;
	}
	
	/**
	*
	* @防黑操作记录
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function webScan(){
		//用户唯一key
		define('WEBSCAN_U_KEY', '2133a3216620b018063b1c4392d28fde');
		//数据回调统计地址
		define('WEBSCAN_API_LOG', 'http://safe.webscan.360.cn/papi/log/?key='.WEBSCAN_U_KEY);
		//版本更新地址
		define('WEBSCAN_UPDATE_FILE','http://safe.webscan.360.cn/papi/update/?key='.WEBSCAN_U_KEY);
		//后台路径
		//define('WEBSCAN_DIRECTORY','Admin|admin');
		//url白名单,可以自定义添加url白名单,默认是对phpcms的后台url放行
		//写法：比如phpcms 后台操作url index.php?m=admin php168的文章提交链接post.php?job=postnew&step=post ,dedecms 空间设置edit_space_info.php
		//$webscan_white_url = array('index.php' => 'm=admin','post.php' => 'job=postnew&step=post','edit_space_info.php'=>'');
		//define('WEBSCAN_URL',$webscan_white_url);
		import("ORG.Custom.webscan"); 	
	}
	
	/**
	*
	* @邮件通知
	* @uid		用户ID
	* @uname	用户名
	* @title	标题
	* @content	内容
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function mailNotice($arr){
		$user=D('User');
		if($arr['uid']){
			$users=$user->where("id=".$arr['uid'])->find();
		}else{
			$users=$user->where('username="'.$this->_post('user').'"')->find();
		}
		$smtp=M('smtp');
		$stmpArr=$smtp->find();
		$stmpArr['receipt_email']	=$users['email'];
		$stmpArr['title']			=$arr['title'];
		$stmpArr['content']			=$arr['content'];
		
		$this->email_send($stmpArr);	
	}
	
	/**
	*
	* @站内信单发
	* @arr		数据
	*	fid		发送者ID	
	*   sid		收件者ID
	*	title	标题
	*  	msg		内容
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function  silSingle($arr){
		$Instation=M('instation');
		$arr['time']=time();
		return $Instation->add($arr);
	}
	
	/**
	*
	* @站内信回复
	* @arr		数据
	*	fid		发送者ID	
	*   sid		回复者ID
	*   pid		回复的站内信ID
	*  	msg		内容
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function  silReply($arr){
		$Instation=M('instation');
		$arr['time']=time();
		return $Instation->add($arr);
	}
	
	/**
	*
	* @站内信群发(限管理员)
	* @arr		数据	
	*   sid		收件用户组
	*	title	标题
	*  	msg		内容
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function  silMass($arr){
		$Instation=M('instation');
		$arr['sid']=array_filter(explode(",",$arr['sid']));
		$arr['sid']=json_encode($arr['sid']);
		$arr['time']=time();
		$arr['type']=1;
		return $Instation->add($arr);
	}
	
	/**
	*
	* @站内信发件箱
	* @uid		用户ID
	* @state	0未读1已读2删除
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function  silSend($uid,$state=''){
		$Instation=M('instation');
		if($state){
			$where=" and `state`=".$state;
		}
		return $Instation->where('`fid`='.$uid.$where)->select();
	}
	
	/**
	*
	* @站内信收件箱
	* @uid		用户ID
	* @state	0未读1已读2删除
	* @limit	条数
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function silReceipt($uid,$state='',$limit){
		$Instation=M('instation');
		if(isset($state)){
			$where=" and `state`=".$state;
		}else{
			$where=" and `state`<2";
		}
		if($limit){
			$instation=$Instation->where('`sid`='.$uid.$where)->order('`id` DESC')->limit($limit)->select();
		}else{
			$instation=$Instation->where('`sid`='.$uid.$where)->order('`id` DESC')->select();
		}
		
		//群发站内信
		$mass=$Instation->where('`type`=1'.$where)->order('`id` DESC')->select();
		foreach($mass as $id=>$m){
			$mass[$id]['sid']=json_decode($m['sid'], true);
			
			if(in_array($uid,$mass[$id]['sid'])){	//如果用户是收件人
				$instations[$id]=$m;
			}
		}
		
		unset($mass);
		if($instations && $instation){
			$instat=array_merge($instation,$instations);
			array_multisort($instat,SORT_DESC);
			return $instat;
		}else{
			return $instation;
			return $instations;
		}
	}
	
	/**
	*
	* @站内信收件箱
	* @id		站内信ID
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function singleReceipt($id){
		$Instation=M('instation');
		$find=$Instation->field('state,msg')->where('`id`="'.$id.'"')->find();
		if($find['state']<1){
			$Instation->where('`id`='.$id)->setField('state',1);
		}
		return $find['msg'];
	}
	
	/**
	*
	* @生成手机验证码
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function cellpcode(){
		$cellpcode=$_SESSION['cellpcode']=mt_rand(100000,999999);
		return $cellpcode;
	}
	
	/**
	*
	* @生成邮箱验证码
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function emailcode(){
		$emailcode=$_SESSION['emailcode']=substr(MD5(mt_rand()),6,6);	//生成验证码
		return $emailcode;
	}
	
	/**
	*
	* @直接跳转
	* @url		跳转地址
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function jumps($url){
		echo '<script>window.location.href="'.$url.'";</script>';
	}
	
	/**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
	 * @action $action 提交地址
     * @return 提交表单HTML文本
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
     */
	protected function requestForm($para_temp, $method, $button_name,$action) {
		//待请求参数数组
		$sHtml = "<form id='alipaysubmit' name='form1' action='".$action."' method='".$method."'>";
		foreach($para_temp as $id=>$p){
            $sHtml.= "<input type='hidden' name='".$id."' value='".$p."'/>";
        }
		//submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
		
		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		
		return $sHtml;
	}
	
	/**
	 * @上传
	 * @approve	路径
     * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function upload($approve){
		import('ORG.Net.UploadFile');
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 3145728 ;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath =  './Public/uploadify/uploads/'.$approve.'/';// 设置附件上传目录
		if(!$upload->upload()) {// 上传错误提示错误信息
		$this->error($upload->getErrorMsg());
		}else{// 上传成功 获取上传文件信息
		return $info =  $upload->getUploadFileInfo();
		}
	}
	
	/**
	 * @检测是否手机访问
     * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 */
	protected function isMobile(){  
		$useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';  
		$useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';        
		function CheckSubstrs($substrs,$text){  
			foreach($substrs as $substr)  
				if(false!==strpos($text,$substr)){  
					return true;  
				}  
				return false;  
		}
		$mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
		$mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');  
			  
		$found_mobile=CheckSubstrs($mobile_os_list,$useragent_commentsblock) ||  
				  CheckSubstrs($mobile_token_list,$useragent);  
			  
		if ($found_mobile){  
			return true;  
		}else{  
			echo "Not allowed to access!";
			echo '<br/>
			<script language="JavaScript">
			function myrefresh()
			{
				   window.location.href="'.__APP__.'";
			}
			setTimeout("myrefresh()",2000); //指定1秒刷新一次
			</script>
			';
			exit;
		}  
	}
	
	/**
	*
	* @图片处理
	* @$i		打开的图片
	* @width	保存的宽度
	* @height	保存的高度
	* @save		保存的图片名称
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function imageProcessing($i,$width,$height,$save){
		import('ORG.Util.Image.ThinkImage');
		$img =new ThinkImage();
		$img->open($i)->crop($img->width(), $img->height(), 0, 0,$width,$height)->save($save);
	}
	
	/**
	*
	* @清空文件夹
	* @$dir		绝对路径
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function deldir($dir) {
	  //先删除目录下的文件：
	  $dh=opendir($dir);
	  while ($file=readdir($dh)) {
		if($file!="." && $file!="..") {
		  $fullpath=$dir."/".$file;
		  if(!is_dir($fullpath)) {
			  unlink($fullpath);
		  } else {
			  $this->deldir($fullpath);
		  }
		}
	  }
	}
	
	/**
	 *
	 * @资金记录详细属性获取
	 * @id		值		
	 * @作者		shop猫
	 * @版权		宁波天发网络
	 * @官网		http://www.tifaweb.com http://www.dswjcms.com
	 *
	 */
	protected function finetypeName($id){
		
		switch($id){
			case 1:
			$record='充值';
			break;
			case 2:
			$record='提现';
			break;
			case 3:
			$record='交易';
			break;
			case 4:
			$record='充值手续费';
			break;
			case 5:
			$record='提现撤回';
			break;
			case 6:
			$record='提现手续费';
			break;
		}
		return $record;
	}

    /**
     * @获取微信access_token获取
     * @作者		shop猫
     * @版权		宁波天发网络
     * @官网		http://www.tifaweb.com http://www.dswjcms.com
     */
    protected function wxAccessToken(){
        if($this->_session('access_token')){	//已获取access_token
            if($this->_session('access_token_time')>=time()){	//未失效
                $data['state']=1;
                $data['access_token']=$this->_session('access_token');
                return $data;
            }
        }
        $getInterface=$this->getInterface(3);
        $datas['grant_type']='client_credential';
        $datas['appid']=$getInterface['AppID'];
        $datas['secret']=$getInterface['AppSecret'];
        $Curl=$this->Curl($datas,'https://api.weixin.qq.com/cgi-bin/token');
        if($Curl['errcode']){	//错误
            $data['state']=0;
            $data['errcode']=$Curl['errcode'];
            $data['msg']=$Curl['errmsg'];
        }else{
            session('access_token',$Curl['access_token']);
            session('access_token_time',time()+$Curl['expires_in']);
            $data['state']=1;
            $data['access_token']=$Curl['access_token'];

        }
        return $data;

    }

	/**
	*数据库备份还原扫描文件
	* @作者		shop猫
	* @版权		宁波天发网络
	* @官网		http://www.tifaweb.com http://www.dswjcms.com
	*/
	protected function MyScandir($FilePath = './', $Order = 0) {
        $FilePath = opendir($FilePath);
        while (false !== ($filename = readdir($FilePath))) {
            $FileAndFolderAyy[] = $filename;
        }
        $Order == 0 ? sort($FileAndFolderAyy) : rsort($FileAndFolderAyy);
        return $FileAndFolderAyy;
    }

    /**
     * 将xml转为array
     * @param  string 	$xml xml字符串或者xml文件名
     * @param  bool 	$isfile 传入的是否是xml文件名
     * @return array    转换得到的数组
     */
    protected function xmlToArray($xml,$isfile=false){

        libxml_disable_entity_loader(true);
        $message=iconv("UTF-8","GB2312",$xml);
        $xmlstring = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }
}
?>