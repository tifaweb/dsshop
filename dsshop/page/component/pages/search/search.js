// page/component/pages/search/search.js
Page({
  data: {
    keywordArray: ''
  },
  onLoad: function (options) {
    //点击进来分享
    if (options.keyword){
      this.setData({
        keyword: options.keyword,
      });
    }
    if (wx.getStorageSync('keyword')) {
      this.setData({
        keywordArray: wx.getStorageSync('keyword'),
      });
    }
  },
  //表单提交
  formSubmit: function (e) {
    var that=this;
    if (!e.detail.value){
      wx.showToast({
        title: "请输入搜索内容",
        icon: "none",
        duration: 1000
      })
    }else{
      //记录
      var keywordname = new Object();
      var keywordnameKeys;
      keywordnameKeys = Object.keys(wx.getStorageSync('keyword'));
      
      if (keywordnameKeys.length>0){
        keywordname = wx.getStorageSync('keyword');
        keywordname[keywordnameKeys.length] = e.detail.value;
      }else{
        keywordname[0] = e.detail.value;
      }
      //console.log(keywordname)
      wx.setStorage({
        key: "keyword",
        data: keywordname,
        success: function (res) {
          that.setData({
            keywordArray: keywordname,
          });
        }
      })
      wx.navigateTo({
        url: 'search-results?keyword=' + e.detail.value
      })
    }
  },
  //清空
  emptykeyword:function(){
    var that = this;
    wx.removeStorage({
      key: 'keyword',
      success: function (res) {
        that.setData({
          keywordArray: '',
        });
      }
    })
  }
})