const getUrl = require('../../../../config').getUrl
const uploadImgUrl = require('../../../../config').uploadImgUrl
var util = require('../../../../utils/util.js');  
Page({
  data: {
    start: '',
    end: '',
    typeOn: { 0: 'on' },
    starttime: 0,
    endtime: 0,
    uploadImgUrl: uploadImgUrl,
  },
  onLoad: function (options) {
    //通过GET栏目切换
    var types = options.types;
    var typeOnData;
    if (types>0){
      switch (types) {
        case '1':
          typeOnData = { 0: '', 1: 'on', 2: '', 3: '' };
          break;
        case '2':
          typeOnData = { 0: '', 1: '', 2: 'on', 3: '' };
          break;
        case '3':
          typeOnData = { 0: '', 1: '', 2: '', 3: 'on' };
          break;
        
      }
      this.setData({
        typeOn: typeOnData,
        types: types,
        
      });
      //console.log(types);
    }
    

    
    var date = new Date();
    var year = date.getFullYear() + "";
    var month = (date.getMonth() + 1) + "";

    // 本月第一天日期
    var begin = year + "-" + month + "-01"
    // 本月最后一天日期    
    var lastDateOfCurrentMonth = new Date(year, month, 0);
    var end = year + "-" + month + "-" + lastDateOfCurrentMonth.getDate(); 
    this.setData({
      start: begin,
      end: end,
    });
    
    wx.showToast({
      title: '加载中...',
      icon: 'loading',
    })
    this.getOrderList();
  },
  //栏目切换
  setType: function (e) {

    var typeOnData;
    switch (e.currentTarget.dataset.value) {
      case '1':
        typeOnData = { 0: '', 1: 'on', 2: '', 3: '' };
      break;
      case '2':
        typeOnData = { 0: '', 1: '', 2: 'on', 3: '' };
      break;
      case '3':
        typeOnData = { 0: '', 1: '', 2: '', 3: 'on' };
        break;
      default:
        typeOnData = { 0: 'on', 1: '', 2: '', 3: ''};
    }

    this.setData({
      typeOn: typeOnData,
      types: e.currentTarget.dataset.value,
      starttime: 0,
      endtime: 0,
    });
    //console.log(this.data.order);
    this.getOrderList();
    //返回顶部
    wx.pageScrollTo({
      scrollTop: 0,
    })
  },
  //订单列表
  //shows为1时将延时隐藏加载进度
  //page为1时将会累加
  getOrderList: function (shows, page) {
    var goodsOld = this.data.goods;
    var pages = this.data.p + 1;
    var that = this, starttime, endtime
    wx.request({
      url: getUrl + 'getOrderList',
      data: {
        p: page == 1 ? pages : 1,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
        types: that.data.types,
        starttime: that.data.starttime,
        endtime: that.data.endtime,
      },
      success: function (res) {
        //console.log(res);
        
        var gettype = '', goods = res.data.info;
        
        
        
        if (res.data.data > 0) { //结果大于0
          if (res.data.data < 10) {
            that.setData({
              p: page == 1 ? pages : 1,
              goods: page == 1 ? goodsOld.concat(res.data.info) : goods,
              noContent: 'display: none;',
              bottomline: 'display: block;'
            });
          } else {
            that.setData({
              p: page == 1 ? pages : 1,
              goods: page == 1 ? goodsOld.concat(goods) : goods,
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
  //上拉加载
  onReachBottom: function () {
    if (this.data.bottomline == undefined) { //没有到最底
      //提示框配置
      wx.showToast({
        title: '加载中...',
        icon: 'loading'
      })
      this.getOrderList(1, 1);  //上拉加载商品列表
    }

  },
  bindDateChangeStart: function (e) {
    this.setData({
      start: e.detail.value
    })
  },
  bindDateChangeEnd: function (e) {
    this.setData({
      end: e.detail.value
    })
  },
  //时间选择
  formSubmit: function (e){
    var that = this
    this.setData({
      starttime: that.data.start,
      endtime: that.data.end,
    });
    
    this.getOrderList();
    //返回顶部
    wx.pageScrollTo({
      scrollTop: 0,
    })
  },
    //确认收货
    setConfirmReceipt: function (e){
        wx.request({
            url: getUrl + 'setConfirmReceipt',
            data: {
                openid: wx.getStorageSync('openid'),
                verify: wx.getStorageSync('verify'),
                uid: wx.getStorageSync('id'),
                id: e.currentTarget.dataset.value,
            },
            success: function (res) {

                if (res.data.status == 1) {
                    wx.showToast({
                        title: res.data.info,
                        icon: 'success',
                    })

                    that.getOrderList();

                } else {
                    wx.showToast({
                        title: res.data.info,
                    })
                }


            }
        })
    },
  //删除订单
  deleteOrder: function (e) {
    var that = this
    wx.showModal({
      content: "删除订单后无法找回，是否删除？",
      confirmText: "确定",
      cancelText: "取消",
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: getUrl + 'deleteOrder',
            data: {
              openid: wx.getStorageSync('openid'),
              verify: wx.getStorageSync('verify'),
              uid: wx.getStorageSync('id'),
              id: e.currentTarget.dataset.value,
            },
            success: function (res) {

              if (res.data.status == 1) {
                wx.showToast({
                  title: res.data.info,
                  icon: 'success',
                })
                
                that.getOrderList();

              } else {
                wx.showToast({
                  title: res.data.info,
                })
              }

             
            }
          })
        }
      }
    })
  },
  //微信支付
  setwxPay(e){
    var that = this
    wx.request({
      url: getUrl + 'setwxPay',
      data: {
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        uid: wx.getStorageSync('id'),
        number:e.currentTarget.dataset.value,
      },
      success: function (res) {

        if (res.data.status == 1) {
          var payargs = res.data.info
          wx.requestPayment({
            timeStamp: payargs.timeStamp,
            nonceStr: payargs.nonceStr,
            package: payargs.package,
            signType: payargs.signType,
            paySign: payargs.paySign,
            'success': function (res) {
              //console.log('支付成功')
              //console.log(res)
            },
            'fail': function (res) {
              
            },
            'complete':function (res){
              wx.pageScrollTo({
                scrollTop: 0,
              })
              that.getOrderList();
              
            }
          })
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
