 var XTools  = {
     error: "",
     //链接跳转
     jumpUrl : function (url) {
         if(url == '') {
             return false;
         }
         window.location.href    = url;
 
         return true;
     },
     //定时执行,单位秒
     setTimeExec: function(callback, t) {
         setTimeout(callback, t * 1000);
     },
     //检测当前可用的字数
     checkValidWords: function (contentDomId, showDomId, totalWords) {
         var realLength  = 0;
         var lenInfoHtml = ['剩余<em class="valid_words" id="limit_words_id"></em>字',
                            '已超出<em class="over_words"></em>字'];
         var content     =(this.$(contentDomId).value).replace(/\r\n/g,"");
        if(false === this.isEmpty(content)) {
             var keyValue    = this.getKeyCode();
             if(keyValue !== false && this.isEnterKey(keyValue) == true) {
                 return true;
             }
             var realContent = this.filterChar(content, [10]);
	     realContent = this.filterMoreSpace(realContent);
             realLength      = realContent.length;
 
         }
         if(totalWords < realLength) {
             this.html(showDomId, lenInfoHtml[1]);
             //this.$(contentDomId).value      = this.substr(content,0, totalWords);
         } else {
             this.html(showDomId, lenInfoHtml[0]);
         }
         var parentDom     = this.$(showDomId);
         for(var loc = 0; loc < parentDom.childNodes.length; loc ++) {
             var childNode   = parentDom.childNodes[loc];
             if(this.isEmpty(childNode) || this.isEmpty(childNode.tagName)) {
                 continue;
             }
             if((childNode.tagName).toLowerCase() == 'em') {
                 childNode.innerHTML     = Math.abs(totalWords - realLength);
                 break;
             }
         }
         //由于有一个页面多个字符检测的情况，采用上面的方法来处理这样的情况
         //this.html('limit_words_id', Math.abs(totalWords - realLength));
 
         return true;
     },
     //截取字符串[普通方式]
     substr: function (sourceString, start, end) {
         return sourceString.substring(start, end);
     },
     //截取字符串[按不同的编码截取方式]
     mb_substr: function(sourceString, start, end, encoding) {
         return "";
     },
     //得到字符串的字符长度［普通方式］
     strlen: function (sourceString) {
         return sourceString.length;
     },
     //得到字符串的字符长度［按不同的编码来计算］
     mb_strlen: function (sourceString, encoding) {
         var totalChars  = 0;
         var codeStep    = 2;
         encoding        = encoding.toLowerCase();
         if(encoding == 'utf-8') {
             codeStep    = 3;
         }
         for(var loc = 0; loc < sourceString.length; loc ++) {
             var charCodeValue   = sourceString.charCodeAt(loc);
             if(charCodeValue < 0 || charCodeValue > 255) {
                 totalChars += codeStep;
             } else {
                 totalChars ++;
             }
         }
 
         return totalChars;
     },
     //过滤无用的字符，支持数组，不过只能传keyCode
     filterChar: function (content, filterCode) {
         if(true == this.isEmpty(filterCode)) {
             filterCode  = [10];
         }
         var tempContent     = '';
         for(var loc = 0; loc < content.length; loc ++) {
             var charCode    = content.charCodeAt(loc);
             for(var filterLoc = 0; filterLoc < filterCode.length; filterLoc ++) {
                 if(charCode == filterCode[filterLoc]) {
                     break;
                 }
             }
             if(filterLoc == filterCode.length) {
                 tempContent += content.charAt(loc);
             }
         }
 
         return tempContent;
     },
     //过滤掉多个空格
     filterMoreSpace: function(content) {
         return content.replace(/\s+/g, ' ');
     },
     //得到当前访问地址
     getLocationUrl: function () {
         return window.location.href;
     },
     //得到键输入的Code值
     getKeyCode: function(evt) {
         var event       = evt || window.event;
         if(this.isEmpty(event)) {
             return false;
         }
         return event.charCode || event.keyCode;
     },
     //是否是回车键值
     isEnterKey: function(keyValue) {
         if(keyValue == 13) {
             return true;
         }
 
         return false;
     },
     //是否是空对象或未定义对象
     isEmpty: function(param) {
         if(typeof param == 'undefined' || param == null || param == '') {
             return true;
         }
 
         return false;
     },
     /**
      * 验证字符串的长度，
      * 成功：并返回处理后的结果。
      * 失败：返回fase, 错误信息在this.error里，调用方式：XTools.error
      * 
      * 使用示例：
      * (1) message  = verifyStringLen(message);
      * (2) message  = verifyStringLen(message, maxLen);
      * (3) message  = verifyStringLen(message, maxLen, [10], true);
      * (4) message  = verifyStringLen(message, [minLen, maxLen], [10], true);
      * (5) 其它
      * 
      * @param string content 验证的内容
      * @param int or array lenInfo 长度信息,默认158
      *        格式说明：int：0 ~ lenInfo; array: lenInfo[0] ~ lenInfo[1]
      * @param array filterCode 过滤的字符Unicode值数组，如回车：[10]
      * @param boolean filterMoreSpace 过滤多个空格
      * 
      * @return boolean or string 
      */
     verifyStringLen: function (content, lenInfo, filterCode, filterMoreSpace) {
         var minLen  = 1;
         var maxLen  = 0;
         var lenErrorMsg   = '字数错误！允许范围：';
 
         if(typeof lenInfo == 'undefined') {
             maxLen  = 158;
         } else if(typeof lenInfo == 'number') {
             maxLen  = parseInt(lenInfo);
         } else {
             minLen  = parseInt(lenInfo[0]);
             maxLen  = parseInt(lenInfo[1]);
         }
         if(typeof filterMoreSpace == 'undefined') {
             filterMoreSpace     = true;
         }
         if(typeof filterCode == 'undefined') {
             filterCode  = [10];
         }
         if(filterCode != null) {
             content     = this.filterChar(content, filterCode);
         }
 
         this.error  = '';
         if(true == filterMoreSpace) {
             content = this.filterMoreSpace(content);
         }
         if(minLen > content.length || maxLen < content.length) {
             if(minLen == 0) {
                 this.error  = lenErrorMsg + '不超过' + maxLen + '个字符。';
             }
             this.error  = lenErrorMsg + minLen + '~' + maxLen + '个字符。';
 
             return false;
         }
 
 
         return content;
     },
     /**
      *验证去掉图片链接后的字符串长度    
      */
     checkLen: function (content, lenInfo) {
          var minLen  = 0;
          var maxLen  = 0;
          var lenErrorMsg   = '字数错误！允许范围：';
 
          if(typeof lenInfo == 'undefined') {
              maxLen  = 158;
          } else if(typeof lenInfo == 'number') {
              maxLen  = parseInt(lenInfo);
          } else {
              minLen  = parseInt(lenInfo[0]);
              maxLen  = parseInt(lenInfo[1]);
          }
 
          this.error  = '';
          var text = content.replace(/<img\s.*\s\/>/,"");
          text = text.replace(/\s+/gi,"");
          text = text.replace(/<br\/>/gi,"");
          text = text.replace(/&nbsp;/gi,"");
          text = text.replace(/<p>/gi,"");
          text = text.replace(/<\/p>/gi,"");
 
         if( maxLen < text.length) {
              this.error  = lenErrorMsg + minLen + '~' + maxLen + '个字符。';
              return false;
          }
 
          return content;
      },
 
     //得到或设置给定DomId的innerHtml
     html: function(domId, htmlContent) {
         if(typeof htmlContent == 'undefined') {
             return this.$(domId).innerHTML;
         } else {
             this.$(domId).innerHTML  = htmlContent;
         }
     },
     //按Dom ID得到选择的元素
     $: function (domId) {
         return document.getElementById(domId);
     }
 };


