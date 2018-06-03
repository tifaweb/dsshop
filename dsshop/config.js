/**
 * 小程序配置文件
 */

// 此处主机域名是腾讯云解决方案分配的域名
// 小程序后台服务解决方案：https://www.qcloud.com/solution/la

var host = "填写自己的服务器地址"

var config = {

    // 下面的地址配合云端 Server 工作
    host,

    // 域名地址
    getUrl: `https://${host}/Api/Index/`,
    
    // 临时文件地址
    getResourcesUrl: `https://${host}/Resources/`,

    // 登录地址，用于建立会话
    loginUrl: `https://${host}/login`,

    // 测试的请求地址，用于测试会话
    requestUrl: `https://${host}/testRequest`,

    // 用code换取openId
    openIdUrl: `https://${host}/Api/Index/getwxopenID`,

    // 用同步用户个人资料
    userinfoUrl: `https://${host}/Api/Index/getuserinfo`,

    // 获取商品列表
    getGoodsUrl: `https://${host}/Api/Index/getGoods`,

    // 测试的信道服务接口
    tunnelUrl: `https://${host}/tunnel`,

    // 生成支付订单的接口
    paymentUrl: `https://${host}/payment`,

    // 发送模板消息接口
    templateMessageUrl: `https://${host}/templateMessage`,

    // 上传文件接口
    uploadFileUrl: `https://${host}/upload`,

    // 图片接口
    uploadImgUrl: `https://${host}/Public/uploadify/uploads/`,
};

module.exports = config
