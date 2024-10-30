<?php
add_action( 'woocommerce_before_single_product', 'c4d_woo_vs_datas' );
add_action( 'c4d_woo_qv_before_single_product_summary', 'c4d_woo_vs_datas' );
add_action( 'yith_wcqv_product_summary', 'c4d_woo_vs_datas' );
add_action( 'after_setup_theme', 'c4d_woo_vs_after_setup_theme' );
add_filter( 'woocommerce_add_cart_item', 'c4d_woo_vs_woocommerce_add_cart_item', 10, 2);
add_filter( 'woocommerce_add_cart_item_data', 'c4d_woo_vs_woocommerce_add_cart_item_data', 10, 4 );
add_action( 'wp_enqueue_scripts', 'c4d_woo_vs_styles_inline' );

function c4d_woo_vs_styles_inline() {
  global $c4d_plugin_manager, $product;
  if (function_exists('is_product') && is_product()) {
    $custom_css = '';
    if (isset($c4d_plugin_manager['c4d-woo-vs-single-button-text-height']) && $c4d_plugin_manager['c4d-woo-vs-single-button-text-height'] > 0) {
      $custom_css .= "
        .c4d-woo-vs-type-text .c4d-woo-vs-attribute {
          height: {$c4d_plugin_manager['c4d-woo-vs-single-button-text-height']}px;
        }
      ";
    }

    if (isset($c4d_plugin_manager['c4d-woo-vs-single-button-text-width']) && $c4d_plugin_manager['c4d-woo-vs-single-button-text-width'] > 0) {
      $custom_css .= "
        .c4d-woo-vs-type-text .c4d-woo-vs-attribute {
          width: {$c4d_plugin_manager['c4d-woo-vs-single-button-text-width']}px;
        }
      ";
    }

    if (isset($c4d_plugin_manager['c4d-woo-vs-single-button-text-border-rounded']) && $c4d_plugin_manager['c4d-woo-vs-single-button-text-border-rounded'] == 'yes') {
      $custom_css .= "
        .c4d-woo-vs-type-text .c4d-woo-vs-attribute {
          border-radius: 50%;
          padding: 0;
        }
      ";
    }

    if (isset($c4d_plugin_manager['c4d-woo-vs-multi-order-height']) && $c4d_plugin_manager['c4d-woo-vs-multi-order-height'] > 0) {
      $custom_css .= "
        .c4d-woo-vs-single-list-box .multi-field {
          height: {$c4d_plugin_manager['c4d-woo-vs-multi-order-height']}px;
        }
      ";
    }

    if (isset($c4d_plugin_manager['c4d-woo-vs-multi-order-width']) && $c4d_plugin_manager['c4d-woo-vs-multi-order-width'] > 0) {
      $custom_css .= "
        .c4d-woo-vs-single-list-box .multi-field {
          width: {$c4d_plugin_manager['c4d-woo-vs-multi-order-width']}px;
        }
      ";
    }

    if (isset($c4d_plugin_manager['c4d-woo-vs-multi-order-border-rounded']) && $c4d_plugin_manager['c4d-woo-vs-multi-order-border-rounded'] == 'yes') {
      $custom_css .= "
        .c4d-woo-vs-single-list-box .multi-field {
          border-radius: 50%;
          padding: 0;
        }
      ";
    }

    if ($custom_css != '') {
      wp_register_style( 'c4d-woo-vs-inline-style', false, array( 'c4d-woo-vs-site-style' )  );
      wp_enqueue_style( 'c4d-woo-vs-inline-style' );
      wp_add_inline_style( 'c4d-woo-vs-inline-style', $custom_css );
    }
  }
}

function c4d_woo_vs_after_setup_theme() {
  global $c4d_plugin_manager;
  if (isset($c4d_plugin_manager['c4d-woo-vs-single-zoom-load-lib']) && $c4d_plugin_manager['c4d-woo-vs-single-zoom-load-lib'] == 'yes') {
    add_theme_support( 'wc-product-gallery-zoom' );  
  }
  add_theme_support( 'wc-product-gallery-lightbox' );
}

function c4d_woo_vs_datas() {
  global $product;
  if ($product) {
    $customAttributes  = get_post_meta( $product->get_id(), 'c4d_woo_vs', true );
    $defaultAttributes = get_option('c4d_woo_vs_attributes');
    $defaultAttributes = is_array($defaultAttributes) ? $defaultAttributes                   : array();
    $variations        = $product->is_type('variable') ? $product->get_available_variations(): array();
    $colors            = get_post_meta( $product->get_id(), 'c4d_woo_vs_product_image_gallery_values', true );
    $taxs              = (array)json_decode(get_post_meta( $product->get_id(), 'c4d_woo_vs_product_image_gallery_tax', true ));
    $ids               = (array)json_decode($colors);
    $imagesObj         = array();
    $defaultTypes      = array();

    foreach ($ids as $att => $values) {
      if ($values != '') {
        $values = explode(',', $values);
       
        if (isset($taxs[$att]) && $taxs[$att] != '') {
          $termTranslated = get_term_by('slug', $att, $taxs[$att]);
          if ($termTranslated) {
            $att = $termTranslated->slug;
          }
        }
        
        $imagesObj[$att] = array();

        foreach($values as $key => $id) {
          $obj                    = new stdClass();
          $obj->thumb             = wp_get_attachment_image_src($id, 'woocommerce_gallery_thumbnail');
          if ($obj->thumb) {
            $obj->full              = wp_get_attachment_image_src($id, 'woocommerce_gallery_full_size');
            $obj->medium            = wp_get_attachment_image_src($id, 'woocommerce_single');
            //$obj->size              = wp_get_attachment_image_sizes($id);
            //$obj->srcset            = wp_get_attachment_image_srcset($id);
            $obj->title             = get_the_title($id);
            $imagesObj[$att][$key + 1][] = $obj;
            if ($key == 0) {
              $imagesObj[$att][0] = $obj;
            }
          }
        }
      }
    }

    // default gallery
    $gallery        = array();
    $mainImage      = $product->get_image_id();
    $attachment_ids = $product->get_gallery_image_ids();
    array_unshift($attachment_ids, (int)$mainImage);

    if ( $attachment_ids ) {
      foreach ( $attachment_ids as $attachment_id ) {
        if ($attachment_id) {
          $obj         = new stdClass();
          $obj->thumb  = wp_get_attachment_image_src($attachment_id, 'woocommerce_gallery_thumbnail');
          if ($obj->thumb) {
            $obj->full   = wp_get_attachment_image_src($attachment_id, 'woocommerce_gallery_full_size');
            $obj->medium = wp_get_attachment_image_src($attachment_id, 'woocommerce_single');
            //$obj->size   = wp_get_attachment_image_sizes($attachment_id);
            $obj->title  = get_the_title($attachment_id);
            $gallery[]   = $obj;
          }
        }
      }
    }

    $fieldName = 'c4d_woo_vs';
    echo '<div class="c4d-woo-vs-datas pid-'.esc_attr($product->get_id()).'"
            data-custom_attributes="'.htmlspecialchars( wp_json_encode($customAttributes)).'"
            data-default_attributes="'.htmlspecialchars( wp_json_encode($defaultAttributes)).'"
            data-color="'.htmlspecialchars( $colors).'"
            data-images="'.htmlspecialchars(wp_json_encode($imagesObj)).'"
            data-gallery="'.htmlspecialchars(wp_json_encode($gallery)).'"
            data-variations="'.htmlspecialchars(wp_json_encode($variations)).'"
          ></div>';
  }
}

// need hook 2 functions to always created new cart item
function c4d_woo_vs_woocommerce_add_cart_item($cart_item_data, $cart_item_key) {
  return c4d_woo_vs_prepare_meta_multi_order($cart_item_data);
}

function c4d_woo_vs_woocommerce_add_cart_item_data($cart_item_data, $product_id, $variation_id, $quantity) {
  return c4d_woo_vs_prepare_meta_multi_order($cart_item_data);
}

function c4d_woo_vs_prepare_meta_multi_order($cart_item_data) {
  if (isset($_POST['multi_order_meta']) && is_array($_POST['multi_order_meta'])) {
    $cart_item_data['variation'] = array();
    foreach($_POST['multi_order_meta'] as $key => $value) {
      if (strpos($key, 'attribute_') !== false) {
        $cart_item_data['variation'][$key] = sanitize_text_field($value);
      }
    }
  }
  return $cart_item_data;
}
