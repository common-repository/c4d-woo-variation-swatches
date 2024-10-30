<?php
/*
Plugin Name: C4D Woo Variation Images
Plugin URI: http://coffee4dev.com/woocommerce-variation-swatches/
Description: C4D WooCommerce Variation Swatches can show product variation items in images, colors, and label.
Author: Coffee4dev.com
Author URI: http://coffee4dev.com/
Text Domain: c4d-woo-vs
Domain Path: /languages
Version: 1.3.64
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

add_filter( 'plugin_row_meta', 'c4d_woo_vs_plugin_row_meta', 10, 2 );

function c4d_woo_vs_plugin_row_meta( $links, $file ) {
  if ( 'c4d-woo-variation-swatches/c4d-woo-variation-swatches.php' == $file) {
    $new_links = array(
      'options' => '<a target="blank" href="admin.php?page=c4d-plugin-manager">Settings</a>',
      'detail' => '<a target="blank" href="http://coffee4dev.com/woocommerce-variation-swatches/">Detail</a>',
      'demo' => '<a target="blank" href="http://30tet.coffee4dev.com/product/variation-swatches/">Demo</a>',
      'document' => '<a target="blank" href="http://coffee4dev.com/docs/document-c4d-woo-variation-swatches/">Docs</a>'
    );
    if (!defined('C4DPMANAGER_PLUGIN_URI')) {
      $new_links['options'] = '<a target="blank" href="https://wordpress.org/plugins/c4d-plugin-manager/">Settings</a>';
    }
    $links = array_merge( $links, $new_links );
  }
  return $links;
}

if ( !in_array(
  'woocommerce/woocommerce.php',
   get_option( 'active_plugins' )
)  ) return;

$uploadDir = wp_get_upload_dir();

define('C4DWOOVS_PLUGIN_URI', plugins_url('', __FILE__));
define('C4DWOOVS_UPLOAD_DIR', trailingslashit($uploadDir['basedir'].'/c4d-woo-variation-swatches/'));

include_once (dirname(__FILE__). '/includes/required.php');
include_once (dirname(__FILE__). '/includes/color.php');
include_once (dirname(__FILE__). '/includes/options.php');
include_once (dirname(__FILE__). '/includes/single.php');
include_once (dirname(__FILE__). '/includes/loop.php');
include_once (dirname(__FILE__). '/includes/metaboxes.php');
include_once (dirname(__FILE__). '/includes/tab.php');
include_once (dirname(__FILE__). '/includes/cart.php');

add_action( 'admin_enqueue_scripts', 'c4d_woo_vs_load_scripts_admin' );
add_action( 'wp_enqueue_scripts', 'c4d_woo_vs_load_scripts_site');
add_action( 'admin_enqueue_scripts', 'c4d_woo_vs_load_scripts_admin');
add_action( 'c4d-plugin-manager-section', 'c4d_woo_vs_section_options', 1000);
add_action( 'woocommerce_after_add_attribute_fields', 'c4d_woo_vs_create_list_color');

add_action( 'plugins_loaded', 'c4d_woo_vs_load_textdomain' );

function c4d_woo_vs_load_textdomain() {
  load_plugin_textdomain( 'c4d-woo-vs', false, dirname(plugin_basename( __FILE__ )) . '/languages' );
}

function c4d_woo_vs_load_scripts_site() {
  global $c4d_plugin_manager;
  wp_enqueue_script( 'tippy', C4DWOOVS_PLUGIN_URI . '/assets/tippy.min.js', array( 'jquery' ), true, true );
  wp_enqueue_script( 'c4d-woo-vs-site-js', C4DWOOVS_PLUGIN_URI . '/assets/default.js', array( 'jquery' ), false, true );
  wp_enqueue_style( 'c4d-woo-vs-site-style', C4DWOOVS_PLUGIN_URI.'/assets/default.css' );

  if (is_single()) {
    if (!isset($c4d_plugin_manager['c4d-woo-vs-load-slick-js']) || isset($c4d_plugin_manager['c4d-woo-vs-load-slick-js']) && $c4d_plugin_manager['c4d-woo-vs-load-slick-js'] == 1) {
      wp_enqueue_script( 'jquery-slick', C4DWOOVS_PLUGIN_URI.'/lib/slick/slick.js', array( 'jquery' ), true, true );  
    }
    
    wp_enqueue_script( 'fullscreen-zoom-pan', C4DWOOVS_PLUGIN_URI . '/assets/jquery.pan.js', array( 'jquery' ), false, true );
    wp_enqueue_style( 'jquery-slick', C4DWOOVS_PLUGIN_URI.'/lib/slick/slick.css');
    wp_enqueue_style( 'jquery-slick-theme', C4DWOOVS_PLUGIN_URI.'/lib/slick/slick-theme.css');
    wp_enqueue_style( 'fullscreen-zoom-pan', C4DWOOVS_PLUGIN_URI.'/assets/css/jquery.pan.css', array(), false);
  }

  // Localize the script with new data

  $options = array(
    'clear' => __( 'Clear', 'c4d-woo-vs' ),
    'zoom' => (isset($c4d_plugin_manager['c4d-woo-vs-global-zoom-text']) ? $c4d_plugin_manager['c4d-woo-vs-global-zoom-text'] : __( '+', 'c4d-woo-vs' )),
    'fullscreen' => __('Fullscreen', 'c4d-woo-vs'),
    'out_stock_text' => __('Out Of Stock', 'c4d-woo-vs'),
    'out_stock_type' => (isset($c4d_plugin_manager['c4d-woo-vs-single-outstock-type']) ? $c4d_plugin_manager['c4d-woo-vs-single-outstock-type'] : 'show'),
    'hide_clear_button' => (isset($c4d_plugin_manager['c4d-woo-vs-single-hide-clear-button']) ? $c4d_plugin_manager['c4d-woo-vs-single-hide-clear-button'] : 'no'),

    'nav_type' => (isset($c4d_plugin_manager['c4d-woo-vs-single-nav-type']) ? $c4d_plugin_manager['c4d-woo-vs-single-nav-type'] : 'slider'),
    'nav_display' => (isset($c4d_plugin_manager['c4d-woo-vs-single-nav-show']) ? $c4d_plugin_manager['c4d-woo-vs-single-nav-show'] : 'yes'),
    'nav_direction' => (isset($c4d_plugin_manager['c4d-woo-vs-single-nav-direction']) ? $c4d_plugin_manager['c4d-woo-vs-single-nav-direction'] : 'hoz'),
    'nav_item_show' => (isset($c4d_plugin_manager['c4d-woo-vs-single-nav-item-show']) ? $c4d_plugin_manager['c4d-woo-vs-single-nav-item-show'] : '3'),
    'nav_item_margin' => (isset($c4d_plugin_manager['c4d-woo-vs-single-nav-item-margin']) ? $c4d_plugin_manager['c4d-woo-vs-single-nav-item-margin'] : array('width' => '5px')),
    'nav_button_hide' => (isset($c4d_plugin_manager['c4d-woo-vs-single-nav-button']) ? $c4d_plugin_manager['c4d-woo-vs-single-nav-button'] : 'no'),

    'box_shape' => (isset($c4d_plugin_manager['c4d-woo-vs-single-box-shape']) ? $c4d_plugin_manager['c4d-woo-vs-single-box-shape'] : 'circle'),

    'main_gallery_replace_class' => (isset($c4d_plugin_manager['c4d-woo-vs-single-gallery-replace-class']) ? $c4d_plugin_manager['c4d-woo-vs-single-gallery-replace-class'] : '.woocommerce-product-gallery'),
    'main_gallery_variation' => (isset($c4d_plugin_manager['c4d-woo-vs-single-gallery-replace']) ? $c4d_plugin_manager['c4d-woo-vs-single-gallery-replace'] : 'yes'),
    'main_gallery_default' => (isset($c4d_plugin_manager['c4d-woo-vs-single-gallery-default']) ? $c4d_plugin_manager['c4d-woo-vs-single-gallery-default'] : 'no'),
    'main_gallery_button' => (isset($c4d_plugin_manager['c4d-woo-vs-single-gallery-button']) ? $c4d_plugin_manager['c4d-woo-vs-single-gallery-button'] : 'no'),

    'sort_attribute' => (isset($c4d_plugin_manager['c4d-woo-vs-single-sort-order']) ? $c4d_plugin_manager['c4d-woo-vs-single-sort-order'] : 'no'),
    'variation_hover_popup' => (isset($c4d_plugin_manager['c4d-woo-vs-single-variation-popup']) ? $c4d_plugin_manager['c4d-woo-vs-single-variation-popup'] : 'no'),
    'zoom_out_box' => (isset($c4d_plugin_manager['c4d-woo-vs-single-zoom-out-box']) ? $c4d_plugin_manager['c4d-woo-vs-single-zoom-out-box'] : 'no'),
    'zoom_pan_image' => (isset($c4d_plugin_manager['c4d-woo-vs-single-zoom-pan']) ? $c4d_plugin_manager['c4d-woo-vs-single-zoom-pan'] : 'no'),
    'placeholder_image' => wc_placeholder_img_src(),
    'single_responsive' => (isset($c4d_plugin_manager['c4d-woo-vs-single-responsive']) ? $c4d_plugin_manager['c4d-woo-vs-single-responsive'] : 'plugin'),
    'tooltip_mobile' => (isset($c4d_plugin_manager['c4d-woo-vs-listing-tooltip-mobile']) ? $c4d_plugin_manager['c4d-woo-vs-listing-tooltip-mobile'] : '0'),
    'run_after_ajax' => (isset($c4d_plugin_manager['c4d-woo-vs-support-ajax-request']) ? $c4d_plugin_manager['c4d-woo-vs-support-ajax-request'] : '1'),
    'ajax_match' => (isset($c4d_plugin_manager['c4d-woo-vs-support-ajax-match']) ? $c4d_plugin_manager['c4d-woo-vs-support-ajax-match'] : 'admin-ajax.php'),
    'insert_before_default_attribute' => (isset($c4d_plugin_manager['c4d-woo-vs-single-insert-position']) ? $c4d_plugin_manager['c4d-woo-vs-single-insert-position'] : '1'),
    'related_selector_item' => (isset($c4d_plugin_manager['c4d-woo-vs-related-selector-item']) ? $c4d_plugin_manager['c4d-woo-vs-related-selector-item'] : 'li.product'),
  );

  wp_localize_script( 'c4d-woo-vs-site-js', 'c4dWooWsOptions', $options );

  $colorCodes = json_decode(C4DWOOVS_COLOR_STRINGS);

  $customCss = '';
  foreach($colorCodes as $key => $color) {

    $customCss .=
    ".c4d-woo-vs-single-list-box .c4d-woo-vs-attribute_pa_color-".$key . ",".
    ".c4d-woo-vs-box-colors .c4d-woo-vs-attribute_pa_color-".$key . ",".
    ".c4d-woo-vs-single-list-box .c4d-woo-vs-attribute_colors-".$key . ",".
    ".c4d-woo-vs-single-list-box .c4d-woo-vs-attribute_color-".$key . " {".
      "background-color: " . $color . ";" .
    "}";
  }

  wp_add_inline_style( 'c4d-woo-vs-site-style', $customCss );
}

function c4d_woo_vs_load_scripts_admin($hook) {
  if (in_array($hook, array('plugins.php','post-new.php', 'post.php', 'toplevel_page_c4d-plugin-manager'))) {
    wp_enqueue_script( 'c4d-woo-vs-admin-js', C4DWOOVS_PLUGIN_URI . '/assets/admin.js' );
    wp_enqueue_style( 'c4d-woo-vs-admin-style', C4DWOOVS_PLUGIN_URI.'/assets/admin.css' );
  }
}

function c4d_woo_vs_filesystem_init() {
  $creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());
  /* initialize the API */
  if ( ! WP_Filesystem($creds) ) {
    /* any problems and we exit */
    return false;
  }
}
function c4d_woo_vs_create_list_color() {
  c4d_woo_vs_filesystem_init();
  global $wp_filesystem, $c4d_plugin_manager;
  $colorName = (isset($c4d_plugin_manager['c4d-woo-vs-color-field-name']) && $c4d_plugin_manager['c4d-woo-vs-color-field-name'] !== '' ) ? $c4d_plugin_manager['c4d-woo-vs-color-field-name'] : 'color';
  $terms = get_terms( wc_attribute_taxonomy_name(trim($colorName)), array('orderby'=>'name',  'hide_empty' => 0));
  if (!is_wp_error($terms)) {
    $filename = C4DWOOVS_UPLOAD_DIR.'colors.txt';
    $currentContent = $wp_filesystem->get_contents($filename);
    if ($currentContent !== '') {
      $currentContent = json_decode($currentContent);
      if (is_array($currentContent)) {
        $terms = array_merge($currentContent, $terms);
      }
    }
    
    if(!$wp_filesystem->is_dir(C4DWOOVS_UPLOAD_DIR) ) {
      $wp_filesystem->mkdir(C4DWOOVS_UPLOAD_DIR); 
    }
    
    $wp_filesystem->put_contents( $filename, json_encode($terms), FS_CHMOD_FILE);
  }
}

function c4d_woo_vs_section_options(){
  $opt_name = 'c4d_plugin_manager';
  $colors = array();
  $fileds = array(
    array(
        'id'       => 'c4d-woo-vs-color-field-name',
        'type'     => 'text',
        'title'    => esc_html__('Color Field Name', 'c4d-woo-vs'),
        'subtitle'    => esc_html__('Insert the name of color attribute, so plugin can create color settings', 'c4d-woo-vs'),
        'default' => 'color'
    ),
  );
  $fileColor = C4DWOOVS_UPLOAD_DIR.'colors.txt';
  if (!file_exists($fileColor)) {
    $fileColor = dirname(__FILE__).'/colors.json';
  }

  if (file_exists($fileColor)) {
    $content = file_get_contents($fileColor);
    $colors = json_decode($content);

    if (is_array($colors)) {
      foreach($colors as $color) {
        if (isset($color->slug)) {
          $fileds[] = array(
            'id'       => 'c4d-woo-vs-color-'.$color->slug,
            'type'     => 'color_rgba',
            'title'    => $color->name,
            'default'  => '',
            'output'    => array(
              'background-color' => '.c4d-woo-vs-single-list-box .c4d-woo-vs-attribute_pa_color-'.$color->slug . ', .c4d-woo-vs-box-colors .c4d-woo-vs-attribute_pa_color-'.$color->slug . ', .c4d-woo-vs-single-list-box .c4d-woo-vs-attribute_colors-'.$color->slug . ', .c4d-woo-vs-single-list-box .c4d-woo-vs-attribute_color-'.$color->slug
            )
          );
        }
      }
    }
  }

  Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Variation Swatches', 'c4d-woo-vs' ),
    'desc'             => '',
    'customizer_width' => '400px',
    'icon'             => 'el el-home',
  ));

  Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Color Defined', 'c4d-woo-vs' ),
    'id'               => 'section-c4d-woo-vs-color',
    'desc'             => '',
    'customizer_width' => '400px',
    'icon'             => '',
    'subsection'       => true,
    'fields'           => $fileds
  ));

  Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Listing Page', 'c4d-woo-vs' ),
    'id'               => 'section-c4d-woo-vs-listing',
    'desc'             => '',
    'customizer_width' => '400px',
    'icon'             => '',
    'subsection'       => true,
    'fields'           => array(
      array(
        'id'       => 'c4d-woo-vs-listing-display',
        'type'     => 'button_set',
        'title'    => esc_html__('Show On Listing Page', 'c4d-woo-vs'),
        'options' => array(
          '1' => esc_html__('Yes', 'c4d-woo-vs'),
          '0' => esc_html__('No', 'c4d-woo-vs')
         ),
        'default' => '1'
      ),
      array(
          'id'       => 'c4d-woo-vs-listing-flip-thumbnail',
          'type'     => 'button_set',
          'title'    => esc_html__('Flip Thumbnail', 'c4d-woo-vs'),
          'options' => array(
            '1' => esc_html__('Yes', 'c4d-woo-vs'),
            '0' => esc_html__('No', 'c4d-woo-vs')
           ),
          'default' => '1'
      ),
      array(
          'id'       => 'c4d-woo-vs-listing-variation-align',
          'type'     => 'button_set',
          'title'    => __('Swatch Algin', 'c4d-woo-vs'),
          'options' => array(
              'align-left' => __('Left', 'c4d-woo-vs'),
              'align-center' => __('Center', 'c4d-woo-vs'),
              'align-right' => __('Right', 'c4d-woo-vs')
           ),
          'default' => 'align-left'
      ),
      array(
          'id'       => 'c4d-woo-vs-listing-display-image',
          'type'     => 'button_set',
          'title'    => __('Display Image instead of Color', 'c4d-woo-vs'),
          'options' => array(
              '1' => __('Yes', 'c4d-woo-vs'),
              '0' => __('No', 'c4d-woo-vs')
           ),
          'default' => '1'
      ),
      array(
          'id'       => 'c4d-woo-vs-listing-view-more',
          'type'     => 'button_set',
          'title'    => __('Display View More', 'c4d-woo-vs'),
          'options' => array(
              '1' => __('Yes', 'c4d-woo-vs'),
              '0' => __('No', 'c4d-woo-vs')
           ),
          'default' => '0'
      ),
      array(
          'id'       => 'c4d-woo-vs-listing-view-more-number',
          'type'     => 'text',
          'title'    => __('View More Number', 'c4d-woo-vs'),
          'validate' => 'numeric',
          'default' => '5'
      ),
      array(
          'id'       => 'c4d-woo-vs-listing-view-more-text',
          'type'     => 'text',
          'title'    => __('View More Text', 'c4d-woo-vs'),
          'default' => 'View More'
      ),
      array(
        'id'       => 'c4d-woo-vs-listing-box-size',
        'type'     => 'dimensions',
        'units'    => array('px'),
        'title'    => __('Color/Image Box Dimensions', 'c4d-woo-vs'),
        'output'  => array(
          'width' => '.c4d-woo-vs-box-color',
          'height' => '.c4d-woo-vs-box-color'
        ),
        'default'  => array(
          'width'   => '12px',
          'height'  => '12px'
        )
      ),
      array(
        'id'             => 'c4d-woo-vs-listing-box-space',
        'type'           => 'spacing',
        'mode'           => 'margin',
        'units'          => array('px'),
        'title'          => __('Color/Image Box Margin', 'c4d-woo-vs'),
        'output'         => array('.c4d-woo-vs-box-color'),
        'default'            => array(
          'margin-top'     => '5px',
          'margin-right'   => '5px',
          'margin-bottom'  => '5px',
          'margin-left'    => '0px',
          'units'          => 'px',
        )
      ),
      array(
       'id' => 'section-start-listing-tooltip',
       'type' => 'section',
       'title' => __('Tooltip', 'c4d-woo-vs'),
       'indent' => true
      ),
      array(
        'id'       => 'c4d-woo-vs-listing-tooltip-mobile',
        'type'     => 'button_set',
        'title'    => esc_html__('Enable on Mobile', 'c4d-woo-vs'),
        'options' => array(
          '1' => esc_html__('Yes', 'c4d-woo-vs'),
          '0' => esc_html__('No', 'c4d-woo-vs')
         ),
        'default' => '0'
      ),
      array(
        'id'       => 'c4d-woo-vs-listing-tooltip-background',
        'type'     => 'color',
        'title'    => esc_html__('Tooltip Background Color', 'c4d-woo-vs'),
        'default'  => '#333',
        'transparent' => false,
        'validate' => 'color',
        'output'    => array(
          'background-color' => 'body .c4d-woo-vs-category-theme.tippy-tooltip',
          'border-top-color' => 'body .tippy-popper[x-placement^=top] .c4d-woo-vs-category-theme .tippy-arrow'
        )
      ),
      array(
        'id'       => 'c4d-woo-vs-listing-tooltip-text-color',
        'type'     => 'color',
        'title'    => esc_html__('Tooltip Text Color', 'c4d-woo-vs'),
        'default'  => '#fff',
        'transparent' => false,
        'validate' => 'color',
        'output'    => array(
          'color' => 'body .c4d-woo-vs-category-theme.tippy-tooltip, body .c4d-woo-vs-single-theme.tippy-tooltip'
        )
      ),
      array(
        'id'          => 'c4d-woo-vs-listing-tooltip-typo',
        'type'        => 'typography',
        'title'       => esc_html__('Tooltip Font Size', 'c4d-woo-vs'),
        'output'      => array('body .c4d-woo-vs-category-theme.tippy-tooltip[data-size=small]'),
        'units'       =>'px',
        'font-weight' => false,
        'text-align'  => false,
        'subsets'     => false,
        'font-family' => false,
        'font-style'  => false,
        'text-transform' => false,
        'letter-spacing' => false,
        'line-height' => false,
        'color'   => false,
        'default'     => array(
          'font-size'   => '12px',
        )
      ),
      array(
        'id'             => 'c4d-woo-vs-listing-tooltip-space',
        'type'           => 'spacing',
        'mode'           => 'padding',
        'units'          => array('px'),
        'title'          => __('Tooltip Padding', 'c4d-woo-vs'),
        'output'         => array('body .c4d-woo-vs-category-theme.tippy-tooltip[data-size=small]'),
        'default'            => array(
          'units'          => 'px'
        )
      )
    )
  ));

  Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Single Page', 'c4d-woo-vs' ),
    'id'               => 'section-c4d-woo-vs-single',
    'desc'             => '',
    'customizer_width' => '400px',
    'icon'             => '',
    'subsection'       => true,
    'fields'           => array(
      array(
        'id'       => 'c4d-woo-vs-single-gallery-default',
        'type'     => 'button_set',
        'title'    => esc_html__('Default Gallery', 'c4d-woo-vs'),
        'subtitle'    => __('Using default gallery of plugin instead theme', 'c4d-woo-vs'),
        'options' => array(
          'yes' => esc_html__('Yes', 'c4d-woo-vs'),
          'no' => esc_html__('No', 'c4d-woo-vs')
         ),
        'default' => 'no'
      ),
      array(
        'id'       => 'c4d-woo-vs-single-gallery-replace',
        'type'     => 'button_set',
        'title'    => esc_html__('Variation Gallery', 'c4d-woo-vs'),
        'subtitle'    => __('Show variation gallery when change variation', 'c4d-woo-vs'),
        'options' => array(
          'yes' => esc_html__('Yes', 'c4d-woo-vs'),
          'no' => esc_html__('No', 'c4d-woo-vs')
         ),
        'default' => 'yes'
      ),
      array(
        'id'       => 'c4d-woo-vs-single-gallery-replace-class',
        'type'     => 'text',
        'title'    => __('Main Gallery Class', 'c4d-woo-vs'),
        'subtitle'    => __('Insert selector to find main gallery to replace with gallery by plugin.', 'c4d-woo-vs'),
        'validate' => 'no_html',
        'default'  => '.woocommerce-product-gallery'
      ),
      array(
        'id'       => 'c4d-woo-vs-single-sort-order',
        'type'     => 'button_set',
        'title'    => esc_html__('Sort Order', 'c4d-woo-vs'),
        'subtitle'    => __('Sort variations by alphabetic', 'c4d-woo-vs'),
        'options' => array(
          'no' => esc_html__('No', 'c4d-woo-vs'),
          'yes' => esc_html__('Yes', 'c4d-woo-vs'),
         ),
        'default' => 'no'
      ),
      array(
        'id'       => 'c4d-woo-vs-single-insert-position',
        'type'     => 'button_set',
        'title'    => esc_html__('Render Place', 'c4d-woo-vs'),
        'subtitle'    => __('Render swatch images before or after default attribute of theme', 'c4d-woo-vs'),
        'options' => array(
          '1' => esc_html__('Before', 'c4d-woo-vs'),
          '0' => esc_html__('After', 'c4d-woo-vs'),
         ),
        'default' => '1'
      ),
      array(
        'id'       => 'c4d-woo-vs-single-outstock-type',
        'type'     => 'button_set',
        'title'    => esc_html__('Out Stock Attribute', 'c4d-woo-vs'),
        'subtitle'    => __('Set outstock status for attribute', 'c4d-woo-vs'),
        'options' => array(
          'hide' => esc_html__('Hide Attribute', 'c4d-woo-vs'),
          'show' => esc_html__('Show Out Stock Label', 'c4d-woo-vs'),
         ),
        'default' => 'show'
      ),
      array(
        'id'       => 'c4d-woo-vs-single-hide-clear-button',
        'type'     => 'button_set',
        'title'    => esc_html__('Hide Clear Button', 'c4d-woo-vs'),
        'options' => array(
          'no' => esc_html__('No', 'c4d-woo-vs'),
          'yes' => esc_html__('Yes', 'c4d-woo-vs'),
         ),
        'default' => 'no'
      ),
      array(
        'id'       => 'c4d-woo-vs-single-responsive',
        'type'     => 'button_set',
        'title'    => esc_html__('Responsive', 'c4d-woo-vs'),
        'subtitle'    => __('Revert to dropdow select box of theme or keep image labels of plugin on small screen.', 'c4d-woo-vs'),
        'options' => array(
          'plugin' => esc_html__('Plugin Labels', 'c4d-woo-vs'),
          'theme' => esc_html__('Theme Select Box', 'c4d-woo-vs'),
         ),
        'default' => 'plugin'
      ),
      array(
       'id' => 'section-start-single-section-variation-popup',
       'type' => 'section',
       'title' => __('Variation Popup', 'c4d-woo-vs'),
       'indent' => true
      ),
        array(
          'id'       => 'c4d-woo-vs-single-variation-popup',
          'type'     => 'button_set',
          'title'    => esc_html__('Image Popup', 'c4d-woo-vs'),
          'subtitle'    => __('Popup image when hover on variation', 'c4d-woo-vs'),
          'options' => array(
            'no' => esc_html__('No', 'c4d-woo-vs'),
            'yes' => esc_html__('Yes', 'c4d-woo-vs'),
           ),
          'default' => 'no'
        ),
        array(
          'id'       => 'c4d-woo-vs-single-variation-popup-size',
          'type'     => 'dimensions',
          'units'    => array('px'),
          'title'    => __('Width/Height Popup', 'c4d-woo-vs'),
          'width'    => true,
          'height'   => false,
          'output'   => '.c4d-woo-vs-single-list-box .variation-image-popup',
          'default'  => array(
              'width'   => '300'
          )
        ),
        array(
          'id'       => 'c4d-woo-vs-single-variation-popup-size-color',
          'type'     => 'color',
          'title'    => __('Box Color', 'c4d-woo-vs'),
          'default'  => '#fed700',
          'validate' => 'color',
          'transparent' => false,
          'output'    => array(
            'border-color' => '.c4d-woo-vs-single-list-box .variation-image-popup',
            'border-top-color' => '.c4d-woo-vs-single-list-box .variation-image-popup:after'
          )
        ),
      array(
       'id' => 'section-start-single-section-zoom-out-box',
       'type' => 'section',
       'title' => __('Zoom Settings', 'c4d-woo-vs'),
       'indent' => true
      ),
        array(
          'id'       => 'c4d-woo-vs-single-zoom-load-lib',
          'type'     => 'button_set',
          'title'    => esc_html__('Load Zoom Lib', 'c4d-woo-vs'),
          'subtitle'    => __('Load Zoom lib of woocommerce if your theme does not support.', 'c4d-woo-vs'),
          'options' => array(
            'no' => esc_html__('No', 'c4d-woo-vs'),
            'yes' => esc_html__('Yes', 'c4d-woo-vs'),
           ),
          'default' => 'yes'
        ),
        array(
          'id'       => 'c4d-woo-vs-global-zoom-text',
          'type'     => 'text',
          'title'    => esc_html__('Defaul Zoom Text', 'c4d-woo-vs'),
          'default'  => '+'
        ),
        array(
          'id'       => 'c4d-woo-vs-single-zoom-pan',
          'type'     => 'button_set',
          'title'    => esc_html__('Zoom & Pan', 'c4d-woo-vs'),
          'subtitle'    => __('Open image in fullscreen with pan & zoom effect', 'c4d-woo-vs'),
          'options' => array(
            'no' => esc_html__('No', 'c4d-woo-vs'),
            'yes' => esc_html__('Yes', 'c4d-woo-vs'),
           ),
          'default' => 'no'
        ),
        array(
          'id'       => 'c4d-woo-vs-single-zoom-out-box',
          'type'     => 'button_set',
          'title'    => esc_html__('Zoom Outbox', 'c4d-woo-vs'),
          'subtitle'    => __('Display zoom image out of box', 'c4d-woo-vs'),
          'options' => array(
            'no' => esc_html__('No', 'c4d-woo-vs'),
            'yes' => esc_html__('Yes', 'c4d-woo-vs'),
           ),
          'default' => 'no'
        ),
        array(
          'id'       => 'c4d-woo-vs-single-zoom-out-box-size',
          'type'     => 'dimensions',
          'units'    => array('px'),
          'title'    => __('Width/Height Zoom Out Box', 'c4d-woo-vs'),
          'width'    => true,
          'height'   => true,
          'output'   => '.c4d-woo-vs-zoom-box',
          'default'  => array(
              'width'   => '500',
              'height' => '450'
          )
        ),
        array(
          'id'       => 'c4d-woo-vs-single-zoom-out-box-color',
          'type'     => 'color',
          'title'    => __('Box Color', 'c4d-woo-vs'),
          'default'  => '#fed700',
          'validate' => 'color',
          'transparent' => false,
          'output'    => array(
            'border-color' => '.c4d-woo-vs-zoom-box',
            'border-right-color' => '.c4d-woo-vs-zoom-box:after'
          )
        ),
      array(
       'id' => 'section-start-single-button-color',
       'type' => 'section',
       'title' => __('Color/Image Button', 'c4d-woo-vs'),
       'indent' => true
      ),
      array(
        'id'          => 'c4d-woo-vs-single-label-typo',
        'type'        => 'typography',
        'title'       => esc_html__('Typo', 'c4d-woo-vs'),
        'output'      => array('.c4d-woo-vs-single-list-box label'),
        'units'       =>'px',
        'text-align'  => false,
        'subsets'     => false,
        'font-family' => false,
        'font-style'  => false,
        'text-transform' => true,
        'letter-spacing' => true,
        'line-height' => false,
        'default'     => array(
          'color'       => '#000',
          'font-size'   => '14px',
        )
      ),
      array(
        'id'       => 'c4d-woo-vs-single-box-size',
        'type'     => 'dimensions',
        'units'    => array('px'),
        'title'    => __('Desktop Dimensions', 'c4d-woo-vs'),
        'output'  => array(
          'width' => '.c4d-woo-vs-type-color .c4d-woo-vs-attribute, .c4d-woo-vs-type-image .c4d-woo-vs-attribute',
          'height' => '.c4d-woo-vs-type-color .c4d-woo-vs-attribute, .c4d-woo-vs-type-image .c4d-woo-vs-attribute'
        ),
        'default'  => array(
          'width'   => '50px',
          'height'  => '50px'
        )
      ),
      array(
        'id'       => 'c4d-woo-vs-single-box-size-mobile',
        'type'     => 'dimensions',
        'units'    => array('px'),
        'title'    => __('Mobile Dimensions', 'c4d-woo-vs'),
        'output'  => array(
          'width' => '.c4d-woo-vs-mobile .c4d-woo-vs-type-color .c4d-woo-vs-attribute, .c4d-woo-vs-mobile .c4d-woo-vs-type-image .c4d-woo-vs-attribute',
          'height' => '.c4d-woo-vs-mobile .c4d-woo-vs-type-color .c4d-woo-vs-attribute, .c4d-woo-vs-mobile .c4d-woo-vs-type-image .c4d-woo-vs-attribute'
        ),
        'default'  => array(
          'width'   => '36px',
          'height'  => '36px'
        )
      ),
      array(
        'id'             => 'c4d-woo-vs-single-box-space',
        'type'           => 'spacing',
        'mode'           => 'margin',
        'units'          => array('px'),
        'title'          => __('Margin', 'c4d-woo-vs'),
        'output'         => array('.c4d-woo-vs-type-color .c4d-woo-vs-attribute, .c4d-woo-vs-type-image .c4d-woo-vs-attribute'),
        'default'            => array(
          'margin-top'     => '8px',
          'margin-right'   => '8px',
          'margin-bottom'  => '8px',
          'margin-left'    => '8px',
          'units'          => 'px',
        )
      ),
      array(
          'id'       => 'c4d-woo-vs-single-box-border',
          'type'     => 'border',
          'title'    => __('Border', 'c4d-woo-vs'),
          'output'   => array('.c4d-woo-vs-type-color .c4d-woo-vs-attribute, .c4d-woo-vs-type-image .c4d-woo-vs-attribute'),
          'default'  => array(
              'border-color'  => '#fff',
              'border-style'  => 'solid',
              'border-top'    => '4px',
              'border-right'  => '4px',
              'border-bottom' => '4px',
              'border-left'   => '4px'
          )
      ),
      array(
        'id'       => 'c4d-woo-vs-single-box-border-hover',
          'type'     => 'border',
          'title'    => __('Border Hover', 'c4d-woo-vs'),
          'output'   => array('.c4d-woo-vs-type-color:not(.outstock) .c4d-woo-vs-attribute:hover, .c4d-woo-vs-type-image .c4d-woo-vs-attribute:hover, .c4d-woo-vs-type-color:not(.outstock) .attribute-item.active .c4d-woo-vs-attribute, .c4d-woo-vs-type-image:not(.outstock) .attribute-item.active .c4d-woo-vs-attribute'),
          'default'  => array(
              'border-color'  => '#81d742',
              'border-style'  => 'solid',
              'border-top'    => '4px',
              'border-right'  => '4px',
              'border-bottom' => '4px',
              'border-left'   => '4px'
          )
      ),
      array(
        'id'       => 'c4d-woo-vs-single-box-shape',
        'type'     => 'button_set',
        'title'    => esc_html__('Shape', 'c4d-woo-vs'),
        'options' => array(
          'circle' => esc_html__('Circle', 'c4d-woo-vs'),
          'rectangle' => esc_html__('Rectangle', 'c4d-woo-vs')
         ),
        'default' => 'circle'
      ),
      array(
       'id' => 'section-start-single-button-text',
       'type' => 'section',
       'title' => __('Text Button', 'c4d-woo-vs'),
       'indent' => true
      ),
      array(
        'id'          => 'c4d-woo-vs-single-button-text-typo',
        'type'        => 'typography',
        'title'       => esc_html__('Typo', 'c4d-woo-vs'),
        'output'      => array('.c4d-woo-vs-type-text .c4d-woo-vs-attribute'),
        'units'       =>'px',
        'text-align'  => false,
        'subsets'     => false,
        'font-family' => false,
        'font-style'  => false,
        'text-transform' => true,
        'letter-spacing' => true,
        'line-height' => false,
        'default'     => array(
          'color'       => '#000',
          'font-size'   => '12px',
        )
      ),

      array(
        'id'       => 'c4d-woo-vs-single-button-color-hover',
        'type'     => 'color',
        'title'    => esc_html__('Hover Color', 'c4d-woo-vs'),
        'default'  => '#81d742',
        'transparent' => false,
        'validate' => 'color',
        'output'    => array(
          'color' => '.c4d-woo-vs-type-text:not(.outstock) .c4d-woo-vs-attribute:hover, .c4d-woo-vs-type-text:not(.outstock) .attribute-item.active .c4d-woo-vs-attribute',
          'border-color' => '.c4d-woo-vs-type-text:not(.outstock) .c4d-woo-vs-attribute:hover, .c4d-woo-vs-type-text:not(.outstock) .attribute-item.active .c4d-woo-vs-attribute',
        )
      ),
      array(
        'id'       => 'c4d-woo-vs-single-button-background',
        'type'     => 'color',
        'title'    => esc_html__('Background Color', 'c4d-woo-vs'),
        'default'  => '#fff',
        'transparent' => false,
        'validate' => 'color',
        'output'    => array(
          'background-color' => '.c4d-woo-vs-type-text .c4d-woo-vs-attribute'
        )
      ),
      array(
        'id'       => 'c4d-woo-vs-single-button-background-hover',
        'type'     => 'color',
        'title'    => esc_html__('Background Hover Color', 'c4d-woo-vs'),
        'default'  => '#fff',
        'transparent' => false,
        'validate' => 'color',
        'output'    => array(
          'background-color' => '.c4d-woo-vs-type-text:not(.outstock) .c4d-woo-vs-attribute:hover, .c4d-woo-vs-type-text:not(.outstock) .attribute-item.active .c4d-woo-vs-attribute'
        )
      ),
      array(
          'id'       => 'c4d-woo-vs-single-button-text-border',
          'type'     => 'border',
          'title'    => __('Border', 'c4d-woo-vs'),
          'output'   => array('.c4d-woo-vs-type-text .c4d-woo-vs-attribute'),
          'default'  => array(
              'border-color'  => '#ddd',
              'border-style'  => 'solid',
              'border-top'    => '2px',
              'border-right'  => '2px',
              'border-bottom' => '2px',
              'border-left'   => '2px'
          )
      ),
      array(
        'id'       => 'c4d-woo-vs-single-button-text-border-hover',
          'type'     => 'border',
          'title'    => __('Border Hover', 'c4d-woo-vs'),
          'output'   => array('.c4d-woo-vs-type-text:not(.outstock) .c4d-woo-vs-attribute:hover, .c4d-woo-vs-type-text:not(.outstock) .attribute-item.active .c4d-woo-vs-attribute'),
          'default'  => array(
              'border-color'  => '#81d742',
              'border-style'  => 'solid',
              'border-top'    => '2px',
              'border-right'  => '2px',
              'border-bottom' => '2px',
              'border-left'   => '2px'
          )
      ),
      array(
        'id'             => 'c4d-woo-vs-single-button-text-space',
        'type'           => 'spacing',
        'mode'           => 'margin',
        'units'          => array('px'),
        'title'          => __('Margin', 'c4d-woo-vs'),
        'output'         => array('.c4d-woo-vs-type-text .c4d-woo-vs-attribute'),
        'default'            => array(
          'margin-top'     => '8px',
          'margin-right'   => '8px',
          'margin-bottom'  => '8px',
          'margin-left'    => '0px',
          'units'          => 'px',
        )
      ),
      array(
        'id'       => 'c4d-woo-vs-single-button-text-border-rounded',
        'type'     => 'button_set',
        'title'    => esc_html__('Border Rounded', 'c4d-woo-vs'),
        'options' => array(
          'yes' => esc_html__('Yes', 'c4d-woo-vs'),
          'no' => esc_html__('No', 'c4d-woo-vs')
         ),
        'default' => 'no'
      ),
      array(
        'id'       => 'c4d-woo-vs-single-button-text-width',
        'type'     => 'text',
        'title'    => __('Width', 'c4d-woo-vs'),
        'validate' => 'numeric',
        'msg'      => 'Numeric Only',
        'default'  => '0'
      ),
      array(
        'id'       => 'c4d-woo-vs-single-button-text-height',
        'type'     => 'text',
        'title'    => __('Height', 'c4d-woo-vs'),
        'validate' => 'numeric',
        'msg'      => 'Numeric Only',
        'default'  => '0'
      ),
      array(
       'id' => 'section-start-single-tooltip',
       'type' => 'section',
       'title' => __('Tooltip', 'c4d-woo-vs'),
       'indent' => true
      ),
      array(
        'id'       => 'c4d-woo-vs-single-tooltip-background',
        'type'     => 'color',
        'title'    => esc_html__('Tooltip Background Color', 'c4d-woo-vs'),
        'default'  => '#333',
        'transparent' => false,
        'validate' => 'color',
        'output'    => array(
          'background-color' => 'body .c4d-woo-vs-single-theme.tippy-tooltip',
          'border-top-color' => 'body .tippy-popper[x-placement^=top] .c4d-woo-vs-single-theme .tippy-arrow'
        )
      ),
      array(
        'id'       => 'c4d-woo-vs-single-tooltip-text-color',
        'type'     => 'color',
        'title'    => esc_html__('Tooltip Text Color', 'c4d-woo-vs'),
        'default'  => '#fff',
        'transparent' => false,
        'validate' => 'color',
        'output'    => array(
          'color' => 'body .c4d-woo-vs-single-theme .tippy-tooltip'
        )
      ),
      array(
        'id'          => 'c4d-woo-vs-single-tooltip-typo',
        'type'        => 'typography',
        'title'       => esc_html__('Tooltip Font Size', 'c4d-woo-vs'),
        'output'      => array('body .c4d-woo-vs-single-theme.tippy-tooltip[data-size=small]'),
        'units'       =>'px',
        'font-weight' => false,
        'text-align'  => false,
        'subsets'     => false,
        'font-family' => false,
        'font-style'  => false,
        'text-transform' => false,
        'letter-spacing' => false,
        'line-height' => false,
        'color'   => false,
        'default'     => array(
          'font-size'   => '12px',
        )
      ),
      array(
        'id'             => 'c4d-woo-vs-single-tooltip-space',
        'type'           => 'spacing',
        'mode'           => 'padding',
        'units'          => array('px'),
        'title'          => __('Tooltip Padding', 'c4d-woo-vs'),
        'output'         => array('body .c4d-woo-vs-single-theme.tippy-tooltip[data-size=small]'),
        'default'            => array(
          'units'          => 'px'
        )
      ),
      array(
       'id' => 'section-start-single-gallery-slider',
       'type' => 'section',
       'title' => __('Gallery Slider', 'c4d-woo-vs'),
       'indent' => true
      ),
        array(
          'id'       => 'c4d-woo-vs-single-gallery-button',
          'type'     => 'button_set',
          'title'    => esc_html__('Hide Left/Right Button', 'c4d-woo-vs'),
          'options' => array(
            'yes' => esc_html__('Yes', 'c4d-woo-vs'),
            'no' => esc_html__('No', 'c4d-woo-vs')
           ),
          'default' => 'no'
        ),
      array(
       'id' => 'section-start-single-nav',
       'type' => 'section',
       'title' => __('Gallery NAV', 'c4d-woo-vs'),
       'indent' => true
      ),
        array(
          'id'       => 'c4d-woo-vs-single-nav-type',
          'type'     => 'button_set',
          'title'    => esc_html__('Nav Type', 'c4d-woo-vs'),
          'options' => array(
            'slider' => esc_html__('Slider', 'c4d-woo-vs'),
            'grid' => esc_html__('Grid', 'c4d-woo-vs')
           ),
          'default' => 'slider'
        ),
        array(
          'id'       => 'c4d-woo-vs-single-nav-width',
          'type'     => 'dimensions',
          'title'    => esc_html__('Item Width', 'c4d-woo-vs'),
          'width' => true,
          'height' => false,
          'required' => array('c4d-woo-vs-single-nav-type','equals','slider'),
          'output' => array('.c4d-woo-vs-nav-grid .c4d-woo-vs-gallery .c4d-woo-vs-nav .item-slide'),
          'default'  => array(
            'width'   => '80'
          )
        ),
        array(
          'id'       => 'c4d-woo-vs-single-nav-show',
          'type'     => 'button_set',
          'title'    => esc_html__('Nav Display', 'c4d-woo-vs'),
          'options' => array(
            'yes' => esc_html__('Yes', 'c4d-woo-vs'),
            'no' => esc_html__('No', 'c4d-woo-vs')
           ),
          'default' => 'yes'
        ),
        array(
          'id'       => 'c4d-woo-vs-single-nav-button',
          'type'     => 'button_set',
          'title'    => esc_html__('Hide Left/Right Button', 'c4d-woo-vs'),
          'options' => array(
            'yes' => esc_html__('Yes', 'c4d-woo-vs'),
            'no' => esc_html__('No', 'c4d-woo-vs')
           ),
          'default' => 'no'
        ),
        array(
          'id'       => 'c4d-woo-vs-single-nav-direction',
          'type'     => 'button_set',
          'title'    => esc_html__('Nav Direction', 'c4d-woo-vs'),
          'options' => array(
            'vertical' => esc_html__('Vertical', 'c4d-woo-vs'),
            'hoz' => esc_html__('Horizontal', 'c4d-woo-vs')
           ),
          'required' => array('c4d-woo-vs-single-nav-type','equals','slider'),
          'default' => 'hoz'
        ),
        array(
          'id'       => 'c4d-woo-vs-single-nav-item-show',
          'type'     => 'button_set',
          'title'    => __('Nav Item Show', 'c4d-woo-vs'),
          'options' => array(
              '1' => esc_html__('1', 'c4d-woo-vs'),
              '2' => esc_html__('2', 'c4d-woo-vs'),
              '3' => esc_html__('3', 'c4d-woo-vs'),
              '4' => esc_html__('4', 'c4d-woo-vs'),
              '5' => esc_html__('5', 'c4d-woo-vs'),
              '6' => esc_html__('6', 'c4d-woo-vs')
           ),
          'required' => array('c4d-woo-vs-single-nav-type','equals','slider'),
          'default' => '3'
        ),
        array(
          'id'       => 'c4d-woo-vs-single-nav-item-margin',
          'type'     => 'dimensions',
          'units'    => array('px'),
          'title'    => __('Nav Item Margin', 'c4d-woo-vs'),
          'height' => false,
          'required' => array('c4d-woo-vs-single-nav-type','equals','slider'),
          'default'  => array(
            'width'   => '10px'
          )
        )
    )
  ));

  Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Cart', 'c4d-woo-vs' ),
    'id'               => 'c4d-woo-vs-cart',
    'desc'             => '',
    'customizer_width' => '400px',
    'icon'             => '',
    'subsection'       => true,
    'fields'           => array(
      array(
        'id'       => 'c4d-woo-vs-cart-replace-variation-image',
        'type'     => 'button_set',
        'title'    => esc_html__('Show Variation Image', 'c4d-woo-vs'),
        'options' => array(
          'yes' => esc_html__('Yes', 'c4d-woo-vs'),
          'no' => esc_html__('No', 'c4d-woo-vs')
         ),
        'default' => 'yes'
      )
    )
  ));

  Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Multi Order', 'c4d-woo-vs' ),
    'id'               => 'c4d-woo-vs-multi-order',
    'desc'             => '',
    'customizer_width' => '400px',
    'icon'             => '',
    'subsection'       => true,
    'fields'           => array(
      array(
        'id'          => 'c4d-woo-vs-multi-order-text-typo',
        'type'        => 'typography',
        'title'       => esc_html__('Input Typo', 'c4d-woo-vs'),
        'output'      => array('.c4d-woo-vs-single-list-box .multi-field'),
        'units'       =>'px',
        'text-align'  => false,
        'subsets'     => false,
        'font-family' => false,
        'font-style'  => false,
        'text-transform' => true,
        'letter-spacing' => true,
        'line-height' => false,
        'default'     => array(
          'color'       => '#000',
          'font-size'   => '12px',
        )
      ),

      array(
        'id'       => 'c4d-woo-vs-multi-order-color-hover',
        'type'     => 'color',
        'title'    => esc_html__('Active Color', 'c4d-woo-vs'),
        'default'  => '#81d742',
        'transparent' => false,
        'validate' => 'color',
        'output'    => array(
          'color' => '.c4d-woo-vs-single-list-box .attribute-item.active .multi-field input',
          'border-color' => '.c4d-woo-vs-single-list-box .attribute-item.active .multi-field'
        )
      ),
      array(
        'id'       => 'c4d-woo-vs-multi-order-background',
        'type'     => 'color',
        'title'    => esc_html__('Background Color', 'c4d-woo-vs'),
        'default'  => '#fff',
        'transparent' => false,
        'validate' => 'color',
        'output'    => array(
          'background-color' => '.c4d-woo-vs-single-list-box .multi-field, .c4d-woo-vs-single-list-box .multi-field input'
        )
      ),
      array(
        'id'       => 'c4d-woo-vs-multi-order-background-hover',
        'type'     => 'color',
        'title'    => esc_html__('Background Active Color', 'c4d-woo-vs'),
        'default'  => '#fff',
        'transparent' => false,
        'validate' => 'color',
        'output'    => array(
          'background-color' => '.c4d-woo-vs-single-list-box .attribute-item.active .multi-field, .c4d-woo-vs-single-list-box .attribute-item.active .multi-field input'
        )
      ),
      array(
          'id'       => 'c4d-woo-vs-multi-order-border',
          'type'     => 'border',
          'title'    => __('Border', 'c4d-woo-vs'),
          'output'   => array('.c4d-woo-vs-single-list-box .multi-field'),
          'default'  => array(
              'border-color'  => '#ddd',
              'border-style'  => 'solid',
              'border-top'    => '2px',
              'border-right'  => '2px',
              'border-bottom' => '2px',
              'border-left'   => '2px'
          )
      ),
      array(
        'id'       => 'c4d-woo-vs-multi-order-border-hover',
          'type'     => 'border',
          'title'    => __('Border Active', 'c4d-woo-vs'),
          'output'   => array('.c4d-woo-vs-single-list-box .attribute-item.active .multi-field'),
          'default'  => array(
              'border-color'  => '#81d742',
              'border-style'  => 'solid',
              'border-top'    => '2px',
              'border-right'  => '2px',
              'border-bottom' => '2px',
              'border-left'   => '2px'
          )
      ),
      array(
        'id'             => 'c4d-woo-vs-multi-order-space',
        'type'           => 'spacing',
        'mode'           => 'margin',
        'units'          => array('px'),
        'title'          => __('Margin', 'c4d-woo-vs'),
        'output'         => array('.c4d-woo-vs-single-list-box .multi-field'),
        'default'            => array(
          'margin-top'     => '8px',
          'margin-right'   => '8px',
          'margin-bottom'  => '8px',
          'margin-left'    => '0px',
          'units'          => 'px',
        )
      ),
      array(
        'id'       => 'c4d-woo-vs-multi-order-border-rounded',
        'type'     => 'button_set',
        'title'    => esc_html__('Border Rounded', 'c4d-woo-vs'),
        'options' => array(
          'yes' => esc_html__('Yes', 'c4d-woo-vs'),
          'no' => esc_html__('No', 'c4d-woo-vs')
         ),
        'default' => 'no'
      ),
      array(
        'id'       => 'c4d-woo-vs-multi-order-width',
        'type'     => 'text',
        'title'    => __('Width', 'c4d-woo-vs'),
        'validate' => 'numeric',
        'msg'      => 'Numeric Only',
        'default'  => '0'
      ),
      array(
        'id'       => 'c4d-woo-vs-multi-order-height',
        'type'     => 'text',
        'title'    => __('Height', 'c4d-woo-vs'),
        'validate' => 'numeric',
        'msg'      => 'Numeric Only',
        'default'  => '0'
      )
    )
  ));

  Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Related Products', 'c4d-woo-vs' ),
    'id'               => 'c4d-woo-vs-related',
    'desc'             => '',
    'customizer_width' => '400px',
    'icon'             => '',
    'subsection'       => true,
    'fields'           => array(
      array(
        'id'          => 'c4d-woo-vs-related-selector-item',
        'type'     => 'text',
        'title'    => esc_html__('Item Selector', 'c4d-woo-vs'),
        'subtitle'    => esc_html__('Selector to find wrapper of item to insert swatches box', 'c4d-woo-vs'),
        'default' => 'li.product'
      )
    )
  ));

  Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Support Plugins', 'c4d-woo-vs' ),
    'id'               => 'c4d-woo-vs-support',
    'desc'             => '',
    'customizer_width' => '400px',
    'icon'             => '',
    'subsection'       => true,
    'fields'           => array(
      array(
        'id'          => 'c4d-woo-vs-support-ajax-request',
        'type'     => 'button_set',
        'title'    => esc_html__('Run After Ajax Request', 'c4d-woo-vs'),
        'options' => array(
          '1' => esc_html__('Yes', 'c4d-woo-vs'),
          '0' => esc_html__('No', 'c4d-woo-vs')
         ),
        'default' => '1'
      ),
      array(
        'id'          => 'c4d-woo-vs-support-ajax-match',
        'type'     => 'text',
        'title'    => esc_html__('Ajax Match Pattern', 'c4d-woo-vs'),
        'default' => 'admin-ajax.php'
      )
    )
  ));

  Redux::setSection( $opt_name, array(
    'title'            => esc_html__( 'Load JS/CSS', 'c4d-woo-vs' ),
    'id'               => 'c4d-woo-vs-load',
    'desc'             => '',
    'customizer_width' => '400px',
    'icon'             => '',
    'subsection'       => true,
    'fields'           => array(
      array(
        'id'          => 'c4d-woo-vs-load-slick-js',
        'type'     => 'button_set',
        'title'    => esc_html__('Load Slick JS', 'c4d-woo-vs'),
        'options' => array(
          '1' => esc_html__('Yes', 'c4d-woo-vs'),
          '0' => esc_html__('No', 'c4d-woo-vs')
         ),
        'default' => '1'
      )
    )
  ));

}
