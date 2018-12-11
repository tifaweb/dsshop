const uploadImgUrl = require('../../../../config').uploadImgUrl
const getUrl = require('../../../../config').getUrl
Page({
  data: {
    getAllCheckbox:'circle',
    getAllCheckboxColor: '',
    statisticalPrice:'0.00',  //默认总计
    statisticalNum:0, //默认总计数量
    checkboxData:{},
    uploadImgUrl: uploadImgUrl,
    cartempty:1,
  },
  onShow: function (options) {
    wx.removeStorageSync('getcartselected');
    //更新底部购物车角标
    var app = getApp();
    app.getCarAngle();
    this.setData({
      getAllCheckbox: 'circle',
      getAllCheckboxColor: '',
      statisticalPrice:'0.00',
      statisticalNum: 0,
      checkboxData: {},
    });
    this.getCarList();
  },
  //获取购物车列表
  getCarList(){
    var getcart = wx.getStorageSync('getcart'), cartdata = {}, getCheckbox = {}, getCheckboxColor = {}, getid = {}, that = this;
    
    if (!getcart){
      that.setData({
        cartList:{},
        cartempty: 1,
      });
      return false;
    }
    that.setData({
      cartempty: 0,
    });
    for (var i in getcart) {
        getid[i]=i;
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

          var getdata = res.data.info;
          
          for (var i in getcart) {

            var getdatas = {} = getdata[getcart[i]['id']];
            
            cartdata[i] = {};
            cartdata[i]['id'] = getdatas['id'];
            cartdata[i]['title'] = getdatas['title'];
            cartdata[i]['zimg'] = getdatas['zimg'];
            cartdata[i]['color'] = getdatas['attribute']['color'][getcart[i]['color']];
            cartdata[i]['size'] = getdatas['attribute']['size'][getcart[i]['size']];
            cartdata[i]['p'] = parseInt(getcart[i]['color']) * getdatas['attribute']['size'].length + parseInt(getcart[i]['size']); //键名
            cartdata[i]['stock'] = getdatas['attribute']['stock'][cartdata[i]['p']]; //库存
            cartdata[i]['nub'] = getcart[i]['nub'];  //当前购物车数量
            cartdata[i]['price'] = getdatas['attribute']['price'][cartdata[i]['p']]//价格
            if (cartdata[i]['nub'] == 1) {
              cartdata[i]['nubclassmin'] = "zan-stepper--disabled";
            } else {
              cartdata[i]['nubclassmin'] = "";
            }
            if (cartdata[i]['nub'] >= cartdata[i]['stock']) {
              cartdata[i]['nubclassmax'] = "zan-stepper--disabled";
            } else {
              cartdata[i]['nubclassmax'] = "";
            }
            if (!cartdata[i]['stock'] || cartdata[i]['stock'] < 1) {  //无库存

            } else if (cartdata[i]['nub'] > cartdata[i]['stock']) { //库存低于购物车数量
              cartdata[i]['nub'] = cartdata[i]['stock'];
            }
            getCheckbox[i] = 'circle';
            getCheckboxColor[i] = '';
          }
          // console.log(getdata);
          // console.log(cartdata);
          that.setData({
            cartList: cartdata,
            getCheckbox: getCheckbox,//复选框默认不勾选
            getCheckboxColor: getCheckboxColor,//复选框默认不勾选，无颜色
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
  //勾选产品
  setCheckbox(e){
    var getCheckbox = this.data.getCheckbox, getCheckboxColor = this.data.getCheckboxColor, checkboxData = this.data.checkboxData, getAllCheckbox = this.data.getAllCheckbox, getAllCheckboxColor = this.data.getAllCheckboxColor;
    
    if (getCheckbox[e.currentTarget.dataset.value] =='circle'){
      getCheckbox[e.currentTarget.dataset.value] = 'success';
      getCheckboxColor[e.currentTarget.dataset.value] = '#d81e06';
      checkboxData[e.currentTarget.dataset.value] = e.currentTarget.dataset.value;
    }else{
      getCheckbox[e.currentTarget.dataset.value] = 'circle';
      getCheckboxColor[e.currentTarget.dataset.value] = '';
      checkboxData[e.currentTarget.dataset.value] = '';
    }

    //改变全选全不选按钮
    getAllCheckbox = 'success';
    getAllCheckboxColor = '#d81e06';
    for (var i in getCheckbox) {
      if (getCheckbox[i] =='circle'){
        getAllCheckbox = 'circle';
        getAllCheckboxColor ='';
        break;
      }
      getCheckbox[i] = 'success';
      getCheckboxColor[i] = '#d81e06';
      checkboxData[i] = i;

    }
    
    
    this.setData({
      getCheckbox: getCheckbox,
      getCheckboxColor: getCheckboxColor,
      getAllCheckbox: getAllCheckbox,
      getAllCheckboxColor: getAllCheckboxColor,

    });
    
    this.setCheckboxData();
  },
  //全选
  setAllCheckbox(e) {
    var getAllCheckbox = this.data.getAllCheckbox, getAllCheckboxColor = this.data.getAllCheckboxColor, getCheckbox = this.data.getCheckbox, getCheckboxColor = this.data.getCheckboxColor, checkboxData = this.data.checkboxData;
    if (getAllCheckbox == 'circle') { //全选
      getAllCheckbox = 'success';
      getAllCheckboxColor= '#d81e06';
      
      for (var i in getCheckbox) {
          getCheckbox[i] ='success';
          getCheckboxColor[i] = '#d81e06';
          checkboxData[i] = i;
        
      }
      
    } else {  //全不选
      getAllCheckbox = 'circle';
      getAllCheckboxColor = '';
      for (var i in getCheckbox) {
        getCheckbox[i] = 'circle';
        getCheckboxColor[i] = '';
        checkboxData[i] = '';
      }
      
    }
    this.setData({
      getAllCheckbox: getAllCheckbox,
      getAllCheckboxColor: getAllCheckboxColor,
      getCheckbox: getCheckbox,
      getCheckboxColor: getCheckboxColor,
      checkboxData: checkboxData,
    });
    this.setCheckboxData();
  },
  //勾选后进行数据统计
  setCheckboxData(){
    var checkboxData = this.data.checkboxData, cartList = this.data.cartList, price = 0, nub = 0;
    
    for (var i in checkboxData) {
      if (checkboxData[i]){
        price += cartList[i]['price'] * cartList[i]['nub'];
        nub++;
      }
    } 
    this.setData({
      statisticalPrice: this.fmoney(price,2),
      statisticalNum: nub,

    });
  },
  //购买数量操作
  checkboxPrice(e) {
    //e.currentTarget.dataset.type
    var num, getonNumber, id = e.currentTarget.dataset.id, cartList = this.data.cartList, getcart = wx.getStorageSync('getcart');
    
    if (e.currentTarget.dataset.type == 1) {
      if (cartList[id]['nub'] == 1) {
        cartList[id]['nub'] = 1;
        cartList[id]['nubclassmin'] ='zan-stepper--disabled';
      } else {
        cartList[id]['nub'] = cartList[id]['nub'] - 1;
        cartList[id]['nubclassmin'] = '';
      }
      cartList[id]['nubclassmax'] = '';

    } else {
      if (cartList[id]['nub'] >= cartList[id]['stock']-1){
        cartList[id]['nub'] = cartList[id]['stock'];
        cartList[id]['nubclassmax'] = 'zan-stepper--disabled';
      }else{
        cartList[id]['nub'] = cartList[id]['nub'] + 1;
        cartList[id]['nubclassmax'] = '';
      }
      cartList[id]['nubclassmin'] = '';
      
    }
	getcart[id]['nub'] = cartList[id]['nub'];
    wx.setStorageSync('getcart', getcart);
    //console.log(cartList[id]);
    this.setData({
      cartList: cartList,
    });
    this.setCheckboxData();
  },
  //删除商品
  deleteCheckbox(e){
    var that=this,cartList = this.data.cartList, id = e.currentTarget.dataset.id, checkboxData = this.data.checkboxData, setcart = {};
    delete cartList[id];
    checkboxData[id] = '';
    //删除购买车缓存
    if (wx.getStorageSync('getcart')) {
      setcart = wx.getStorageSync('getcart');
      delete setcart[id];
      
      if (JSON.stringify(setcart) == "{}"){ //购买车为空
      
        wx.removeStorageSync('getcart');
        that.setData({
          cartempty: 1,
        });
      }else{
        wx.setStorageSync('getcart', setcart);
      }
     
    }
    //更新底部购物车角标
    var app = getApp();
    app.getCarAngle(1);
    this.setData({
      cartList: cartList,
      //checkboxData: checkboxData,
    });
    this.setCheckboxData();
  },
  //结算购物车
  getSettlement(e){
    

    if (JSON.stringify(this.data.checkboxData) == "{}"){
      wx.showToast({
        title: '请选择商品',
        icon: 'none',
        duration: 2000
      })
      return false;
    }else{
      var checkboxData = this.data.checkboxData;
      for (var i in checkboxData) {
        if (checkboxData[i] == ""){
          delete checkboxData[i];
        }
      }
      if (JSON.stringify(checkboxData) == "{}") {
        wx.showToast({
          title: '请选择商品',
          icon: 'none',
          duration: 2000
        })
        return false;
      }
    }
    
    wx.setStorageSync('getcartselected', this.data.checkboxData);
    wx.navigateTo({
      url: '../details/makeorder'
    })
  },
  //金额格式化
  fmoney(s, n) {
    n = n > 0 && n <= 20 ? n : 2;
    s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
    var l = s.split(".")[0].split("").reverse(), r = s.split(".")[1];
    var t = "";
    for(var i = 0; i <l.length; i++) {
      t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");
    }
    return t.split("").reverse().join("") + "." + r;
  },
  //跳到首页
  goshop(){
    wx.switchTab({
      url: '../../index'
    })
  }
})
