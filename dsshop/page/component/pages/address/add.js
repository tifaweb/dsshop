const uploadImgUrl = require('../../../../config').uploadImgUrl
const getUrl = require('../../../../config').getUrl
Page({
  data: {
    region:'请选择区域',
    cityarray:[]
  },
  onLoad: function (options) {
    var title,that=this;
    if (options.id){
      this.setData({
        id: options.id
      })
      title='编辑收货地址';
      //获取收货地址
      wx.request({
        url: getUrl + 'getAddressDetails',
        data: {
          id: options.id,
          openid: wx.getStorageSync('openid'),
          verify: wx.getStorageSync('verify'),
          uid: wx.getStorageSync('id'),
        },
        success: function (res) {
          
          if (res.data.status == 1) {
            that.setData({
              Address: res.data.info,
              region:res.data.info.information.city,
              cityarray: res.data.info.cityarray
            });
            
          } else {
            wx.showToast({
              title: res.data.info,
              icon: 'none',
            })
          }
        }
      })
    }else{
      title = '新增收货地址';
    }

    wx.setNavigationBarTitle({
      title: title
    })
  },
  //选择省市区
  bindRegionChange: function (e) {
    
    this.setData({
      region: e.detail.value
    })
  },
  formSubmit: function (e) {
    
    var that = this, val = e.detail.value, id=this.data.id?this.data.id:0
    if (!val.recipient){
      wx.showToast({
        title: '收货人姓名有误',
        icon: 'none',
      })
      return false;
    }
    if (!val.telephone) {
      wx.showToast({
        title: '手机号码有误',
        icon: 'none',
      })
      return false;
    }
    if (!val.region[0]) {
      wx.showToast({
        title: '请选择省市区',
        icon: 'none',
      })
      return false;
    }
    if (!val.address) {
      wx.showToast({
        title: '详细地址不能为空',
        icon: 'none',
      })
      return false;
    }
    
    wx.request({
      url: getUrl + 'getAddAddress',
      data: {
        information: val,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
        id:id,
      },
      success: function (res) {
        //console.log(res);
        if (res.data.status == 1) {
          wx.navigateBack();
        } else {
          wx.showToast({
            title: res.data.info,
            icon: 'none',
          })
        }
      }
    })
    
    //console.log(val);
    
  },
})
