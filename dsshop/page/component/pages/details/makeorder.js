const uploadImgUrl = require('../../../../config').uploadImgUrl
const getUrl = require('../../../../config').getUrl
Page({
  data: {
    statisticalPrice: '0.00',  //默认总计
    statisticalNum: 0, //默认总计数量
    uploadImgUrl: uploadImgUrl,
    arrayvalue: 0,
    arrayIndex: 0,
  },
  onLoad: function (options) {
    
    if (options.type==1){
      this.setData({
        type: options.type,
      });
    }
    
    // this.getaddress();
    // this.getCarList();

  },
  onShow:function(){  //设置默认收货地址后重新加载
    var array = [];
    array[0] = '快递 免邮';
    this.setData({
      array: array,
    });
    this.getaddress();
    this.getCarList();
    
  },
  //获取快递费
  getExpress(id){
    var that = this;
    wx.request({
      url: getUrl + 'getExpress',
      data: {
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
        idarr: id.join(",")
      },
      success: function (res) {
        var array = [],arrayval=[], statisticalPrice = that.data.price;
        if (res.data.status == 1) {
          if (typeof (res.data.info.express) != 'undefined'){
            if (res.data.info.express === 0){
              array.push('快递 包邮')
            }else{
              array.push('快递 ¥ ' + that.fmoney(res.data.info.express, 2))
            }
            arrayval.push(res.data.info.express);
          }
          if (typeof (res.data.info.ems) != 'undefined') {
            if (res.data.info.ems === 0) {
              array.push('EMS 包邮')
            } else {
              array.push('EMS ¥ ' + that.fmoney(res.data.info.ems, 2))
            }
            arrayval.push(res.data.info.ems);
          }
          if (typeof (res.data.info.snailmail) != 'undefined') {
            if (res.data.info.snailmail === 0) {
              array.push('平邮 包邮')
            } else {
              array.push('平邮 ¥ ' + that.fmoney(res.data.info.snailmail, 2))
            }
            arrayval.push(res.data.info.snailmail);
          }
          if (typeof (res.data.info.logistics) != 'undefined') {
            if (res.data.info.logistics === 0) {
              array.push('物流 包邮')
            } else {
              array.push('物流 ¥ ' + that.fmoney(res.data.info.logistics, 2))
            }
            arrayval.push(res.data.info.logistics);
          }
          that.setData({
            array: array,
            checkedtemplate: arrayval[0], //选中的运费价格
            arrayval: arrayval,
            statisticalPrice: that.fmoney(statisticalPrice + arrayval[0], 2)
          });
        } else if (res.data.status == 2){
          
        }else {
          
          console.log(res.data.info);
        }
        
      }
    })
  },
  //设置选择的类目
  onAreaChange: function (e) {
    var arrayval = this.data.arrayval, statisticalPrice = this.data.price
    this.setData({
      arrayIndex: e.detail.value,
      checkedtemplate: arrayval[e.detail.value], //选中的运费价格
      statisticalPrice: this.fmoney(statisticalPrice + arrayval[e.detail.value], 2)
    })
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
        //console.log(res.data.info);
        if (res.data.status == 1) {

          var getdata = res.data.info, price = 0, nub = 0, ordgetcart;
          if (that.data.type == 1){
            ordgetcart = getgoods;
          }else{
            ordgetcart = getcart;
          }
          var expressid=[]
          for (var i in ordgetcart) {
            
            if (getcartselected.hasOwnProperty(i) || that.data.type ==1) {
              var getdatas = {} = getdata[ordgetcart[i]['id']];

              cartdata[i] = {};
              expressid.push(getdatas['lid']);
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
          
          that.setData({
            cartList: cartdata,
            statisticalPrice: that.fmoney(price, 2),
            price: price,
            statisticalNum: nub,
          });
          
          that.getExpress(expressid);//快递费
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
    
    wx.request({
      url: getUrl + 'getGenerateOrders',
      data: {
        cart: getid,
        addressid: addressid,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
        expressfee: this.data.checkedtemplate,  //快递费
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
