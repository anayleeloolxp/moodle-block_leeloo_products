<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Products block
 *
 * @package   block_leeloo_prodcuts
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Products block
 *
 * @package   block_leeloo_prodcuts
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_leeloo_prodcuts extends block_base {
    /**
     * Block initialization.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_leeloo_prodcuts');
    }

    /**
     * Allow instace configration.
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * Dont allow multiple blocks
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Return contents of leeloo_prodcuts block
     *
     * @return stdClass contents of block
     */
    public function get_content() {

        global $CFG;
        global $SESSION;
        $jsession_id = $SESSION->jsession_id;

        require_once($CFG->libdir . '/filelib.php');

        $this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/leeloo_prodcuts/js/custom.js'));

        if ($this->content !== null) {
            return $this->content;
        }

        $leeloolxplicense = get_config('block_leeloo_prodcuts')->license;

        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $postdata = '&license_key=' . $leeloolxplicense;

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = get_string('nolicense', 'block_leeloo_prodcuts');
            return $this->content;
        }

        $infoleeloolxp = json_decode($output);

        if ($infoleeloolxp->status != 'false') {
            $leeloolxpurl = $infoleeloolxp->data->install_url;
        } else {
            $this->content->text = get_string('nolicense', 'block_leeloo_prodcuts');
            return $this->content;
        }

        $url = $leeloolxpurl . '/admin/Theme_setup/get_courses_for_sale';

        $postdata = '&license_key=' . $leeloolxplicense;

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = get_string('nolicense', 'block_leeloo_prodcuts');
            return $this->content;
        }

        $resposedata = json_decode($output);
        $settingleeloolxp = $resposedata->data->sale_courses_data;

        if (empty($settingleeloolxp->pro_block_title)) {
            $settingleeloolxp->pro_block_title = get_string('displayname', 'block_leeloo_prodcuts');
        }
        $this->title = $settingleeloolxp->pro_block_title;

        $productsarr = explode(',', $settingleeloolxp->pro_product_id);

        $this->content = new stdClass();

        $leelooapi_base_url = 'https://leeloolxp.com/api/moodle_sell_course_plugin/';

        $vendorkey = get_config('block_leeloo_prodcuts', 'vendorkey');

        $encryptionMethod = "AES-256-CBC"; // AES is used by the U.S. gov't to encrypt top secret documents.
        $secretHash = "25c6c7ff35b9979b151f2136cd13b0ff";

        $encrypt_licensekey = openssl_encrypt($vendorkey, $encryptionMethod, $secretHash);

        $post = [
            'license_key' => $encrypt_licensekey,
        ];

        $url = $leelooapi_base_url . 'get_products_by_licensekey.php';
        $postdata = '&license_key=' . $encrypt_licensekey;
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_POSTFIELDS' => $post,
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = get_string('nolicense', 'block_leeloo_prodcuts');
            return $this->content;
        }

        $products_response = json_decode($output);

        $prodcutslist = array();
        if ($products_response->status == 'true') {
            foreach ($products_response->data->prodcuts as $product) {
                if (in_array($product->product_id, $productsarr)) {
                    $prodcutslist[$product->product_id] = $product;
                }
            }
        }

        $this->content->text = '<div class="leeloo_prodcutslist">';

        foreach ($prodcutslist as $product_sin) {
            $productprice = $product_sin->product_msrp + 0;
            $productid = $product_sin->product_id;
            $product_alias = $product_sin->product_alias;
            $url_alias = $productid . '-' . $product_alias;

            $leeloo_div = "<div class='leeloo_productdiv' id='leeloo_div_$productid'><a class='leeloo_pricut_buy' id='leeloo_cert_$productid' data-toggle='modal' data-target='#leelooprodcutModal_$productid' href='https://leeloolxp.com/products-listing/product/$url_alias?session_id=$jsession_id'>Buy</a></div>";

            $leeloo_modal = "<div class='modal fade leelooProdcutModal' tabindex='-1' aria-labelledby='gridSystemModalLabel' id='leelooprodcutModal_$productid' role='dialog' style='max-width: 90%;'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h4 class='modal-title'>$product_sin->product_name</h4>
                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                        </div>
                        <div class='modal-body'>

                        </div>
                    </div>
                </div>
            </div>
            ";

            $this->content->text .= '<div class="leeloo_product">';

            $this->content->text .= '<div class="leeloo_product_name">' . $product_sin->product_name . '</div>';

            $this->content->text .= '<div class="leeloo_product_details">';

            $this->content->text .= '<div class="leeloo_product_price">' . '$' . $productprice . '</div>';

            $this->content->text .= '</div>';

            $this->content->text .= $leeloo_div . $leeloo_modal;

            $this->content->text .= '</div>';
        }

        $this->content->text .= '</div>';

        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }
}
