<?php
add_filter('woocommerce_cart_item_thumbnail', 'c4d_woo_vs_cart_item_thumbnail', 10, 3);

function c4d_woo_vs_cart_item_thumbnail($img, $cart_item, $cart_item_key ) {
  global $_wp_additional_image_sizes, $c4d_plugin_manager;

  if (isset($c4d_plugin_manager['c4d-woo-vs-cart-replace-variation-image']) && $c4d_plugin_manager['c4d-woo-vs-cart-replace-variation-image'] == 'yes') {
    $customAttributes  = get_post_meta( $cart_item['product_id'], 'c4d_woo_vs_product_image_gallery_images', true );
    $file = $_wp_additional_image_sizes['woocommerce_gallery_thumbnail']['width'] .'x'. $_wp_additional_image_sizes['woocommerce_gallery_thumbnail']['height'];

    if ($customAttributes) {
      $customAttributes  = (array)json_decode($customAttributes);
      if (isset($cart_item['variation'])) {
        foreach ($cart_item['variation'] as $key => $value) {
          if(isset($customAttributes[$value]) && $customAttributes[$value] !== '') {
            $images = explode(',', $customAttributes[$value]);
            if (isset($images[0]) && $images[0] !== '') {
              $image = $images[0];
              $ext = 'jpg';
              if (strpos($image, '.png') > 0) {
                $ext = 'png';
              }
              $file .= '.'.$ext;

              $image = preg_replace('/-[0-9]+x[0-9]+\.'.$ext.'/', '-'.$file, $image);

              return '<img src="'.$image.'">';
            }
          }
        }
      }
    }
  }
  return $img;
}