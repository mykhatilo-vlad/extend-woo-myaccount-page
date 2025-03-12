<?php


class Webspark_Media
{
    function render_field($ids = [])
    {
        if (current_user_can('upload_files')) {
            $html = '<div class="webspark-media-uploader-wrapper">';
            $html .= sprintf('<button id="product_media" class="button">%s</button>', __('+ Add Product Image', 'wpmyproductwebspark'));
            $html .= sprintf('<input type="hidden" name="media_ids" value="%s">', implode(',', $ids));

            if($ids && is_array($ids)) {
                foreach($ids as $id) {
                    $html .= sprintf('<img src="%s">', wp_get_attachment_image_url($id));
                }
            }
            $html .= '</div>';
            return $html;
        }
    }
}
