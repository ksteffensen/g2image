/*
    Gallery 2 Image Chooser
    Version 3.0.3 - updated 13 MAY 2008
    Documentation: http://g2image.steffensenfamily.com/

    Author: Kirk Steffensen with inspiration, code snipets,
        and assistance as listed in CREDITS.HTML

    Released under the GPL version 2.
    A copy of the license is in the root folder of this plugin.

    See README.HTML for installation info.
    See CHANGELOG.HTML for a history of changes.
*/

(function() {
		
	/** 
	 * Load plugin specific language pack
	 */
	tinymce.PluginManager.requireLangPack('g2image');

	tinymce.create('tinymce.plugins.g2imagePlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
		
			var t = this
		
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mceg2image', function() {
				var focusElm = ed.selection.getNode();
				var focusElmId = ed.dom.getAttrib(focusElm, 'id', '');

				var flag = "";
				var template = new Array();
				var file_name  = "";

				if (focusElm != null && focusElmId == "mce_plugin_g2image_wpg2"){
					file_name = ed.dom.getAttrib(focusElm, 'alt');
					template['file']   = url + '/popup_wpg2.htm'; // Relative to theme
					template['width']  = '600px';
					template['height'] = '180px';
				}
				else if (focusElm != null && focusElmId == "mce_plugin_g2image_wpg2id"){
					file_name = ed.dom.getAttrib(focusElm, 'alt');
					template['file']   = url + '/popup_wpg2id.htm'; // Relative to theme
					template['width']  = '600px';
					template['height'] = '180px';
				}
				else {
					template['file'] = url + '/g2image.php?g2ic_tinymce=1'; // Relative to theme
					template['width'] = 800;
					template['height'] = 600;
				}

				ed.windowManager.open({
					file : template['file'],
					width : template['width'],
					height : template['height'],
					resizable : "yes",
					scrollbars : "yes"
				}, {
					file_name : file_name,
					plugin_url : url
				});
			});

			// Register example button
			ed.addButton('g2image', {
				title : 'g2image.button_title',
				cmd : 'mceg2image',
				image : url + '/images/g2image.gif'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, node) {
				cm.setActive('g2image', false);
				do {
					if ((node.nodeName.toLowerCase() == "img") && ((ed.dom.getAttrib(node, 'id').indexOf('mce_plugin_g2image_wpg2') == 0) || (ed.dom.getAttrib(node, 'id').indexOf('mce_plugin_g2image_wpg2id') == 0)))
						cm.setActive('g2image', true);
				} while ((node = node.parentNode));
			});
			
			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t._wpg2tohtml(o.content, url);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.set)
					o.content = t._wpg2tohtml(o.content, url);

				if (o.get)
					o.content = t._htmltowpg2(o.content, t);
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname  : 'Gallery 2 Image plugin',
				author    : 'Kirk Steffensen',
				authorurl : 'http://www.steffensenfamily.com',
				infourl   : 'http://g2image.steffensenfamily.com/',
				version   : "3.0.3"
			};
		},
		
		// Private methods
		
		_wpg2tohtml : function(content, pluginURL) {
			// Parse all <wpg2> tags and replace them with images
			var startPos = 0;

			while ((startPos = content.indexOf('<wpg2>', startPos)) != -1) {

				var endPos       = content.indexOf('</wpg2>', startPos);
				var contentAfter = content.substring(endPos+7);

				var file_name   = content.substring(startPos + 6,endPos);

				var empty_image_path = pluginURL + '/images/wpg2_placeholder.jpg';

				// Insert image
				content = content.substring(0, startPos);

				content += '<img src="' + empty_image_path + '" ';
				content += 'alt="'+file_name+'" title="'+file_name+'" class="mceItem" id="mce_plugin_g2image_wpg2" />';

				content += contentAfter;
				
				//alert('insert_to_editor: content='+content);

				startPos++;
			};

			// Parse all <wpg2id> tags and replace them with images
			var startPos = 0;

			while ((startPos = content.indexOf('<wpg2id>', startPos)) != -1) {

				var endPos       = content.indexOf('</wpg2id>', startPos);
				var contentAfter = content.substring(endPos+9);

				var file_name   = content.substring(startPos + 8,endPos);

				var empty_image_path = pluginURL + '/images/wpg2_placeholder.jpg';

				// Insert image
				content = content.substring(0, startPos);

				content += '<img src="' + empty_image_path + '" ';
				content += 'alt="'+file_name+'" title="'+file_name+'" class="mceItem" id="mce_plugin_g2image_wpg2id" />';

				content += contentAfter;

				startPos++;
			};
			
			return content;
		},
	
		_htmltowpg2 : function(content, t) {
			// Parse all WPG2 placeholder img tags and replace them with <wpg2>
			var startPos = -1;

			while ((startPos = content.indexOf('<img', startPos+1)) != -1) {

				var endPos = content.indexOf('/>', startPos);
				var attribs = t._parseAttributes(content.substring(startPos + 4, endPos));

				if (attribs['id'] == "mce_plugin_g2image_wpg2") {

					endPos += 2;
					var embedHTML = '<wpg2>' + attribs['alt'] + '</wpg2>';

					// Insert embed/object chunk
					chunkBefore = content.substring(0, startPos);
					chunkAfter  = content.substring(endPos);

					content = chunkBefore + embedHTML + chunkAfter;
				}
			}

			// Parse all WPG2ID placeholder img tags and replace them with <wpg2id>
			var startPos = -1;

			while ((startPos = content.indexOf('<img', startPos+1)) != -1) {

				var endPos = content.indexOf('/>', startPos);
				var attribs = t._parseAttributes(content.substring(startPos + 4, endPos));

				if (attribs['id'] == "mce_plugin_g2image_wpg2id") {

					endPos += 2;
					var embedHTML = '<wpg2id>' + attribs['alt'] + '</wpg2id>';

					// Insert embed/object chunk
					chunkBefore = content.substring(0, startPos);
					chunkAfter  = content.substring(endPos);

					content = chunkBefore + embedHTML + chunkAfter;
				}
			}
	
			return content;
		},

		_parseAttributes : function(attribute_string) {
	
			var attributeName = "";
			var attributeValue = "";
			var withInName;
			var withInValue;
			var attributes = new Array();
			var whiteSpaceRegExp = new RegExp('^[ \n\r\t]+', 'g');
	
			if (attribute_string == null || attribute_string.length < 2)
				return null;
	
			withInName = withInValue = false;
	
			for (var i=0; i<attribute_string.length; i++) {
				var chr = attribute_string.charAt(i);
	
				if ((chr == '"' || chr == "'") && !withInValue)
					withInValue = true;
	
				else if ((chr == '"' || chr == "'") && withInValue) {
	
					withInValue = false;
	
					var pos = attributeName.lastIndexOf(' ');
					if (pos != -1)
						attributeName = attributeName.substring(pos+1);
	
					attributes[attributeName.toLowerCase()] = attributeValue.substring(1).toLowerCase();
	
					attributeName = "";
					attributeValue = "";
				}
				else if (!whiteSpaceRegExp.test(chr) && !withInName && !withInValue)
					withInName = true;
	
				if (chr == '=' && withInName)
					withInName = false;
	
				if (withInName)
					attributeName += chr;
	
				if (withInValue)
					attributeValue += chr;
			}
	
			return attributes;
		}
		
	});

	// Register plugin
	tinymce.PluginManager.add('g2image', tinymce.plugins.g2imagePlugin);
})();