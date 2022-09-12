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
 * @package   block_leeloo_products
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Products block
 *
 * @package   block_leeloo_products
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_leeloo_products extends block_base {
    /**
     * Block initialization.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_leeloo_products');
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
     * Return contents of leeloo_products block
     *
     * @return stdClass contents of block
     */
    public function get_content() {

        global $CFG;
        global $SESSION;
        @$jsessionid = $SESSION->jsession_id;

        require_once($CFG->libdir . '/filelib.php');

        $this->page->requires->js(new moodle_url('/blocks/leeloo_products/js/custom.js'));

        if ($this->content !== null) {
            return $this->content;
        }

        $leeloolxplicense = get_config('block_leeloo_products')->license;
        $settingsjson = get_config('block_leeloo_products')->settingsjson;
        $resposedata = json_decode(base64_decode($settingsjson));

        if (!isset($resposedata->data->sale_courses_data)) {
            $this->title = get_string('displayname', 'block_leeloo_products');
            $this->content = new stdClass();
            $this->content->text = '';
            $this->content->footer = '';
            return $this->content;
        }

        $settingleeloolxp = $resposedata->data->sale_courses_data;

        if (empty($settingleeloolxp->pro_block_title)) {
            $settingleeloolxp->pro_block_title = get_string('displayname', 'block_leeloo_products');
        }
        $this->title = $settingleeloolxp->pro_block_title;

        $productsarr = explode(',', $settingleeloolxp->pro_product_id);

        $this->content = new stdClass();

        $leelooapibaseurl = 'https://leeloolxp.com/api/moodle_sell_course_plugin/';

        $vendorkey = get_config('block_leeloo_products', 'vendorkey');

        $encryptionmethod = "AES-256-CBC"; // AES is used by the U.S. gov't to encrypt top secret documents.
        $secrethash = "25c6c7ff35b9979b151f2136cd13b0ff";

        $encryptlicensekey = @openssl_encrypt($vendorkey, $encryptionmethod, $secrethash);

        $post = [
            'license_key' => $encryptlicensekey,
        ];

        $url = $leelooapibaseurl . 'get_products_by_licensekey.php';
        $postdata = [
            'license_key' => $encryptlicensekey,
        ];
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = get_string('nolicense', 'block_leeloo_products');
            return $this->content;
        }

        $productsresponse = json_decode($output);

        $prodcutslist = array();
        if ($productsresponse->status == 'true') {
            foreach ($productsresponse->data->prodcuts as $product) {
                if (in_array($product->product_id, $productsarr)) {
                    $prodcutslist[$product->product_id] = $product;
                }
            }
        }

        $this->content->text = '<div class="leeloo_productslist">';

        foreach ($prodcutslist as $productsin) {
            $productprice = $productsin->product_msrp + 0;
            $productid = $productsin->product_id;
            $productalias = $productsin->product_alias;
            $urlalias = $productid . '-' . $productalias;

            $buybutton = get_string('buy', 'block_leeloo_products') . '$' . $productprice;

            if (!$jsessionid) {
                $loginurl = $CFG->wwwroot . '/login/index.php';
                $buybuttonhtml = "<a href='$loginurl'>
                    $buybutton
                </a>";
            } else {
                $buybuttonhtml = "<a class='leeloo_pricut_buy'
                    id='leeloo_cert_$productid'
                    data-toggle='modal'
                    data-target='#leelooprodcutModal_$productid'
                    href='https://leeloolxp.com/products-listing/product/$urlalias?session_id=$jsessionid'>
                        $buybutton
                </a>";
            }

            $leeloodiv = "<div class='leeloo_productdiv' id='leeloo_div_$productid'>
                $buybuttonhtml
            </div>";

            $leeloomodal = "<div class='modal fade leelooProdcutModal' tabindex='-1' " .
                "aria-labelledby='gridSystemModalLabel' id='leelooprodcutModal_$productid' role='dialog'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h4 class='modal-title'>$productsin->product_name</h4>
                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                        </div>
                        <div class='modal-body'>

                        </div>
                    </div>
                </div>
            </div>
            ";

            $this->content->text .= '<div class="leeloo_product">';

            $this->content->text .= '<div class="leeloo_product_image"><img style="width: 100%;" src="' .
                $productsin->imgurl . '"/></div>';

            $this->content->text .= '<div class="leeloo_product_name">' . $productsin->product_name . '</div>';

            $this->content->text .= $leeloodiv . $leeloomodal;

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
