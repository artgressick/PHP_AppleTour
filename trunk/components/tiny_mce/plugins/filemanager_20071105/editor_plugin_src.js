/**
 * $Id: editor_plugin_src.js 42 2006-08-08 14:32:24Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2007, Moxiecode Systems AB, All rights reserved.
 */

var TinyMCE_FileManagerPlugin = {
	getInfo : function() {
		return {
			longname : 'MCFileManager PHP',
			author : 'Moxiecode Systems AB',
			authorurl : 'http://tinymce.moxiecode.com',
			infourl : 'http://tinymce.moxiecode.com/paypal/item_filemanager.php',
			version : "3.0.2"
		};
	},

	initInstance : function(inst) {
		inst.settings['file_browser_callback'] = 'mcFileManager.filebrowserCallBack';
	},

	getControlHTML : function(cn) {
		switch (cn) {
			case "insertfile":
				return tinyMCE.getButtonHTML(cn, 'lang_filemanager_insertfile_desc', '{$pluginurl}/pages/fm/img/insertfile.gif', 'mceInsertFile', false);
		}

		return "";
	},

	execCommand : function(editor_id, element, command, user_interface, value) {
		var inst = tinyMCE.getInstanceById(editor_id), nl, i, t = this;

		switch (command) {
			case "mceInsertFile":
				s = {
					path : tinyMCE.getParam("filemanager_path"),
					rootpath : tinyMCE.getParam("filemanager_rootpath"),
					remember_last_path : tinyMCE.getParam("filemanager_remember_last_path"),
					custom_data : tinyMCE.getParam("filemanager_custom_data")
				};

				mcFileManager.open(0, '', '', function(url, info) {
					if (!inst.selection.isCollapsed()) {
						inst.execCommand("createlink", false, "javascript:mce_temp_url();");

						nl = tinyMCE.selectElements(inst.getBody(), 'A', function(n) {
							return tinyMCE.getAttrib(n, 'href') == "javascript:mce_temp_url();"
						});

						for (i=0; i<nl.length; i++)
							nl[i].href = url;
					} else
						inst.execCommand('mceInsertContent', false, tinyMCE.storeAwayURLs(t._replace(
							tinyMCE.getParam('filemanager_insert_template', '<a href="{$url}" mce_href="{$url}">{$name}</a>'),
							info, {
								urlencode : function(v) {
									return escape(v);
								},

								xmlEncode : function(v) {
									return tinyMCE.xmlEncode(v);
								}
							}
						)));
				}, s);

				return true;
		}

		return false;
	},

	/* Plugin internal functions */

	_init : function() {
		var ls = tinyMCE.__loadScript ? tinyMCE.__loadScript : tinyMCE.loadScript;

		ls.call(tinyMCE, tinyMCE.baseURL + '/plugins/filemanager/js/mcfilemanager.js');
		ls.call(tinyMCE, tinyMCE.baseURL + '/plugins/filemanager/language/?type=fm&format=tinymce&group=tinymce&prefix=filemanager_');
	},

	_replace : function(t, d, e) {
		var i, r;

		function get(d, s) {
			for (i=0, r=d, s=s.split('.'); i<s.length; i++)
				r = r[s[i]];

			return r;
		};

		// Replace variables
		t = '' + t.replace(/\{\$([^\}]+)\}/g, function(a, b) {
			var l = b.split('|'), v = get(d, l[0]);

			// Default encoding
			if (l.length == 1 && e && e.xmlEncode)
				v = e.xmlEncode(v);

			// Execute encoders
			for (i=1; i<l.length; i++)
				v = e[l[i]](v, d, b);

			return v;
		});

		// Execute functions
		t = t.replace(/\{\=([\w]+)([^\}]+)\}/g, function(a, b, c) {
			return get(e, b)(d, b, c);
		});

		return t;
	}
};

TinyMCE_FileManagerPlugin._init();

tinyMCE.addPlugin("filemanager", TinyMCE_FileManagerPlugin);
