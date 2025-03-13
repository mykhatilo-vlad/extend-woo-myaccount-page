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
        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
        $this->heading     = __('Product Updated', 'custom-wc-email');

        $this->template_html  = 'emails/product-updated.php';
        $this->template_plain = 'emails/plain/product-updated.php';
        $this->template_base  = plugin_dir_path(__FILE__) . 'templates/';

        // I saw that example. But I cannot figgured it out, which hook I have to use to handle form submission to trigger that hook later than the class initialized.
        //add_action('webspark_product_updated', [$this, 'trigger']);
        
        parent::__construct();
    }

    function trigger($product_id, $is_updated) {
        error_log('trigger ' . $this->is_enabled() . ' ' . $this->get_recipient());

        $this->object = wc_get_product( $product_id );

        $this->subject = sprintf(__('The product %s is %s', 'wpmyproductwebspark'), $this->object->get_title(), $is_updated ? 'updated' : 'created' );

        if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
            return;
        }


        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }

    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'product'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'			=> $this
        ), '', $this->template_base );
    }

    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'product'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'			=> $this
        ), '', $this->template_base );
    }
}
