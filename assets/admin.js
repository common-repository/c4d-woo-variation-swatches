(function($){
	$(document).ready(function(){
    var fields = [
      'c4d-woo-vs-single-variation-popup',
      'c4d-woo-vs-single-variation-popup-size',
      'c4d-woo-vs-single-variation-popup-size-color',
      'c4d-woo-vs-single-zoom-out-box',
      'c4d-woo-vs-single-zoom-out-box-size',
      'c4d-woo-vs-single-zoom-out-box-color',
      'c4d-woo-vs-listing-tooltip-background',
      'c4d-woo-vs-listing-tooltip-text-color',
      'c4d-woo-vs-listing-tooltip-typo',
      'c4d-woo-vs-listing-tooltip-space',
      'c4d-woo-vs-single-tooltip-background',
      'c4d-woo-vs-single-tooltip-text-color',
      'c4d-woo-vs-single-tooltip-typo',
      'c4d-woo-vs-single-tooltip-space',
      'c4d-woo-vs-single-nav-show',
      'c4d-woo-vs-single-nav-direction',
      'c4d-woo-vs-single-nav-item-show',
      'c4d-woo-vs-single-nav-item-margin',
      'c4d-woo-vs-single-zoom-pan',
      'c4d-woo-vs-single-box-size',
      'c4d-woo-vs-single-box-size-mobile',
      'c4d-woo-vs-listing-box-size'
    ];
    $.each(fields, function(index, el){
      var element = $('fieldset[id*="' + el + '"]');
      element.append('<div class="c4d-label-pro-version"><a target="blank" href="http://coffee4dev.com/woocommerce-variation-swatches">Pro Version</a></div>');
    });
  });
})(jQuery);
// Product gallery file uploads.
(function($){
	$(document).ready(function(){
		$( '.c4d-woo-vs-add-product-images' ).on( 'click', 'a', function( event ) {
			var $el = $( this ),
			product_gallery_frame;
			$parent = $el.parents('.c4d-woo-vs-product-images-container'),
			$image_gallery_ids = $parent.find( '.c4d_woo_vs_product_image_gallery_values' ),
			$image_gallery_images = $parent.find( '.c4d_woo_vs_product_image_gallery_images' ),
			$product_images    = $parent.find( 'ul.product_images' );
			event.preventDefault();

			// If the media frame already exists, reopen it.
			if ( product_gallery_frame ) {
				product_gallery_frame.open();
				return;
			}

			// Create the media frame.
			product_gallery_frame = wp.media.frames.product_gallery = wp.media({
				// Set the title of the modal.
				title: $el.data( 'choose' ),
				button: {
					text: $el.data( 'update' )
				},
				states: [
					new wp.media.controller.Library({
						title: $el.data( 'choose' ),
						filterable: 'all',
						multiple: true
					})
				]
			});

			// When an image is selected, run a callback.
			product_gallery_frame.on( 'select', function() {
				var selection = product_gallery_frame.state().get( 'selection' ),
				attachment_ids = '',
				attachment_images = '',
				images = '';
				selection.map( function( attachment, index ) {
					attachment = attachment.toJSON();
					if ( attachment.id ) {
						var attachment_image = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
						attachment_ids   = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
						attachment_images  = attachment_images ? attachment_images + ',' + attachment_image : attachment_image;
						var className = '';
						images += '<li class="image '+ className +'" data-attachment_id="' + attachment.id + '" data-attachment_image="' + attachment_image + '">';
						images += '<img src="' + attachment_image + '" />';
						images += '<a target="blank" href="http://coffee4dev.com/woocommerce-variation-swatches/" class="c4d-woo-vs-pro-badge">Pro Version</a>';
						images += '<ul class="actions"><li><a href="#" class="delete" title="' + $el.data('delete') + '">x</a></li></ul></li>';
					}
				});
				$image_gallery_ids.val( attachment_ids );
				$image_gallery_images.val( attachment_images );
				$product_images.html(images);
			});

			// Finally, open the modal.
			product_gallery_frame.open();
		});

		// Image ordering.
		$('.c4d-woo-vs-product-images-container ul.product_images').each(function(index, el){
			var $values = $(el).parents('.c4d-woo-vs-product-images-container').find('.c4d_woo_vs_product_image_gallery_values'),
			$images = $(el).parents('.c4d-woo-vs-product-images-container').find('.c4d_woo_vs_product_image_gallery_images');
			$(el).sortable({
				items: 'li.image:not(.pro-only)',
				cursor: 'move',
				scrollSensitivity: 40,
				forcePlaceholderSize: true,
				forceHelperSize: false,
				helper: 'clone',
				opacity: 0.65,
				placeholder: 'wc-metabox-sortable-placeholder',
				start: function( event, ui ) {
					ui.item.css( 'background-color', '#f6f6f6' );
				},
				stop: function( event, ui ) {
					ui.item.removeAttr( 'style' );
				},
				update: function() {
					var attachment_ids = '',
					attachment_images = '';

					$(el).find( 'li.image:not(.pro-only)' ).css( 'cursor', 'default' ).each( function() {
						var attachment_id = $( this ).attr( 'data-attachment_id' ),
						attachment_image = $( this ).attr( 'data-attachment_image' );
						attachment_ids   = attachment_ids ? attachment_ids + ',' + attachment_id : attachment_id;
						attachment_images  = attachment_images ? attachment_images + ',' + attachment_image : attachment_image;
					});

					$values.val( attachment_ids );
					$images.val( attachment_images );
				}
			});
		});

		// Remove images.
		$( '.c4d-woo-vs-product-images-container' ).on( 'click', 'a.delete', function() {
			var attachment_ids = '',
			attachment_images = '';
			$parent = $(this).parents('.c4d-woo-vs-product-images-container'),
			$values = $parent.find('.c4d_woo_vs_product_image_gallery_values');
			$images = $parent.find('.c4d_woo_vs_product_image_gallery_images');
			$( this ).closest( 'li.image' ).remove();
			$parent.find( 'ul li.image' ).css( 'cursor', 'default' ).each( function() {
				var attachment_id = $( this ).attr( 'data-attachment_id' ),
				attachment_image = $( this ).attr( 'data-attachment_image' );
				attachment_ids = attachment_ids + attachment_id + ',';
				attachment_images = attachment_images +  attachment_image + ',';
			});
			$images.val( attachment_images );
			$values.val( attachment_ids );
			return false;
		});
		
		$('#c4d-woo-variation-swatches-update p .update-link').html('update FREE version now')
		$('#c4d-woo-variation-swatches-update p').append(' If you have premium account, please login and download <a target="blank" href="http://coffee4dev.com/woocommerce-variation-swatches/">your PRO version</a>.');
});
})(jQuery);
