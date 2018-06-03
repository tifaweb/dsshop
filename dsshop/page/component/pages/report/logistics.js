const getUrl = require('../../../../config').getUrl
Page({
  data: {
    
  },
  onLoad: function (options) {
    var that = this;
   
    wx.request({
      url: getUrl + 'getLogistics',
      data: {
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
        id: options.id,
      },
      success: function (res) {
        
        if (res.data.status == 1) {
          if (res.data.data==1){
            for (var i in res.data.info) {
              if (i == 0) {
                res.data.info[i]['class'] = ' zan-steps__step--first-child zan-steps__step--done zan-steps__step--cur';
              } else if (i == (res.data.info.length - 1)) {
                res.data.info[i]['class'] = 'zan-steps__step--last-child';
              }
            }
          }
          
         // console.log(res.data.info);
          that.setData({
            data: res.data.data,
            logistics: res.data.info,
          });
        } else {
          wx.showToast({
            title: res.data.info,
          })
        }

        wx.hideLoading();
      }
    })
  }
});
