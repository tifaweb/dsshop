const getResourcesUrl = require('../../../../config').getResourcesUrl
const getUrl = require('../../../../config').getUrl
var getdata;
Page({
  data: {
    canvasstyle: '',
    nr:"",
    commission: "", //佣金
    commissionRatio: "",  //佣金比例
  },
  modalTap: function (e) {
    wx.showModal({
      title: "佣金计算规则",
      content: "此处展示佣金为卖家设置的佣金\r\n不同用户能申请到的佣金不同\r\n最终以实际计算结果为准\r\n佣金计算规则：\r\n宝贝成交价格x佣金比率",
      showCancel: false,
      confirmText: "知道了"
    })
  },
  canvasIdErrorCallback: function (e) {
    console.error(e.detail.errMsg)
  },
  onLoad: function (options) {
    var id = options.id;
    //console.log(id);
    var that = this;
    

    wx.request({
      url: getUrl+'getGoodsShareInformation',
      data: {
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
        id: id,
      },
      success: function (res) {
        //console.log(res);
        var getnr, getcommission, getcommissionRatio;
        getnr = res.data.info.name + "\r\n【在售价】" + res.data.info.price + "元";
        if (res.data.info.coupons_price>0){
          getnr += "\r\n【券后价】" + res.data.info.coupons_price + "元";
        }
        if (res.data.info.commission_type==1){
          getcommission = res.data.info.vipcommission;
          getcommissionRatio = res.data.info.vipcommissionProportion;
        }else{
          getcommission = res.data.info.generalcommission;
          getcommissionRatio = res.data.info.generalcommissionProportion;
        }
        that.setData({
          nr: getnr,
          commission: getcommission,
          commissionRatio: getcommissionRatio,
        });
        //console.log(res.data.info)
        getdata=res.data.info;
      }
    })
    
  },
  savePhotoAlbum:function (){
    //console.log(getdata);
    wx.showLoading({
      title: "生成中",
      mask: true
    })
    var img, qrcode;
    var windowWidth = wx.getSystemInfoSync().windowWidth;
    var that=this;
    wx.downloadFile({
      url: getResourcesUrl + getdata.getimg,
      success: function (res) {
        //console.log(res)
        if (res.statusCode === 200) {
          img = res.tempFilePath

          wx.downloadFile({
            url: getdata.qrcode,
            success: function (res) {
              //console.log(res)
              if (res.statusCode === 200) {
                qrcode = res.tempFilePath

                // wx.playVoice({
                //   filePath: res.tempFilePath
                // })
                const ctx = wx.createCanvasContext('firstCanvas')
                //windowWidth=800;

                var FontSize, Fontvalue;
                Fontvalue = 390 / 12;
                FontSize = windowWidth / Fontvalue;
                //背景
                ctx.setFillStyle('White')
                ctx.fillRect(0, 0, windowWidth, windowWidth + 200)
                //图片
                ctx.drawImage(img, 0, 0, windowWidth, windowWidth)
                //标题
                var titlelength = getdata.name.length/15;  //名字长度
                for (var i = 0; i < titlelength;i++){
                  ctx.setFillStyle('#4f4f4f')
                  ctx.setFontSize(FontSize + 3)
                  ctx.setTextAlign('left')
                  ctx.fillText(getdata.name.substr(i*15, 15), 10, windowWidth + windowWidth / 15 * (1.5+i*0.8))
                  //console.log(i * 15 + windowWidth / 15 * (1.5 + i * 0.8))
                }
                //二维码框

                ctx.drawImage('../../../../image/give_code_box.png', windowWidth / 3.12 * 2, windowWidth + windowWidth / 15, windowWidth / 3, windowWidth / 3)
                //二维码
                ctx.drawImage(qrcode, windowWidth / 3 * 2, windowWidth + windowWidth / 11.5, windowWidth / 3.6, windowWidth / 3.6)
                if (getdata.coupons_price > 0) {  //有优惠券
                  //现价
                  ctx.setFillStyle('#9d9d9d')
                  ctx.setFontSize(FontSize + 1)
                  ctx.setTextAlign('left')
                  ctx.fillText('现价 ¥' + getdata.price, 10, windowWidth + windowWidth / 15 * 4)
                  //--券后价--
                  //背景方框
                  var vouchers = windowWidth + windowWidth / 15 * 5;
                  ctx.setStrokeStyle('#1296db')
                  ctx.strokeRect(10, vouchers, 70, 25)
                  //券背景
                  ctx.setFillStyle('#1296db')
                  ctx.fillRect(10, vouchers, 25, 25)
                  //券字
                  ctx.setFillStyle('White')
                  ctx.setFontSize(FontSize + 3)
                  ctx.setTextAlign('left')
                  ctx.fillText('券', 15, vouchers + 18)
                  //券价值
                  ctx.setFillStyle('#1296db')
                  ctx.setFontSize(FontSize + 3)
                  ctx.setTextAlign('left')
                  ctx.fillText(getdata.coupon_amount+'元', 40, vouchers + 18)
                  //券后价
                  ctx.setFillStyle('#4f4f4f')
                  ctx.setFontSize(FontSize + 3)
                  ctx.setTextAlign('left')
                  ctx.fillText('券后价', 90, vouchers + 18)
                  //RMB符号
                  ctx.setFillStyle('#1296db')
                  ctx.setFontSize(FontSize + 3)
                  ctx.setTextAlign('left')
                  ctx.fillText('¥', 140, vouchers + 18)
                  //券后价格
                  ctx.setFillStyle('#1296db')
                  ctx.setFontSize(FontSize + 10)
                  ctx.setTextAlign('left')
                  ctx.fillText(getdata.coupons_price, 150, vouchers + 18)
                }else{  //无优惠券
                  var vouchers = windowWidth + windowWidth / 15 * 4;
                  //现价
                  ctx.setFillStyle('#4f4f4f')
                  ctx.setFontSize(FontSize + 3)
                  ctx.setTextAlign('left')
                  ctx.fillText('现价', 90, vouchers + 18)
                  //RMB符号
                  ctx.setFillStyle('#1296db')
                  ctx.setFontSize(FontSize + 3)
                  ctx.setTextAlign('left')
                  ctx.fillText('¥', 140, vouchers + 18)
                  // 现价价格
                  ctx.setFillStyle('#1296db')
                  ctx.setFontSize(FontSize + 10)
                  ctx.setTextAlign('left')
                  ctx.fillText(getdata.price, 150, vouchers + 18)
                }
                
                ctx.draw();
                that.setData({
                  canvasstyle: "display: block;	position: fixed;width:" + wx.getSystemInfoSync().windowWidth + "px;height:" + wx.getSystemInfoSync().windowHeight + "px;",
                });
                // //保存文案到剪贴板
                // var getdatas = that.data.nr + '\r\n【下单链接】' + getdata.click_url;
                // //根据渠道展示不同内容
                // if (getdata.storetype == 1){  //淘宝
                //   getdatas += '复制这条信息， ' + getdata.taobaoPassword + ' ，打开【手机淘宝】即可查看';
                // } else if (getdata.storetype == 2) {  //天猫
                //   getdatas += '复制这条信息， ' + getdata.taobaoPassword + ' ，打开【手机天猫】即可查看';
                // }
                // wx.setClipboardData({
                  
                //   data: getdatas,
                //   success: function (res) {
                //     wx.getClipboardData({
                //       success: function (res) {
                //         //console.log(res.data) // data
                //       }
                //     })
                //   }
                // })
                // setTimeout(function () {  //刷新模拟过程
                //   //生成图片
                //   wx.canvasToTempFilePath({
                //     canvasId: 'firstCanvas',
                //     width: windowWidth,
                //     height: windowWidth + windowWidth / 2,
                //     destWidth: 1080,
                //     destHeight: 1535,
                //     success: function (res) {

                //       //console.log(res.tempFilePath)
                //       //保存到本地相册
                //       wx.saveImageToPhotosAlbum({
                //         filePath: res.tempFilePath,
                //         success: function (res) {
                          
                //           wx.hideLoading();
                //           wx.showToast({
                //             title: "完成",
                //             icon: "success",
                //             duration: 1000
                //           })
                //           that.setData({
                //             canvasstyle: "",
                //           });
                //         },
                //         fail:function(res){
                //           wx.hideLoading();
                //           that.setData({
                //             canvasstyle: "",
                //           });
                //           wx.getSetting({ //查看权限
                //             success: (res) => {
                              
                //               if (res.authSetting['scope.writePhotosAlbum'] === false) {
                //                 wx.showToast({
                //                   title: "无权限保存资源\r\n请设置“保存到相册”为允许",
                //                   icon: "none",
                //                   duration: 2000
                //                 })
                //                 setTimeout(function () {
                //                   that.openSetting();
                //                 }, 2000)
                //               } else {
                //                 wx.showToast({
                //                   title: "您取消了资源保存",
                //                   icon: "none",
                //                   duration: 2000
                //                 })
                                
                //               }

                //             }
                //           })

                          
                          
                //         }
                //       })
                //     }
                //   })
                // }, 1000)

              }
            }
          })
        }
      }
    }); 
  },
  //仅复制文案
  getShareCopy:function(){
    var that = this;
    //保存文案到剪贴板
    var getdatas = that.data.nr + '\r\n【下单链接】' + getdata.click_url;
    //根据渠道展示不同内容
    if (getdata.storetype == 1) {  //淘宝
      getdatas += '复制这条信息， ' + getdata.taobaoPassword + ' ，打开【手机淘宝】即可查看';
    } else if (getdata.storetype == 2) {  //天猫
      getdatas += '复制这条信息， ' + getdata.taobaoPassword + ' ，打开【手机天猫】即可查看';
    }
    wx.setClipboardData({

      data: getdatas,
      success: function (res) {
        wx.getClipboardData({
          success: function (res) {
            wx.showToast({
              title: "复制成功",
              icon: "success",
              duration: 1000
            })
            //console.log(res.data) // data
          }
        })
      }
    })
  },
  //设置授权
  openSetting: function () {
    var that = this
    wx.openSetting({

      success: (res) => {
        //console.log(res)
        if (res.authSetting['scope.writePhotosAlbum'] === false) {
          this.openSetting();
        }
      },
      fail: (res) => {

      }
    })
  }
})