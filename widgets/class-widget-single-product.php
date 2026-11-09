<?php
/**
 * Kinboni Single Product Atelier Showcase - Elementor Widget
 *
 * Implements the full editorial Single Product Details & Experience showcase,
 * completely dynamic from WooCommerce with zero omissions:
 * 1. Breadcrumb & Prev/Next Product Switcher
 * 2. Visual Gallery with alternate angle switcher & status badges
 * 3. Title, Atelier Category, Verified Ratings & Stock Availability
 * 4. Price & Savings Badge
 * 5. Short Description
 * 6. Master Artisan Highlights & Size/Fit Guide Modal Trigger
 * 7. Interactive Bespoke Monogram / Engraving Live Studio & 0% EMI Trigger
 * 8. Actions: Add to Bag (Ajax), Quick Order (Fast 3-Field COD Drawer), Full Checkout, Wishlist
 * 9. Product Meta (SKU, Category, White-Glove Dispatch Guarantee)
 * 10. Luxury Trust Badges
 * 11. WooCommerce Product Data Tabs (Description + Included in Box, Specs Table, Patron Reviews + Form)
 * 12. Related Atelier Creations Grid
 * 13. Recently Viewed Creations Grid
 * 14. Ambient Architectural Glows & Geometric Arch Shapes
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;

class Kinboni_Widget_Single_Product extends Widget_Base {

    public function get_name() {
        return 'kinboni_single_product';
    }

    public function get_title() {
        return esc_html__('Single Product Atelier Showcase', 'kinboni-core');
    }

    public function get_icon() {
        return 'eicon-single-product';
    }

    public function get_categories() {
        return array('kinboni-category');
    }

    public function get_keywords() {
        return array('product', 'single', 'details', 'showcase', 'atelier', 'woocommerce', 'kinboni', 'gallery');
    }

    /**
     * Fetch all WooCommerce products for Select2 control
     */
    private function get_all_woocommerce_products() {
        $options = array();
        if (class_exists('WooCommerce')) {
            $products = wc_get_products(array(
                'limit'   => 150,
                'status'  => 'publish',
                'orderby' => 'title',
                'order'   => 'ASC',
            ));
            foreach ($products as $product) {
                $options[$product->get_id()] = sprintf('%s (৳%s)', $product->get_name(), $product->get_price());
            }
        }
        return $options;
    }

    /**
     * Register Widget Controls
     */
    protected function register_controls() {

        // ==========================================
        // CONTENT TAB: PRODUCT SELECTION
        // ==========================================
        $this->start_controls_section(
            'section_product_selection',
            array(
                'label' => esc_html__('Product Source', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'source_mode',
            array(
                'label'   => esc_html__('Source Mode', 'kinboni-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'auto',
                'options' => array(
                    'auto'   => esc_html__('Auto-Detect (Current Product on Single Page)', 'kinboni-core'),
                    'manual' => esc_html__('Select Specific Product (Custom Showcase)', 'kinboni-core'),
                ),
                'description' => esc_html__('On Single Product templates, Auto-Detect pulls the viewed product. On normal pages, pick a specific product.', 'kinboni-core'),
            )
        );

        $this->add_control(
            'product_id',
            array(
                'label'       => esc_html__('Select Product', 'kinboni-core'),
                'type'        => Controls_Manager::SELECT2,
                'label_block' => true,
                'options'     => $this->get_all_woocommerce_products(),
                'condition'   => array(
                    'source_mode' => 'manual',
                ),
                'description' => esc_html__('Choose the WooCommerce product to showcase.', 'kinboni-core'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: BREADCRUMB & SWITCHER
        // ==========================================
        $this->start_controls_section(
            'section_breadcrumb',
            array(
                'label' => esc_html__('Breadcrumb & Prev/Next Switcher', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_breadcrumb',
            array(
                'label'        => esc_html__('Show Breadcrumb', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'home_label',
            array(
                'label'     => esc_html__('Home Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Home', 'kinboni-core'),
                'condition' => array('show_breadcrumb' => 'yes'),
            )
        );

        $this->add_control(
            'shop_label',
            array(
                'label'     => esc_html__('Shop Label', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Shop', 'kinboni-core'),
                'condition' => array('show_breadcrumb' => 'yes'),
            )
        );

        $this->add_control(
            'show_switcher',
            array(
                'label'        => esc_html__('Show Prev/Next Switcher', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: GALLERY & VISUALS
        // ==========================================
        $this->start_controls_section(
            'section_gallery',
            array(
                'label' => esc_html__('Gallery & Visual Badges', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_badges',
            array(
                'label'        => esc_html__('Show Status Badges', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'featured_badge_text',
            array(
                'label'     => esc_html__('Featured Badge Text', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('SIGNATURE', 'kinboni-core'),
                'condition' => array('show_badges' => 'yes'),
            )
        );

        $this->add_control(
            'show_thumbnails',
            array(
                'label'        => esc_html__('Show Gallery Thumbnails', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: ARTISAN HIGHLIGHTS
        // ==========================================
        $this->start_controls_section(
            'section_highlights',
            array(
                'label' => esc_html__('Artisan Highlights & Sizing', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_highlights',
            array(
                'label'        => esc_html__('Show Highlights Block', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'highlights_title',
            array(
                'label'     => esc_html__('Highlights Title', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Master Artisan Highlights', 'kinboni-core'),
                'condition' => array('show_highlights' => 'yes'),
            )
        );

        $this->add_control(
            'show_size_guide',
            array(
                'label'        => esc_html__('Show Size & Fit Guide Link', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_highlights' => 'yes'),
            )
        );

        $repeater_highlights = new Repeater();
        $repeater_highlights->add_control(
            'text',
            array(
                'label'       => esc_html__('Highlight Point', 'kinboni-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Artisanal Limited Production', 'kinboni-core'),
                'label_block' => true,
            )
        );

        $this->add_control(
            'highlights_list',
            array(
                'label'       => esc_html__('Highlights Points', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater_highlights->get_controls(),
                'default'     => array(
                    array('text' => esc_html__('Artisanal Limited Production', 'kinboni-core')),
                    array('text' => esc_html__('Accompanied by Signed Certificate', 'kinboni-core')),
                    array('text' => esc_html__('White-Glove Insured Delivery', 'kinboni-core')),
                    array('text' => esc_html__('Private Concierge Lifetime Care', 'kinboni-core')),
                ),
                'title_field' => '{{{ text }}}',
                'condition'   => array('show_highlights' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: BESPOKE MONOGRAM & EMI
        // ==========================================
        $this->start_controls_section(
            'section_monogram',
            array(
                'label' => esc_html__('Monogram Studio & 0% EMI', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_monogram',
            array(
                'label'        => esc_html__('Show Bespoke Monogram Studio', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'monogram_title',
            array(
                'label'     => esc_html__('Studio Title', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Complimentary Bespoke Monogram / Engraving', 'kinboni-core'),
                'condition' => array('show_monogram' => 'yes'),
            )
        );

        $this->add_control(
            'show_emi',
            array(
                'label'        => esc_html__('Show 0% EMI Calculator Trigger', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => array('show_monogram' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: ACTIONS & CTAS
        // ==========================================
        $this->start_controls_section(
            'section_actions',
            array(
                'label' => esc_html__('Action Buttons & Guarantees', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_qty',
            array(
                'label'        => esc_html__('Show Quantity Selector', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'btn_cart_text',
            array(
                'label'   => esc_html__('Add to Bag Button Text', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Add to Shopping Bag', 'kinboni-core'),
            )
        );

        $this->add_control(
            'show_quick_order',
            array(
                'label'        => esc_html__('Show Quick Order (COD) Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'btn_cod_text',
            array(
                'label'     => esc_html__('Quick COD Button Text', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Quick Order (3-Field COD)', 'kinboni-core'),
                'condition' => array('show_quick_order' => 'yes'),
            )
        );

        $this->add_control(
            'show_full_checkout',
            array(
                'label'        => esc_html__('Show Full Checkout Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'btn_checkout_text',
            array(
                'label'     => esc_html__('Full Checkout Button Text', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Full Checkout Page', 'kinboni-core'),
                'condition' => array('show_full_checkout' => 'yes'),
            )
        );

        $this->add_control(
            'show_wishlist',
            array(
                'label'        => esc_html__('Show Wishlist Button', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'dispatch_text',
            array(
                'label'   => esc_html__('Dispatch Promise Text', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Complimentary White-Glove Express Delivery (24–48 hrs in Dhaka)', 'kinboni-core'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: LUXURY TRUST BADGES
        // ==========================================
        $this->start_controls_section(
            'section_trust_badges',
            array(
                'label' => esc_html__('Luxury Trust Badges', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_trust_badges',
            array(
                'label'        => esc_html__('Show Trust Badges Row', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $repeater_trust = new Repeater();
        $repeater_trust->add_control(
            'icon',
            array(
                'label'   => esc_html__('Lucide Icon Name', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => 'truck',
            )
        );
        $repeater_trust->add_control(
            'text',
            array(
                'label'   => esc_html__('Badge Text', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Express Dispatch (24-48h Dhaka)', 'kinboni-core'),
            )
        );

        $this->add_control(
            'trust_badges_list',
            array(
                'label'       => esc_html__('Badges List', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater_trust->get_controls(),
                'default'     => array(
                    array('icon' => 'truck', 'text' => esc_html__('Express Dispatch (24-48h Dhaka)', 'kinboni-core')),
                    array('icon' => 'shield-check', 'text' => esc_html__('100% Certified Authentic', 'kinboni-core')),
                    array('icon' => 'credit-card', 'text' => esc_html__('0% EMI Available (Up to 12M)', 'kinboni-core')),
                    array('icon' => 'phone-call', 'text' => esc_html__('Boutique Concierge Support', 'kinboni-core')),
                ),
                'title_field' => '{{{ text }}}',
                'condition'   => array('show_trust_badges' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: WOOCOMMERCE PRODUCT DATA TABS
        // ==========================================
        $this->start_controls_section(
            'section_tabs',
            array(
                'label' => esc_html__('Product Data Tabs', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_tabs',
            array(
                'label'        => esc_html__('Show Product Data Tabs', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'tab_desc_title',
            array(
                'label'     => esc_html__('Description Tab Title', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Atelier Narrative & Description', 'kinboni-core'),
                'condition' => array('show_tabs' => 'yes'),
            )
        );

        $this->add_control(
            'tab_specs_title',
            array(
                'label'     => esc_html__('Specs Tab Title', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Additional Specifications', 'kinboni-core'),
                'condition' => array('show_tabs' => 'yes'),
            )
        );

        $this->add_control(
            'tab_reviews_title',
            array(
                'label'     => esc_html__('Reviews Tab Title', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Patron Reviews', 'kinboni-core'),
                'condition' => array('show_tabs' => 'yes'),
            )
        );

        $this->add_control(
            'included_box_title',
            array(
                'label'     => esc_html__('Included In Box Title', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Included in the Box', 'kinboni-core'),
                'condition' => array('show_tabs' => 'yes'),
            )
        );

        $repeater_box = new Repeater();
        $repeater_box->add_control(
            'item_text',
            array(
                'label'   => esc_html__('Item Text', 'kinboni-core'),
                'type'    => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Hand-Numbered Authenticity Card', 'kinboni-core'),
            )
        );

        $this->add_control(
            'included_box_items',
            array(
                'label'       => esc_html__('Included in Box Items', 'kinboni-core'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater_box->get_controls(),
                'default'     => array(
                    array('item_text' => esc_html__('Hand-Numbered Authenticity Card', 'kinboni-core')),
                    array('item_text' => esc_html__('Atelier Velvet & Wooden Presentation Case', 'kinboni-core')),
                    array('item_text' => esc_html__('Lifetime Refurbishment & Warranty Pass', 'kinboni-core')),
                    array('item_text' => esc_html__('Microfiber Polishing Cloth & Toolset', 'kinboni-core')),
                ),
                'title_field' => '{{{ item_text }}}',
                'condition'   => array('show_tabs' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: RELATED CREATIONS
        // ==========================================
        $this->start_controls_section(
            'section_related',
            array(
                'label' => esc_html__('Related Atelier Creations', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_related',
            array(
                'label'        => esc_html__('Show Related Products', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'related_subtitle',
            array(
                'label'     => esc_html__('Subtitle', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Complementary Curations', 'kinboni-core'),
                'condition' => array('show_related' => 'yes'),
            )
        );

        $this->add_control(
            'related_title',
            array(
                'label'     => esc_html__('Heading', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Related Atelier Creations', 'kinboni-core'),
                'condition' => array('show_related' => 'yes'),
            )
        );

        $this->add_control(
            'related_count',
            array(
                'label'     => esc_html__('Number of Related Products', 'kinboni-core'),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 2,
                'max'       => 12,
                'step'      => 1,
                'default'   => 4,
                'condition' => array('show_related' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // CONTENT TAB: RECENTLY VIEWED CREATIONS
        // ==========================================
        $this->start_controls_section(
            'section_recent',
            array(
                'label' => esc_html__('Recently Viewed Creations', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_recent',
            array(
                'label'        => esc_html__('Show Recently Viewed History', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'recent_subtitle',
            array(
                'label'     => esc_html__('Subtitle', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Your Browsing History', 'kinboni-core'),
                'condition' => array('show_recent' => 'yes'),
            )
        );

        $this->add_control(
            'recent_title',
            array(
                'label'     => esc_html__('Heading', 'kinboni-core'),
                'type'      => Controls_Manager::TEXT,
                'label_block' => true,
                'default'   => esc_html__('Recently Viewed Creations', 'kinboni-core'),
                'condition' => array('show_recent' => 'yes'),
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: AMBIENT GLOW & ARCHITECTURAL SHAPES
        // ==========================================
        $this->start_controls_section(
            'section_style_ambient',
            array(
                'label' => esc_html__('Ambient Glow & Architectural Shapes', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'show_ambient_glow',
            array(
                'label'        => esc_html__('Show Ambient Glow', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'show_arch_shapes',
            array(
                'label'        => esc_html__('Show Geometric Arch Shapes', 'kinboni-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'kinboni-core'),
                'label_off'    => esc_html__('No', 'kinboni-core'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: TYPOGRAPHY
        // ==========================================
        $this->start_controls_section(
            'section_style_typography',
            array(
                'label' => esc_html__('Typography & Colors', 'kinboni-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'label'    => esc_html__('Product Title Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} .product_title',
            )
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name'     => 'price_typography',
                'label'    => esc_html__('Price Typography', 'kinboni-core'),
                'selector' => '{{WRAPPER}} .price, {{WRAPPER}} .kinboni-single-price',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Resolve the target WooCommerce Product
     */
    private function get_target_product($settings) {
        if (!class_exists('WooCommerce')) {
            return null;
        }

        if ('manual' === $settings['source_mode'] && !empty($settings['product_id'])) {
            $product = wc_get_product($settings['product_id']);
            if ($product) {
                return $product;
            }
        }

        global $product;
        if (is_a($product, 'WC_Product')) {
            return $product;
        }

        $post_id = get_the_ID();
        if ($post_id && 'product' === get_post_type($post_id)) {
            $product = wc_get_product($post_id);
            if ($product) {
                return $product;
            }
        }

        // Fallback to first published product
        $recent = wc_get_products(array(
            'limit'   => 1,
            'status'  => 'publish',
            'orderby' => 'date',
            'order'   => 'DESC',
        ));

        if (!empty($recent)) {
            return $recent[0];
        }

        return null;
    }

    /**
     * Render product card helper for Related & Recently Viewed grids
     */
    private function render_luxury_card($p) {
        $pid          = $p->get_id();
        $pname        = $p->get_name();
        $plink        = $p->get_permalink();
        $price        = (float) $p->get_price();
        $reg_price    = (float) $p->get_regular_price();
        $is_sale      = $p->is_on_sale();
        $discount_pct = ($is_sale && $reg_price > $price) ? round((($reg_price - $price) / $reg_price) * 100) : 0;
        $rating       = (float) $p->get_average_rating();
        $reviews      = (int) $p->get_review_count();

        $main_img_id  = $p->get_image_id();
        $main_img     = $main_img_id ? wp_get_attachment_image_url($main_img_id, 'large') : wc_placeholder_img_src('large');
        $gallery_ids  = $p->get_gallery_image_ids();
        $hover_img    = !empty($gallery_ids) ? wp_get_attachment_image_url($gallery_ids[0], 'large') : $main_img;

        $terms     = get_the_terms($pid, 'product_cat');
        $cat_label = ($terms && !is_wp_error($terms)) ? $terms[0]->name : esc_html__('Atelier Collection', 'kinboni-core');
        $badge     = $p->is_featured() ? 'SIGNATURE' : ($is_sale ? 'PRIVILEGE' : '');
        ?>
        <div class="kinboni-product-card group relative flex flex-col bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-luxury dark:hover:shadow-luxury-dark hover:border-gold-500/40 dark:hover:border-gold-500/40 transition-all duration-500 cursor-pointer" data-product-id="<?php echo esc_attr($pid); ?>">
          <div class="relative w-full aspect-[4/5] bg-stone-100 dark:bg-stone-950 overflow-hidden cursor-pointer" onclick="if (!event.target.closest('button, a')) window.location.href='<?php echo esc_url($plink); ?>'">
            <img loading="lazy" src="<?php echo esc_url($main_img); ?>" 
              alt="<?php echo esc_attr($pname); ?>" 
              loading="lazy" 
              class="w-full h-full object-cover object-center transition-all duration-700 ease-out group-hover:scale-105"
              onerror="this.src='<?php echo esc_url(wc_placeholder_img_src('large')); ?>';this.onerror=null;"
            />
            <?php if (!empty($hover_img) && $hover_img !== $main_img) : ?>
            <img loading="lazy" src="<?php echo esc_url($hover_img); ?>" 
              alt="<?php echo esc_attr($pname); ?> alternate angle" 
              loading="lazy" 
              class="absolute inset-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-700 ease-out group-hover:opacity-100 group-hover:scale-105 pointer-events-none"
            />
            <?php endif; ?>

            <?php if (!empty($badge) || $discount_pct > 0) : ?>
            <div class="absolute top-3 left-3 right-14 flex flex-row flex-wrap items-center gap-1.5 z-10 pointer-events-none">
              <?php if (!empty($badge)) : ?>
              <span class="w-fit flex-shrink-0 inline-flex items-center px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em] rounded-full bg-stone-950/90 text-stone-100 dark:bg-white/95 dark:text-stone-950 shadow-md backdrop-blur-sm border border-white/10 dark:border-stone-900/10">
                <?php echo esc_html($badge); ?>
              </span>
              <?php endif; ?>
              <?php if ($discount_pct > 0) : ?>
              <span class="w-fit flex-shrink-0 inline-flex items-center px-2.5 py-1 text-[11px] font-bold tracking-wider rounded-full bg-rose-600 text-white shadow-md">
                -<?php echo esc_html($discount_pct); ?>%
              </span>
              <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="absolute top-3 right-3 z-10 flex flex-col gap-2">
              <button 
                type="button" 
                class="btn-toggle-wishlist w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-stone-700 dark:text-stone-200 hover:text-rose-500 hover:border-rose-400 dark:hover:border-rose-400 hover:scale-110 active:scale-90 transition-all shadow-md group/heart cursor-pointer" 
                data-product-id="<?php echo esc_attr($pid); ?>" 
                aria-label="<?php echo esc_attr(sprintf(__('Save %s to Wishlist', 'kinboni-core'), $pname)); ?>"
              >
                <svg class="w-4 h-4 transition-transform group-hover/heart:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
              <button 
                type="button" 
                class="btn-open-quickview w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-stone-700 dark:text-stone-200 hover:bg-gold-400 hover:text-stone-950 hover:border-gold-400 dark:hover:bg-gold-400 dark:hover:text-stone-950 hover:scale-110 active:scale-90 transition-all shadow-md cursor-pointer"
                data-product-id="<?php echo esc_attr($pid); ?>"
                aria-label="<?php echo esc_attr(sprintf(__('Quick View %s', 'kinboni-core'), $pname)); ?>"
              >
                <i data-lucide="eye" class="w-4 h-4"></i>
              </button>
            </div>
          </div>

          <div class="p-4 sm:p-5 flex flex-col flex-grow">
            <div class="flex items-center justify-between gap-2 mb-2 min-w-0">
              <span class="text-xs uppercase tracking-wider font-bold text-gold-700 dark:text-gold-400 truncate">
                <?php echo esc_html($cat_label); ?>
              </span>
              <?php if ($rating > 0) : ?>
              <div class="flex items-center gap-1 text-[11px] font-semibold text-stone-700 dark:text-stone-300 flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span><?php echo esc_html(number_format($rating, 1)); ?></span>
                <span class="text-stone-400 dark:text-stone-500 font-normal">(<?php echo esc_html($reviews); ?>)</span>
              </div>
              <?php endif; ?>
            </div>

            <h3 class="kinboni-product-card-title font-serif text-xl sm:text-2xl font-bold text-stone-900 dark:text-white leading-snug truncate mb-2 group-hover:text-gold-600 dark:group-hover:text-gold-400 transition-colors" title="<?php echo esc_attr($pname); ?>">
              <a href="<?php echo esc_url($plink); ?>">
                <?php echo esc_html($pname); ?>
              </a>
            </h3>

            <div class="flex items-baseline gap-2 mb-3">
              <span class="font-serif text-xl sm:text-2xl font-bold text-stone-950 dark:text-white">
                ৳<?php echo esc_html(number_format($price)); ?>
              </span>
              <?php if ($reg_price > $price) : ?>
              <span class="text-xs text-stone-400 dark:text-stone-500 line-through font-normal">
                ৳<?php echo esc_html(number_format($reg_price)); ?>
              </span>
              <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 ml-auto">
                Save ৳<?php echo esc_html(number_format($reg_price - $price)); ?>
              </span>
              <?php endif; ?>
            </div>

            <div class="flex items-center gap-2 mt-auto pt-3 border-t border-stone-100 dark:border-stone-800/80">
              <a 
                href="<?php echo esc_url($p->add_to_cart_url() ?: $plink); ?>" 
                data-quantity="1"
                data-product_id="<?php echo esc_attr($pid); ?>"
                class="ajax_add_to_cart add_to_cart_button flex-1 py-2.5 px-3 rounded-xl bg-stone-200/80 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-stone-900 dark:text-stone-100 text-xs sm:text-[13px] font-bold transition-all duration-200 flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer" 
              >
                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 flex-shrink-0 text-stone-600 dark:text-stone-300"></i>
                <span class="whitespace-nowrap"><?php esc_html_e('Add to Bag', 'kinboni-core'); ?></span>
              </a>

              <button 
                type="button" 
                class="btn-trigger-quick-order flex-1 py-2.5 px-3 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs sm:text-[13px] font-bold transition-all duration-200 flex items-center justify-center gap-1.5 shadow-sm hover:shadow-gold-500/25 active:scale-95 cursor-pointer" 
                data-product-id="<?php echo esc_attr($pid); ?>"
                data-product-name="<?php echo esc_attr($pname); ?>"
                data-product-price="<?php echo esc_attr($price); ?>"
                data-product-image="<?php echo esc_url($main_img); ?>"
              >
                <i data-lucide="zap" class="w-3.5 h-3.5 fill-stone-950 flex-shrink-0"></i>
                <span class="whitespace-nowrap"><?php esc_html_e('Order Now', 'kinboni-core'); ?></span>
              </button>
            </div>
          </div>
        </div>
        <?php
    }

    /**
     * Render the Widget Output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $product  = $this->get_target_product($settings);

        if (!$product) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                ?>
                <div class="p-8 text-center bg-stone-100 dark:bg-stone-900 rounded-2xl border border-dashed border-stone-300 dark:border-stone-700 text-stone-600 dark:text-stone-300">
                  <p class="font-bold text-base mb-1"><?php esc_html_e('No WooCommerce Product Found', 'kinboni-core'); ?></p>
                  <p class="text-xs"><?php esc_html_e('Please publish at least one WooCommerce product or select a specific product in the widget panel.', 'kinboni-core'); ?></p>
                </div>
                <?php
            }
            return;
        }

        $product_id   = $product->get_id();
        $product_name = $product->get_name();
        $product_sku  = $product->get_sku() ? $product->get_sku() : sprintf('KB-%s-0%d', date('Y'), $product_id);
        $rating_count = (int) $product->get_rating_count();
        $review_count = (int) $product->get_review_count();
        $average      = (float) $product->get_average_rating();
        $price        = (float) $product->get_price();
        $regular_price= (float) $product->get_regular_price();
        $is_on_sale   = $product->is_on_sale();
        $discount_pct = ($is_on_sale && $regular_price > $price) ? round((($regular_price - $price) / $regular_price) * 100) : 0;
        $savings_amt  = ($is_on_sale && $regular_price > $price) ? ($regular_price - $price) : 0;

        // Main Image & Gallery
        $main_image_id = $product->get_image_id();
        $main_image    = $main_image_id ? wp_get_attachment_image_url($main_image_id, 'full') : wc_placeholder_img_src('full');
        $gallery_ids   = $product->get_gallery_image_ids();

        // Category Info
        $terms         = get_the_terms($product_id, 'product_cat');
        $category_name = ($terms && !is_wp_error($terms)) ? $terms[0]->name : esc_html__('Atelier Collection', 'kinboni-core');
        $category_link = ($terms && !is_wp_error($terms)) ? get_term_link($terms[0]) : wc_get_page_permalink('shop');

        // Prev / Next Navigation
        $prev_product = get_adjacent_post(true, '', true, 'product_cat');
        $next_product = get_adjacent_post(true, '', false, 'product_cat');

        $show_glow     = ('yes' === $settings['show_ambient_glow']);
        $show_shapes   = ('yes' === $settings['show_arch_shapes']);
        $show_badges   = ('yes' === $settings['show_badges']);
        $show_crumbs   = ('yes' === $settings['show_breadcrumb']);
        $show_switcher = ('yes' === $settings['show_switcher']);
        $show_monogram = ('yes' === $settings['show_monogram']);
        $show_emi      = ('yes' === $settings['show_emi']);
        $show_qty      = ('yes' === $settings['show_qty']);
        $show_tabs     = ('yes' === $settings['show_tabs']);
        $show_related  = ('yes' === $settings['show_related']);
        $show_recent   = ('yes' === $settings['show_recent']) && (get_option('kinboni_recently_viewed_enabled', 'yes') === 'yes');
        ?>
        <main id="page-view-product" class="wc-page-view relative overflow-hidden py-8 sm:py-12 min-h-[75vh]" data-product-id="<?php echo esc_attr($product_id); ?>">

          <?php if ($show_glow) : ?>
          <div class="absolute inset-0 bg-gradient-to-b from-gold-200/35 via-transparent to-transparent dark:from-gold-900/15 pointer-events-none"></div>
          <div class="absolute inset-0 opacity-35 dark:opacity-20 bg-[radial-gradient(#C5A880_1px,transparent_1px)] dark:bg-[radial-gradient(#D4AF37_1px,transparent_1px)] [background-size:26px_26px] pointer-events-none"></div>
          <div class="absolute -top-24 right-1/4 w-96 h-96 bg-gold-300/25 dark:bg-gold-500/15 rounded-full blur-3xl pointer-events-none"></div>
          <div class="absolute top-1/2 -left-24 w-80 h-80 bg-amber-300/20 dark:bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
          <?php endif; ?>

          <?php if ($show_shapes) : ?>
          <div class="block absolute top-14 right-8 w-72 h-72 rounded-full border-2 border-gold-600/25 dark:border-gold-500/15 bg-gold-200/10 dark:bg-gold-500/[0.02] pointer-events-none"></div>
          <div class="block absolute top-24 right-16 w-52 h-52 rounded-full border border-dashed border-gold-700/30 dark:border-gold-400/20 pointer-events-none"></div>
          <div class="block absolute bottom-14 left-8 w-80 h-80 rounded-t-[999px] border-2 border-gold-600/20 dark:border-gold-500/12 bg-gold-100/15 dark:bg-transparent pointer-events-none"></div>
          <div class="block absolute top-1/3 left-1/2 -translate-x-1/2 w-[520px] h-[520px] rounded-full border border-gold-600/15 dark:border-gold-500/10 pointer-events-none"></div>
          <?php endif; ?>

          <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <?php if ($show_crumbs || $show_switcher) : ?>
            <div class="mb-8 pb-4 border-b border-stone-200 dark:border-stone-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <?php if ($show_crumbs) : ?>
              <nav class="woocommerce-breadcrumb flex items-center flex-wrap gap-2 text-xs text-stone-600 dark:text-stone-300" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                  <?php echo esc_html(!empty($settings['home_label']) ? $settings['home_label'] : __('Home', 'kinboni-core')); ?>
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                  <?php echo esc_html(!empty($settings['shop_label']) ? $settings['shop_label'] : __('Shop', 'kinboni-core')); ?>
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="<?php echo esc_url($category_link); ?>" id="single-product-breadcrumb-cat" class="hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                  <?php echo esc_html($category_name); ?>
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span id="single-product-breadcrumb-title" class="text-stone-900 dark:text-white font-semibold truncate max-w-[200px] sm:max-w-none">
                  <?php echo esc_html($product_name); ?>
                </span>
              </nav>
              <?php endif; ?>

              <?php if ($show_switcher) : ?>
              <div class="flex items-center gap-2 self-end sm:self-auto">
                <?php if (!empty($prev_product)) : ?>
                <a href="<?php echo esc_url(get_permalink($prev_product->ID)); ?>" class="px-3 py-1.5 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-xs font-medium text-stone-700 dark:text-stone-300 hover:border-gold-500 transition-colors flex items-center gap-1 cursor-pointer" title="<?php echo esc_attr($prev_product->post_title); ?>">
                  <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i> <span><?php esc_html_e('Prev', 'kinboni-core'); ?></span>
                </a>
                <?php endif; ?>
                <?php if (!empty($next_product)) : ?>
                <a href="<?php echo esc_url(get_permalink($next_product->ID)); ?>" class="px-3 py-1.5 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 text-xs font-medium text-stone-700 dark:text-stone-300 hover:border-gold-500 transition-colors flex items-center gap-1 cursor-pointer" title="<?php echo esc_attr($next_product->post_title); ?>">
                  <span><?php esc_html_e('Next', 'kinboni-core'); ?></span> <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>
            <?php endif; ?>

            <div id="single-product-main-container" class="product">
              <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start" id="product-<?php echo esc_attr($product_id); ?>">

                <div class="lg:col-span-6 space-y-4">
                  <div class="relative aspect-[4/5] rounded-3xl overflow-hidden bg-stone-100 dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 shadow-luxury">
                    <img loading="lazy" id="sp-main-gallery-image" 
                      src="<?php echo esc_url($main_image); ?>" 
                      alt="<?php echo esc_attr($product_name); ?>" 
                      class="w-full h-full object-cover object-center transition-all duration-500"
                    />

                    <?php if ($show_badges) : ?>
                    <div class="absolute top-4 left-4 flex flex-col gap-2 z-10 pointer-events-none">
                      <?php if ($product->is_featured()) : ?>
                      <span class="px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] rounded-full bg-stone-950/90 text-white shadow-md">
                        <?php echo esc_html(!empty($settings['featured_badge_text']) ? $settings['featured_badge_text'] : __('SIGNATURE', 'kinboni-core')); ?>
                      </span>
                      <?php endif; ?>

                      <?php if ($discount_pct > 0) : ?>
                      <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-600 text-white shadow-md">
                        -<?php echo esc_html($discount_pct); ?>% OFF
                      </span>
                      <?php endif; ?>
                    </div>
                    <?php endif; ?>
                  </div>

                  <?php if ('yes' === $settings['show_thumbnails']) : ?>
                  <div class="grid grid-cols-4 sm:grid-cols-5 gap-3" id="sp-gallery-thumbnails">
                    <button 
                      type="button" 
                      onclick="window.SingleProductController ? window.SingleProductController.switchMainImage('<?php echo esc_url($main_image); ?>', this) : (document.getElementById('sp-main-gallery-image').src='<?php echo esc_url($main_image); ?>')" 
                      class="sp-gallery-thumb aspect-square rounded-2xl overflow-hidden border-2 border-gold-500 ring-2 ring-gold-500/30 transition-all cursor-pointer"
                    >
                      <img loading="lazy" src="<?php echo esc_url($main_image); ?>" alt="<?php echo esc_attr($product_name); ?> view 1" class="w-full h-full object-cover" />
                    </button>

                    <?php 
                    if (!empty($gallery_ids)) :
                        foreach ($gallery_ids as $g_id) :
                            $thumb_url = wp_get_attachment_image_url($g_id, 'full');
                            if (!$thumb_url) continue;
                            ?>
                            <button 
                              type="button" 
                              onclick="window.SingleProductController ? window.SingleProductController.switchMainImage('<?php echo esc_url($thumb_url); ?>', this) : (document.getElementById('sp-main-gallery-image').src='<?php echo esc_url($thumb_url); ?>')" 
                              class="sp-gallery-thumb aspect-square rounded-2xl overflow-hidden border-2 border-stone-200 dark:border-stone-800 opacity-70 hover:opacity-100 transition-all cursor-pointer"
                            >
                              <img loading="lazy" src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($product_name); ?> alternate view" class="w-full h-full object-cover" />
                            </button>
                            <?php
                        endforeach;
                    else :
                        // If product only has 1 image, provide elegant alternate zoom angle
                        ?>
                        <button 
                          type="button" 
                          onclick="window.SingleProductController ? window.SingleProductController.switchMainImage('<?php echo esc_url($main_image); ?>', this) : (document.getElementById('sp-main-gallery-image').src='<?php echo esc_url($main_image); ?>')" 
                          class="sp-gallery-thumb aspect-square rounded-2xl overflow-hidden border-2 border-stone-200 dark:border-stone-800 opacity-70 hover:opacity-100 transition-all cursor-pointer"
                        >
                          <img loading="lazy" src="<?php echo esc_url($main_image); ?>" alt="<?php echo esc_attr($product_name); ?> detail" class="w-full h-full object-cover scale-125" />
                        </button>
                    <?php endif; ?>
                  </div>
                  <?php endif; ?>
                </div>

                <div class="lg:col-span-6 summary entry-summary bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 p-6 sm:p-8 lg:p-10 shadow-sm space-y-6">

                  <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400">
                      <?php echo esc_html($category_name); ?>
                    </span>
                    <div class="flex items-center gap-2">
                      <?php if ($rating_count > 0 || $average > 0) : ?>
                      <div class="flex items-center gap-1 text-xs text-stone-700 dark:text-stone-300 font-semibold" title="<?php echo esc_attr(sprintf(__('%s out of 5 stars from %d verified reviews', 'kinboni-core'), number_format($average, 1), $review_count)); ?>">
                        <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span><?php echo esc_html(number_format($average, 1)); ?></span>
                        <span class="text-stone-400 dark:text-stone-500 font-normal">(<?php echo esc_html($review_count); ?>)</span>
                      </div>
                      <?php endif; ?>
                      <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                        &bull; <?php echo $product->is_in_stock() ? esc_html__('In Stock', 'kinboni-core') : esc_html__('Out of Stock', 'kinboni-core'); ?>
                      </span>
                    </div>
                  </div>

                  <h1 class="product_title entry-title font-serif text-3xl sm:text-4xl font-medium text-stone-900 dark:text-white leading-tight">
                    <?php echo esc_html($product_name); ?>
                  </h1>

                  <div class="flex items-baseline flex-wrap gap-3 pb-5 border-b border-stone-200 dark:border-stone-800">
                    <div class="price kinboni-single-price font-serif text-3xl font-bold text-stone-900 dark:text-white">
                      <?php echo $product->get_price_html(); ?>
                    </div>
                    <?php if ($savings_amt > 0) : ?>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
                      <?php printf(esc_html__('Save %s', 'kinboni-core'), wc_price($savings_amt)); ?>
                    </span>
                    <?php endif; ?>
                  </div>

                  <div class="woocommerce-product-details__short-description text-sm text-stone-600 dark:text-stone-300 leading-relaxed font-normal">
                    <?php 
                    $short_desc = $product->get_short_description();
                    if (!empty($short_desc)) {
                        echo apply_filters('woocommerce_short_description', $short_desc);
                    } else {
                        echo wpautop(wp_trim_words($product->get_description(), 30));
                    }
                    ?>
                  </div>

                  <?php if ('yes' === $settings['show_highlights']) : ?>
                  <div>
                    <div class="flex items-center justify-between mb-3">
                      <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 dark:text-stone-200">
                        <?php echo esc_html(!empty($settings['highlights_title']) ? $settings['highlights_title'] : __('Master Artisan Highlights', 'kinboni-core')); ?>
                      </h4>
                      <?php if ('yes' === $settings['show_size_guide']) : ?>
                      <button type="button" onclick="window.SingleProductController ? window.SingleProductController.switchTab('specs') : null; document.querySelector('.woocommerce-tabs')?.scrollIntoView({behavior:'smooth'});" class="text-[11px] font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400 hover:underline flex items-center gap-1 cursor-pointer">
                        <i data-lucide="ruler" class="w-3.5 h-3.5"></i>
                        <span><?php esc_html_e('Size & Fit Guide', 'kinboni-core'); ?></span>
                      </button>
                      <?php endif; ?>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                      <?php 
                      if (!empty($settings['highlights_list'])) :
                          foreach ($settings['highlights_list'] as $item) :
                              if (empty($item['text'])) continue;
                              ?>
                              <div class="flex items-center gap-2 p-2.5 rounded-xl bg-stone-50 dark:bg-stone-950 border border-stone-200/70 dark:border-stone-800 text-xs text-stone-700 dark:text-stone-300">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-gold-500 flex-shrink-0"></i>
                                <span><?php echo esc_html($item['text']); ?></span>
                              </div>
                              <?php
                          endforeach;
                      endif;
                      ?>
                    </div>
                  </div>
                  <?php endif; ?>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div>
                      <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5"><?php esc_html_e('Edition Finish', 'kinboni-core'); ?></label>
                      <select class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs font-medium text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500">
                        <option><?php esc_html_e('Atelier Signature Finish', 'kinboni-core'); ?></option>
                        <option><?php esc_html_e('Collector\'s Brushed Gold Edition', 'kinboni-core'); ?></option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 mb-1.5"><?php esc_html_e('Presentation Packaging', 'kinboni-core'); ?></label>
                      <select class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-950 text-xs font-medium text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500">
                        <option><?php esc_html_e('Wooden Lacquer Box (Complimentary)', 'kinboni-core'); ?></option>
                        <option><?php esc_html_e('Gift Wrap + Wax-Sealed Note (Free)', 'kinboni-core'); ?></option>
                      </select>
                    </div>
                  </div>

                  <?php if ($show_monogram) : ?>
                  <div class="p-4 rounded-2xl bg-gold-50/70 dark:bg-stone-950/90 border border-gold-500/30 space-y-3">
                    <div class="flex items-center justify-between">
                      <span class="text-[11px] font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400 flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span><?php echo esc_html(!empty($settings['monogram_title']) ? $settings['monogram_title'] : __('Complimentary Bespoke Monogram / Engraving', 'kinboni-core')); ?></span>
                      </span>
                      <?php if ($show_emi && $price > 0) : ?>
                      <button type="button" class="text-[11px] font-bold text-stone-700 dark:text-stone-300 hover:text-gold-600 underline flex items-center gap-1 cursor-pointer">
                        <i data-lucide="calculator" class="w-3 h-3 text-gold-500"></i>
                        <span><?php printf(esc_html__('0%% EMI from %s/mo', 'kinboni-core'), wc_price(round($price / 12))); ?></span>
                      </button>
                      <?php endif; ?>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center">
                      <div class="sm:col-span-5">
                        <input 
                          type="text" 
                          id="sp-monogram-input" 
                          maxlength="10" 
                          placeholder="<?php esc_attr_e('Initials (e.g. T.R. or 1998)', 'kinboni-core'); ?>" 
                          oninput="window.SingleProductController ? window.SingleProductController.updateMonogram(this.value) : null"
                          class="w-full px-3 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs uppercase tracking-widest text-stone-900 dark:text-white focus:outline-none focus:border-gold-500"
                        />
                      </div>
                      <div class="sm:col-span-3">
                        <select id="sp-monogram-font" onchange="window.SingleProductController ? window.SingleProductController.updateMonogramFont(this.value) : null" class="w-full px-2.5 py-2 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs text-stone-800 dark:text-stone-200">
                          <option value="serif"><?php esc_html_e('Roman Serif', 'kinboni-core'); ?></option>
                          <option value="italic"><?php esc_html_e('Atelier Script', 'kinboni-core'); ?></option>
                        </select>
                      </div>
                      <div class="sm:col-span-4 px-3 py-2 rounded-xl bg-stone-950 text-center border border-gold-500/40">
                        <span class="text-[10px] uppercase tracking-widest text-stone-400 block leading-none mb-0.5"><?php esc_html_e('Live Foil Preview', 'kinboni-core'); ?></span>
                        <span id="sp-monogram-preview" class="font-serif text-sm font-bold tracking-[0.25em] text-gold-400">K • A</span>
                      </div>
                    </div>
                  </div>
                  <?php endif; ?>

                  <div class="pt-4 space-y-3">
                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                      <?php if ($show_qty) : ?>
                      <div class="flex items-center border border-stone-300 dark:border-stone-700 rounded-xl bg-stone-50 dark:bg-stone-950 px-2 py-1.5 flex-shrink-0">
                        <button type="button" onclick="window.SingleProductController ? window.SingleProductController.changeQty(-1) : null" class="w-9 h-9 flex items-center justify-center text-stone-600 dark:text-stone-300 hover:text-gold-500 cursor-pointer" aria-label="<?php esc_attr_e('Decrease quantity', 'kinboni-core'); ?>">
                          <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                        <span id="sp-qty-display" class="w-10 text-center font-bold text-sm text-stone-900 dark:text-white">1</span>
                        <button type="button" onclick="window.SingleProductController ? window.SingleProductController.changeQty(1) : null" class="w-9 h-9 flex items-center justify-center text-stone-600 dark:text-stone-300 hover:text-gold-500 cursor-pointer" aria-label="<?php esc_attr_e('Increase quantity', 'kinboni-core'); ?>">
                          <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                      </div>
                      <?php endif; ?>

                      <button 
                        type="button" 
                        class="single_add_to_cart_button ajax_add_to_cart add_to_cart_button flex-grow py-3.5 px-6 rounded-xl bg-stone-900 text-white dark:bg-white dark:text-stone-950 hover:bg-gold-500 hover:text-stone-950 font-bold text-xs uppercase tracking-widest transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer active:scale-95"
                        data-product_id="<?php echo esc_attr($product_id); ?>"
                        data-quantity="1"
                        id="sp-add-to-cart-btn"
                      >
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        <span><?php echo esc_html(!empty($settings['btn_cart_text']) ? $settings['btn_cart_text'] : __('Add to Shopping Bag', 'kinboni-core')); ?></span>
                      </button>

                      <?php if ('yes' === $settings['show_wishlist']) : ?>
                      <button 
                        type="button" 
                        class="btn-toggle-wishlist p-3.5 rounded-xl border border-stone-300 dark:border-stone-700 hover:border-rose-500 text-stone-700 dark:text-stone-200 transition-colors cursor-pointer group/heart flex-shrink-0"
                        data-product-id="<?php echo esc_attr($product_id); ?>"
                        aria-label="<?php esc_attr_e('Save to Wishlist', 'kinboni-core'); ?>"
                      >
                        <svg class="w-5 h-5 transition-transform group-hover/heart:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                      </button>
                      <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                      <?php if ('yes' === $settings['show_quick_order']) : ?>
                      <button 
                        type="button" 
                        class="btn-trigger-quick-order w-full py-3.5 px-4 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 font-bold text-xs uppercase tracking-widest shadow-md hover:shadow-gold-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-95"
                        data-product-id="<?php echo esc_attr($product_id); ?>"
                        data-product-name="<?php echo esc_attr($product_name); ?>"
                        data-product-price="<?php echo esc_attr($price); ?>"
                        data-product-image="<?php echo esc_url($main_image); ?>"
                        id="sp-quick-order-btn"
                      >
                        <i data-lucide="zap" class="w-4 h-4 fill-stone-950"></i>
                        <span><?php echo esc_html(!empty($settings['btn_cod_text']) ? $settings['btn_cod_text'] : __('Quick Order (3-Field COD)', 'kinboni-core')); ?></span>
                      </button>
                      <?php endif; ?>

                      <?php if ('yes' === $settings['show_full_checkout']) : ?>
                      <a 
                        href="<?php echo esc_url(wc_get_checkout_url()); ?>" 
                        class="w-full py-3.5 px-4 rounded-xl border border-gold-500/60 hover:bg-gold-500/10 text-stone-900 dark:text-stone-100 font-bold text-xs uppercase tracking-widest transition-all flex items-center justify-center gap-2 cursor-pointer text-center"
                      >
                        <i data-lucide="credit-card" class="w-4 h-4 text-gold-500"></i>
                        <span><?php echo esc_html(!empty($settings['btn_checkout_text']) ? $settings['btn_checkout_text'] : __('Full Checkout Page', 'kinboni-core')); ?></span>
                      </a>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="product_meta pt-5 border-t border-stone-200 dark:border-stone-800 space-y-1.5 text-xs text-stone-600 dark:text-stone-300">
                    <p><strong class="text-stone-800 dark:text-stone-200"><?php esc_html_e('SKU:', 'kinboni-core'); ?></strong> <span class="font-mono"><?php echo esc_html($product_sku); ?></span></p>
                    <p><strong class="text-stone-800 dark:text-stone-200"><?php esc_html_e('Category:', 'kinboni-core'); ?></strong> <a href="<?php echo esc_url($category_link); ?>" class="text-gold-700 dark:text-gold-400 hover:underline"><?php echo esc_html($category_name); ?></a></p>
                    <?php if (!empty($settings['dispatch_text'])) : ?>
                    <p><strong class="text-stone-800 dark:text-stone-200"><?php esc_html_e('Dispatch:', 'kinboni-core'); ?></strong> <?php echo esc_html($settings['dispatch_text']); ?></p>
                    <?php endif; ?>
                  </div>

                  <?php if ('yes' === $settings['show_trust_badges'] && !empty($settings['trust_badges_list'])) : ?>
                  <div class="pt-6 border-t border-stone-200 dark:border-stone-800 grid grid-cols-2 gap-4 text-xs text-stone-600 dark:text-stone-400">
                    <?php foreach ($settings['trust_badges_list'] as $b_idx => $badge) :
                        $icon = !empty($badge['icon']) ? sanitize_html_class($badge['icon']) : 'shield-check';
                        $b_text = !empty($badge['text']) ? $badge['text'] : '';
                        if (empty($b_text) && class_exists('Kinboni_Content')) {
                            $b_text = Kinboni_Content::get('trust_badge_' . ($b_idx + 1), '');
                        }
                        ?>
                        <div class="flex items-center gap-2.5">
                          <i data-lucide="<?php echo esc_attr($icon); ?>" class="w-4 h-4 text-gold-500 flex-shrink-0"></i>
                          <span><?php echo esc_html($b_text); ?></span>
                        </div>
                    <?php endforeach; ?>
                  </div>
                  <?php endif; ?>

                </div>
              </div>
            </div>

            <?php if ($show_tabs) : ?>
            <div class="woocommerce-tabs wc-tabs-wrapper mt-14 bg-white/95 dark:bg-stone-900/90 backdrop-blur-md rounded-3xl border border-stone-200/80 dark:border-stone-800 p-6 sm:p-10 shadow-sm">
              
              <div class="flex items-center gap-2 sm:gap-4 border-b border-stone-200 dark:border-stone-800 pb-4 mb-8 overflow-x-auto" role="tablist">
                <button 
                  type="button" 
                  onclick="window.SingleProductController ? window.SingleProductController.switchTab('description') : null" 
                  data-sp-tab="description" 
                  class="sp-tab-btn px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-widest transition-all bg-stone-900 text-white dark:bg-gold-400 dark:text-stone-950 cursor-pointer"
                >
                  <?php echo esc_html(!empty($settings['tab_desc_title']) ? $settings['tab_desc_title'] : __('Atelier Narrative & Description', 'kinboni-core')); ?>
                </button>
                <button 
                  type="button" 
                  onclick="window.SingleProductController ? window.SingleProductController.switchTab('specs') : null" 
                  data-sp-tab="specs" 
                  class="sp-tab-btn px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-widest transition-all text-stone-600 dark:text-stone-400 hover:text-gold-600 cursor-pointer"
                >
                  <?php echo esc_html(!empty($settings['tab_specs_title']) ? $settings['tab_specs_title'] : __('Additional Specifications', 'kinboni-core')); ?>
                </button>
                <button 
                  type="button" 
                  onclick="window.SingleProductController ? window.SingleProductController.switchTab('reviews') : null" 
                  data-sp-tab="reviews" 
                  id="sp-tab-reviews-btn" 
                  class="sp-tab-btn px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-widest transition-all text-stone-600 dark:text-stone-400 hover:text-gold-600 cursor-pointer"
                >
                  <?php echo esc_html(!empty($settings['tab_reviews_title']) ? $settings['tab_reviews_title'] : __('Patron Reviews', 'kinboni-core')); ?> (<?php echo esc_html($review_count); ?>)
                </button>
              </div>

              <div id="sp-tab-panel-description" class="sp-tab-panel space-y-6 text-sm text-stone-600 dark:text-stone-300 leading-relaxed font-normal">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                  <div class="md:col-span-7 space-y-4">
                    <h3 class="font-serif text-2xl font-medium text-stone-900 dark:text-white">
                      <?php printf(esc_html__('Crafted for Permanence: %s', 'kinboni-core'), esc_html($product_name)); ?>
                    </h3>
                    <div class="text-stone-600 dark:text-stone-300 leading-relaxed space-y-3 font-normal">
                      <?php 
                      $full_desc = $product->get_description();
                      if (!empty($full_desc)) {
                          echo wp_kses_post(wpautop($full_desc));
                      } else {
                          echo '<p>' . esc_html__('Each creation undergoes a rigorous 14-point inspection at our atelier before being sealed in its presentation box. Designed for collectors and patrons who appreciate quiet luxury, enduring materials, and timeless proportions.', 'kinboni-core') . '</p>';
                      }
                      ?>
                    </div>
                  </div>
                  <div class="md:col-span-5 p-6 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 space-y-3 text-xs">
                    <h4 class="font-bold uppercase tracking-widest text-gold-700 dark:text-gold-400">
                      <?php echo esc_html(!empty($settings['included_box_title']) ? $settings['included_box_title'] : __('Included in the Box', 'kinboni-core')); ?>
                    </h4>
                    <ul class="space-y-2 text-stone-700 dark:text-stone-300">
                      <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-gold-500"></i><span><?php echo esc_html($product_name); ?></span></li>
                      <?php 
                      if (!empty($settings['included_box_items'])) :
                          foreach ($settings['included_box_items'] as $item) :
                              if (empty($item['item_text'])) continue;
                              ?>
                              <li class="flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-gold-500"></i>
                                <span><?php echo esc_html($item['item_text']); ?></span>
                              </li>
                              <?php
                          endforeach;
                      endif;
                      ?>
                    </ul>
                  </div>
                </div>
              </div>

              <div id="sp-tab-panel-specs" class="sp-tab-panel hidden">
                <div class="overflow-x-auto">
                  <table class="woocommerce-product-attributes shop_attributes w-full text-left border-collapse text-xs sm:text-sm">
                    <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                      <tr>
                        <th class="py-3.5 px-4 font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 w-1/3 bg-stone-50/60 dark:bg-stone-950/60"><?php esc_html_e('Creation Name', 'kinboni-core'); ?></th>
                        <td class="py-3.5 px-4 text-stone-600 dark:text-stone-400"><?php echo esc_html($product_name); ?></td>
                      </tr>
                      <tr>
                        <th class="py-3.5 px-4 font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 bg-stone-50/60 dark:bg-stone-950/60"><?php esc_html_e('Discipline', 'kinboni-core'); ?></th>
                        <td class="py-3.5 px-4 text-stone-600 dark:text-stone-400"><?php echo esc_html($category_name); ?></td>
                      </tr>
                      <?php if ($product->has_weight()) : ?>
                      <tr>
                        <th class="py-3.5 px-4 font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 bg-stone-50/60 dark:bg-stone-950/60"><?php esc_html_e('Weight', 'kinboni-core'); ?></th>
                        <td class="py-3.5 px-4 text-stone-600 dark:text-stone-400"><?php echo esc_html(wc_format_weight($product->get_weight())); ?></td>
                      </tr>
                      <?php endif; ?>
                      <?php if ($product->has_dimensions()) : ?>
                      <tr>
                        <th class="py-3.5 px-4 font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 bg-stone-50/60 dark:bg-stone-950/60"><?php esc_html_e('Dimensions', 'kinboni-core'); ?></th>
                        <td class="py-3.5 px-4 text-stone-600 dark:text-stone-400"><?php echo esc_html(wc_format_dimensions($product->get_dimensions(false))); ?></td>
                      </tr>
                      <?php endif; ?>
                      <?php
                      $attributes = $product->get_attributes();
                      if (!empty($attributes)) {
                          foreach ($attributes as $attribute) {
                              $attr_label = wc_attribute_label($attribute->get_name());
                              $attr_val = '';
                              if ($attribute->is_taxonomy()) {
                                  $values = wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'names'));
                                  $attr_val = implode(', ', $values);
                              } else {
                                  $values = $attribute->get_options();
                                  $attr_val = implode(', ', $values);
                              }
                              if (!empty($attr_val)) {
                                  ?>
                                  <tr>
                                    <th class="py-3.5 px-4 font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 bg-stone-50/60 dark:bg-stone-950/60"><?php echo esc_html($attr_label); ?></th>
                                    <td class="py-3.5 px-4 text-stone-600 dark:text-stone-400"><?php echo esc_html($attr_val); ?></td>
                                  </tr>
                                  <?php
                              }
                          }
                      }
                      ?>
                      <tr>
                        <th class="py-3.5 px-4 font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300 bg-stone-50/60 dark:bg-stone-950/60"><?php esc_html_e('Origin & Warranty', 'kinboni-core'); ?></th>
                        <td class="py-3.5 px-4 text-stone-600 dark:text-stone-400"><?php esc_html_e('Small-Batch Atelier Edition • Lifetime Master Guild Warranty', 'kinboni-core'); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div id="sp-tab-panel-reviews" class="sp-tab-panel hidden">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                  <div class="lg:col-span-7 space-y-6">
                    <div class="flex items-center justify-between">
                      <h3 class="font-serif text-2xl font-medium text-stone-900 dark:text-white"><?php esc_html_e('Verified Patron Acclaim', 'kinboni-core'); ?></h3>
                      <span class="text-xs text-stone-500 font-semibold" id="sp-reviews-count-badge"><?php echo esc_html($review_count); ?> <?php esc_html_e('Reviews', 'kinboni-core'); ?></span>
                    </div>

                    <div id="sp-reviews-list" class="space-y-4">
                      <?php
                      $wc_reviews = get_comments(array(
                          'post_id' => $product_id,
                          'status'  => 'approve',
                          'number'  => 10,
                      ));
                      if (!empty($wc_reviews)) :
                          foreach ($wc_reviews as $r) :
                              $r_rating = intval(get_comment_meta($r->comment_ID, 'rating', true)) ?: 5;
                              $r_city   = get_comment_meta($r->comment_ID, 'city', true) ?: __('Verified Patron', 'kinboni-core');
                              ?>
                              <div class="p-4 rounded-2xl bg-stone-50/80 dark:bg-stone-950/80 border border-stone-200/80 dark:border-stone-800 space-y-2">
                                <div class="flex items-center justify-between">
                                  <div class="flex items-center gap-2">
                                    <span class="font-serif font-semibold text-sm text-stone-900 dark:text-white"><?php echo esc_html($r->comment_author); ?></span>
                                    <span class="px-2 py-0.5 rounded-full bg-gold-500/15 text-gold-700 dark:text-gold-400 text-[10px] font-bold uppercase tracking-wider"><?php echo esc_html($r_city); ?></span>
                                  </div>
                                  <span class="text-[11px] text-stone-400"><?php echo esc_html(get_comment_date('', $r)); ?></span>
                                </div>
                                <div class="flex items-center gap-1">
                                  <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <svg class="w-3.5 h-3.5 <?php echo ($i <= $r_rating) ? 'text-amber-400 fill-amber-400' : 'text-stone-300 dark:text-stone-700'; ?>" viewBox="0 0 20 20" fill="currentColor">
                                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                  <?php endfor; ?>
                                </div>
                                <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed"><?php echo esc_html($r->comment_content); ?></p>
                              </div>
                          <?php endforeach;
                      else : ?>
                          <div class="p-6 rounded-2xl bg-stone-50/60 dark:bg-stone-950/60 border border-dashed border-stone-300 dark:border-stone-800 text-center py-8" id="sp-no-reviews-msg">
                            <i data-lucide="message-square" class="w-8 h-8 text-gold-500 mx-auto mb-2 opacity-60"></i>
                            <p class="text-xs text-stone-500 dark:text-stone-400"><?php esc_html_e('No reviews recorded yet for this piece. Be the first patron to share your impression.', 'kinboni-core'); ?></p>
                          </div>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="lg:col-span-5 bg-stone-50 dark:bg-stone-950 p-6 rounded-2xl border border-stone-200 dark:border-stone-800">
                    <h4 class="font-serif text-xl font-medium text-stone-900 dark:text-white mb-1"><?php esc_html_e('Record Your Impression', 'kinboni-core'); ?></h4>
                    <p class="text-sm text-stone-600 dark:text-stone-300 mb-5"><?php esc_html_e('Your review will be submitted for patron moderation.', 'kinboni-core'); ?></p>
                    <form id="sp-review-form" class="space-y-4" data-product-id="<?php echo esc_attr($product_id); ?>">
                      <div style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden;" aria-hidden="true"><input type="text" name="kinboni_hp" value="" tabindex="-1" autocomplete="off"></div>
                      <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Your Rating *', 'kinboni-core'); ?></label>
                        <select id="sp-review-rating" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500">
                          <option value="5">★★★★★ — <?php esc_html_e('Exceptional (5/5)', 'kinboni-core'); ?></option>
                          <option value="4">★★★★☆ — <?php esc_html_e('Distinguished (4/5)', 'kinboni-core'); ?></option>
                          <option value="3">★★★☆☆ — <?php esc_html_e('Satisfactory (3/5)', 'kinboni-core'); ?></option>
                          <option value="2">★★☆☆☆ — <?php esc_html_e('Fair (2/5)', 'kinboni-core'); ?></option>
                          <option value="1">★☆☆☆☆ — <?php esc_html_e('Needs Improvement (1/5)', 'kinboni-core'); ?></option>
                        </select>
                      </div>
                      <div>
                        <label for="sp-review-author" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Your Name *', 'kinboni-core'); ?></label>
                        <input type="text" id="sp-review-author" placeholder="<?php esc_attr_e('e.g. Tariq Mahmood', 'kinboni-core'); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                      <div>
                        <label for="sp-review-city" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('City / Location', 'kinboni-core'); ?></label>
                        <input type="text" id="sp-review-city" placeholder="<?php esc_attr_e('e.g. Gulshan, Dhaka', 'kinboni-core'); ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500" />
                      </div>
                      <div>
                        <label for="sp-review-comment" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5"><?php esc_html_e('Your Review *', 'kinboni-core'); ?></label>
                        <textarea id="sp-review-comment" rows="3" placeholder="<?php esc_attr_e('Share your experience with the craftsmanship, materials, and delivery...', 'kinboni-core'); ?>" required class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-xs text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500"></textarea>
                      </div>
                      <button type="submit" class="w-full py-3 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest rounded-xl shadow-md transition-all cursor-pointer font-sans" id="sp-review-submit-btn">
                        <?php esc_html_e('Submit Patron Review', 'kinboni-core'); ?>
                      </button>
                    </form>
                  </div>
                </div>
              </div>

            </div>
            <?php endif; ?>

            <?php if ($show_related) :
                $related_count = !empty($settings['related_count']) ? absint($settings['related_count']) : 4;
                $related_ids   = wc_get_related_products($product_id, $related_count);

                if (empty($related_ids)) {
                    // Fallback to latest products
                    $fallback = wc_get_products(array(
                        'limit'   => $related_count,
                        'status'  => 'publish',
                        'exclude' => array($product_id),
                    ));
                    $related_ids = wp_list_pluck($fallback, 'id');
                }

                if (!empty($related_ids)) :
                ?>
                <section class="related products mt-16 pt-12 border-t border-stone-200 dark:border-stone-800">
                  <div class="flex items-end justify-between mb-8">
                    <div>
                      <span class="text-xs font-bold uppercase tracking-[0.25em] text-gold-700 dark:text-gold-400">
                        <?php echo esc_html(!empty($settings['related_subtitle']) ? $settings['related_subtitle'] : __('Complementary Curations', 'kinboni-core')); ?>
                      </span>
                      <h2 class="font-serif text-2xl sm:text-3xl font-medium text-stone-900 dark:text-white mt-1">
                        <?php echo esc_html(!empty($settings['related_title']) ? $settings['related_title'] : __('Related Atelier Creations', 'kinboni-core')); ?>
                      </h2>
                    </div>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="text-xs font-bold uppercase tracking-widest text-gold-700 dark:text-gold-400 hover:underline flex items-center gap-1 cursor-pointer">
                      <span><?php esc_html_e('View Full Shop', 'kinboni-core'); ?></span>
                      <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                  </div>
                  <div id="single-product-related-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php 
                    foreach ($related_ids as $r_id) {
                        $rel_prod = wc_get_product($r_id);
                        if ($rel_prod) {
                            $this->render_luxury_card($rel_prod);
                        }
                    }
                    ?>
                  </div>
                </section>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($show_recent) : ?>
            <section id="recently-viewed-section" class="mt-14 pt-10 border-t border-stone-200/70 dark:border-stone-800/70">
              <div class="flex items-center justify-between mb-6">
                <div>
                  <span class="text-xs font-bold uppercase tracking-[0.25em] text-stone-600 dark:text-stone-300">
                    <?php echo esc_html(!empty($settings['recent_subtitle']) ? $settings['recent_subtitle'] : __('Your Browsing History', 'kinboni-core')); ?>
                  </span>
                  <h3 class="font-serif text-xl sm:text-2xl font-medium text-stone-900 dark:text-white">
                    <?php echo esc_html(!empty($settings['recent_title']) ? $settings['recent_title'] : __('Recently Viewed Creations', 'kinboni-core')); ?>
                  </h3>
                </div>
              </div>
              <div id="recently-viewed-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <?php
                // Pre-populate with recent creations
                $recent_query = wc_get_products(array(
                    'limit'   => 4,
                    'status'  => 'publish',
                    'exclude' => array($product_id),
                    'orderby' => 'rand',
                ));
                foreach ($recent_query as $rp) :
                    $r_id    = $rp->get_id();
                    $r_img   = $rp->get_image_id() ? wp_get_attachment_image_url($rp->get_image_id(), 'thumbnail') : wc_placeholder_img_src('thumbnail');
                    $r_terms = get_the_terms($r_id, 'product_cat');
                    $r_cat   = ($r_terms && !is_wp_error($r_terms)) ? $r_terms[0]->name : __('Atelier', 'kinboni-core');
                    ?>
                    <div onclick="window.location.href='<?php echo esc_url($rp->get_permalink()); ?>'" class="flex items-center gap-3 p-3 rounded-2xl bg-white/90 dark:bg-stone-900/90 border border-stone-200/80 dark:border-stone-800 cursor-pointer hover:border-gold-500 transition-colors">
                      <img loading="lazy" src="<?php echo esc_url($r_img); ?>" alt="<?php echo esc_attr($rp->get_name()); ?>" class="w-12 h-14 object-cover rounded-xl flex-shrink-0" />
                      <div class="min-w-0">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400 block"><?php echo esc_html($r_cat); ?></span>
                        <h4 class="font-serif text-xs font-semibold text-stone-900 dark:text-white truncate"><?php echo esc_html($rp->get_name()); ?></h4>
                        <span class="text-xs font-bold text-stone-700 dark:text-stone-300">৳<?php echo esc_html(number_format((float)$rp->get_price())); ?></span>
                      </div>
                    </div>
                <?php endforeach; ?>
              </div>
            </section>
            <?php endif; ?>

          </div>
        </main>
        <?php
    }
}

