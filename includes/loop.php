<?php
add_action('woocommerce_after_shop_loop_item', 'c4d_woo_vs_color_box_loop', 60);
add_action('woocommerce_after_shop_loop_item', 'c4d_woo_vs_flip_thumbnail', 60);

function c4d_woo_vs_flip_thumbnail() {
  global $product, $c4d_plugin_manager;

  if (isset($c4d_plugin_manager['c4d-woo-vs-listing-flip-thumbnail']) && $c4d_plugin_manager['c4d-woo-vs-listing-flip-thumbnail'] == 0 ) return false;

  $attachment_ids = $product->get_gallery_image_ids();
  if (count($attachment_ids) < 1) return;
  $image_link = wp_get_attachment_image_src( $attachment_ids[0], 'woocommerce_thumbnail' );

  if (is_array($image_link) && count($image_link) > 0) {
  $html = '<a href="'.esc_url($product->get_permalink()).'"><div class="c4d-woo-vs-flip-thumbnail">';
    $html .= '<img class="flip-image" src="'.esc_attr($image_link[0]).'"/>';
    $html .= '</div></a>';
    echo $html;
  }
}

function c4d_woo_vs_color_box_loop() {
  global $product, $c4d_plugin_manager;

  if (isset($c4d_plugin_manager['c4d-woo-vs-listing-display']) && $c4d_plugin_manager['c4d-woo-vs-listing-display'] == 0 ) return false;
  if (!$product->is_type('variable')) return '';

  $atts = $product->get_attributes();
  $images = get_post_meta( $product->get_id(), 'c4d_woo_vs_product_image_gallery_images', true );
  $images = $images ? json_decode($images) : array();
  $useImage = isset($c4d_plugin_manager['c4d-woo-vs-listing-display-image']) ? $c4d_plugin_manager['c4d-woo-vs-listing-display-image'] : '1';
  $viewMore = isset($c4d_plugin_manager['c4d-woo-vs-listing-view-more']) ? $c4d_plugin_manager['c4d-woo-vs-listing-view-more'] : '0';
  $viewMoreNumber = isset($c4d_plugin_manager['c4d-woo-vs-listing-view-more-number']) ? $c4d_plugin_manager['c4d-woo-vs-listing-view-more-number'] : '5';
  $viewMoreText = isset($c4d_plugin_manager['c4d-woo-vs-listing-view-more-text']) ? $c4d_plugin_manager['c4d-woo-vs-listing-view-more-text'] : __('View More', 'c4d-woo-vs');
  $displayImage = $useImage == 0 ? 'undisplay-image' : '';
  $class = isset($c4d_plugin_manager['c4d-woo-vs-listing-variation-align']) ? $c4d_plugin_manager['c4d-woo-vs-listing-variation-align'] : '';
  $htmlColor = '<div class="c4d-woo-vs-box-colors '.esc_attr($class . ' ' . $displayImage).'">';
  $htmlImage = '<div class="c4d-woo-vs-box-images">';
  $defaultAttributes = get_option('c4d_woo_vs_attributes');
  $defaultAttributes = is_array($defaultAttributes) ? $defaultAttributes : array();
  $customAttributes  = get_post_meta( $product->get_id(), 'c4d_woo_vs', true );
  
  foreach($atts as $key => $att) {
    if (!is_object($att)) continue;
    $attKey = sanitize_title($att->get_name());
    $attName = str_replace('pa_', '', $attKey);
    $type = isset($defaultAttributes[$attName]) ? $defaultAttributes[$attName]['c4d_woo_vs_type'] : 'select';
    $type = isset($customAttributes[$key]) ? $customAttributes[$key]['type'] : $type;

    if (!in_array($type, array('color', 'image'))) continue;
    $terms = $att->get_terms();
    if (!$terms) {
      $terms = array();
      $attDatas = $att->get_data();
      if (isset($attDatas['options'])) {
        foreach ($attDatas['options'] as $option)  {
          $obj = new stdClass();
          $obj->name = $option;
          $obj->slug = sanitize_title($option);
          $terms[] = $obj;
        }
      }
    }
    
    foreach ($terms as $key => $term) {
      $image = isset($images->{$term->slug}) ? $images->{$term->slug} : '';
      $image = array_filter( explode( ',', $image ) );
      $imageBackground = '';
      $index = $attKey . '-index-' . $key;
      if ($image && isset($image[0])) {
        $htmlImage .= '<img class="'.$index.'" src="'.$image[0].'"/>';
        $imageBackground = 'style="background-image: url('.$image[0].')"';
      }
      $color = isset($c4d_plugin_manager['c4d-woo-vs-color-'. $term->slug]['color']) ? $c4d_plugin_manager['c4d-woo-vs-color-'. $term->slug]['color'] : '';
      $htmlColor .= '<div '.$imageBackground.' title="'.esc_attr($term->name).'" class="c4d-woo-vs-box-color c4d-woo-vs-attribute_pa_color-'.$term->slug.' '.$index.'"" data-index="'.esc_attr($index).'"  data-color="'.esc_attr($color).'" data-slug="'.esc_attr($term->slug).'">'.$term->name.'</div>';
      if ($viewMore && $key >= ($viewMoreNumber - 1)) {
        $htmlColor .= '<a href="'.esc_attr(get_permalink($product->get_id())).'" class="c4d-woo-vs-box-viewmore">'.$viewMoreText.'</a>';    
        break;
      } 
    }
  }
  
  $htmlColor .= '</div>';
  $htmlImage .= '</div>';
  echo '<!-- C4D Woocommerce Variation Images by Coffee4dev.com -->';
  echo $htmlColor.$htmlImage;
  echo '<!-- C4D Woocommerce Variation Images by Coffee4dev.com -->';
}
