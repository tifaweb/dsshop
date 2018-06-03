const getUrl = require('../../../../config').getUrl
Page({
  data: {
    data: '',
  },
  //表单提交
  bindFormSubmit: function (e) {
    //console.log(e);
    var that = this;
    wx.request({
      url: getUrl + 'setfeedback',
      data: {
        note: e.detail.value.note,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
      },
      success: function (res) {
        //console.log(res);
        wx.showToast({
          title: '提交成功',
          icon: 'success',
          duration: 1000
        })
        that.setData({
          data: '',
        });
      }
    })
  },
})