<?php
require_once dirname(__FILE__) . '/webspark-media.php';


class Webspark_Add_Product_Form
{
    private $product = null;

    function __construct($product_id = null)
    {
        if ($product_id) {
            $this->product = wc_get_product($product_id);
        }

        //add_action( 'template_redirect', array( __CLASS__, 'save_account_details' ) );
    }

    function render_form()
    {
        $media_button = new Webspark_Media();
?>

        <form action="" method="post" class="woocommerce-AddProductForm">
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="product_title"><?php esc_html_e('Product Title', 'wpmyproductwebspark'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="product_title" id="product_title" value="<?php echo $this->product ? $this->product->get_title() : ''; ?>" aria-required="true" required />
            </p>


            <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                <label for="product_price"><?php esc_html_e('Product Price', 'wpmyproductwebspark'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
                <input type="number" class="woocommerce-Input woocommerce-Input--text input-text" name="product_price" id="product_price" value="<?php echo $this->product ? $this->product->get_regular_price() : ''; ?>" aria-required="true" required />
            </p>

            <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
                <label for="stock_qty"><?php esc_html_e('Product Quantity', 'wpmyproductwebspark'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
                <input type="number" class="woocommerce-Input woocommerce-Input--text input-text" name="stock_qty" id="stock_qty" value="<?php echo $this->product ? $this->product->get_stock_quantity() : ''; ?>" aria-required="true" required />
            </p>

            <div class="clear"></div>


            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <?php wp_editor($this->product ? $this->product->get_description() : '', 'product_description', [
                    'textarea_rows' => 10,
                ]); ?>
            </p>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <?php
                $media_ids = [];
                if ($this->product) {
                    $main_image = $this->product->get_image_id();

                    if ($main_image) {
                        $media_ids[] = $main_image;
                    }

                    $gallery = $this->product->get_gallery_image_ids();
                    if ($gallery) {
                        $media_ids = [...$media_ids, ...$gallery];
                    }
                }
                echo $media_button->render_field($media_ids);
                ?>
            </p>

            <p>
                <?php
                $action = $this->product ? 'update_product_form' : 'save_product_form';
                wp_nonce_field($action, "{$action}-nonce");
                ?>
                <button class="woocommerce-Button button wp-element-button" name="<?php echo $action; ?>">Save Product</button>
                <input type="hidden" name="action" value="<?php echo $action; ?>" />

                <?php if ($this->product) {
                    printf('<input type="hidden" name="product_id" value="%s">', $this->product->get_id());
                } ?>
            </p>
        </form>

<?php
    }
}
