const getUrl = require('../../../../config').getUrl
const uploadImgUrl = require('../../../../config').uploadImgUrl
Page({
  data: {
    value:'',
    coupons:1,
    orderOn:{0:'on'},
    dataValue:'2',
    uploadImgUrl: uploadImgUrl,
  },
  onShow: function (options) {
    //更新底部购物车角标
    var app = getApp();
    app.getCarAngle();
  },
  onLoad: function (options) {
    if (options.keyword){ //关键字
      this.setData({
        value: options.keyword,
        type:1,
        datavalue: options.keyword,
      });
      wx.setNavigationBarTitle({
        title: '搜索结果'
      })
    }else{  //栏目访问
      
      this.setData({
        type: 2,
        datavalue: options.fid,
      });
      wx.setNavigationBarTitle({
        title: options.name
      })
    }
    
    wx.showToast({
      title: '加载中...',
      icon: 'loading',
      duration: 3000,
    })
    this.getGoodslist();  //首次加载商品列表 
  },
  getnavigateBack:function(e){
    if (this.data.type==1){ //搜索
      wx.navigateBack({
        delta: 1
      })
    }else{
      wx.navigateTo({
        url: '../search/search'
      })
    }
    
  },
  //排序切换
  setOrder:function(e){
    
    var orderOnData, dataValues='2';
    switch(e.currentTarget.dataset.value){
      case '1':
        orderOnData = { 0: '', 1: 'on', 2: '', 3: '', 4: ''};
      break;
      case '2':
        orderOnData = { 0: '', 1: '', 2: 'on', 3: '', 4: '' };
        dataValues='3';
        
      break;
      case '3':
        orderOnData = { 0: '', 1: '', 2: '', 3: 'on', 4: '' };
        dataValues = '2';
      break;
      case '4':
        orderOnData = { 0: '', 1: '', 2: '', 3: '', 4: 'on' };
      break;
      default:
        orderOnData = { 0: 'on', 1: '', 2: '', 3: '', 4: '' };
    }
   
    this.setData({
      dataValue: dataValues,
      orderOn: orderOnData,
      order: e.currentTarget.dataset.value,
    });
    //提示框配置
    wx.showToast({
      title: '加载中...',
      icon: 'loading'
    })
    //console.log(this.data.order);
    this.getGoodslist();
    //返回顶部
    wx.pageScrollTo({
      scrollTop: 0,
    })
  },
  
  //商品列表获取
  //shows为1时将延时隐藏加载进度
  //page为1时将会累加
  getGoodslist: function (shows, page) {

    var goodsOld = this.data.goods, datavalue = this.data.datavalue, type = this.data.type;
    var pages = this.data.p + 1;
    var that = this
    
    //商品列表
    wx.request({
      url: getUrl +'getCommoditySearch',
      
      data: {
        p: page == 1 ? pages : 1,
        datavalue: datavalue,
        type: type,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        id: wx.getStorageSync('id'),
        order: that.data.order ? that.data.order:'',
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
