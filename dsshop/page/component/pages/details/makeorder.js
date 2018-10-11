const uploadImgUrl = require('../../../../config').uploadImgUrl
const getUrl = require('../../../../config').getUrl
Page({
  data: {
    statisticalPrice: '0.00',  //默认总计
    statisticalNum: 0, //默认总计数量
    uploadImgUrl: uploadImgUrl,

  },
  onLoad: function (options) {
    
    if (options.type==1){
      this.setData({
        type: options.type,
      });
    }
    
    this.getaddress();
    this.getCarList();

  },
  onShow:function(){  //设置默认收货地址后重新加载
    this.getaddress();
  },
  //获取收货地址
  getaddress(){
    var that = this;
    wx.request({
      url: getUrl + 'getShippingAddressShow',
      data: {
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
      },
      success: function (res) {

        if (res.data.status == 1) {
          //console.log(res.data.status);
          that.setData({
            addresson: 1,
            addressdata: res.data.info,
            addressid:res.data.info.id, //地址ID
          });
        } else {
          that.setData({
            addresson: 0,
          });
        }
      }
    })
  },
  //获取购物车列表
  getCarList() {
    var getcart = wx.getStorageSync('getcart'), getgoods = wx.getStorageSync('getgoods'), getcartselected = wx.getStorageSync('getcartselected'), cartdata = {}, getCheckbox = {}, getCheckboxColor = {}, getid = {}, that = this;
    if (this.data.type==1){ //直接购买
      
      if (!getgoods) {
        console.log('非法操作');
        return false;
      }

      for (var i in getgoods) {
        getid[i] = i;
      }
      
    }else{  //购物车
      if (!getcart) {
        console.log('非法操作');
        return false;
      }

      for (var i in getcart) {
        if (getcartselected.hasOwnProperty(i)) {
          getid[i] = i;
        }

      }
    }
    
    
    wx.request({
      url: getUrl + 'getCarGoods',
      data: {
        getid: getid,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
      },
      success: function (res) {
        
        if (res.data.status == 1) {

          var getdata = res.data.info, price = 0, nub = 0, ordgetcart;
          if (that.data.type == 1){
            ordgetcart = getgoods;
          }else{
            ordgetcart = getcart;
          }
          
          for (var i in ordgetcart) {
            if (getcartselected.hasOwnProperty(i) || that.data.type ==1) {
              var getdatas = {} = getdata[ordgetcart[i]['id']];

              cartdata[i] = {};
              cartdata[i]['id'] = getdatas['id'];
              cartdata[i]['title'] = getdatas['title'];
              cartdata[i]['zimg'] = getdatas['zimg'];
              cartdata[i]['color'] = getdatas['attribute']['color'][ordgetcart[i]['color']];
              cartdata[i]['size'] = getdatas['attribute']['size'][ordgetcart[i]['size']];
              cartdata[i]['p'] = parseInt(ordgetcart[i]['color']) * getdatas['attribute']['size'].length + parseInt(ordgetcart[i]['size']); //键名

              cartdata[i]['nub'] = ordgetcart[i]['nub'];  //当前购物车数量
              cartdata[i]['price'] = getdatas['attribute']['price'][cartdata[i]['p']]//价格
              price += parseInt(cartdata[i]['price'])* parseInt(cartdata[i]['nub']);
              nub+=1;
            }
          }
          
          //console.log(price);
          that.setData({
            cartList: cartdata,
            statisticalPrice: that.fmoney(price, 2),
            statisticalNum: nub,
          });
          
        } else {
          wx.showToast({
            title: res.data.info,
            icon: 'none',
          })
        }
      }
    })
    return false;
  },
  
  //数据统计
  setCheckboxData() {
    var checkboxData = this.data.checkboxData, cartList = this.data.cartList, price = 0, nub = 0;

    for (var i in checkboxData) {
      if (checkboxData[i]) {
        price += cartList[i]['price'] * cartList[i]['nub'];
        nub++;
      }
    }
    this.setData({
      statisticalPrice: this.fmoney(price, 2),
      statisticalNum: nub,

    });
  },
  
  //金额格式化
  fmoney(s, n) {
    n = n > 0 && n <= 20 ? n : 2;
    s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
    var l = s.split(".")[0].split("").reverse(), r = s.split(".")[1];
    var t = "";
    for (var i = 0; i < l.length; i++) {
      t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");
    }
    return t.split("").reverse().join("") + "." + r;
  },
  //提交订单
  getGenerateOrders() {
    var getcart = wx.getStorageSync('getcart'), getgoods = wx.getStorageSync('getgoods'), getid = {},getcartselected = wx.getStorageSync('getcartselected'), addressid = this.data.addressid;
    if (!addressid) {
      wx.showToast({
        title: '请选择收货信息',
        icon: 'none',
        duration: 2000
      })
      return false;
    }
    if (this.data.type == 1) { //直接购买
      for (var i in getgoods) {
        getid[i] = getgoods[i];
      }
    }else{
      
      for (var i in getcart) {
        if (getcartselected.hasOwnProperty(i)) {
          getid[i] = getcart[i];
        }

      }
    }
    
    //console.log(getid);
    wx.request({
      url: getUrl + 'getGenerateOrders',
      data: {
        cart: getid,
        addressid: addressid,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
      },
      success: function (res) {

        if (res.data.status == 1) {
          //删除已购买的商品
          for (var i in getcart) {
            if (getcartselected.hasOwnProperty(i)) {
              delete getcart[i];
            }
            
            if (JSON.stringify(getcart) == "{}") { //购买车为空
              wx.removeStorageSync('getcart');
              
            } else {
              wx.setStorageSync('getcart', getcart);
            }

          }
          //微信支付
          wx.request({
            url: getUrl + 'setwxPay',
            data: {
              openid: wx.getStorageSync('openid'),
              verify: wx.getStorageSync('verify'),
              uid: wx.getStorageSync('id'),
              number: res.data.info,
            },
            success: function (res) {

              if (res.data.status == 1) {
                var payargs = res.data.info
                wx.requestPayment({
                  timeStamp: payargs.timeStamp,
                  nonceStr: payargs.nonceStr,
                  package: payargs.package,
                  signType: payargs.signType,
                  paySign: payargs.paySign,
                  'success': function (res) {
                    //console.log('支付成功')
                    wx.redirectTo({
                      url: '../report/order'
                    })
                  },
                  'fail': function (res) {
                    
                    wx.redirectTo({
                      url: '../report/order'
                    })
                  }
                })
              } else {
                wx.showToast({
                  title: res.data.info,
                  icon: 'none',
                })
              }
            }
          })
          
        } else {
          wx.showToast({
            title: res.data.info,
            icon: 'none',
          })
        }
      }
    })
  }
})
