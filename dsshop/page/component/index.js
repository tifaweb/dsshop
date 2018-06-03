
const getUrl = require('../../config').getUrl
const getGoodsUrl = require('../../config').getGoodsUrl
const uploadImgUrl = require('../../config').uploadImgUrl
var app = getApp()
Page({
  data: {
    height: wx.getSystemInfoSync().windowHeight,
    getselectedId:0,
    uploadImgUrl: uploadImgUrl,
    p:1,
   
  },
  
  //商品列表获取
  //shows为1时将延时隐藏加载进度
  //page为1时将会累加
  getGoodslist: function (shows, page) {

    var goodsOld = this.data.goods;
    var pages = this.data.p + 1;
    var that = this
    
    //商品列表
    wx.request({
      url: getGoodsUrl,
      data: {
        types: this.data.getselectedId,
        p: page == 1 ? pages : 1,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        id: wx.getStorageSync('id'),
      },
      success: function (res) {
        //console.log(res);
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

  onShow: function (options) {
    //更新底部购物车角标
    var app = getApp();
    app.getCarAngle();
  },
  onLoad: function (options) {
    var that = this
    //首页轮播
    wx.request({
      url: getUrl + 'getShuffling',
      data: {
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
      },
      success: function (res) {
        //console.log(res.data);
        that.setData({
          imgUrls: res.data.info,
        });

      }
    })
    
    //提示框配置
    wx.showToast({
      title: '加载中...',
      icon: 'loading'
    })
    this.getGoodslist();  //首次加载商品列表 
  },
  //标签切换状态
  handleZanTabChange(e) {
    var that = this
    var componentId = e.componentId;
    var selectedId = e.selectedId;

    this.setData({
      [`${componentId}.selectedId`]: selectedId,
      'getselectedId': selectedId,
    });
    //切换栏目
    //提示框配置
    wx.showToast({
      title: '加载中...',
      icon: 'loading'
    })

    this.getGoodslist();  //切换类目加载商品列表
    //返回顶部
    wx.pageScrollTo({
      scrollTop: 0,
    })
  },
  durationChange: function (e) {
    //console.log(e);
    //console.log(e.currentTarget.dataset.url);
    wx.navigateTo({
      url: e.currentTarget.dataset.url
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
    this.getGoodslist(1);  //切换类目加载商品列表

  },
  //上拉加载
  onReachBottom: function () {
    if (this.data.bottomline == undefined) { //没有到最底
      //提示框配置
      wx.showToast({
        title: '加载中...',
        icon: 'loading'
      })
      this.getGoodslist(1, 1);  //上拉加载商品列表
    }

  },
  
  
});


