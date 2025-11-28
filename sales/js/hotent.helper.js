/**
 * !Copyright (c) 2021 Hotent(http://www.hotent.com)
 *
 * Version: 1.0.0
 */
;(function () {
  var hotent = {
    alreadyGetHeight: false,
  }
  window.hotent = hotent
  setTimeout(function () {
    // 如果url表单未传递高度消息时，会默认在300ms后传递
    if (!hotent.alreadyGetHeight) {
      hotent.getHeight()
    }
  }, 300)
  // 是否现代浏览器
  hotent.isModern = function () {
    return window.addEventListener ? true : false
  }
  // 获取url参数
  hotent.getUrlParam = function (key) {
    var query = window.location.search.substring(1)
    var vars = query.split('&')
    for (var i = 0; i < vars.length; i++) {
      var pair = vars[i].split('=')
      if (pair[0] == key) {
        return pair[1]
      }
    }
    return false
  }
  // 添加事件监听
  hotent.addListener = function (name, fn) {
    if (!name || name.constructor != String) {
      throw 'name could not be empty and must be String.'
    }
    if (!fn || fn.constructor != Function) {
      throw 'fn could not be empty and must be Function.'
    }
    var eventMethod = hotent.isModern ? 'addEventListener' : 'attachEvent',
      eventer = window[eventMethod]

    var match = name.match(/^on(\w+)$/)
    if (match && match.length == 2) {
      name = match[1]
    }
    eventer(hotent.isModern ? name : 'on' + name, fn)
  }
  // 发送消息给父页面
  hotent.sendMessage = function (params) {
    window.parent && window.parent.postMessage(params, '*')
  }

  // 监听父页面发送过来的message
  hotent.addListener('message', function (e) {
    var type = e.data || 0

    // window.addEventListener('message', (res) => {
    //
    // })
    if (typeof type != 'string' && type.data) {
      type = type.data.type ? type.data.type : type
    }
    if (!type || type.type != 'roger') {
      // 子页面先回复父页面：收到消息
      hotent.sendMessage({ type: 'roger' })
    }
    var alias = ''

    // frm.contentWindow.postMessage({ messageName: 'saveData', alias }, '*')
    if (type.messageName) {
      alias = type.alias
      type = type.messageName
    }
    switch (type) {
      case 'getHeight' /*获取页面高度*/:
        hotent.getHeight()
        break
      case 'saveData': /*保存页面数据*/
      case 'modifyForm': /*打开新页面编辑数据*/
      case 'printForm': /*打印*/
      case 'validForm': /*验证数据*/
      case 'getSubPageData' /*获取子页面数据的data 回执数据格式为{type:"getSubPageData",data:{表单数据}}*/:
        hotent.invoke(type, alias)

        break
      case 'getParentPageData':
        hotent.invoke(type, type.data)
        break
    }
  })

  // 调用页面定义的方法
  hotent.invoke = function (methodName, params) {
    var r = window[methodName]
    if (!r || r.constructor != Function) {
      throw '页面未提供方法：' + methodName
    } else {
      r.apply(null, [params])
    }
  }

  // 获取页面高度
  hotent.getHeight = function () {
    var height = document.getElementsByTagName('body')[0].scrollHeight,
      params = { type: 'height', height: height }
    hotent.sendMessage(params)
    hotent.alreadyGetHeight = true
  }

  // 监听鼠标滚动事件
  hotent.addListener('wheel', function (e) {
    var params = { type: 'wheel', wheelDeltaY: -e.deltaY }
    hotent.sendMessage(params)
  })
})()