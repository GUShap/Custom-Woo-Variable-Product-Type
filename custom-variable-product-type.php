<?php

/* Prodcut Type */
function register_custom_product_type()
{
    class WC_Product_Custom_Product extends WC_Product_Variable
    {

        public function __construct($product)
        {
            parent::__construct($product);
        }

        public function get_type()
        {
            return 'custom_product';
        }
    }
}
add_action('init', 'register_custom_product_type');

function custom_product_class($class_name, $product_type)
{
    if ($product_type == 'custom_product') {
        $class_name = 'WC_Product_Custom_Product';
    }
    return $class_name;
}
add_filter('woocommerce_product_class', 'custom_product_class', 10, 2);

function add_custom_product_type_selector($types)
{
    $types['custom_product'] = __('Attributed Product', 'woocommerce');
    return $types;
}

add_filter('product_type_selector', 'add_custom_product_type_selector');

function custom_product_add_to_cart_handler($handler, $adding_to_cart)
{
    if ($handler == 'custom_product') {
        $handler = 'variable';
    }
    return $handler;
}
add_filter('woocommerce_add_to_cart_handler', 'custom_product_add_to_cart_handler', 10, 2);

function custom_product_add_to_cart()
{
    $product_id = get_the_ID();
    $product = wc_get_product($product_id);
    $available_variations = $product->get_available_variations();
    $attributes_obj = $product->get_attributes();
    $attributes = [];

    foreach ($attributes_obj as $attr_key => $attr_obj) {
        $terms_ids = $attr_obj->get_options();
        $terms = [];

        foreach ($terms_ids as $term_id) {
            $term = get_term($term_id);
            $terms[] = $term->slug;
        }
        $attributes[$attr_key] = $terms;
    }
    $template_path = ADD_TO_CART_DIR . 'woocommerce/single-product/add-to-cart/';
    $args = [
        'attributes' => $attributes,
        'available_variations' => $available_variations,
    ];
    wc_get_template('variable.php', $args, '', $template_path);
}
add_action('woocommerce_custom_product_add_to_cart', 'custom_product_add_to_cart');

function add_custom_product_store_data($stores)
{
    $stores['product-custom_product'] = 'WC_Product_Variable_Data_Store_CPT';
    return $stores;
}
add_filter('woocommerce_data_stores', 'add_custom_product_store_data', 10, 1);

function custom_product_js()
{
    ?>
    <script type='text/javascript'>
        jQuery(document).ready(function () {
            jQuery('.options_group.show_if_simple').addClass('show_if_custom_product').show();
            jQuery('.options_group.show_if_variable').addClass('show_if_custom_product').show();
            jQuery('.show_if_custom_product').show();
            jQuery('.enable_variation').addClass('show_if_custom_product').show();

        });
    </script>
    <?php
}
add_action('admin_footer', 'custom_product_js');

function add_custom_product_tabs($tabs)
{
    global $post;
    if (!$post) {
        return $tabs;
    }
    $product = wc_get_product($post->ID);
    if (!$product) {
        return $tabs;
    }
    $product_type = $product->get_type();
    if ($product_type !== 'custom_product' && $product_type !== 'variable') {
        return $tabs;
    }
    foreach ($tabs as $tab_key => $tab_data) {
        $is_classes_array = is_array($tab_data['class']);
        $is_visible_in_vaiable_product = $is_classes_array && in_array('show_if_variable', $tab_data['class']);
        $is_visible_in_custom_product = $is_classes_array && in_array('show_if_custom_product', $tab_data['class']);

        if( $is_visible_in_vaiable_product && !$is_visible_in_custom_product ){
            $tabs[$tab_key]['class'][] = 'show_if_custom_product';
        }

        if(!$is_classes_array){
            $current_value = $tab_data['class'];
            $tabs[$tab_key]['class'] = [$current_value];
            $tabs[$tab_key]['class'][] = 'show_if_custom_product';
        }

        if(empty($is_classes_array)){
            $tabs[$tab_key]['class'] = ['show_if_custom_product'];
        }
  
    }
    return $tabs;
}
add_filter('woocommerce_product_data_tabs', 'add_custom_product_tabs');
/********/
