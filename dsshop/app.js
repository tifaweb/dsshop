const openIdUrl = require('./config').openIdUrl
const userinfoUrl = require('./config').userinfoUrl
const getUrl = require('./config').getUrl


App({
  
  onLaunch: function () {
    wx.getSystemInfo({
      success: function (res) {
        //console.log(res.SDKVersion)
        if (res.SDKVersion < '1.6') {
          wx.showModal({
            title: '提示',
            content: '当前微信版本过低，无法使使用功能，建议升级到最新微信版本后重试。'
          })
        }
      }
    })

    var that = this
    
    if (!wx.getStorageSync('openid')) { //判断是否登录过
      
      that.getUserOpenId();
    } else {
      wx.checkSession({ //登录验证
        success: function () {
          wx.getSetting({ //查看权限
            success: (res) => {
              //console.log(res);
              if (res.authSetting['scope.userInfo'] === false) {  //用户信息
                that.unauthorizedPrompt();
              }

            }
          })
          //console.log('App Launch')
        },
        fail: function () {
          that.getUserOpenId();
        }
      })
    }

  },
  //未授权提示
  unauthorizedPrompt: function () {
    wx.showModal({
      title: "未授权提醒",
      content: "DSSHOP需要获取一些资料和权限，才能进行相应数据同步\r\n设置方法：“我的”-“微信权限设置”-所有权限设置为允许",
      showCancel: false,
      confirmText: "知道了"
    })
  },
  onShow: function () {
    
    //console.log(wx.getStorageSync('openid'))
    
    //wx.clearStorageSync();//清除缓存


    //console.log('App Show')
  },
  //购物车角标
  getCarAngle: function (t) {
    var getcart = wx.getStorageSync('getcart'), carttotalnum
    carttotalnum = Object.keys(getcart).length.toString()
    if (Object.keys(getcart).length>0){
      //console.log(carttotalnum)
      wx.setTabBarBadge({
        index: 2,
        text: carttotalnum,
        complete:function(res){
          //console.log(res)
        }
      })
    }else{  //没有购物车，移除角标
        wx.removeTabBarBadge({
          index: 2,
          complete: function (res) {
            //console.log(res)
          }
        })
      
    }
    

  },
  
  globalData: {
    hasLogin: false,
    openid: null,
    uid: null,
  },
  //获取用户详情
  getToUserInfo: function () {
    
    var self = this
    wx.getUserInfo({
  
      success: function (res) {
        
        wx.setStorage({
          key: "userInfo",
          data: res.userInfo
        })
        wx.request({
          url: userinfoUrl,
          data: {
            userInfo: res.userInfo,
            openid: wx.getStorageSync('openid'),
            verify: wx.getStorageSync('verify'),
            id: wx.getStorageSync('id'),
          },
          success: function (res) {
            self.globalData.hasLogin = true;
            //console.log(res.data);

          }
        })

      }, fail: function (res){
        //console.log(res);
      }
    })
  },
  // 登录处理+获取用户详情
  getUserOpenId: function (callback) {
    var self = this
    
    if (!wx.getStorageSync('openid')) {
      
      wx.login({
        success: function (data) {
          //console.log(data);
          wx.request({
            url: openIdUrl,
            data: {
              code: data.code
            },
            success: function (res) {
              //console.log(res);
              if (res.data.status == 1) {  //成功
                self.globalData.openid = res.data.info.openid;
                self.globalData.uid = res.data.info.id;
                wx.setStorage({
                  key: "openid",
                  data: res.data.info.openid
                })
                wx.setStorage({
                  key: "id",
                  data: res.data.info.id
                })
                wx.setStorage({
                  key: "verify",
                  data: res.data.info.verify
                })
                //成功获取用户详情
                self.getToUserInfo();
                //获取通知角标
                //self.getNoticeAngle();
              } else {
                //console.log('拉取openid失败');
              }
            },
            fail: function (res) {
              //console.log('拉取用户openid失败，将无法正常使用开放接口等服务', res)

            }
          })
        },
        fail: function (err) {
          //console.log('wx.login 接口调用失败，将无法正常使用开放接口等服务', err)

        }
      })
    }
  },
  /**
   * 购物车处理
   * id   商品ID
   * color  颜色
   * size   尺寸
   * nub    数量
   */
  setgoodscat(id, color, size, nub) {
    //wx.removeStorageSync('getcart')
    var code,setcart={};
    if (wx.getStorageSync('getcart')){
      setcart = wx.getStorageSync('getcart');
    }
    
    code = id +'-' +color +'-'+ size;
    
    if (!setcart[code]){
      setcart[code] = {};
      setcart[code]['nub'] = nub;
    }else{
      setcart[code]['nub'] += nub;
    }
    
    setcart[code]['id'] = id;
    setcart[code]['color'] = color;
    setcart[code]['size'] = size;
    wx.setStorageSync('getcart', setcart);
    // wx.setStorage({
    //   key: "getcart",
    //   data: setcart
    // })
  },
  /**
   * 购买处理
   * id   商品ID
   * color  颜色
   * size   尺寸
   * nub    数量
   */
  setgoods(id, color, size, nub) {
    //wx.removeStorageSync('getgoods')
    var code, setcart = {};
    if (wx.getStorageSync('getgoods')) {
      setcart = wx.getStorageSync('getgoods');
    }

    code = id + '-' + color + '-' + size;

    setcart[code] = {};
    setcart[code]['nub'] = nub;

    setcart[code]['id'] = id;
    setcart[code]['color'] = color;
    setcart[code]['size'] = size;
    wx.setStorageSync('getgoods', setcart);
    
  },
})

