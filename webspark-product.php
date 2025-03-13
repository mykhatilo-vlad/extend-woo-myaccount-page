<?php

if ( ! class_exists( 'WC_Product' ) ) {
	return;
}
class Webspark_Product extends WC_Product
{

    public function update_data($data)
    {
        if (isset($data['qty'])) {
            $this->set_stock_quantity($data['qty']);
            $this->set_manage_stock('yes');
        }

        if (isset($data['media'])) {
            $media_array = explode(',', $data['media']);
            $first_media = array_splice($media_array, 0, 1);

            if ($first_media) {
                $this->set_image_id($first_media[0]);
            }

            if (!empty($media_array)) {
                $this->set_gallery_image_ids($media_array);
            }
        }

        if (isset($data['price'])) {
            $this->set_regular_price($data['price']);
        }

        $this->save();
    }

    public function get_data_for_table() {

        return [
            'title' => $this->get_title(),
            'qty' => $this->get_stock_quantity(),
            'price' => wc_price($this->get_regular_price()),
            'status' => $this->get_status(),
        ];
    }
}
