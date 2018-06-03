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
set_time_limit(0);//防止PHP执行时间超时
class AutosAction extends CommAction {
	
	public function index(){	
		$this->automaticBackup();	//数据库备份邮箱改送
	}
	
	public function timing(){
			
			//VIP是否过期
			$vippoints=D('Vip_points');
			
			$id=$this->_post("id");
			$list=$vippoints->relation(true)->field('uid,expiration_time,audit')->where('audit=2')->limit(10)->select();
			
			if($list){
				foreach($list as $bw){
					$vip=F('vip');
					if(($bw['expiration_time']-time())<0){	//如果到期
					
						$vippoints->where(array('uid'=>$bw['uid']))->save(array('audit'=>4));
						$this->silSingle(array('title'=>'VIP到期通知','sid'=>$bw['uid'],'msg'=>'用户'.$bw['username'].'您的VIP已经到期！'));//站内信
						$this->userLog('VIP到期',$bw['uid']);//会员记录
						//邮件通知
						$mailNotice['uid']=$bw['uid'];
						$mailNotice['title']='VIP到期通知';
						$mailNotice['content']='
							<div style="margin: 6px 0 60px 0;">
								<p>用户'.$bw['username'].'您的VIP已经到期！</p>
							</div>
							<div style="color: #999;">
								<p>发件时间：'.date('Y/m/d H:i:s').'</p>
								<p>此邮件为系统自动发出的，请勿直接回复。</p>
							</div>';
						$this->mailNotice($mailNotice);
					}else if((($bw['expiration_time']-time())<432000) && (time()-$vip['vip_'.$bw['uid']])>=86400){	//如果离到小于5天提示并且一天只能会发一条
						
						if($vip){
							$vip['vip_'.$bw['uid']]=time();
							F('vip',$vip);
						}else{
							
							F('vip',array('vip_'.$bw['uid']=>time()));
						}
						
						if(floor(($bw['expiration_time']-time())/86400)<=1){	
							$remaining=floor(($bw['expiration_time']-time())/3600).'小时';		//剩余时间/3600
						}else{
							$remaining=floor(($bw['expiration_time']-time())/86400).'天';		//剩余时间/86400
						}
						$this->silSingle(array('title'=>'VIP即将到期通知','sid'=>$bw['uid'],'msg'=>'您的VIP将于'.$remaining.'后到期！'));//站内信
						//邮件通知
						$mailNotice['uid']=$bw['uid'];
						$mailNotice['title']='VIP即将到期通知';
						$mailNotice['content']='
							<div style="margin: 6px 0 60px 0;">
								<p>用户'.$bw['username'].'您的VIP将于<font color="#ff0000"><b>'.$remaining.'</b></font>后到期！</p>
							</div>
							<div style="color: #999;">
								<p>发件时间：'.date('Y/m/d H:i:s').'</p>
								<p>此邮件为系统自动发出的，请勿直接回复。</p>
							</div>';
						$this->mailNotice($mailNotice);
					}
					unset($vip);
				}
			}
			
	}
}