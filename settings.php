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
 * leeloo_featured_courses block settings
 *
 * @package   block_leeloo_products
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/blocks/leeloo_products/lib.php');

    $setting = new admin_setting_configtext(
        'block_leeloo_products/license',
        get_string('license', 'block_leeloo_products'),
        get_string('license', 'block_leeloo_products'),
        0
    );

    $settings->add($setting);

    $setting = new admin_setting_configleeloo_products('block_leeloo_products/settingsjson', '', '', '', PARAM_RAW);
    $settings->add($setting);

    $settings->add(new admin_setting_configtext(
        'block_leeloo_products/vendorkey',
        get_string('vendorkey', 'block_leeloo_products'),
        get_string('vendorkey_help', 'block_leeloo_products'),
        0
    ));
}
