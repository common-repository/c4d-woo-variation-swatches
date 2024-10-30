<?php
add_action( 'add_meta_boxes', 'c4d_woo_vs_add_meta_boxes', 30 );
add_action( 'woocommerce_process_product_meta', 'c4d_woo_vs_save_meta_boxes', 20, 2 );
add_action( 'woocommerce_after_add_attribute_fields', 'c4d_woo_vs_after_add_attribute_fields' );
add_action( 'woocommerce_after_edit_attribute_fields', 'c4d_woo_vs_after_edit_attribute_fields' );
add_action( 'woocommerce_attribute_added', 'c4d_woo_vs_attribute_added', 10, 2 );
add_action( 'woocommerce_attribute_updated', 'c4d_woo_vs_attribute_updated', 10, 3 );

function c4d_woo_vs_update_attribute($id, $data, $post) {
  $options = get_option('c4d_woo_vs_attributes');
  $options = is_array($options) ? $options : array();
  if (isset($post['c4d_woo_vs_type'])) {
    $data['c4d_woo_vs_type'] = esc_sql($post['c4d_woo_vs_type']);
    $options[$data['attribute_name']] = $data;
    update_option('c4d_woo_vs_attributes', $options);
  }
}

function c4d_woo_vs_attribute_updated($id, $data, $old) {
  c4d_woo_vs_update_attribute($id, $data, $_POST);
}

function c4d_woo_vs_attribute_added($id, $data) {
  c4d_woo_vs_update_attribute($id, $data, $_POST);
}

function c4d_woo_vs_get_attribute($attr) {
  global $wpdb;
  $attr = esc_sql($attr);
  $attr = substr( $attr, 3 );
  $attr = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = '$attr'" );

  return $attr;
}

function c4d_woo_vs_add_meta_boxes($post_type) {
  $post_types = array('product');     //limit meta box to certain post types
  global $post;
  $product = wc_get_product( $post->ID );

  if ( in_array( $post_type, $post_types ) && ($product->get_type() == 'variable' ) ) {
    add_meta_box(
        'c4d-woo-vs-color',
        __( 'Swatch Images', 'c4d-woo-vs' ),
        'c4d_woo_vs_color_form',
        'product',
        'side',
        'low'
    );
  }
}

function c4d_woo_vs_save_meta_boxes( $post_id, $post ) {
  $attachment_ids = isset( $_POST['c4d_woo_vs_product_image_gallery_values'] ) ? wc_clean( $_POST['c4d_woo_vs_product_image_gallery_values'] ) : array();
  $attachment_images = isset( $_POST['c4d_woo_vs_product_image_gallery_images'] ) ? wc_clean( $_POST['c4d_woo_vs_product_image_gallery_images'] ) : array();
  $tax = isset( $_POST['c4d_woo_vs_product_image_gallery_tax'] ) ? wc_clean( $_POST['c4d_woo_vs_product_image_gallery_tax'] ) : array();

  update_post_meta( $post_id, 'c4d_woo_vs_product_image_gallery_tax', json_encode($tax) );
  update_post_meta( $post_id, 'c4d_woo_vs_product_image_gallery_values', json_encode($attachment_ids) );
  update_post_meta( $post_id, 'c4d_woo_vs_product_image_gallery_images', json_encode($attachment_images) );
}

function c4d_woo_vs_color_form( $post ) {
  // Add nonce for security and authentication.
  wp_nonce_field( 'c4d_woo_vs_action', 'c4d_woo_vs_nonce' );

  $product = wc_get_product($post->ID);

  $atts = $product->get_attributes();
  $html = '<div class="c4d-woo-vs-box-colors">';
  $product_image_galleries = get_post_meta( $post->ID, 'c4d_woo_vs_product_image_gallery_values', true );
  $product_image_images = get_post_meta( $post->ID, 'c4d_woo_vs_product_image_gallery_images', true );


  if ($product_image_galleries) {
    $product_image_galleries = json_decode($product_image_galleries);
    $product_image_images = json_decode($product_image_images);
  }

  foreach($atts as $att) {
    $attId = $att->get_id();
    $attKey = sanitize_title($att->get_name());
    $attDatas = $att->get_data();

    //if (in_array($type, array('select', 'text', 'image', 'color'))) {
      $terms = $att->get_terms();

      if (!$terms) {
        $terms = array();
        if (isset($attDatas['options'])) {
          foreach ($attDatas['options'] as $option)  {
            $obj = new stdClass();
            $obj->name = $option;
            $obj->slug = sanitize_title($option);
            $terms[] = $obj;
          }
        }
      }

      foreach ($terms as $key => $term) { ?>
        <div class="c4d-woo-vs-product-images-container">
        <h3><?php echo $term->name; ?></h3>
        <ul class="product_images">
          <?php
            $product_image_gallery = isset($product_image_galleries->{$term->slug}) ? $product_image_galleries->{$term->slug} : '';
            $product_image_image = isset($product_image_images->{$term->slug}) ? $product_image_images->{$term->slug} : '';
            $attachments         = array_filter( explode( ',', $product_image_gallery ) );

            if ( ! empty( $attachments ) ) {
              foreach ( $attachments as $attachment_id ) {
                $attachment = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
                // if attachment is empty skip
                if ( empty( $attachment ) ) {
                  continue;
                }

                echo '<li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '" data-attachment_image="' . esc_attr( $attachment[0] ) . '">
                  <img src="' . $attachment[0] . '"/>
                  <ul class="actions">
                    <li><a href="#" class="delete">x</a></li>
                  </ul>
                </li>';
              }
            }
          ?>
        </ul>

        <input type="hidden" name="c4d_woo_vs_product_image_gallery_tax[<?php echo $term->slug; ?>]" value="<?php echo @$term->taxonomy; ?>" />
        <input type="hidden" class="c4d_woo_vs_product_image_gallery_values" name="c4d_woo_vs_product_image_gallery_values[<?php echo $term->slug; ?>]" value="<?php echo esc_attr( $product_image_gallery ); ?>" />
        <input type="hidden" class="c4d_woo_vs_product_image_gallery_images" name="c4d_woo_vs_product_image_gallery_images[<?php echo $term->slug; ?>]" value="<?php echo esc_attr( $product_image_image ); ?>" />
        <p class="c4d-woo-vs-add-product-images hide-if-no-js">
          <a href="#" data-choose="<?php esc_attr_e( 'Add images to product gallery', 'c4d-woo-vs' ); ?>" data-update="<?php esc_attr_e( 'Add to gallery', 'c4d-woo-vs' ); ?>" data-delete="<?php esc_attr_e( 'Delete image', 'c4d-woo-vs' ); ?>" data-text="<?php esc_attr_e( 'Delete', 'c4d-woo-vs' ); ?>"><?php _e( 'Add product gallery images', 'c4d-woo-vs' ); ?></a>
        </p>
      </div>

      <?php }
    //}
  }
}

function c4d_woo_vs_after_add_attribute_fields(){
  ?>
  <div class="form-field">
    <label for="c4d_woo_vs_attribute_type"><?php esc_html_e( 'Type', 'c4d-woo-vs' ); ?></label>
    <select name="c4d_woo_vs_type" id="c4d_woo_vs_attribute_type">
      <option value="select"><?php esc_html_e( 'Select', 'c4d-woo-vs' ); ?></option>
      <option value="text"><?php esc_html_e( 'Text', 'c4d-woo-vs' ); ?></option>
      <option value="color"><?php esc_html_e( 'Color', 'c4d-woo-vs' ); ?></option>
      <option value="image"><?php esc_html_e( 'Image', 'c4d-woo-vs' ); ?></option>
    </select>
  </div>
  <?php
}

function c4d_woo_vs_after_edit_attribute_fields() {
  global $wpdb;
  $options = get_option('c4d_woo_vs_attributes');
  $edit = absint( $_GET['edit'] );
  $attribute_to_edit = $wpdb->get_row( 'SELECT attribute_type, attribute_label, attribute_name, attribute_orderby, attribute_public FROM ' . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_id = '$edit'" );
  $selected = 'select';
  if ($attribute_to_edit && isset($options[$attribute_to_edit->attribute_name])) {
    $selected = $options[$attribute_to_edit->attribute_name]['c4d_woo_vs_type'];
  }
  ?>
  <tr class="form-field form-required">
    <th scope="row" valign="top">
      <label for="c4d_woo_vs_attribute_type"><?php esc_html_e( 'Type', 'c4d-woo-vs' ); ?></label>
    </th>
    <td>
      <select name="c4d_woo_vs_type" id="c4d_woo_vs_attribute_type">
        <option value="select" <?php selected( $selected, 'select' ); ?>><?php esc_html_e( 'Select', 'c4d-woo-vs' ); ?></option>
        <option value="text" <?php selected( $selected, 'text' ); ?>><?php esc_html_e( 'Text', 'c4d-woo-vs' ); ?></option>
        <option value="color" <?php selected( $selected, 'color' ); ?>><?php esc_html_e( 'Color', 'c4d-woo-vs' ); ?></option>
        <option value="image" <?php selected( $selected, 'image' ); ?>><?php esc_html_e( 'Image', 'c4d-woo-vs' ); ?></option>
      </select>

    </td>
  </tr>
  <?php
}
