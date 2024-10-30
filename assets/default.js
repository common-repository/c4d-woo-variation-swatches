var c4dWooVS = {
  isCreateSlider: false
};

(function($){
  "use strict";
  //// START FUNCTIONS  //////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  // define filters
  c4dWooVS.options = {
    zoom: {}
  };

  c4dWooVS.flipThumbnail = function() {
    $('body').on('click mouseover mouseout', '.products li img:not(.flip-image)', function(){
      var parents = $(this).parents('li');
      if ($(this).next('.c4d-woo-vs-flip-thumbnail').length < 1) {
        $(this).parent().append(parents.find('.c4d-woo-vs-flip-thumbnail').detach());
      }
      var flip = parents.find('.c4d-woo-vs-flip-thumbnail img');
      if (typeof flip != 'undefined') {
        flip.addClass('active');
      }
    });

    $('body').on('click mouseover mouseout', '.c4d-woo-vs-flip-thumbnail img', function(event){
      var flip = $(this);
      if (typeof flip != 'undefined') {
        if (event.type == 'mouseout') {
          flip.removeClass('active');
        }
      }
    });
  };

  c4dWooVS.loopTooltip = function() {
    $('body').on('mouseover touchstart', '.c4d-woo-vs-box-colors .c4d-woo-vs-box-color', function(){
      if (!($(window).width() < 1024 && c4dWooWsOptions.tooltip_mobile == 0)) {
        tippy('.c4d-woo-vs-box-colors .c4d-woo-vs-box-color', {size: 'small', arrow: true, animateFill: true, theme: 'c4d-woo-vs-category'});
      }
    });
  }

  c4dWooVS.loopColorHover = function() {
    $('body').on('click mouseover mouseout touchstart touchend', '.c4d-woo-vs-box-colors .c4d-woo-vs-box-color', function(event){
      var that = this,
      index =  $(that).data('index'),
      parentSelectors = ['li', c4dWooWsOptions.related_selector_item],
      parent = null;

      $.each(parentSelectors, function(index, parentSelector){
        if($(that).parents(parentSelector).length > 0) {
          parent = $(that).parents(parentSelector);
          return false;
        }
      });

      var mainImage = parent.find('img.wp-post-image, img.attachment-woocommerce_thumbnail, img.size-woocommerce_thumbnail');

      if (mainImage.parent().find('.c4d-woo-vs-box-images').length < 1) {
        mainImage.parent().append(parent.find('.c4d-woo-vs-box-images').detach());
      }

      var current = parent.find('.c4d-woo-vs-box-images img[class*='+ index +']');
      current.addClass('active').siblings().removeClass('active');

      if (event.type == 'mouseout') {
        current.removeClass('active').siblings().removeClass('active');
      }
    });
    c4dWooVS.loopTooltip();
  },

  c4dWooVS.singleCreateSwatches = function() {
    // check nav show
    if (c4dWooWsOptions.nav_type == 'grid') {
      $('body').addClass('c4d-woo-vs-nav-grid');
    }

    if (c4dWooWsOptions.nav_display == 'yes') {
      $('body').addClass('c4d-woo-vs-nav-show');
    }

    // default gallery
    if (c4dWooWsOptions.main_gallery_default == 'yes') {
      var defaulReplace = [],
      datas             = $('.c4d-woo-vs-datas'),
      gallery           = datas.data('gallery');

      $.each(gallery, function(index, el){
        defaulReplace.push([el]);
      });

      if (defaulReplace.length > 0) {
        $('body').addClass('c4d-woo-vs-main-gallery-hidden');
        c4dWooWsOptions.woocommerceWrap = $(c4dWooWsOptions.main_gallery_replace_class).closest('.woocommerce');
        c4dWooVS.createSlider('default-gallery', defaulReplace, true);
      }
    }

    // create color/image label
    $('.variations_form').each(function(index, form){
      if ($(this).hasClass('is-created-swatches-images')) {
        return;
      } 
      $(this).addClass('is-created-swatches-images');
      $('body').addClass('c4d-woo-vs-single-responsive-' + c4dWooWsOptions.single_responsive).addClass('c4d-woo-vs-single-outstock-type-' + c4dWooWsOptions.out_stock_type);
      $(form).addClass('c4d-woo-vs-single-color-box-active');
      var colorHtml       = '<!-- C4D Woocommerce Variation Images by Coffee4dev.com -->',
      variations          = $(form).find('table.variations select'),
      pid                 = $(form).data('product_id'),
      datas               = $('.c4d-woo-vs-datas.pid-' + pid),
      customAttributes    = datas.data('custom_attributes'),
      defaultAttributes   = datas.data('default_attributes'),
      availableVariations = datas.data('variations'),
      images              = datas.data('images'),
      clearLink           = '<div class="c4d-woo-vs-clear"><a class="reset_variations" href="#" style="visibility: visible; display: inline;">'+ c4dWooWsOptions.clear +'</a>';

      if (typeof defaultAttributes == 'undefined') return false;

      if (customAttributes) {
        if (typeof customAttributes.multi_order !== 'undefined' && customAttributes.multi_order !== '-1') {
          $(form).addClass('c4d-woo-vs-allow-multi-order');
        }
      }

      variations.each(function(index, vari){
        var att = $(vari).attr('name'),
        realName = $(vari).parents('tr').find('.label label').text(),
        className = ['c4d-woo-vs-single-list-box'],
        typeName = '',
        selectedValue = $(this).val();
        className.push('c4d-woo-vs-box-shape-'+c4dWooWsOptions.box_shape);

        if (index < 1) {
          className.push('first-list');
        }
        if (att.indexOf('pa_') >= 0) {
          if (typeof defaultAttributes[att.replace('attribute_pa_', '')] != 'undefined') {
            typeName = defaultAttributes[att.replace('attribute_pa_', '')]['c4d_woo_vs_type'] !== '' ? defaultAttributes[att.replace('attribute_pa_', '')]['c4d_woo_vs_type'] : 'text';
            className.push('c4d-woo-vs-type-' + typeName);
          } else {
            typeName = 'image';
            className.push('c4d-woo-vs-type-image');
          }
        } else {
          if (customAttributes) {
            if (typeof customAttributes[att.replace('attribute_', '')] != 'undefined') {
              typeName = customAttributes[att.replace('attribute_', '')]['type'] !== '' ? customAttributes[att.replace('attribute_', '')]['type'] : 'text';
              className.push('c4d-woo-vs-type-' + typeName);
            } else {
              typeName = 'image';
              className.push('c4d-woo-vs-type-image');
            }
          }
        }

        // hide selectbox
        if (typeName !== 'select') {
          $(vari).parents('tr').css('display', 'none');
        } else {
          clearLink = '';
        }

        colorHtml += '<div class="'+ className.join(' ') +'" data-attr="'+att+'">';
        colorHtml += '<label>'+ realName +'</label>';
        var selectBox = $(vari);
        if (c4dWooWsOptions.sort_attribute == 'yes') {
          selectBox = $(vari).clone().sortSelect();
        }
        selectBox.find('option').each(function(index, option){
          var value = $(option).val(),
          name = value,
          prettyName = $(option).text();
          
          if (value != '') {
            var selectedClass = selectedValue == value ? 'active' : '';
            var valueSantize = wpFeSanitizeTitle(value);
            var image = '', imageUrl = '', popupImage = '';
            if (availableVariations) {
              $.each(availableVariations, function(index, el){
                var attributes = el.attributes;
                if (typeof images[valueSantize] != 'undefined' && images[valueSantize] != '') {
                  image = 'style="background-image: url('+images[valueSantize][0]['thumb'][0]+');"';
                  image += ' data-src="' + images[valueSantize][0]['full'][0] + '"';
                  popupImage = typeof images[valueSantize][0]['medium'][0] !== 'undefined' ? images[valueSantize][0]['medium'][0] : images[valueSantize][0]['full'][0];
                } else if (typeof attributes[att] != 'undefined' && attributes[att] == name) {
                  if (typeof el.image !== 'undefined' && el.image.gallery_thumbnail_src !== 'undefined' && el.image.gallery_thumbnail_src !== null) {
                    imageUrl = el.image.gallery_thumbnail_src; // thumbnail 100x100
                    popupImage = el.image.src; // medium 600x600
                    image = 'style="background-image: url('+imageUrl+');"';
                    image += ' data-src="' + el.image.src + '"';
                    image += ' data-thumb="' + imageUrl + '"';
                    image += ' data-large_image="' + el.image.full_src + '"';
                    image += ' data-large_src_w="' + el.image.full_src_w + '"';
                    image += ' data-large_src_h="' + el.image.full_src_h + '"';
                    image += ' data-src_w="' + el.image.src_w + '"';
                    image += ' data-src_h="' + el.image.src_h + '"';
                    return false;
                  }
                }
              });
            } else {
              $.each(images, function(){
                if (typeof images[valueSantize] != 'undefined' && images[valueSantize] != '') {
                  image = 'style="background-image: url('+images[valueSantize][0]['thumb'][0]+');"';
                  image += ' data-src="' + images[valueSantize][0]['full'][0] + '"';
                  popupImage = typeof images[valueSantize][0]['medium'][0] !== 'undefined' ? images[valueSantize][0]['medium'][0] : images[valueSantize][0]['full'][0];
                }
              });
            }

            var popupImageWrap = '';
            if (c4dWooWsOptions.variation_hover_popup == 'yes' && popupImage !== '' && typeName !== 'text' && typeName !== 'select' && typeName !== '') {
              popupImageWrap = '<div class="variation-image-popup" style="background-image: url('+ c4dWooWsOptions.placeholder_image +');"><span class="arrow"></span><img data-src="'+ popupImage +'"></div>';
            }

            var outStockText = '<div class="out-stock">'+ c4dWooWsOptions.out_stock_text +'</div>';
            
            colorHtml += '<div class="attribute-item '+ selectedClass + '">'+ popupImageWrap + outStockText + '<div '+
                          image +
                          ' class="c4d-woo-vs-attribute c4d-woo-vs-attribute_pa c4d-woo-vs-'+ att +
                          ' c4d-woo-vs-'+ att +'-'+ valueSantize +
                          '" data-name="'+ c4dEscAttr(name) +
                          '" data-value="'+ valueSantize +
                          '" data-value_raw="'+ value +
                          '" title="'+ prettyName +'">'+ prettyName +'</div>';
            if (customAttributes) {
              if (att.indexOf(customAttributes.multi_order) > -1) {
                colorHtml += '<div class="multi-field"><input type="number" class="attribute-qty" step="1" min="1" max="" name="att_qty" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric"></div>';
              }
            }
            colorHtml += '</div>';
          }
        });

        colorHtml += '</div>';
      });

      colorHtml += '</div>';
      if (c4dWooWsOptions.hide_clear_button == 'no') {
        colorHtml += clearLink;
      }
      colorHtml += '<!-- C4D Woocommerce Variation Images by Coffee4dev.com -->';

      var appendHtml = '<div>'+ colorHtml +'</div>';

      var placeInser = $(form).find('table.variations');

      if (c4dWooWsOptions.insert_before_default_attribute == 1) {
        placeInser.before(appendHtml);
      } else {
        placeInser.after(appendHtml);
      }

      if (!($(window).width() < 1024 && c4dWooWsOptions.tooltip_mobile == 0)) {
        if (c4dWooWsOptions.variation_hover_popup == 'yes') {
          tippy('.c4d-woo-vs-single-list-box.c4d-woo-vs-type-image .c4d-woo-vs-attribute_pa', {placement: 'bottom', size: 'small', arrow: true, animateFill: true, theme: 'c4d-woo-vs-single'});
          tippy('.c4d-woo-vs-single-list-box:not(.c4d-woo-vs-type-image) .c4d-woo-vs-attribute_pa', {placement: 'top', size: 'small', arrow: true, animateFill: true, theme: 'c4d-woo-vs-single'});
        } else {
          tippy('.c4d-woo-vs-single-list-box .c4d-woo-vs-attribute_pa', {placement: 'top', size: 'small', arrow: true, animateFill: true, theme: 'c4d-woo-vs-single'});
        }
      }
    });
  }

  c4dWooVS.singleColorBox = function() {
    var hasMoreThan2Attributes = 0;
    // change gallery when click on label
    $('body').on('click touch selfClick triggerDefault', '.c4d-woo-vs-single-list-box .c4d-woo-vs-attribute', function(event){
      var self         = $(this),
      topParent        = self.parents('.variations_form'),
      parent           = self.parents('.c4d-woo-vs-single-list-box'),
      value            = self.data('value'),
      valueRaw         = self.data('value_raw'),
      name             = self.data('name'),
      select           = topParent.find('[name = "'+parent.data('attr')+'"]'),
      imageUrl         = !parent.hasClass('c4d-woo-vs-type-text') ? self.data('src') : '',
      form             = self.parents('form'),
      pid              = form.data('product_id'),
      datas            = $('.c4d-woo-vs-datas.pid-' + pid),
      customAttributes = datas.data('custom_attributes'),
      gallery          = datas.data('gallery'),
      images           = datas.data('images'),
      variations       = datas.data('variations'),
      outStockStatus   = false,
      attributeClick   = parent.data('attr');
      hasMoreThan2Attributes = 0;
      c4dWooWsOptions.woocommerceWrap = $(this).closest('.woocommerce');
      
      for (var key in variations[0].attributes) {
        hasMoreThan2Attributes++;
      }
      // check product outstock
      if (parent.hasClass('first-list') || event.type == 'triggerDefault') {
        $('.c4d-woo-vs-single-list-box .attribute-item').removeClass('outstock');
        $('.c4d-woo-vs-single-list-box').removeClass('outstock');
        
        $.each(variations, function(index, variation){
          var attributes = variation.attributes;
          if (variation.is_in_stock == false) {
            $.each(attributes, function(findAttr, value){
              // if product has more than 2 attributes
              if (hasMoreThan2Attributes > 1 && findAttr != attributeClick && (attributes[attributeClick] == valueRaw || attributes[attributeClick] == '')) {
                if (value !== '') {
                  $('[data-attr="'+ findAttr +'"].c4d-woo-vs-single-list-box [data-value_raw="'+ value +'"]').parents('.attribute-item').addClass('outstock');
                } else {
                  $('[data-attr="'+ findAttr +'"].c4d-woo-vs-single-list-box .attribute-item').addClass('outstock');
                }
                outStockStatus = true;
              }

              // if product has lass than 2 attributes
              if (hasMoreThan2Attributes < 2 && value !== '') {
                $('[data-attr="'+ findAttr +'"].c4d-woo-vs-single-list-box [data-value_raw="'+ value +'"]').parents('.attribute-item').addClass('outstock');
                outStockStatus = true; 
              }
            });
          }
        });
      }

      // set default value
      if (
        event.type !== 'selfClick' && 
        !(outStockStatus == true && parent.hasClass('first-list') && event.type == 'triggerDefault')
      ) {
        // reset value and set new value
        if (select.find('[value="'+ c4dAddSlashes(name) +'"]').length < 1 || (parent.hasClass('first-list') && c4dWooWsOptions.out_stock_type == 'hide' && outStockStatus == true) || outStockStatus == true) {
          topParent.find('select').each(function(index, el){
            if (event.type == 'triggerDefault' && index < 1) {
              // do not thing
            } else {
              $(el).val('').trigger('change');
            }

          })
          $('.attribute-item').removeClass('active');
        }

        if ((event.type == 'triggerDefault' && parent.hasClass('first-list')) || (event.type == 'triggerDefault' && c4dWooWsOptions.out_stock_type == 'hide' && outStockStatus == true)) {
          // do not thing
        } else {
          // trigger event to apply new value
          if (select.find('[value="'+ c4dAddSlashes(name) +'"]').length > 0) {
            select.val(name).trigger('change');
          } else if (select.find('[value="'+ value +'"]').length > 0) {
            select.val(value).trigger('change');
          } else if (select.find('[value="'+ valueRaw +'"]').length > 0) {
            select.val(valueRaw).trigger('change');
          }
        }
      }

      // active current label and remove other label active status
      if (customAttributes) {
        if (parent.data('attr').indexOf(customAttributes.multi_order) < 0) {
          self.parents('.attribute-item').addClass('active');
          self.parents('.attribute-item').siblings('.attribute-item').removeClass('active');
        } else {
          self.parents('.attribute-item').toggleClass('active');
        }
      } else {
        self.parents('.attribute-item').addClass('active');
        self.parents('.attribute-item').siblings('.attribute-item').removeClass('active');
      }

      // change gallery image
      if (c4dWooWsOptions.main_gallery_variation == 'yes') {
        var replace = [];

        if (typeof imageUrl == 'undefined') return;

        if (typeof images[value] != 'undefined' && images[value] != '') {
          $.each(images[value], function(index, el){
            if (parseInt(index) !== 0) {
              replace.push(el);
            }
          });
        } else if (imageUrl !== '') {
          replace.push([{
            full: [self.data('large_image'), self.data('large_src_w'), self.data('large_src_h')],
            medium: [self.data('src'), self.data('src_w'), self.data('src_h')],
            srcset: '',
            thumb: [self.data('thumb')],
            title: self.attr('data-original-title')
          }]);

          // add gallery image if exist
          $.each(gallery, function(index, el){
            replace.push([el]);
          });
        }

        if (replace.length > 0) {
          $('body').addClass('c4d-woo-vs-main-gallery-hidden');
          var id = parent.data('attr') + value;
          c4dWooVS.createSlider(id, replace, true);
        }
      }
    });

    // zoom function
    $('body').on('click', '.c4d-woo-vs-zoom', function(event){
      event.preventDefault();
      var slider = $(this).parent().find('.c4d-woo-vs-slider');
      slider.attr('data-pswp-uid', slider.attr('id'));
      c4dWooVS.openPhotoswipe(slider);
    });

    // reset function
    $('body').on('click', '.reset_variations', function(event){
      event.preventDefault();
      $('.c4d-woo-vs-single-list-box .attribute-item').removeClass('active');
      $('.c4d-woo-vs-gallery').removeClass('active');
      //remove outstock label
      $('.c4d-woo-vs-single-list-box .attribute-item').removeClass('outstock');
      $('.c4d-woo-vs-single-list-box').removeClass('outstock');
      if (c4dWooWsOptions.main_gallery_default == 'yes') {
        $('#c4d-woo-vs-slider-default-gallery').parents('.c4d-woo-vs-gallery').addClass('active');
        // trigger window resize to fix some borken ui
        $(window).trigger('resize');
      } else {
        $('body').removeClass('c4d-woo-vs-main-gallery-hidden');
      }
      return false;
    });

    // show the matching variation when user select
    $('.variations select').on('change', function(){
      var self = this;
      var value = $(self).val();
      $('.attribute-item [data-value="'+wpFeSanitizeTitle(value)+'"]').trigger('selfClick');

      if ($('.c4d-woo-vs-single-list-box[data-attr="'+ $(self).attr('data-attribute_name') +'"]').hasClass('first-list')) {
        setTimeout(function(){
          $(self).addClass('current');
          $(self).parents('.variations').find('select:not(.current)').each(function(index, select){
            var attr = $(select).attr('data-attribute_name');
            var label = $('.c4d-woo-vs-single-list-box[data-attr="'+attr+'"]');
            label.find('.attribute-item').addClass('not-match');
            $(select).find('option').each(function(index, option){
              if ($(option).val() !== '') {
                $(label.find('.attribute-item [data-value="'+wpFeSanitizeTitle($(option).val())+'"]')).parents('.attribute-item').removeClass('not-match');
              }
            });
          });
          $(self).removeClass('current');
        }, 400);
      }
    });

    // active the default value, need to strigger 2 time when multi order is enabled
    var selectedDefaultAttributes = $('.variations select').length > 1 ? $('.c4d-woo-vs-single-list-box .attribute-item.active .c4d-woo-vs-attribute') : $('.c4d-woo-vs-single-list-box .attribute-item:first .c4d-woo-vs-attribute');
    selectedDefaultAttributes.each(function(){
      var el = this;
      setTimeout(function(){
        $(el).trigger('triggerDefault');
      }, 1000);
      // multi order need this task to activate label
      setTimeout(function(){
        $(el).trigger('triggerDefault');
      }, 2000);
    });

    //popup images
    $('body').on('mouseover', '.c4d-woo-vs-single-list-box .attribute-item', function(){
      var image = $(this).find('.variation-image-popup img');
      if (image.length && image.attr('src') == undefined) {
        image.attr('src', image.attr('data-src'));
      }
    });

    $('body').on('click touchstart', '.c4d-woo-vs-single-list-box .attribute-item', function(event){
      if (event.currentTarget !== this) return;
      var popup = $(this).find('.variation-image-popup');
      if (popup.length < 1) return;
      if (popup.offset().left < 0) {
        popup.css({
          'transform': 'translate(0%,-10px) scale(1,1)',
          'left': 0 - $(this).offset().left + 25
        });

        popup.find('.arrow').css({
          'left': $(this).offset().left,
          'right': 'auto'
        });
      }

      if ((popup.offset().left + popup.width()) > $(window).width()) {
        popup.css({
          'transform': 'translate(0%,-10px) scale(1,1)',
          'left': 0 - $(this).offset().left + 25
        });

        popup.find('.arrow').css({
          'left': $(this).offset().left,
          'right': 'auto'
        });
      }

      // mobile show
      if (event.type == 'touchstart' && $(event.target).hasClass('c4d-woo-vs-attribute')) {
        $(this).addClass('mobile-show-image-popup');
        $(this).siblings().removeClass('mobile-show-image-popup');
      }
    });
  };

  $('body').on('click', '.reset_variations', function(){
    $('.c4d-woo-vs-attribute').removeClass('outstock');
    $('.c4d-woo-vs-single-list-box').removeClass('outstock');
  });

  $('body').on('touchstart', '.variation-image-popup', function(event){
    $(this).parents('.attribute-item').removeClass('mobile-show-image-popup');
  });

  c4dWooVS.createSlider = function(id, replace, show) {
    var slider = $('#c4d-woo-vs-slider-' +  id),
    nav = $('#c4d-woo-vs-nav-' + id),
    firstImage = '';

    var sliderOptions = {
      accessibility: true,
      slidesToShow: 1,
      slidesToScroll: 1,
      //lazyLoad: 'ondemand',
      adaptiveHeight: true,
      prevArrow: '<button type="button" class="slick-prev"></button>',
      nextArrow: '<button type="button" class="slick-next"></button>'
    };

    if (c4dWooWsOptions.nav_type == 'slider' && c4dWooWsOptions.nav_display == 'yes') {
      sliderOptions.asNavFor = '#c4d-woo-vs-nav-' +  id
    }

    var navOptions = {
      //lazyLoad: 'ondemand',
      slidesToShow: c4dWooWsOptions.nav_item_show,
      slidesToScroll: 1,
      asNavFor: '#c4d-woo-vs-slider-' +  id,
      focusOnSelect: true
    };

    if (c4dWooWsOptions.nav_direction === 'vertical') {
      navOptions.vertical = true;
      navOptions.variableWidth = false;
      navOptions.centerMode = false;
    }

    if (slider.length < 1) {
      var sliderHtml = '<div id="c4d-woo-vs-slider-'+id+'" class="c4d-woo-vs-slider  nav-direction-'+ c4dWooWsOptions.nav_direction +'">',
      navsHtml = '<div id="c4d-woo-vs-nav-'+id+'" class="c4d-woo-vs-nav  nav-direction-'+ c4dWooWsOptions.nav_direction +'">';

      $.each(replace, function(index, el){
        var image = el[0];
        if (index == 0) {
          firstImage = image['medium'][0];
        }
        sliderHtml += '<div class="item-slide">';
        if (c4dWooWsOptions.zoom_pan_image == 'yes') {
          sliderHtml += '<a href="#" class="pan c4d-woo-vs-pan-zoom" data-big="'+ image['full'][0] +'">';
        }

        sliderHtml += '<img ';
        sliderHtml += 'title="'+ image['title']+'" ';
        sliderHtml += 'data-large_image="'+ image['full'][0] +'" ';
        sliderHtml += 'data-large_image_width="'+ image['full'][1]+'" ';
        sliderHtml += 'data-large_image_height="'+ image['full'][2]+'" ';
        sliderHtml += 'data-src="'+ image['full'][0] +'" ';
        sliderHtml += 'src="'+ image['medium'][0] +'">';
        if (c4dWooWsOptions.zoom_pan_image == 'yes') {
          sliderHtml += '</a>';
        }
        sliderHtml += '</div>';

        navsHtml += '<div class="item-slide" ><img src="'+ image['thumb'][0] +'"></div>';
      });

      sliderHtml += '</div>';
      navsHtml += '</div>';

      if (c4dWooWsOptions.nav_display == 'no') {
        navsHtml = '';
      }

      //hide theme gallery and display plugin gallery
      $('head').append('<style>.c4d-woo-vs-main-gallery-hidden '+ c4dWooWsOptions.main_gallery_replace_class +' > * { display: none; }</style>');

      var photoswipeElement = c4dWooWsOptions.zoom_pan_image == 'no' ? '<span title="'+c4dWooWsOptions.fullscreen+'" class="c4d-woo-vs-zoom">'+c4dWooWsOptions.zoom+'</span>' : '';
      c4dWooWsOptions.woocommerceWrap.find(c4dWooWsOptions.main_gallery_replace_class).addClass('is-active-swatch-gallery');
      if (c4dWooWsOptions.woocommerceWrap.find(c4dWooWsOptions.main_gallery_replace_class + ' .c4d-woo-vs-gallery-wrap').length < 1) {
        var wrapClass = ['c4d-woo-vs-gallery-wrap'];
        wrapClass.push(c4dWooWsOptions.nav_button_hide == 'yes' ? 'c4d-woo-vs-nav-hide-button' : '');
        wrapClass.push(c4dWooWsOptions.main_gallery_button == 'yes' ? 'c4d-woo-vs-slider-hide-button' : '');
        c4dWooWsOptions.woocommerceWrap.find(c4dWooWsOptions.main_gallery_replace_class).append('<div class="'+ wrapClass.join(' ') +'"></div>');
      }

      c4dWooWsOptions.woocommerceWrap.find(c4dWooWsOptions.main_gallery_replace_class + ' .c4d-woo-vs-gallery-wrap').append('<div class="c4d-woo-vs-gallery">' + photoswipeElement  + sliderHtml + navsHtml + '</div>');
      if (!($(window).width() < 1024 && c4dWooWsOptions.tooltip_mobile == 0)) {
        tippy('.c4d-woo-vs-zoom', {size: 'small', arrow: true, animateFill: true, theme: 'c4d-woo-vs-single'});
      }
      $('.c4d-woo-vs-gallery').removeClass('active');

      var slider = $('#c4d-woo-vs-slider-' +  id);
      var nav = $('#c4d-woo-vs-nav-' + id);

      if (firstImage !== '') {
        c4dWooVS.isCreateSlider = true;
        $('<img />', { src: firstImage }).load(function(){
          $('.c4d-woo-vs-gallery').removeClass('active');
          slider.parents('.c4d-woo-vs-gallery').addClass('active');
          slider.slick(sliderOptions);
          if (c4dWooWsOptions.nav_type == 'slider' && c4dWooWsOptions.nav_display == 'yes') {
            nav.slick(navOptions);
          } else {
            nav.find('.item-slide').on('click', function(event){
              $(this).addClass('slick-current slick-active').siblings().removeClass('slick-current slick-active');
              slider.slick('slickGoTo', $(this).index());
            });
            slider.on('afterChange', function(event, slick, currentSlide){
              nav.find('.item-slide:nth-child('+ (currentSlide + 1) +')').addClass('slick-current slick-active').siblings().removeClass('slick-current slick-active');
            });
          }
          c4dWooVS.singleZoomFunctions(slider);
          c4dWooVS.isCreateSlider = false;
        });
      }
    } else {
      if (show && !c4dWooVS.isCreateSlider) {
        $('.c4d-woo-vs-gallery').removeClass('active');
        slider.parents('.c4d-woo-vs-gallery').addClass('active');
        slider.slick('unslick');
        slider.slick(sliderOptions);

        if (c4dWooWsOptions.nav_type == 'slider' && c4dWooWsOptions.nav_display == 'yes') {
          nav.slick('unslick');
          nav.slick(navOptions);
        }
      }
      c4dWooVS.singleZoomFunctions(slider);
    }
  }

  c4dWooVS.singleZoomFunctions = function(slider) {
    if (typeof jQuery.zoom == 'function') {
      
      //slider.slick("slickSetOption", "adaptiveHeight", true, true);
      var currentSlide = slider.find('.slick-active');
      currentSlide.trigger( 'resize' );
      currentSlide.trigger( 'zoom.destroy' );
      if (c4dWooWsOptions.zoom_pan_image == 'no' && c4dWooWsOptions.zoom_out_box == 'yes' && $(window).width() > 768) {
        currentSlide.zoom({target: '.c4d-woo-vs-zoom-box .zoom-area'});
      } else if(c4dWooWsOptions.zoom_pan_image == 'no') {
        currentSlide.zoom();
      }

      // zoom
      slider.on('afterChange', function(event, slick, currentSlide){
        var currentSlide = slider.find('[data-slick-index="'+currentSlide+'"]');
        currentSlide.trigger( 'zoom.destroy' );
        if (c4dWooWsOptions.zoom_pan_image == 'no' && c4dWooWsOptions.zoom_out_box == 'yes' && $(window).width() > 768) {
          currentSlide.zoom({target: '.c4d-woo-vs-zoom-box .zoom-area'});
        } else if (c4dWooWsOptions.zoom_pan_image == 'no') {
          currentSlide.zoom();
        }
      });
    }

    // pan zoom
    if (c4dWooWsOptions.zoom_pan_image == 'yes') {
      slider.find('.c4d-woo-vs-pan-zoom').pan({
        pan: false
      });
    }
    // trigger window resize to fix some borken ui
    $(window).trigger('resize');
  }

  c4dWooVS.getGalleryItems = function(slides) {
    var items = [];

    if ( slides.length > 0 ) {
      slides.find('.slick-slide:not(.slick-cloned)').each( function( i, el ) {
        var img = $( el ).find( 'img' );

        if ( img.length ) {
          var large_image_src = img.attr( 'data-src' ),
            large_image_w   = img.attr( 'data-large_image_width' ),
            large_image_h   = img.attr( 'data-large_image_height' ),
            item            = {
              src  : large_image_src,
              w    : large_image_w,
              h    : large_image_h,
              title: img.attr( 'data-caption' ) ? img.attr( 'data-caption' ) : img.attr( 'title' )
            };
          items.push( item );
        }
      } );
    }

    return items;
  };

  c4dWooVS.photoswipe = [];

  c4dWooVS.openPhotoswipe = function(slider) {
    var pswpElement = $( '.pswp' )[0],
      items       = c4dWooVS.getGalleryItems(slider);
    if (items.length < 1) return;
    var options = $.extend(wc_single_product_params.photoswipe_options, {
      index: parseInt(slider.find('.slick-active').attr('data-slick-index')),
      galleryUID: slider.attr('data-pswp-uid'),
      allowPanToNext: false,
      maxSpreadZoom: 1,
      getDoubleTapZoom: function(){return 1;}
    });

    var photoswipe = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options );
    photoswipe.init();
  };

  c4dWooVS.addToCartMulti = function() {
    if ($('form.c4d-woo-vs-allow-multi-order').length < 1) return;

    $('.single_add_to_cart_button:not(".disabled")').on('click', function(event){
      var $thisbutton = $( this ),
      datas = $('.c4d-woo-vs-datas'),
      customAttributes = datas.data('custom_attributes'),
      productVariations = datas.data('variations'),
      items = [],
      multiAttr = '';
      event.preventDefault();
      $thisbutton.removeClass( 'added' );
      $thisbutton.addClass( 'loading' );

      $('.c4d-woo-vs-single-list-box').each(function(index, el){
        var attr = $(el).attr('data-attr');
        if (attr.indexOf(customAttributes.multi_order) > 0) {
          multiAttr = attr;
          $(el).find('.attribute-item').each(function(){
            var attQty = $(this).find('.attribute-qty').val(),
            attrValue = $(this).find('.c4d-woo-vs-attribute');
            if ($(this).hasClass('active') && attQty > 0) {
              var tempObj = {};
              tempObj[attr] = attrValue.attr('data-value');
              tempObj['qty'] = attQty;
              items.push(tempObj);
            }
          });
          return;
        }
      });

      $('.c4d-woo-vs-single-list-box:not([data-attr="' + multiAttr + '"])').each(function(index, el){
        var attr = $(el).attr('data-attr');
        $.map(items, function(val, i){
          return val[attr] = $(el).find('.attribute-item.active .c4d-woo-vs-attribute').attr('data-value');
        });
      });

      $.each(items, function(itemIndex, item){
        $.each(productVariations, function(variationIndex, variation){
          var attributes = variation.attributes;
          $.each(attributes, function(attributeIndex, attribute){
            if (typeof item[attributeIndex] !== 'undefined' && item[attributeIndex] == attribute) {
              item['variation_id'] = variation.variation_id;
              items[itemIndex] = item;
            }
          });
        });
        setTimeout(function(){
          $.post(
            wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ),
            {
              product_id: item.variation_id,
              quantity: item.qty,
              'multi_order_meta': item
            },
            function( response ) {
              // Trigger event so themes can refresh other areas.
              $( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, $thisbutton ] );
            });
        }, itemIndex * 800);
      });
    });
  };

  c4dWooVS.zoomOutBox = function() {
    if (c4dWooWsOptions.zoom_out_box == 'no') return;
    if (c4dWooWsOptions.zoom_pan_image == 'yes') return;
    if ($('.c4d-woo-vs-zoom-box').length > 0) return;
    if ($(window).width() < 768) return;
    $('body').append('<div class="c4d-woo-vs-zoom-box"><div class="zoom-area"></div></div>');
    var zoomPlace = $('.c4d-woo-vs-zoom-box');
    $('body').on('mouseover', '.c4d-woo-vs-gallery.active .c4d-woo-vs-slider', function(){
      var mainImage = $('.c4d-woo-vs-gallery.active');
      zoomPlace.addClass('is-active');
      zoomPlace.css({
        top: mainImage.offset().top,
        left: mainImage.offset().left + mainImage.outerWidth(true)
      });
    });
    $('body').on('mouseout', '.c4d-woo-vs-gallery.active .c4d-woo-vs-slider', function(){
      zoomPlace.removeClass('is-active');
    });
  }

  c4dWooVS.isSmallScreen = function() {
    $(window).resize(function(){
      if ($(window).width() < 1023) {
        $('body').addClass('c4d-woo-vs-mobile');
      } else {
        $('body').removeClass('c4d-woo-vs-mobile');
      }
    });
  }

  c4dWooVS.runAfterAjax = function() {
    if (c4dWooWsOptions.run_after_ajax == 1) {
      $( document ).ajaxComplete(function( event, request, settings ) {
        if (settings.url.indexOf(c4dWooWsOptions.ajax_match) > -1) {
          c4dWooVS.singleCreateSwatches();  
        }
      });
    }
  }
  //// END FUNCTIONS  //////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //// START SCRIPT //////

  $(document).ready(function(){
    c4dWooVS.isSmallScreen();
    c4dWooVS.flipThumbnail();
    c4dWooVS.singleCreateSwatches();
    c4dWooVS.singleColorBox();
    c4dWooVS.loopColorHover();
    c4dWooVS.addToCartMulti();
    c4dWooVS.zoomOutBox();
    
  });
  c4dWooVS.runAfterAjax();
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //// END SCRIPT //////
})(jQuery);

/**
 * Original Source: https://salferrarello.com/wordpress-sanitize-title-javascript/
 *
 * JavaScript function to mimic the WordPress PHP function sanitize_title()
 * See https://codex.wordpress.org/Function_Reference/sanitize_title
 *
 * Note: the WordPress PHP function sanitize_title() accepts two additional
 * optional parameters. At this time, this function does not.
 *
 * @param string title The title to be sanitized.
 * @return string The sanitized string.
 */
function wpFeSanitizeTitle(title ) {
  var diacriticsMap;

  return removeSingleTrailingDash(
    replaceSpacesWithDash(
      removeAccents(
        // Strip any HTML tags.
        title.replace( /<[^>]+>/ig, '' )
      ).toLowerCase().replace(/[^\w\s-]+/g, '')
      // Replace anything that is not a:
        // word character
        // space
        // nor a dash (-)
      // with an empty string (i.e. remove it).
    )
  );

  /**
   * Replace one or more blank spaces (or repeated dashes) with a single dash.
   *
   * @param str String that may contain spaces or multiple dashes.
   * @return String with spaces replaced by dashes and no more than one dash in a row.
   */
  function replaceSpacesWithDash( str ) {
    return str
      // Replace one or more blank spaces with a single dash (-)
      .replace(/ +/g,'-')
      // Replace two or more dashes (-) with a single dash (-).
      .replace(/-{2,}/g, '-');
  }

  /**
   * If the string end in a dash, remove it.
   *
   * @param string str The string which may or may not end in a dash.
   * @return string The string without a dash on the end.
   */
  function removeSingleTrailingDash( str ) {
    if ( '-' === str.substr( str.length - 1 ) ) {
      return str.substr( 0, str.length - 1 );
    }
    return str;
  }

  /* Remove accents/diacritics in a string in JavaScript
   * from https://stackoverflow.com/a/18391901
   */

  /*
   * Licensed under the Apache License, Version 2.0 (the "License");
   * you may not use this file except in compliance with the License.
   * You may obtain a copy of the License at
   *
   * http://www.apache.org/licenses/LICENSE-2.0
   *
   * Unless required by applicable law or agreed to in writing, software
   * distributed under the License is distributed on an "AS IS" BASIS,
   * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   * See the License for the specific language governing permissions and
   * limitations under the License.
   */
  function getDiacriticsRemovalMap() {
    if ( diacriticsMap ) {
      return diacriticsMap;
    }
    var defaultDiacriticsRemovalMap = [
      {'base':'A', 'letters':'\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F'},
      {'base':'AA','letters':'\uA732'},
      {'base':'AE','letters':'\u00C6\u01FC\u01E2'},
      {'base':'AO','letters':'\uA734'},
      {'base':'AU','letters':'\uA736'},
      {'base':'AV','letters':'\uA738\uA73A'},
      {'base':'AY','letters':'\uA73C'},
      {'base':'B', 'letters':'\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181'},
      {'base':'C', 'letters':'\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E'},
      {'base':'D', 'letters':'\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779\u00D0'},
      {'base':'DZ','letters':'\u01F1\u01C4'},
      {'base':'Dz','letters':'\u01F2\u01C5'},
      {'base':'E', 'letters':'\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E'},
      {'base':'F', 'letters':'\u0046\u24BB\uFF26\u1E1E\u0191\uA77B'},
      {'base':'G', 'letters':'\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E'},
      {'base':'H', 'letters':'\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D'},
      {'base':'I', 'letters':'\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197'},
      {'base':'J', 'letters':'\u004A\u24BF\uFF2A\u0134\u0248'},
      {'base':'K', 'letters':'\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2'},
      {'base':'L', 'letters':'\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780'},
      {'base':'LJ','letters':'\u01C7'},
      {'base':'Lj','letters':'\u01C8'},
      {'base':'M', 'letters':'\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C'},
      {'base':'N', 'letters':'\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4'},
      {'base':'NJ','letters':'\u01CA'},
      {'base':'Nj','letters':'\u01CB'},
      {'base':'O', 'letters':'\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C'},
        {'base':'OI','letters':'\u01A2'},
      {'base':'OO','letters':'\uA74E'},
      {'base':'OU','letters':'\u0222'},
      {'base':'OE','letters':'\u008C\u0152'},
      {'base':'oe','letters':'\u009C\u0153'},
      {'base':'P', 'letters':'\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754'},
      {'base':'Q', 'letters':'\u0051\u24C6\uFF31\uA756\uA758\u024A'},
      {'base':'R', 'letters':'\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782'},
      {'base':'S', 'letters':'\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784'},
      {'base':'T', 'letters':'\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786'},
      {'base':'TZ','letters':'\uA728'},
      {'base':'U', 'letters':'\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244'},
      {'base':'V', 'letters':'\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245'},
      {'base':'VY','letters':'\uA760'},
      {'base':'W', 'letters':'\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72'},
      {'base':'X', 'letters':'\u0058\u24CD\uFF38\u1E8A\u1E8C'},
      {'base':'Y', 'letters':'\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE'},
      {'base':'Z', 'letters':'\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762'},
      {'base':'a', 'letters':'\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250'},
      {'base':'aa','letters':'\uA733'},
      {'base':'ae','letters':'\u00E6\u01FD\u01E3'},
      {'base':'ao','letters':'\uA735'},
      {'base':'au','letters':'\uA737'},
      {'base':'av','letters':'\uA739\uA73B'},
      {'base':'ay','letters':'\uA73D'},
      {'base':'b', 'letters':'\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253'},
      {'base':'c', 'letters':'\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184'},
      {'base':'d', 'letters':'\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A'},
      {'base':'dz','letters':'\u01F3\u01C6'},
      {'base':'e', 'letters':'\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD'},
      {'base':'f', 'letters':'\u0066\u24D5\uFF46\u1E1F\u0192\uA77C'},
      {'base':'g', 'letters':'\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F'},
      {'base':'h', 'letters':'\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265'},
      {'base':'hv','letters':'\u0195'},
      {'base':'i', 'letters':'\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131'},
      {'base':'j', 'letters':'\u006A\u24D9\uFF4A\u0135\u01F0\u0249'},
      {'base':'k', 'letters':'\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3'},
      {'base':'l', 'letters':'\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747'},
      {'base':'lj','letters':'\u01C9'},
      {'base':'m', 'letters':'\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F'},
      {'base':'n', 'letters':'\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5'},
      {'base':'nj','letters':'\u01CC'},
      {'base':'o', 'letters':'\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275'},
        {'base':'oi','letters':'\u01A3'},
      {'base':'ou','letters':'\u0223'},
      {'base':'oo','letters':'\uA74F'},
      {'base':'p','letters':'\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755'},
      {'base':'q','letters':'\u0071\u24E0\uFF51\u024B\uA757\uA759'},
      {'base':'r','letters':'\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783'},
      {'base':'s','letters':'\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B'},
      {'base':'t','letters':'\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787'},
      {'base':'tz','letters':'\uA729'},
      {'base':'u','letters': '\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289'},
      {'base':'v','letters':'\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C'},
      {'base':'vy','letters':'\uA761'},
      {'base':'w','letters':'\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73'},
      {'base':'x','letters':'\u0078\u24E7\uFF58\u1E8B\u1E8D'},
      {'base':'y','letters':'\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF'},
      {'base':'z','letters':'\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763'}
    ];

    diacriticsMap = {};
    for (var i=0; i < defaultDiacriticsRemovalMap .length; i++){
      var letters = defaultDiacriticsRemovalMap [i].letters;
      for (var j=0; j < letters.length ; j++){
        diacriticsMap[letters[j]] = defaultDiacriticsRemovalMap [i].base;
      }
    }
    return diacriticsMap;
  }

  // Remove accent characters/diacritics from the string.
  function removeAccents (str) {
    diacriticsMap = getDiacriticsRemovalMap();
    return str.replace(/[^\u0000-\u007E]/g, function(a) {
      return diacriticsMap[a] || a;
    });
  }
}

/**
 * Sort values alphabetically in select
 * source: http://stackoverflow.com/questions/12073270/sorting-options-elements-alphabetically-using-jquery
 */

jQuery.fn.sortSelect = function() {
  var options = this.find("option"),
      arr = options.map(function(_, o) {
        return { t: jQuery(o).text(), v: o.value }; }
      ).get();

  arr.sort(function(o1, o2) { // sort select
      var t1 = o1.t.toLowerCase(),
          t2 = o2.t.toLowerCase();
      return t1 > t2 ? 1 : t1 < t2 ? -1 : 0;
  });

  options.each(function(i, o) {
      o.value = arr[i].v;
      jQuery(o).text(arr[i].t);
  });

  return this;
}

function c4dAddSlashes(string) {
  return string.toString().replace(/\\/g, '\\\\').
    replace(/\u0008/g, '\\b').
    replace(/\t/g, '\\t').
    replace(/\n/g, '\\n').
    replace(/\f/g, '\\f').
    replace(/\r/g, '\\r').
    replace(/'/g, '\\\'').
    replace(/"/g, '\\"');
}

function c4dEscAttr(s, preserveCR) {
  preserveCR = preserveCR ? '&#13;' : '\n';
  return ('' + s.toString()) /* Forces the conversion to string. */
      .replace(/&/g, '&amp;') /* This MUST be the 1st replacement. */
      .replace(/'/g, '&apos;') /* The 4 other predefined entities, required. */
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      /*
      You may add other replacements here for HTML only
      (but it's not necessary).
      Or for XML, only if the named entities are defined in its DTD.
      */
      .replace(/\r\n/g, preserveCR) /* Must be before the next replacement. */
      .replace(/[\r\n]/g, preserveCR);
      ;
}
