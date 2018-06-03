<?php
require_once("wxPayApi.class.php");
require_once("wxPayNotify.class.php");
/**
 * 
 * 回调基础类
 * @author widyhu
 *
 */
class PayNotifyCallBack extends WxPayNotify
{
    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        $notfiyOutput = array();
        $wxpay=M('wxpay')->where(array('number'=>$data['out_trade_no']))->find();
        if($wxpay['state'] !=1){    //已处理过的支付，不进行处理，直接返回TRUE
            return true;
        }
        $json=json_decode($wxpay['data'], true);
        if(!array_key_exists("transaction_id", $data)){
            //支付记录
            $msg = "输入参数不正确";
            $data['msg']=$msg;
            $json['results']=$data;
            $json=json_encode($json);
            $save['data']=$json;
            $save['state']=3;
            M('wxpay')->where(array('number'=>$data['out_trade_no']))->save($save);
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            //支付记录
            $msg = "订单查询失败";
            $data['msg']=$msg;
            $json['results']=$data;
            $json=json_encode($json);
            $save['data']=$json;
            $save['state']=3;
            M('wxpay')->where(array('number'=>$data['out_trade_no']))->save($save);
            return false;
        }
        //支付记录
        $json['results']=$data;
        $json=json_encode($json);
        $save['data']=$json;
        $save['state']=2;
        M('wxpay')->where(array('number'=>$data['out_trade_no']))->save($save);
        //改变订单状态
        M('indent')->where(array('id'=>$wxpay['iid']))->save(array('state'=>2));
        //记录添加点
        $indent=M('indent')->where(array('id'=>$wxpay['iid']))->find();
        M('instation')->add(array('title'=>'微信支付','sid'=>$wxpay['uid'],'msg'=>'【订单：'.$indent['number'].'】支付成功','time'=>time()));//站内信
        $money=M('money')->where(array('uid'=>$wxpay['uid']))->find();
        $money_log['uid']				= $wxpay['uid'];
        $money_log['type']				= 0;
        $money_log['actionname']		= '【订单：'.$indent['number'].'】支付';
        $money_log['total_money']		= $money['total_money'];
        $money_log['available_funds']	= $money['available_funds'];
        $money_log['freeze_funds']		= $money['freeze_funds'];
        $money_log['counterparty']		= '平台';
        $money_log['operation']		    = $data['total_fee']*0.01;
        $money_log['finetype']			= 3;
        $money_log['time']				= time();
        $money_log['ip']				= get_client_ip();
        M('money_log')->add($money_log);
        return true;
    }
}