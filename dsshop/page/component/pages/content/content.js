const getUrl = require('../../../../config').getUrl
Page({
  onLoad: function (options) {
    var that=this;
    //console.log(options);
    that.setData({
      src: getUrl + 'content?id=' + options.id + '&gid=' + options.gid,
    });
  },
})