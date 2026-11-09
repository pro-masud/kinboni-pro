<?php
/**
 * Kinboni Admin Module: Delivery & Logistics Engine
 *
 * Implements:
 * - Inside Dhaka & Outside Dhaka Delivery Pricing
 * - Free Delivery Threshold Integration
 * - Bangladesh Geo-Hierarchy (8 Divisions, 64 Districts, Thanas/Upazilas)
 * - Dependent Cascading Address Management
 * - Courier Partner Integration Settings
 * - Live Rate Calculator & Tester
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kinboni_Admin_Delivery {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // AJAX Endpoints
        add_action('wp_ajax_kinboni_save_delivery_settings', array($this, 'ajax_save_delivery_settings'));
        add_action('wp_ajax_kinboni_get_geo_districts', array($this, 'ajax_get_geo_districts'));
        add_action('wp_ajax_nopriv_kinboni_get_geo_districts', array($this, 'ajax_get_geo_districts'));
        add_action('wp_ajax_kinboni_get_geo_thanas', array($this, 'ajax_get_geo_thanas'));
        add_action('wp_ajax_nopriv_kinboni_get_geo_thanas', array($this, 'ajax_get_geo_thanas'));
        add_action('wp_ajax_kinboni_calc_delivery_rate', array($this, 'ajax_calc_delivery_rate'));
    }

    /**
     * Render the Delivery Admin Page
     */
    public function render() {
        $settings = $this->get_delivery_settings();
        $divisions = $this->get_bangladesh_divisions();

        $view_file = KINBONI_CORE_PATH . 'admin/views/view-delivery.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Delivery view template not found.', 'kinboni-core') . '</p></div>';
        }
    }

    /**
     * Retrieve Configured Delivery Settings
     */
    public function get_delivery_settings() {
        return array(
            'fee_inside'           => (float) get_option('kinboni_delivery_fee_inside', 70),
            'fee_outside'          => (float) get_option('kinboni_delivery_fee_outside', 130),
            'free_threshold'       => (float) get_option('kinboni_free_shipping_threshold', 2500),
            'time_inside'          => get_option('kinboni_delivery_time_inside', '1 - 2 Business Days'),
            'time_outside'         => get_option('kinboni_delivery_time_outside', '2 - 4 Business Days'),
            'courier_partner'      => get_option('kinboni_primary_courier', 'Steadfast Courier'),
            'courier_api_key'      => get_option('kinboni_courier_api_key', ''),
            'courier_secret_key'   => get_option('kinboni_courier_secret_key', ''),
            'enable_geo_selector'  => get_option('kinboni_enable_geo_selector', 'yes'),
            'cod_inside_enabled'   => get_option('kinboni_cod_inside_enabled', 'yes'),
            'cod_outside_enabled'  => get_option('kinboni_cod_outside_enabled', 'yes'),
            'allow_inspection'     => get_option('kinboni_allow_inspection', 'yes'),
        );
    }

    /**
     * AJAX: Save Delivery Settings
     */
    public function ajax_save_delivery_settings() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('delivery') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $fee_inside    = isset($_POST['fee_inside']) ? max(0, (float) $_POST['fee_inside']) : 70;
        $fee_outside   = isset($_POST['fee_outside']) ? max(0, (float) $_POST['fee_outside']) : 130;
        $threshold     = isset($_POST['free_threshold']) ? max(0, (float) $_POST['free_threshold']) : 2500;
        $time_inside   = isset($_POST['time_inside']) ? sanitize_text_field(wp_unslash($_POST['time_inside'])) : '1 - 2 Business Days';
        $time_outside  = isset($_POST['time_outside']) ? sanitize_text_field(wp_unslash($_POST['time_outside'])) : '2 - 4 Business Days';
        $courier       = isset($_POST['courier_partner']) ? sanitize_text_field(wp_unslash($_POST['courier_partner'])) : 'Steadfast Courier';
        $api_key       = isset($_POST['courier_api_key']) ? sanitize_text_field(wp_unslash($_POST['courier_api_key'])) : '';
        $secret_key    = isset($_POST['courier_secret_key']) ? sanitize_text_field(wp_unslash($_POST['courier_secret_key'])) : '';
        $geo_sel       = isset($_POST['enable_geo_selector']) && wp_unslash($_POST['enable_geo_selector']) === 'yes' ? 'yes' : 'no';
        $cod_in        = isset($_POST['cod_inside_enabled']) && wp_unslash($_POST['cod_inside_enabled']) === 'yes' ? 'yes' : 'no';
        $cod_out       = isset($_POST['cod_outside_enabled']) && wp_unslash($_POST['cod_outside_enabled']) === 'yes' ? 'yes' : 'no';
        $inspect       = isset($_POST['allow_inspection']) && wp_unslash($_POST['allow_inspection']) === 'yes' ? 'yes' : 'no';

        update_option('kinboni_delivery_fee_inside', $fee_inside);
        update_option('kinboni_delivery_fee_outside', $fee_outside);
        update_option('kinboni_free_shipping_threshold', $threshold);
        update_option('kinboni_delivery_time_inside', $time_inside);
        update_option('kinboni_delivery_time_outside', $time_outside);
        update_option('kinboni_primary_courier', $courier);
        update_option('kinboni_courier_api_key', $api_key);
        update_option('kinboni_courier_secret_key', $secret_key);
        update_option('kinboni_enable_geo_selector', $geo_sel);
        update_option('kinboni_cod_inside_enabled', $cod_in);
        update_option('kinboni_cod_outside_enabled', $cod_out);
        update_option('kinboni_allow_inspection', $inspect);

        if (class_exists('Kinboni_Activity_Logger')) {
            Kinboni_Activity_Logger::log(
                'delivery_settings_updated',
                sprintf('Updated delivery rates: Inside ৳%s, Outside ৳%s, Free Threshold ৳%s', $fee_inside, $fee_outside, $threshold)
            );
        }

        wp_send_json_success(array(
            'message' => esc_html__('Delivery rates & logistics preferences updated successfully.', 'kinboni-core')
        ));
    }

    /**
     * AJAX: Live Rate Calculator Tester
     */
    public function ajax_calc_delivery_rate() {
        check_ajax_referer('kinboni_admin_nonce', 'nonce');

        if (class_exists('Kinboni_Admin') ? !Kinboni_Admin::user_can('delivery') : !current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => esc_html__('Permission denied.', 'kinboni-core')));
        }

        $subtotal = isset($_POST['subtotal']) ? (float) $_POST['subtotal'] : 0;
        $zone     = isset($_POST['zone']) ? sanitize_key(wp_unslash($_POST['zone'])) : 'inside';

        $fee_inside = (float) get_option('kinboni_delivery_fee_inside', 70);
        $fee_outside = (float) get_option('kinboni_delivery_fee_outside', 130);
        $threshold  = (float) get_option('kinboni_free_shipping_threshold', 2500);

        $is_free = ($threshold > 0 && $subtotal >= $threshold);
        $charge = $is_free ? 0 : (($zone === 'outside') ? $fee_outside : $fee_inside);
        $diff = max(0, $threshold - $subtotal);

        wp_send_json_success(array(
            'zone'          => $zone,
            'is_free'       => $is_free,
            'charge'        => $charge,
            'charge_html'   => $is_free ? '🎉 ৳0.00 (Complimentary Free Shipping)' : ('৳' . number_format($charge, 2)),
            'total'         => $subtotal + $charge,
            'total_html'    => '৳' . number_format($subtotal + $charge, 2),
            'threshold_gap' => $diff,
            'threshold_gap_html' => ($diff > 0) ? ('Add ৳' . number_format($diff, 2) . ' more for Free Delivery') : 'Free Delivery threshold exceeded!',
        ));
    }

    /**
     * Bangladesh 8 Administrative Divisions
     */
    public function get_bangladesh_divisions() {
        return array(
            'Dhaka'       => 'ঢাকা (Dhaka)',
            'Chattogram'  => 'চট্টগ্রাম (Chattogram)',
            'Rajshahi'    => 'রাজশাহী (Rajshahi)',
            'Khulna'      => 'খুলনা (Khulna)',
            'Barishal'    => 'বরিশাল (Barishal)',
            'Sylhet'      => 'সিলেট (Sylhet)',
            'Rangpur'     => 'রংপুর (Rangpur)',
            'Mymensingh'  => 'ময়মনসিংহ (Mymensingh)',
        );
    }

    /**
     * Bangladesh Districts Mapped to Divisions
     */
    public function get_districts_by_division($division = '') {
        $map = array(
            'Dhaka' => array(
                'Dhaka City', 'Gazipur', 'Narayanganj', 'Narsingdi', 'Tangail',
                'Manikganj', 'Munshiganj', 'Faridpur', 'Gopalganj', 'Madaripur',
                'Rajbari', 'Shariatpur', 'Kishoreganj'
            ),
            'Chattogram' => array(
                'Chattogram', 'Cox\'s Bazar', 'Cumilla', 'Feni', 'Brahmanbaria',
                'Noakhali', 'Chandpur', 'Lakshmipur', 'Rangamati', 'Khagrachhari', 'Bandarban'
            ),
            'Rajshahi' => array(
                'Rajshahi', 'Bogura', 'Pabna', 'Sirajganj', 'Naogaon', 'Natore',
                'Joypurhat', 'Chapainawabganj'
            ),
            'Khulna' => array(
                'Khulna', 'Jashore', 'Kushtia', 'Satkhira', 'Bagerhat', 'Jhenaidah',
                'Chuadanga', 'Magura', 'Meherpur', 'Narail'
            ),
            'Barishal' => array(
                'Barishal', 'Bhola', 'Patuakhali', 'Pirojpur', 'Barguna', 'Jhalokati'
            ),
            'Sylhet' => array(
                'Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj'
            ),
            'Rangpur' => array(
                'Rangpur', 'Dinajpur', 'Gaibandha', 'Kurigram', 'Lalmonirhat',
                'Nilphamari', 'Panchagarh', 'Thakurgaon'
            ),
            'Mymensingh' => array(
                'Mymensingh', 'Jamalpur', 'Netrokona', 'Sherpur'
            ),
        );

        if (!empty($division) && isset($map[$division])) {
            return $map[$division];
        }

        return $map;
    }

    /**
     * Bangladesh Thanas / Upazilas Mapped to Districts
     */
    public function get_thanas_by_district($district = '') {
        $map = array(
            'Dhaka City' => array(
                'Dhanmondi', 'Gulshan-1', 'Gulshan-2', 'Banani', 'Uttara', 'Mirpur-1',
                'Mirpur-2', 'Mirpur-10', 'Mirpur-11', 'Mohammadpur', 'Motijheel', 'Tejgaon',
                'Badda', 'Khilgaon', 'Rampura', 'Paltan', 'Shahbagh', 'Lalbagh',
                'Old Dhaka', 'Wari', 'Malibagh', 'Shantinagar', 'Bashundhara R/A',
                'Baridhara', 'Nikunja', 'Mohakhali', 'Khilkhet', 'Savar', 'Keraniganj', 'Dhamrai'
            ),
            'Gazipur' => array('Gazipur Sadar', 'Tongi', 'Kaliakair', 'Kapasia', 'Sreepur', 'Kaliganj'),
            'Narayanganj' => array('Narayanganj Sadar', 'Bandar', 'Fatullah', 'Siddhirganj', 'Sonargaon', 'Rupganj', 'Araihazar'),
            'Chattogram' => array('Panchlaish', 'Kotwali', 'Agrabad', 'Halishahar', 'Khulshi', 'Bakalia', 'Chandgaon', 'Pahartali', 'Double Mooring', 'Sitakunda', 'Hathazari', 'Patiya'),
            'Cumilla' => array('Cumilla Adarsha Sadar', 'Cumilla Sadar Dakshin', 'Laksam', 'Daudkandi', 'Burichang', 'Brahmanpara', 'Chandina', 'Debidwar', 'Homna'),
            'Sylhet' => array('Sylhet Sadar', 'Amberkhana', 'Zindabazar', 'Beanibazar', 'Golapganj', 'Zakiganj', 'Kanaighat', 'Fenchuganj', 'Biswanath'),
            'Rajshahi' => array('Boalia', 'Motihar', 'Rajpara', 'Shah Makhdum', 'Godagari', 'Tanore', 'Bagmara', 'Durgapur', 'Puthia', 'Charghat'),
            'Khulna' => array('Khulna Sadar', 'Sonadanga', 'Khalishpur', 'Daulatpur', 'Khan Jahan Ali', 'Dumuria', 'Rupsha', 'Batiaghata', 'Paikgachha'),
            'Bogura' => array('Bogura Sadar', 'Sherpur', 'Shibganj', 'Gabtali', 'Kahalu', 'Nandigram', 'Sonatola', 'Dhunat', 'Sariakandi'),
        );

        if (!empty($district) && isset($map[$district])) {
            return $map[$district];
        }

        // Generic fallback thanas if not explicitly defined
        return array($district . ' Sadar', 'Upazila Center', 'North ' . $district, 'South ' . $district);
    }

    /**
     * AJAX: Get Dependent Districts for Division
     */
    public function ajax_get_geo_districts() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'kinboni_admin_nonce') && !wp_verify_nonce($_POST['nonce'] ?? '', 'kinboni_core_nonce')) {
            wp_send_json_error(array('message' => esc_html__('Invalid security token.', 'kinboni-core')));
        }

        $division = isset($_POST['division']) ? sanitize_text_field(wp_unslash($_POST['division'])) : '';
        $districts = $this->get_districts_by_division($division);
        wp_send_json_success($districts);
    }

    /**
     * AJAX: Get Dependent Thanas for District
     */
    public function ajax_get_geo_thanas() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'kinboni_admin_nonce') && !wp_verify_nonce($_POST['nonce'] ?? '', 'kinboni_core_nonce')) {
            wp_send_json_error(array('message' => esc_html__('Invalid security token.', 'kinboni-core')));
        }

        $district = isset($_POST['district']) ? sanitize_text_field(wp_unslash($_POST['district'])) : '';
        $thanas = $this->get_thanas_by_district($district);
        wp_send_json_success($thanas);
    }
}

