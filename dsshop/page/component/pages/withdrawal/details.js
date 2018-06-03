const getUrl = require('../../../../config').getUrl
Page({
  onLoad: function (options) {
    var id = options.id;
    var that = this;
    wx.request({
      url: getUrl + 'getCapitalSubsidiaryDetails',
      data: {
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
        id: id,
      },
      success: function (res) {
        if (res.data.status == 1) {
          //console.log(res.data.info);
          that.setData({
            goods: res.data.info
          })
        } else {
          //console.log(res.data);
        }
      }
    })
  },
})