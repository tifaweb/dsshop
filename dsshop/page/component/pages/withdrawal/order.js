const getUrl = require('../../../../config').getUrl
Page({
  onLoad: function (options) {
    wx.showToast({
      title: '加载中...',
      icon: 'loading',
      duration: 3000,
    })
    this.getCapitalSubsidiary();  //首次加载
  },
  //资金明细
  //shows为1时将延时隐藏加载进度
  //page为1时将会累加
  getCapitalSubsidiary: function (shows, page) {

    var goodsOld = this.data.goods;
    var pages = this.data.p + 1;
    var that = this

    wx.request({
      url: getUrl + 'getCapitalSubsidiary',

      data: {
        p: page == 1 ? pages : 1,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
      },
      success: function (res) {
        //console.log(res.data.info);
        if (res.data.data > 0) { //结果大于0
          if (res.data.data < 10) {
            that.setData({
              p: page == 1 ? pages : 1,
              goods: page == 1 ? goodsOld.concat(res.data.info) : res.data.info,
              noContent: 'display: none;',
              bottomline: 'display: block;'
            });
          } else {
            that.setData({
              p: page == 1 ? pages : 1,
              goods: page == 1 ? goodsOld.concat(res.data.info) : res.data.info,
              noContent: 'display: none;',
              bottomline: null
            });
          }
          if (res.data.status == 2) { //当查询数量正好被每页显示数量相同时，结束查询
            that.setData({
              noContent: 'display: none;',
              bottomline: 'display: block;'
            });
          }

        } else {
          that.setData({
            p: pages,
            noContent: 'display: block;',
            bottomline: 'display: none;',
            goods: null
          });
        }

        if (shows == 1) {
          setTimeout(function () {  //刷新模拟过程，防止加载太快
            wx.hideToast();
            wx.stopPullDownRefresh()
          }, 1000)
        } else {
          wx.hideToast();
        }

      }
    })
  },
  //下拉刷新
  onPullDownRefresh: function () {
    var that = this
    //提示框配置
    wx.showToast({
      title: '刷新中...',
      icon: 'loading'
    })
    this.getCapitalSubsidiary(1);  //切换类目加载商品列表

  },
  //上拉加载
  onReachBottom: function () {
    if (this.data.bottomline == undefined) { //没有到最底
      //提示框配置
      wx.showToast({
        title: '加载中...',
        icon: 'loading'
      })
      this.getCapitalSubsidiary(1, 1);  //上拉加载商品列表
    }

  },
})