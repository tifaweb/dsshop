const getUrl = require('../../../../config').getUrl
const uploadImgUrl = require('../../../../config').uploadImgUrl
Page({
  data: {
    uploadImgUrl: uploadImgUrl,
  },
  onLoad: function (options) {
    var that=this;
    wx.showLoading({
      title: '加载中...',
      icon: 'loading',
    })
    
    wx.request({
      url: getUrl + 'getOrderDetails',
      data: {
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
        id: options.id,
      },
      success: function (res) {
        //console.log(res.data.info);
        if(res.data.status==1){
          that.setData({
            goods: res.data.info,
          });
        }else{
          wx.showToast({
            title: res.data.info,
          })
        }
        
        wx.hideLoading();
      }
    })
  }
})