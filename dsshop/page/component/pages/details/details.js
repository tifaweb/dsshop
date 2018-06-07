const getUrl = require('../../../../config').getUrl
const uploadImgUrl = require('../../../../config').uploadImgUrl
Page({

  /**
   * 页面的初始数据
   */
  data: {
    uploadImgUrl: uploadImgUrl,
    getid: '',
    colorID:0,  //初始化颜色选中ID
    sizeID: 0,  //初始化尺寸选中ID
    colorValue: -1,  //颜色选中的值
    sizeValue: -1,  //尺寸选中的值
    getAction:0,  //加入购买或/购买，默认为加入购买车
    getnumber:1, //默认数量
    onNumber:{1:'on'},  //默认数量减为灰色
    addcardtyle:'请选择 颜色分类 尺寸',
  },
  onLoad: function (options) {
    var id = options.id,getcart = wx.getStorageSync('getcart');
    var that = this;
    that.setData({
      getid: id,
      cartnum: Object.keys(getcart).length,
    })
    wx.request({
      url: getUrl + 'getGoodsDetails',
      data: {
        id: id,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
      },
      success: function (res) {
        //console.log(res.data.info);
        if (res.data.status == 1) {
          
          var goodsdata = res.data.info;
          
          goodsdata['colorstylename']={};
          for (var i in goodsdata.colorstyle) {
            goodsdata['colorstylename'][i]='';
            if (goodsdata.colorstyle[i]==1){
              goodsdata['colorstylename'][i]='none';
            }
            
          }
          
          goodsdata['sizestylename'] = {};
          for (var i in goodsdata.sizestyle) {
            goodsdata['sizestylename'][i] = '';
            if (goodsdata.sizestyle[i] == 1) {
              goodsdata['sizestylename'][i] = 'none';
            }
          }
          //console.log(goodsdata);
          
          that.setData({
            goods: goodsdata,
            addcardprice:goodsdata.price,
            addcardstock: goodsdata.stock,
          })
        } else {
          wx.showToast({
            title: res.data.info,
            icon: 'none',
          })
        }
      }
    })
    
  },
  //加入/购买
  toggleBottomPopup(e) {
    
    if (e.currentTarget.dataset.value){
      this.setData({
        showBottomPopup: !this.data.showBottomPopup,
        getAction: e.currentTarget.dataset.value,
        addcart:this.data.goods,
      });
    }else{
      this.setData({
        showBottomPopup: !this.data.showBottomPopup,
      });
    }
    
  },
  //选择颜色
  getColorClassification(e){
    var  addcardtyle, colorValue
    
    
    //灰色不可选
    if (e.currentTarget.dataset.style == 1) {
      return false;
    }
    var colorOnData = {},colorgetOn={};
    var getdata=this.data,getdataOn;
    if (getdata['colorOn']){
      getdataOn = getdata['colorOn'][this.data.colorID];
    }
    
    if (this.data.colorID == e.currentTarget.dataset.id && getdataOn=='on'){
      colorOnData = {};
      colorValue = -1;
    }else{
      colorOnData[e.currentTarget.dataset.id] = "on";
      colorValue = e.currentTarget.dataset.value;
    }
    
    this.setData({
      colorOn: colorOnData,
      colorID: e.currentTarget.dataset.id,
      colorValue: colorValue,
    });
    var colorValue = this.data.colorValue, sizeValue = this.data.sizeValue;
    if (colorValue != -1 && sizeValue == -1) {
      addcardtyle = '请选择 尺寸';
    } else if (colorValue == -1 && sizeValue == -1) {
      addcardtyle = '请选择 颜色分类 尺寸';
    } else if (colorValue == -1 && sizeValue != -1) {
      addcardtyle = '请选择 颜色分类';
    } else {
      var attribute = this.data.goods.attribute;
      addcardtyle = '已选择“' + attribute['color'][colorValue] + '” “' + attribute['size'][sizeValue] + '”';
      var p = colorValue * attribute.size.length + sizeValue;
      //console.log(attribute['price'][p]);
      this.setData({
        addcardprice: this.fmoney(attribute['price'][p],2),
      });
    }
    this.setData({
      addcardtyle: addcardtyle,
    });
    
  },
  //选择尺寸
  getSizeClassification(e) {
    var  addcardtyle, sizeValue
    var sizeOnData = {}, sizegetOn = {};
    var getdata = this.data, getdataOn;
    if (getdata['sizeOn']) {
      getdataOn = getdata['sizeOn'][this.data.sizeID];
    }

    if (this.data.sizeID == e.currentTarget.dataset.id && getdataOn == 'on') {
      sizeOnData = {};
      sizeValue = -1;
    } else {
      sizeOnData[e.currentTarget.dataset.id] = "on";
      sizeValue = e.currentTarget.dataset.value;
    }
    //console.log(sizeOnData);
    this.setData({
      sizeOn: sizeOnData,
      sizeID: e.currentTarget.dataset.id,
      sizeValue: sizeValue,
    });
    var colorValue = this.data.colorValue, sizeValue = this.data.sizeValue;
    if (sizeValue != -1 && colorValue== -1) {
      addcardtyle = '请选择 颜色分类';
    } else if (colorValue == -1 && sizeValue == -1) {
      addcardtyle = '请选择 颜色分类 尺寸';
    } else if (sizeValue == -1 && colorValue != -1) {
      addcardtyle = '请选择 尺寸';
    } else {
      var attribute = this.data.goods.attribute;
      addcardtyle = '已选择“' + attribute['color'][colorValue] + '” “' + attribute['size'][sizeValue] + '”';
      var p = colorValue * attribute.size.length + sizeValue;
      this.setData({
        addcardprice: this.fmoney(attribute['price'][p], 2),
      });
    }
    this.setData({
      addcardtyle: addcardtyle,
    });
  },
  //图片预览
  imgPreview:function(e){
    
    var that = this, img = that.data.goods.img, current = uploadImgUrl+'commodity/'+e.currentTarget.dataset.url;
    
    for (var i in img) {
      if (img[i].indexOf("https")==-1){
        img[i] = uploadImgUrl + 'commodity/' + img[i];
      }
      
    }
    
    wx.previewImage({
      current: current,
      urls: img
    })
  },
  //购买数量操作
  numberOperation(e){
    //e.currentTarget.dataset.type
    var num, getonNumber;
    
    if (e.currentTarget.dataset.type==1){
      if (this.data.getnumber==1){
        num = 1;
        getonNumber={1:'on'};
      } else{
        num = this.data.getnumber - 1;
        getonNumber = {};
      }
      
     
    }else{
      num = this.data.getnumber + 1;
      getonNumber = {};
    }
    this.setData({
      getnumber: num,
      onNumber:getonNumber,
    });
  },
  //购买/加入购买车
  getGoodsSubmit(){
    // console.log(this.data.colorValue);
    // console.log(this.data.sizeValue);
    //验证表单
    var colorValue = this.data.colorValue, sizeValue = this.data.sizeValue, numberValue = this.data.getnumber,  that = this
    if (colorValue==-1){
      wx.showToast({
        title: '请选择颜色分类',
        icon:'none',
        duration: 2000
      })
      return false;
    } else if (sizeValue==-1){
      wx.showToast({
        title: '请选择尺寸',
        icon: 'none',
        duration: 2000
      })
      return false;
    }
    //console.log('ID：' + this.data.getid+';颜色：' + colorValue + ';尺寸：' + sizeValue + ';数量：' + numberValue);
    if (this.data.getAction==0){  //加入购买车
      var app = getApp();
      app.setgoodscat(this.data.getid, colorValue,sizeValue,numberValue);
      //更新当前页购物车角标
      var getcart = wx.getStorageSync('getcart')
      that.setData({
        cartnum: Object.keys(getcart).length,
      })
      
    }else{  //购买
      var app = getApp();
      app.setgoods(this.data.getid, colorValue, sizeValue, numberValue);
      //直接下单
      wx.navigateTo({
        url: 'makeorder?type=1'
      })
    }
    this.setData({
      showBottomPopup: !this.data.showBottomPopup,
    });
  },
  //千分位显示
  fmoney(s, n) {
    n = n > 0 && n <= 20 ? n : 2;
    s = parseFloat((s + '').replace(/[^\d\.-]/g, '')) + '';
    var l = s.split('.')[0].split('').reverse(),
      r = s.split('.')[1];
    var t = '';
    for (var i = 0; i < l.length; i++) {
      t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? ',' : '');
    }
    if (!r) {
      r = '0';
    }
    if (r.length < n) {
      for (var i = r.length; i < n; i++) {
        r += '0';
      }
    } else {
      r = r.substr(0, n);
    }
    return t.split('').reverse().join('') + '.' + r;
  },
  
})