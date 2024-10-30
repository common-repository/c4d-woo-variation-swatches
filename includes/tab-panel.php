<?php
/**
 * Bundle product options.
 *
 * @package WooCommerce/admin
 */

defined( 'ABSPATH' ) || exit;
global $post, $thepostid, $product_object;
$swatches = get_post_meta( $product_object->get_id(), 'c4d_woo_vs', true );
$fieldName = 'c4d_woo_vs';
$attributes = $product_object->get_attributes();
?>
<div id="c4d-woo-vs-tab-panel" class="panel woocommerce_options_panel hidden">
	<?php
		$customAttributeTitle = false;
		if (count($attributes) < 1) {
			echo '<div class="c4d-woo-vs-notice">' . esc_html__('Please go to Attributes tab and add attribute first', 'c4d-woo-vs') . '</div>';
		}
	?>
	<?php foreach ($attributes as $key => $attribute): ?>
		<?php
			if(strrpos($key, 'pa_') === 0) continue;
			$customAttributeTitle = true;
		?>
	<?php endforeach; ?>

	<?php if ($customAttributeTitle): ?>
		<h3 class="c4d-woo-vs-custom-att-title"><?php echo esc_html__('Custom Attribute Setting', 'c4d-woo-vs'); ?></h3>
	<?php endif; ?>

	<?php foreach ($attributes as $key => $attribute): ?>
		<?php
			if(strrpos($key, 'pa_') === 0) continue;
			$attDatas = c4d_woo_vs_get_attribute($key);
			$fileName = $fieldName.'['.$key.']';
			$options = array(
				array('text' => esc_html__('Text', 'c4d-woo-vs'), 'value' => 'text'),
				array('text' => esc_html__('Color', 'c4d-woo-vs'), 'value' => 'color'),
				array('text' => esc_html__('Image', 'c4d-woo-vs'), 'value' => 'image')
			);
		?>
		<div class="options_group">
			<p class="form-field">
				<label for="grouped_products"><strong><?php echo str_replace('pa_', '', $attribute->get_name()); ?></strong></label>
				<select name="<?php echo $fileName.'[type]'; ?>">
					<?php foreach($options as $option): ?>
						<?php
							$selected = isset($attDatas->attribute_type) ?  selected( $attDatas->attribute_type, $option['value'], false) : '';
							$selected = isset($swatches[$key]) ?  selected( $swatches[$key]['type'], $option['value'], false) : $selected;
						?>
						<option value="<?php echo $option['value']; ?>" <?php echo $selected ?>><?php echo $option['text'] ?></option>
					<?php endforeach;?>
				</select>
			</p>
		</div>
	<?php endforeach; ?>

	<h3 class="c4d-woo-vs-custom-att-title"><?php echo esc_html__('Multi Oders Settings', 'c4d-woo-vs'); ?></h3>
	<div class="options_group">
		<p class="form-field">
		<!-- <p class="form-field"> -->
			<label for="grouped_products"><strong><?php echo esc_html__('Select Attribute', 'c4d-woo-vs'); ?></strong></label>
			<select name="<?php echo $fieldName.'[multi_order]'; ?>">
				<option value="-1">None</option>
				<?php foreach ($attributes as $key => $attribute): ?>
					<?php
						$value = strtolower($attribute->get_name());
						$selected = isset($swatches['multi_order']) ? selected( $swatches['multi_order'], $value, false) : '';
					?>
					<option class="option" value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>><?php echo str_replace('pa_', '', $value); ?></option>
				<?php endforeach;?>
			</select>
		</p>
	</div>
</div>
