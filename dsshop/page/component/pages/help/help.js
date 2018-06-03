const getUrl = require('../../../../config').getUrl
Page({
  onLoad: function (options) {
    var that = this;
    //文章列表
    wx.request({
      url: getUrl + 'getArticleLists',
      data: {
        id: 39,
      },
      success: function (res) {
        //console.log(res);
        that.setData({
          article: res.data.info, 
        });
      }
    })
  },
  
})