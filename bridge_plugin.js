/**
 * Plugin Name: Bridge Hand Editor
 * Plugin URI: http://webther.net/
 * Description: TinyMCE plugin providing a Hand editor feature
 * Version: 1.13.10.13
 * Author: Ther <bszirmay@gmail.com>
 * Author URI: http://webther.net
 * License: GNU All Permissive License
 */
 

/**
 * Register button to TinyMCE used to inserting the suit symbols
 * @param ed The TinyMCE editor being used
 * @param url The TinyMCE plugin's URL
 * @param suit The suit to be inserted when the button is clicked
 *             Can be: spades, hearts, diamonds, clubs
 */
function addButtonForSuit(ed, url, suit){
	ed.addButton('insert_' + suit, {
                title : 'Bridge Hand Editor',
                cmd : 'insert_' + suit,
                image : url + '/' + suit + '.gif'
            });
            
	 ed.addCommand('insert_' + suit, function() {
		 var selected_text = ed.selection.getContent();
                
		 var html = {
			 'spades': '!S',
			 'hearts': '!H',
			 'diamonds': '!D',
			 'clubs': '!C',
		 }
					
		 ed.execCommand('mceInsertContent', 0, html[suit]);
	 });      
	
}

(function() {
	var DOM = tinymce.DOM;

	tinymce.create('tinymce.plugins.Bridge', {
		init : function(ed, url) {
				
			addButtonForSuit(ed, url,'spades');
			addButtonForSuit(ed, url,'hearts');
			addButtonForSuit(ed, url,'diamonds');
			addButtonForSuit(ed, url,'clubs');
				
			ed.addButton('edit_hand', {
                title : 'Bridge Hand Editor',
                cmd : 'edit_hand',
                image : url + '/icon.png'
            });
                       
            ed.addCommand('edit_hand', function() {
                var selected_text = ed.selection.getContent();
                
                var item = jQuery(selected_text);
                
                var params = '';
                if(item.attr('src') !== undefined){
					var text = "" + item.attr('src');
					if(text.indexOf('?') >= 0) {
						params = text.substr(text.indexOf('?')+1);
					
						if(item.attr('width') !== undefined){
							params += '&width=' + parseInt(item.attr('width'));
						}
						if(item.attr('height') !== undefined){
							params += '&height=' + parseInt(item.attr('height'));
						}
					}
				}
				
                
                var return_text = selected_text;
                
                ed.windowManager.open({
						file : url + '/editor.php?' + params.trim(),
						width : 680,
						height : 700,
						inline : 1
				}, {
						plugin_url : url, // Plugin absolute URL
				});
                
                //ed.execCommand('mceInsertContent', 0, return_text);
            });
		},
		
		createControl : function(n, cm) {
            return null;
        },

		getInfo : function() {
			return {
				longname : 'Bridge',
				author : 'Ther',
				authorurl : 'http://webther.net/',
				infourl : 'http://webther.net/',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('bridge', tinymce.plugins.Bridge);
})();

