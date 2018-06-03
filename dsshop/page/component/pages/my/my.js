var app = getApp()
const userinfoUrl = require('../../../../config').userinfoUrl
Page({
  data: {
    userInfo: null,
    permissions: 'hidden',
    uid: wx.getStorageSync('id'),
  },
  
  onLoad: function (options) {
    wx.getSetting({ //查看权限
      success: (res) => {
        
        if (res.authSetting['scope.userInfo']===false){
         
          app.unauthorizedPrompt();
          this.setData({
            permissions: 'show'
          });
        }else{
          this.setData({
            permissions: 'hidden'
          });
        }
        
      }
    })
    if (!wx.getStorageSync('userInfo')){  //默认昵称和头像
      var userInfo = { 'nickName': 'user_' + wx.getStorageSync('id'), 'avatarUrl': '../../../../image/portrait.png'};
      this.setData({
        userInfo: userInfo,
        uid: wx.getStorageSync('id'),
      });
    }else{
      this.setData({
        userInfo: wx.getStorageSync('userInfo'),
        uid: wx.getStorageSync('id'),
      });
    }
    
    
  },
  //下拉刷新
  onPullDownRefresh: function () {
    //提示框配置
    wx.showToast({
      title: '刷新中...',
      icon: 'loading'
    })
    //提示框关闭
    // wx.stopPullDownRefresh({
    //   complete: function (res) {
    //     wx.hideToast()
    //   }
    // })
    //console.log('onPullDownRefresh', new Date())
  },
  //同步资料
  onGotUserInfo:function(res){
    var that = this, userInfo = res.detail.userInfo
    
    wx.setStorage({
      key: "userInfo",
      data: userInfo
    })
    wx.request({
      url: userinfoUrl,
      data: {
        userInfo: userInfo,
        openid: wx.getStorageSync('openid'),
        verify: wx.getStorageSync('verify'),
        id: wx.getStorageSync('id'),
      },
      success: function (res) {
        
        if(res.data.status==1){
          
          that.setData({
            userInfo: wx.getStorageSync('userInfo'),
          });
          
        }
        
        

      }
    })
    
  },
  
  //设置授权
  openSetting:function(){
    var that = this
    wx.openSetting({
      
      success: (res) => {
        //console.log(res)
        if (res.authSetting['scope.userInfo'] === false) {
          this.openSetting();
        }else{
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
                  that.setData({
                    userInfo: wx.getStorageSync('userInfo'),
                    permissions: 'hidden'
                  });

                }
              })

            }
          })
          
          
        }
        
      },
      fail:(res)=>{

      }
    })
  }
})
