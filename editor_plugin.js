tinyMCE.importPluginLanguagePack('g2image','de, de_DE, en, es, es_ES, hu, it, ko_KR, nl, no, pl, zh_TW');var TinyMCE_g2imagePlugin={getInfo:function(){return{longname:'Gallery 2 Image plugin',author:'Kirk Steffensen',authorurl:'http://www.steffensenfamily.com',infourl:'http://g2image.steffensenfamily.com/',version:"2.2.3"}},getControlHTML:function(control_name){switch(control_name){case"g2image":return tinyMCE.getButtonHTML(control_name,'lang_g2image_button_title','{$pluginurl}/images/g2image.gif','mceg2image')}return""},execCommand:function(editor_id,element,command,user_interface,value){var inst=tinyMCE.getInstanceById(editor_id);var focusElm=inst.getFocusElement();var doc=inst.getDoc();function getAttrib(elm,name){return elm.getAttribute(name)?elm.getAttribute(name):""}switch(command){case"mceg2image":var flag="";var template=new Array();var file_name="";if(focusElm!=null&&getAttrib(focusElm,'id')=="mce_plugin_g2image_wpg2"){file_name=getAttrib(focusElm,'alt');template['file']=this.baseURL+'/popup_wpg2.htm';template['width']='600px';template['height']='180px';tinyMCE.openWindow(template,{editor_id:editor_id,file_name:file_name,mceDo:'update'})}else if(focusElm!=null&&getAttrib(focusElm,'id')=="mce_plugin_g2image_wpg2id"){file_name=getAttrib(focusElm,'alt');template['file']=this.baseURL+'/popup_wpg2id.htm';template['width']='600px';template['height']='180px';tinyMCE.openWindow(template,{editor_id:editor_id,file_name:file_name,mceDo:'update'})}else{template['file']=this.baseURL+'/g2image.php?g2ic_tinymce=1';template['width']=600;template['height']=600;tinyMCE.openWindow(template,{editor_id:editor_id,mceDo:'insert',resizable:"yes",scrollbars:"yes"})}return true}return false},cleanup:function(type,content){switch(type){case"insert_to_editor":var startPos=0;while((startPos=content.indexOf('<wpg2>',startPos))!=-1){var endPos=content.indexOf('</wpg2>',startPos);var contentAfter=content.substring(endPos+7);var file_name=content.substring(startPos+6,endPos);var empty_image_path=this.baseURL+'/images/wpg2_placeholder.jpg';content=content.substring(0,startPos);content+='<img src="'+empty_image_path+'" ';content+='alt="'+file_name+'" title="'+file_name+'" id="mce_plugin_g2image_wpg2" />';content+=contentAfter;startPos++}var startPos=0;while((startPos=content.indexOf('<wpg2id>',startPos))!=-1){var endPos=content.indexOf('</wpg2id>',startPos);var contentAfter=content.substring(endPos+9);var file_name=content.substring(startPos+8,endPos);var empty_image_path=this.baseURL+'/images/wpg2_placeholder.jpg';content=content.substring(0,startPos);content+='<img src="'+empty_image_path+'" ';content+='alt="'+file_name+'" title="'+file_name+'" id="mce_plugin_g2image_wpg2id" />';content+=contentAfter;startPos++}break;case"get_from_editor":var startPos=-1;while((startPos=content.indexOf('<img',startPos+1))!=-1){var endPos=content.indexOf('/>',startPos);var attribs=TinyMCE_g2imagePlugin._parseAttributes(content.substring(startPos+4,endPos));if(attribs['id']=="mce_plugin_g2image_wpg2"){endPos+=2;var embedHTML='<wpg2>'+attribs['alt']+'</wpg2>';chunkBefore=content.substring(0,startPos);chunkAfter=content.substring(endPos);content=chunkBefore+embedHTML+chunkAfter}}var startPos=-1;while((startPos=content.indexOf('<img',startPos+1))!=-1){var endPos=content.indexOf('/>',startPos);var attribs=TinyMCE_g2imagePlugin._parseAttributes(content.substring(startPos+4,endPos));if(attribs['id']=="mce_plugin_g2image_wpg2id"){endPos+=2;var embedHTML='<wpg2id>'+attribs['alt']+'</wpg2id>';chunkBefore=content.substring(0,startPos);chunkAfter=content.substring(endPos);content=chunkBefore+embedHTML+chunkAfter}}break}return content},_parseAttributes:function(attribute_string){var attributeName="";var attributeValue="";var withInName;var withInValue;var attributes=new Array();var whiteSpaceRegExp=new RegExp('^[ \n\r\t]+','g');if(attribute_string==null||attribute_string.length<2)return null;withInName=withInValue=false;for(var i=0;i<attribute_string.length;i++){var chr=attribute_string.charAt(i);if((chr=='"'||chr=="'")&&!withInValue)withInValue=true;else if((chr=='"'||chr=="'")&&withInValue){withInValue=false;var pos=attributeName.lastIndexOf(' ');if(pos!=-1)attributeName=attributeName.substring(pos+1);attributes[attributeName.toLowerCase()]=attributeValue.substring(1).toLowerCase();attributeName="";attributeValue=""}else if(!whiteSpaceRegExp.test(chr)&&!withInName&&!withInValue)withInName=true;if(chr=='='&&withInName)withInName=false;if(withInName)attributeName+=chr;if(withInValue)attributeValue+=chr}return attributes},handleNodeChange:function(editor_id,node,undo_index,undo_levels,visual_aid,any_selection){function getAttrib(elm,name){return elm.getAttribute(name)?elm.getAttribute(name):""}tinyMCE.switchClass(editor_id+'_g2image','mceButtonNormal');if(node==null)return;do{if((node.nodeName.toLowerCase()=="img")&&((getAttrib(node,'id').indexOf('mce_plugin_g2image_wpg2')==0)||(getAttrib(node,'id').indexOf('mce_plugin_g2image_wpg2id')==0)))tinyMCE.switchClass(editor_id+'_g2image','mceButtonSelected')}while((node=node.parentNode));return true}};tinyMCE.addPlugin("g2image",TinyMCE_g2imagePlugin);
