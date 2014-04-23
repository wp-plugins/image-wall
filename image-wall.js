var iw_debug = false;

if(iw_debug) console.log('Image Wall: Entering image-wall.js');

/********************************************************************************/
/*				IMAGE WALL CODE					*/
/********************************************************************************/

function isotopeParseLoadMoreImagesPath(path, page){
	var result = path.match(/^(.*tmn_iw_page=)\d*(.*$)/).slice(1);
	if(iw_debug) console.log('Image Wall: Creating a custom path. [path='+path+', page='+page+', result=' + result);
	return result;
}


(function($) {
	
	//// Helper functions to access data generated in the PHP and stored in the HTML ////
	function tmn_iw_get_column_width(){ 
		return  parseInt($('#tmn-image-wall').attr('column_width'));
	}
	
	function tmn_iw_get_buffer_pixels(){ 
		return  parseInt($('#tmn-image-wall').attr('buffer_pixels'));
	}
	
	function tmn_iw_move_image_wall_to_end(){ 
		return  $('#tmn-image-wall').attr('move_to_end') === 'true';
	}	
	
	function tmn_iw_get_scroll_img_url(){ 
		return  $('#tmn-image-wall').attr('scroll_img_url');
	}

	if($('#tmn-image-wall-support.hidden').length != 0 ) {
		if(iw_debug) console.log('Image Wall: Hiding the support text.');
		$('#tmn-image-wall-support').hide();
	}

	var $container = $('#tmn-image-wall');		
	if(tmn_iw_move_image_wall_to_end()){
		if(iw_debug) console.log('Image Wall: Moving the Image Wall to the end.');
		$container.appendTo('body');	
		$('#tmn-image-wall-next').appendTo('body');
	}

	if(iw_debug) console.log('Image Wall: Moving the HTML in the prep area to the Image Wall.');
	var $prep_area = $('#tmn-image-wall-prep');
	$container.html($prep_area.html()).prepend('<div id="tmn-image-wall-scroll-messages"></div>');
	
	if(iw_debug) console.log('Image Wall: Setting up Images Loaded on the Image Wall.');
	$container.imagesLoaded(function(){
		if(iw_debug) console.log('Image Wall: Images loaded. Activating Masonry on the Image Wall.');
		$container.masonry({
			itemSelector 	: '.tmn-image-wall-item',
			columnWidth	: tmn_iw_get_column_width,
			gutter		: 0,
			isFitWidth	: true,
			isAnimated	: false
		});
	});

	if(iw_debug) console.log('Image Wall: Starting Infinite Scroll.');
	$container.infinitescroll({
			navSelector  : '#tmn-image-wall-next',
			bufferPx     : tmn_iw_get_buffer_pixels(),
			nextSelector : '#tmn-image-wall-next',
			itemSelector : '#tmn-image-wall-prep',
			pathParse    : isotopeParseLoadMoreImagesPath,
			loading: {
				msgTextloading		 : "Congratulations, you've reached the end of the internet.", // tmniwjsi18n.msgTextloading ,
				msgText			 : "Loading more images...", //tmniwjsi18n.msgText ,
				finishedMsg		 : "You have reached the end of the internet!", //tmniwjsi18n.finishedMsg ,
				selector		 : '#tmn-image-wall-scroll-messages',
				img			 : tmn_iw_get_scroll_img_url()
			},
			debug        : true
			
		},
		function( newElements ) {
			if(iw_debug) console.log('Image Wall: New images found through Infinite Scroll.');
			var tempHolder = $( newElements );
			var theimages = tempHolder.find('img')
			var theitems = tempHolder.find('.tmn-image-wall-item-link')
			
			if(theimages.length == 0) {
				if(iw_debug) console.log('Image Wall: No new images found. Asking for another batch.');
				$container.infinitescroll('scroll');	
				tempHolder.remove();
			} else {
				theimages.imagesLoaded(function (imgLoad) {
  					if(iw_debug) console.log('Image Wall: ' + imgLoad.images.length +' images loaded from temporary holder. Adding them to the Image Wall.');
  					theimages.css( 'opacity', 0);
					$container.append( theitems ).masonry( 'appended', theitems, true );
					theimages.css( 'opacity', 1);
					
					if(iw_debug) console.log('Image Wall: No more images in the temporary holder. Removing it.');
					tempHolder.remove();
				});
				if(iw_debug) console.log('Image Wall: New image added to the Image Wall. Appending it to the body while we wait for images to load.');
				tempHolder.appendTo($('body'));			
			}
		}
	);
	
	if(iw_debug) console.log('Image Wall: Removing the initial prep area. (Infinite scroll should be done with it by now.)');
	$prep_area.remove();
	
})( jQuery );