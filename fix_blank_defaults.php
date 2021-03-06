<?php
/**
 * A script to fix blank defaults for databases that ran the conversion
 * script before this issue was fixed with the 2021-03-05 release.
 * This script is NOT needed if you have not run the utf8mb4 conversion script already.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 */

$username = 'your_database_username_here';  // same as DB_SERVER_USERNAME in configure.php
$password = 'your_database_password_here';  // same as DB_SERVER_PASSWORD in configure.php
$db = 'your_database_name_here';  // same as DB_DATABASE in configure.php
$host = 'localhost';  // same as DB_SERVER in configure.php
$prefix = '';  // if your tablenames start with "zen_" or some other common prefix, enter that here. // same as DB_PREFIX in configure.php


$tables = array(
   array('admin' => 'admin_name, admin_email, admin_pass, prev_pass1, prev_pass2, prev_pass3, reset_token, last_login_ip, last_failed_ip'),
   array('address_book' => 'entry_gender, entry_firstname, entry_lastname, entry_street_address, entry_postcode, entry_city'), 
   array('address_format' => 'address_format, address_summary'), 
   array('admin_activity_log' => 'page_accessed, ip_address'), 
   array('admin_menus' => 'menu_key, language_key'), 
   array('admin_pages' => 'page_key, language_key,main_page,page_params,menu_key'), 
   array('admin_profiles' => 'profile_name'), 
   array('admin_pages_to_profiles' => 'page_key'), 
   array('authorizenet' => 'response_text, authorization_type, time, session_id'), 
   array('banners' => 'banners_title, banners_url, banners_image, banners_group'),
   array('categories_description' => 'categories_name'), 
   array('configuration' => 'configuration_key'),
   array('configuration_group' => 'configuration_group_title, configuration_group_description'), 
   array('counter_history' => 'startdate'), 
   array('countries' => 'countries_name, countries_iso_code_2, countries_iso_code_3'), 
   array('coupon_gv_queue' => 'ipaddr'),
   array('coupon_redeem_track' => 'redeem_ip'), 
   array('coupons' => 'coupon_code'), 
   array('coupons_description' => 'coupon_name'), 
   array('currencies' => 'title, code'), 
   array('customers' => 'customers_gender, customers_firstname, customers_lastname, customers_email_address, customers_nick, customers_telephone, customers_password, customers_secret, customers_referral, customers_paypal_payerid'), 
   array('db_cache' => 'cache_entry_name'), 
   array('email_archive' => 'email_to_name, email_to_address, email_from_name, email_from_address, email_subject, module'), 
   array('ezpages' => 'alt_url, alt_url_external'), 
   array('ezpages_content' => 'pages_title'), 
   array('files_uploaded' => 'files_uploaded_name'), 
   array('geo_zones' => 'geo_zone_name, geo_zone_description'), 
   array('get_terms_to_filter' => 'get_term_name'), 
   array('group_pricing' => 'group_name'), 
   array('languages' => 'name, code'), 
   array('layout_boxes' => 'layout_template, layout_box_name'), 
   array('manufacturers' => 'manufacturers_name'),
   array('manufacturers_info' => 'manufacturers_url'), 
   array('media_manager' => 'media_name'), 
   array('media_types' => 'type_name, type_ext'), 
   array('meta_tags_categories_description' => 'metatags_title'), 
   array('meta_tags_products_description' => 'metatags_title'), 
   array('music_genre' => 'music_genre_name'), 
   array('newsletters' => 'title, module'), 
   array('orders' => 'customers_name, customers_street_address, customers_city, customers_postcode, customers_country, customers_telephone, customers_email_address, delivery_name, delivery_street_address, delivery_city, delivery_postcode, delivery_country, billing_name, billing_street_address, billing_city, billing_postcode, billing_country, payment_method, payment_module_code, shipping_module_code, coupon_code, ip_address, language_code'), 
   array('orders_products' => 'products_name'), 
   array('orders_products_attributes' => 'products_options, price_prefix, products_attributes_weight_prefix'), 
   array('orders_products_download' => 'orders_products_filename'), 
   array('orders_status' => 'orders_status_name'), 
   array('orders_status_history' => 'updated_by'), 
   array('orders_total' => 'title, text, class'), 
   array('paypal' => 'mc_currency, first_name, last_name, payer_email, payer_id, payer_status, business, receiver_email, receiver_id, txn_id, notify_version, verify_sign'), 
   array('paypal_payment_status' => 'payment_status_name'), 
   array('paypal_payment_status_history' => 'txn_id, parent_txn_id, payment_status'), 
   array('paypal_testing' => 'custom, txn_type, module_name, module_mode, payment_type, payment_status, mc_currency, first_name, last_name, payer_email, payer_id, payer_status, business, receiver_email, receiver_id, txn_id, verify_sign'), 
   array('plugin_control' => 'name'), 
   array('plugin_groups_description' => 'name'), 
   array('product_type_layout' => 'configuration_key'), 
   array('product_types' => 'type_name, type_handler, default_image'), 
   array('products_attributes' => 'price_prefix, products_attributes_weight_prefix'), 
   array('products_attributes_download' => 'products_attributes_filename'), 
   array('products_description' => 'products_name'), 
   array('products_options' => 'products_options_name'), 
   array('products_options_values' => 'products_options_values_name'), 
   array('project_version' => 'project_version_key, project_version_major, project_version_minor, project_version_patch1, project_version_patch2, project_version_patch1_source, project_version_patch2_source, project_version_comment'),
   array('project_version_history' => 'project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_comment'), 
   array('query_builder' => 'query_category, query_name'), 
   array('record_artists' => 'artists_name'), 
   array('record_artists_info' => 'artists_url'), 
   array('record_company' => 'record_company_name'),
   array('record_company_info' => 'record_company_url'), 
   array('reviews' => 'customers_name'), 
   array('salemaker_sales' => 'sale_name'), 
   array('sessions' => 'sesskey'), 
   array('tax_class' => 'tax_class_title, tax_class_description'), 
   array('tax_rates' => 'tax_description'), 
   array('template_select' => 'template_dir'), 
   array('whos_online' => 'full_name, session_id, ip_address, time_entry, time_last_click, last_page_url, user_agent'), 
   array('zones' => 'zone_code, zone_name'), 
); 

$conn = mysqli_connect($host, $username, $password);
mysqli_select_db($conn, $db);

// process tables
foreach ($tables as $table) {
    $t++;

    foreach ($table as $key => $fieldlist) {
       echo "\nProcessing table [{$key}]:\n";
       $fulltablename = $prefix . $key; 
       $fields = explode(",", $fieldlist); 
       foreach ($fields as $field) {
          $field = trim($field); 
          mysqli_query($conn, "ALTER TABLE `{$fulltablename}` ALTER `{$field}` SET DEFAULT ''");
          printMySqlErrors();
       }
    }

    echo "Table defaults updated.\n";
}

function printMySqlErrors()
{
    global $conn;
    if (mysqli_errno($conn)) {
        echo '<span style="color: red; font-weight: bold">MySQL Error: ' . mysqli_error($conn) . '</span>' . "\n";
    }
}
