//  Rable.js is a product made for the pbcms platform by Micha de Vries
//  You're not allowed to  experiment with it or try including it on you're own product
//  It wont work, if you do so, all asset requests are being logged!
//  Enjoy the framework!

$.app = {
  isset: function(item) {
    if (item === false || item === undefined || item === "" || item === null) {
      return false;
    } else {
      return true;
    }
  },
  log: function(string) {
    console.log(string);
  },
  page: {
    generate: {
      page: function(type) {
        if ($.app.page.canLoad() === true) {
          var page = "";
          $.pageinfo.items.forEach(function(obj) {
            item = $.app.page.generate.item(obj);
            if (item !== false) {
              page += item;
            }
          });
          page = document.createRange().createContextualFragment(page);
          $.app.log('Succesfully finished the page structure.');
          $.app.log('now updating page content with the following page: ' + page);
          document.getElementsByClassName('page-content')[0].appendChild(page);
          $.app.log('Finished!');
        } else {
          $.app.log(`ERROR: "${$.pageinfo.errorDesc}" with error code: "${$.pageinfo.errorCode}".`);
        }
      },
      item: function(obj) {
        if ($.app.isset(obj.DefaultObject) && obj.DefaultObject === true) {
          if (!$.app.isset(obj.ObjectID)) {
            $.app.log(`[$.app.page.generate.page] ~> ERROR: Invalid object, missing "ObjectID", "ObjectType" or "ObjectContent" with error code: "E_JS_GLOB_PAGEGEN_01"`);
            return false;
          } else {
            var defObj = false;
            var object = '<';
            $.pageinfo.defaultItems.forEach(function(item) {
              if (obj.ObjectName === item.ObjectName) {
                defObj = item;
              }
            });
            if (defObj === false) {
              console.log(obj);
              $.app.log ('[$.app.page.generate.item] ~> ERROR: "default object is not registered" with error code: "E_JS_GLOB_ITEMGEN_01"');
              return false;
            } else {
              if ($.app.isset(defObj.ObjectContent)) {
                if (!$.app.isset(obj[defObj.ObjectContent])) {
                  if (!$.app.isset(defObj.ObjectDefaultContent)) {
                    obj[defObj.ObjectContent] = "";
                  } else {
                    obj[defObj.ObjectContent] = defObj.ObjectDefaultContent;
                  }
                }
                if (!Array.isArray(obj[defObj.ObjectContent])) {
                  obj[defObj.ObjectContent] = $.app.page.generate.processContent(obj[defObj.ObjectContent]);
                }
                fillContent = true;
              } else {
                fillContent = false
              }
              object += defObj.ObjectType + ' ';
              if ($.app.isset(defObj.ObjectInfo)) {
                defObj.ObjectInfo.forEach(function(info) {
                  if (isset(obj[info.Name])) {
                    object += info.Name + '="' + obj[info.Name] + '"';
                  }
                });
              }
              if ($.app.isset(obj.ObjectClass)) {
                object += 'class="' + obj.ObjectClass + '" ';
              }
              if ($.app.isset(obj.ObjectTag)) {
                object += 'id="' + obj.ObjectTag + '" ';
              }
              object += '>';
              if (fillContent === true) {
                if (!Array.isArray(obj[defObj.ObjectContent])) {
                  object += obj[defObj.ObjectContent];
                } else {
                  var items = "";
                  obj[defObj.ObjectContent].forEach(function(obj) {
                    item = $.app.page.generate.item(obj);
                    if (item !== false) {
                      items += item;
                    }
                  });
                  object += items;
                }
              }
              if (!$.app.isset(defObj.ObjectSingleTag)) {
                object += '</' + defObj.ObjectType + '>';
              }
              return object;
            }
          }
        } else {
          if (!$.app.isset(obj.ObjectID) || !$.app.isset(obj.ObjectType) || !$.app.isset(obj.ObjectContent)) {
            $.app.log(`[$.app.page.generate.page] ~> ERROR: Invalid object, missing "ObjectID", "ObjectType" or "ObjectContent" with error code: "E_JS_GLOB_PAGEGEN_01"`);
            return false;
          } else {
            var object = '<' + obj.ObjectType + ' ';
            if ($.app.isset(obj.ObjectClass)) {
              object += 'class="' + obj.ObjectClass + '" ';
            }
            if ($.app.isset(obj.ObjectTag)) {
              object += 'id="' + obj.ObjectTag + '" ';
            }
            if ($.app.isset(obj.ObjectInfo)) {
              obj.ObjectInfo.forEach(function(info) {
                object += info.Name + '="' + info.Content + '" ';
              });
            }
            object += '>';
            if (!Array.isArray(obj.ObjectContent)) {
              object += $.app.page.generate.processContent(obj.ObjectContent);
            } else {
              var items = "";
              obj.ObjectContent.forEach(function(obj) {
                item = $.app.page.generate.item(obj);
                if (item !== false) {
                  items += item;
                }
              });
              object += items;
            }
            if (!$.app.isset(obj.ObjectSingleTag)) {
              object += '</' + obj.ObjectType + '>';
            }
            return object;
          }
        }
      },
      processContent: function(content) {
        //Detect and process links:
        content = content.replace(/#{(.*?)}/gi, $['app']['page']['generate']['processObjectInContent']['link']);
        content = content.replace(/#\[(.*?)\]/gi, $['app']['page']['generate']['processObjectInContent']['link']);
        content = content.replace(/#\((.*?)\)/gi, $['app']['page']['generate']['processObjectInContent']['link']);

        //Detect and process strong text:
        content = content.replace(/S{(.*?)}/gi, $['app']['page']['generate']['processObjectInContent']['strong']);
        content = content.replace(/S\[(.*?)\]/gi, $['app']['page']['generate']['processObjectInContent']['strong']);
        content = content.replace(/S\((.*?)\)/gi, $['app']['page']['generate']['processObjectInContent']['strong']);

        //Return the reformed string
        return content;
      },
      processObjectInContent: {
        link: function(match, val) {
          if (!val.includes(',')) {
            return '<a href="' + val + '">link</a>';
          } else {
            val = val.split(',');
            val[1] = $.app.page.generate.processContent(val[1]);
            return '<a href="' + val[0] + '">' + val[1] + '</a>';
          }
        },
        strong: function(match, val) {
          val = $.app.page.generate.processContent(val);
          return '<strong>' + val + '</strong>';
        }
      }
    },
    canLoad: function() {
      if ($.pageinfo.canLoad === false) {
        return false;
      } else {
        return true;
      }
    }
  }
}

var $app = $.app;

$.app.page.generate.page();
