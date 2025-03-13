<?php

/**
 * Plugin Name:       WP My Product Webspark
 * Version:           0.1
 * Author:            Vlad Mykhatilo
 * Author URI:        https://www.linkedin.com/in/vlad-mykhatilo/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpmyproductwebspark
 * Requires Plugins:  woocommerce
 */

if (!defined('ABSPATH')) exit;

require_once dirname(__FILE__) . '/wepspark-add-product-form.php';
require_once dirname(__FILE__) . '/webspark-my-products-table.php';
require_once dirname(__FILE__) . '/webspark-product.php';


class Webspark_My_Product
{
    function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'webspark_activate']);
        register_deactivation_hook(__FILE__, [$this, 'webspark_deactivate']);

        add_action('init', [$this, 'register_new_item_endpoint']);

        add_filter('woocommerce_get_query_vars', [$this, 'new_item_query_vars']);
        add_filter('ajax_query_attachments_args', [$this, 'filter_media']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_filter('woocommerce_email_classes', [$this, 'register_email'], 10, 1);
        add_filter('woocommerce_account_menu_items', [$this, 'add_more_menu_items'], 10, 2);

        add_action('woocommerce_account_add-product_endpoint', [$this, 'content_for_add_product_page']);
        add_action('woocommerce_account_my-products_endpoint', [$this, 'content_for_my_products_page']);

        add_filter('woocommerce_endpoint_add-product_title', [$this, 'title_for_add_product_page']);
        add_filter('woocommerce_endpoint_my-products_title', [$this, 'title_for_my_products_page']);


        add_action('template_redirect', [$this, 'save_product']);
        add_action('template_redirect', [$this, 'update_product']);
        add_action('template_redirect', [$this, 'delete_product']);

        // add_action('woocommerce_email')


        add_action('webspark_product_updated', [$this, 'trigger_email'], 10, 2);
    }

    function webspark_activate()
    {
        $this->register_new_item_endpoint();
        flush_rewrite_rules();
    }

    function webspark_deactivate()
    {
        flush_rewrite_rules();
    }

    function register_new_item_endpoint()
    {
        add_rewrite_endpoint('add-product', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('my-products', EP_ROOT | EP_PAGES);
    }

    function enqueue_scripts()
    {
        wp_enqueue_style(
            'webspark-frontend',
            plugins_url('/', __FILE__) . 'css/frontend.css',
            [],
            '1.0.0'
        );

        wp_enqueue_media();
        wp_enqueue_script(
            'webspark-frontend',
            plugins_url('/', __FILE__) . 'js/frontend.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }

    function add_more_menu_items($items, $endpoints)
    {
        $endpoints['add-product'] = 'add-product';
        $endpoints['my-products'] = 'my-products';

        $customer_logout = $items['customer-logout'];
        unset($items['customer-logout']);

        $items['add-product'] = __('Add Product', 'wpmyproductwebspark');
        $items['my-products'] = __('My Products', 'wpmyproductwebspark');
        $items['customer-logout'] = $customer_logout;

        return $items;
    }



    function new_item_query_vars($vars)
    {
        $vars['add-product'] = 'add-product';
        $vars['my-products'] = 'my-products';

        return $vars;
    }

    function filter_media($query)
    {
        if (! current_user_can('manage_options')) {
            $query['author'] = get_current_user_id();
        }

        return $query;
    }

    function content_for_add_product_page()
    {
        $product_id = $_GET['id'] ?? null;
        $form = new Webspark_Add_Product_Form($product_id);
        $form->render_form();
    }

    function content_for_my_products_page()
    {
        $products = new Webspark_My_Products_Table();
        $products->render_table();
    }

    function title_for_add_product_page()
    {
        return __('Add Product', 'wpmyproductwebspark');
    }

    function title_for_my_products_page()
    {
        return __('My Products', 'wpmyproductwebspark');
    }

    function save_product()
    {
        $nonce_value = wc_get_var($_REQUEST['save_product_form-nonce'], wc_get_var($_REQUEST['_wpnonce'], ''));

        if (! wp_verify_nonce($nonce_value, 'save_product_form')) {
            return;
        }

        if (empty($_POST['action']) || 'save_product_form' !== $_POST['action']) {
            return;
        }

        $this->create_update_product();
    }

    function update_product()
    {
        $nonce_value = wc_get_var($_REQUEST['update_product_form-nonce'], wc_get_var($_REQUEST['_wpnonce'], ''));

        if (! wp_verify_nonce($nonce_value, 'update_product_form')) {
            return;
        }

        if (empty($_POST['action']) || 'update_product_form' !== $_POST['action']) {
            return;
        }

        $this->create_update_product();
    }

    function delete_product()
    {
        $nonce_value = wc_get_var($_REQUEST['webspark_delete_product-nonce'], wc_get_var($_REQUEST['_wpnonce'], ''));

        if (! wp_verify_nonce($nonce_value, 'webspark_delete_product')) {
            return;
        }

        if (empty($_POST['action']) || 'webspark_delete_product' !== $_POST['action']) {
            return;
        }

        wc_nocache_headers();

        $user_id = get_current_user_id();

        if ($user_id <= 0) {
            return;
        }


        $product_id = $_POST['product_id'] ?? null;

        if (!$product_id) {
            return;
        }

        $product = wc_get_product($product_id);
        $product->delete();
    }


    function create_update_product()
    {
        wc_nocache_headers();

        $user_id = get_current_user_id();

        if ($user_id <= 0) {
            return;
        }

        $title = $_POST['product_title'] ?? '';
        $price = $_POST['product_price'] ?? '';
        $qty = $_POST['stock_qty'] ?? '';
        $desc = $_POST['product_description'] ?? '';
        $media = $_POST['media_ids'] ?? '';

        if (!$title && !$price && !$qty) {
            return;
        }

        $product_args = [
            'post_status' => 'pending',
            'post_type' => 'product',
            'post_title' => $title,
            'post_content' => $desc,
        ];

        $product_id = $_POST['product_id'] ?? null;

        if ($product_id) {
            $product_args['ID'] = $product_id;
        }

        $new_product = wp_insert_post($product_args);

        if (is_wp_error($new_product)) {
            return;
        }

        $product = new Webspark_Product($new_product);
        $product->update_data([
            'price' => $price,
            'qty' => $qty,
            'media' => $media
        ]);

        do_action('webspark_product_updated', $new_product, $product_id);
    }

    function register_email($emails)
    {
        require dirname(__FILE__) . '/webspark-custom-email.php';
        $emails['Webspark_Products_Update_Email'] = new Webspark_Products_Update_Email();

        return $emails;
    }

    function trigger_email($product_id, $is_updated)
    {
        $email = WC()->mailer()->emails['Webspark_Products_Update_Email'];
        $email->trigger($product_id, $is_updated);
    }
}

new Webspark_My_Product;
