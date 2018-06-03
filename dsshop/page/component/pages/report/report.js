const getUrl = require('../../../../config').getUrl
const uploadImgUrl = require('../../../../config').uploadImgUrl
var app = getApp();
Page({
  data: {
    'blockclass':0,
    'scrollLeft':0,
    uploadImgUrl: uploadImgUrl,
    oneOn:{ 0: 'on' },
  },
  onLoad: function (options) {
    var that = this;
    wx.getSystemInfo({
      success: function (res) {
        
        that.setData({
          scrollHeight: 'height:'+(res.windowHeight)+'px'
        })
      }
    })
    
    wx.request({
      url: getUrl + 'getGoodsList',
      
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
  //一级栏目切换
  oneList: function (e) {
    var value = e.currentTarget.dataset.value, oneOn = {}, toList
    oneOn[value]='on';
    toList = 'go' + value;
    this.setData({
      oneOn: oneOn,
      toList: toList
    })
  },
  scroll: function (e) {
    
  }
  
})
