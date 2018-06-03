const uploadImgUrl = require('../../../../config').uploadImgUrl
const getUrl = require('../../../../config').getUrl
Page({
  data: {
    statisticalPrice: '0.00',  //默认总计
    statisticalNum: 0, //默认总计数量
    uploadImgUrl: uploadImgUrl,

  },
  
  onShow: function (options) {
    
    this.getShippingDddress();

  },
  //获取收货地址列表
  getShippingDddress(){
    var that = this, addressList={};
    wx.request({
      url: getUrl + 'getShippingAddress',
      data: {
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
      },
      success: function (res) {
        //console.log(res.data.info);
        if (res.data.status == 1) {
          addressList = res.data.info;
          
          for (var i in addressList) {
            
            if (addressList[i]['default']==1){
              addressList[i]['checked']='true';
            }else{
              addressList[i]['checked'] = '';
            }
          }
          //console.log(addressList);
          that.setData({
            addressList: addressList,
            
          }); 
        } else {
          
          wx.showToast({
            title: res.data.info,
            icon: 'none',
          })
        }
      }
    })
  },
  //设为默认
  radioChange: function (e) {
    var that = this, addressList = {};
    wx.request({
      url: getUrl + 'getDefaultAddress',
      data: {
        id: e.detail.value,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
      },
      success: function (res) {

        if (res.data.status == 1) {   
          //console.log(res.data);
        } else {
          wx.showToast({
            title: res.data.info,
            icon: 'none',
          })
        }
      }
    })
  },
  //删除地址
  deleteAddress:function(e){
    var that = this, addressList = {};
    wx.request({
      url: getUrl + 'getDeleteAddress',
      data: {
        id: e.target.dataset.id,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
      },
      success: function (res) {

        if (res.data.status == 1) {
          //console.log(res.data);
          that.getShippingDddress();
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
