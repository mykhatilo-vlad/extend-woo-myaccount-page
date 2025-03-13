<?php


if (! class_exists('WC_Email')) {
    return;
}

class Webspark_Products_Update_Email extends WC_Email
{

    function __construct()
    {
        $this->id          = 'wc_notify_products_update';
        $this->title       = __('Notify Products Update', 'wpmyproductwebspark');
        $this->description = __('An email sent to the admin when an product is updated or created.', 'wpmyproductwebspark');
        $this->customer_email = false;
        $this->heading     = __('Product Updated', 'custom-wc-email');

        $this->template_html  = 'emails/wc-customer-cancelled-order.php';
        $this->template_plain = 'emails/plain/wc-customer-cancelled-order.php';
        $this->template_base  = plugin_dir_path(__FILE__) . 'emails/';

        add_action('webspark_product_updated', [$this, 'trigger'], 10, 2);

        parent::__construct();
    }

    function trigger($product_id, $is_updated) {

    }
}
