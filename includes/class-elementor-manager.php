<?php
/**
 * Kinboni Elementor Manager
 * Handles Widget Category & Widget Registrations
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Kinboni_Elementor_Manager {

    /**
     * Singleton instance
     */
    private static $_instance = null;

    /**
     * Flag to guard against double widget registration across modern & legacy hooks
     */
    private $widgets_registered = false;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        // Register Widget Category (priority 999 to place it at the very top after core categories)
        add_action('elementor/elements/categories_registered', array($this, 'register_categories'), 999);

        // Register Widgets
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        // Fallback for Elementor < 3.5
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets_legacy'));

        // Enqueue Elementor Editor & Preview Assets
        add_action('elementor/editor/after_enqueue_scripts', array($this, 'editor_scripts'));
        add_action('elementor/preview/enqueue_scripts', array($this, 'editor_scripts'));

        // Enqueue Frontend Assets for Widgets (Swiper & Lucide)
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
    }

    /**
     * Register Custom Category: Kinboni Atelier
     * Promoted to index 0 so it appears first before Basic, Pro, General, etc.
     */
    public function register_categories($elements_manager) {
        $category_name = 'kinboni-category';
        $category_data = array(
            'title'  => esc_html__('Kinboni Atelier', 'kinboni-core'),
            'icon'   => 'eicon-star',
            'active' => true,
        );

        $elements_manager->add_category($category_name, $category_data);

        // Reorder category array using Reflection so Kinboni Atelier is at the absolute top
        try {
            $reflector = new \ReflectionClass($elements_manager);
            if ($reflector->hasProperty('categories')) {
                $prop = $reflector->getProperty('categories');
                $prop->setAccessible(true);
                $categories = $prop->getValue($elements_manager);

                if (is_array($categories) && isset($categories[$category_name])) {
                    $kinboni = array($category_name => $categories[$category_name]);
                    unset($categories[$category_name]);

                    // Place Kinboni Atelier at the very beginning of the panel categories list
                    $reordered = array_merge($kinboni, $categories);
                    $prop->setValue($elements_manager, $reordered);
                }
            }
        } catch (\Exception $e) {
            // Graceful fallback
        }
    }

    /**
     * Register Custom Elementor Widgets (Elementor 3.5+)
     */
    public function register_widgets($widgets_manager) {
        if ($this->widgets_registered) {
            return;
        }
        $this->widgets_registered = true;

        // Load Widget Files
        $this->include_widget_files();

        // Register Header & Navigation Widget
        if (class_exists('Kinboni_Widget_Header')) {
            $widgets_manager->register(new \Kinboni_Widget_Header());
        }

        // Register Hero Triple-Slider Showcase Widget
        if (class_exists('Kinboni_Widget_Hero')) {
            $widgets_manager->register(new \Kinboni_Widget_Hero());
        }

        // Register Categories Grid Widget
        if (class_exists('Kinboni_Widget_Categories')) {
            $widgets_manager->register(new \Kinboni_Widget_Categories());
        }

        // Register Featured Products Grid Widget
        if (class_exists('Kinboni_Widget_Featured_Products')) {
            $widgets_manager->register(new \Kinboni_Widget_Featured_Products());
        }

        // Register Promotional Privilege Banner Widget
        if (class_exists('Kinboni_Widget_Promotional_Banner')) {
            $widgets_manager->register(new \Kinboni_Widget_Promotional_Banner());
        }

        // Register Best Sellers Filterable Showcase Widget
        if (class_exists('Kinboni_Widget_Bestsellers')) {
            $widgets_manager->register(new \Kinboni_Widget_Bestsellers());
        }

        // Register Heritage & Craftsmanship Story Widget
        if (class_exists('Kinboni_Widget_Heritage')) {
            $widgets_manager->register(new \Kinboni_Widget_Heritage());
        }

        // Register Patron Acclaim Testimonials Slider Widget
        if (class_exists('Kinboni_Widget_Testimonials')) {
            $widgets_manager->register(new \Kinboni_Widget_Testimonials());
        }

        // Register Private Circle Newsletter Widget
        if (class_exists('Kinboni_Widget_Newsletter')) {
            $widgets_manager->register(new \Kinboni_Widget_Newsletter());
        }

        // Register Trust Badges & Benefits Widget
        if (class_exists('Kinboni_Widget_Trust_Benefits')) {
            $widgets_manager->register(new \Kinboni_Widget_Trust_Benefits());
        }

        // Register FAQ & Policy Guarantee Widget
        if (class_exists('Kinboni_Widget_Faq')) {
            $widgets_manager->register(new \Kinboni_Widget_Faq());
        }

        // Register Private Archive Flash Deals Widget
        if (class_exists('Kinboni_Widget_Flash_Deals')) {
            $widgets_manager->register(new \Kinboni_Widget_Flash_Deals());
        }

        // Register Atelier Size & Fitting Guide Modal Widget
        if (class_exists('Kinboni_Widget_Size_Guide')) {
            $widgets_manager->register(new \Kinboni_Widget_Size_Guide());
        }

        // Register Private Salon Contact & Concierge Widget
        if (class_exists('Kinboni_Widget_Contact')) {
            $widgets_manager->register(new \Kinboni_Widget_Contact());
        }

        // Register Single Product Atelier Showcase Widget
        if (class_exists('Kinboni_Widget_Single_Product')) {
            $widgets_manager->register(new \Kinboni_Widget_Single_Product());
        }

        // Register Shop Catalogue & Archive Showcase Widget
        if (class_exists('Kinboni_Widget_Shop')) {
            $widgets_manager->register(new \Kinboni_Widget_Shop());
        }

        // Register Shopping Bag & Cart Showcase Widget
        if (class_exists('Kinboni_Widget_Cart')) {
            $widgets_manager->register(new \Kinboni_Widget_Cart());
        }

        // Register Atelier Checkout Showcase Widget
        if (class_exists('Kinboni_Widget_Checkout')) {
            $widgets_manager->register(new \Kinboni_Widget_Checkout());
        }

        // Register My Account & Patron Concierge Portal Widget
        if (class_exists('Kinboni_Widget_My_Account')) {
            $widgets_manager->register(new \Kinboni_Widget_My_Account());
        }

        // Register Footer Showcase Widget
        if (class_exists('Kinboni_Widget_Footer')) {
            $widgets_manager->register(new \Kinboni_Widget_Footer());
        }

        /**
         * Developer Note: To add a new widget:
         * 1. Create your widget class file in /widgets/class-widget-yourname.php
         * 2. Add it to include_widget_files() below
         * 3. Register it here: $widgets_manager->register(new \Kinboni_Widget_Yourname());
         */
    }

    /**
     * Legacy Widget Registration (Elementor < 3.5 fallback)
     */
    public function register_widgets_legacy() {
        if ($this->widgets_registered) {
            return;
        }
        $this->widgets_registered = true;

        $this->include_widget_files();

        if (class_exists('Kinboni_Widget_Header')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Header());
        }
        if (class_exists('Kinboni_Widget_Hero')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Hero());
        }
        if (class_exists('Kinboni_Widget_Categories')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Categories());
        }
        if (class_exists('Kinboni_Widget_Featured_Products')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Featured_Products());
        }
        if (class_exists('Kinboni_Widget_Promotional_Banner')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Promotional_Banner());
        }
        if (class_exists('Kinboni_Widget_Bestsellers')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Bestsellers());
        }
        if (class_exists('Kinboni_Widget_Heritage')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Heritage());
        }
        if (class_exists('Kinboni_Widget_Testimonials')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Testimonials());
        }
        if (class_exists('Kinboni_Widget_Newsletter')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Newsletter());
        }
        if (class_exists('Kinboni_Widget_Trust_Benefits')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Trust_Benefits());
        }
        if (class_exists('Kinboni_Widget_Faq')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Faq());
        }
        if (class_exists('Kinboni_Widget_Flash_Deals')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Flash_Deals());
        }
        if (class_exists('Kinboni_Widget_Size_Guide')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Size_Guide());
        }
        if (class_exists('Kinboni_Widget_Contact')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Contact());
        }
        if (class_exists('Kinboni_Widget_Single_Product')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Single_Product());
        }
        if (class_exists('Kinboni_Widget_Shop')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Shop());
        }
        if (class_exists('Kinboni_Widget_Cart')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Cart());
        }
        if (class_exists('Kinboni_Widget_Checkout')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Checkout());
        }
        if (class_exists('Kinboni_Widget_My_Account')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_My_Account());
        }
        if (class_exists('Kinboni_Widget_Footer')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Kinboni_Widget_Footer());
        }
    }

    /**
     * Include all widget files
     */
    private function include_widget_files() {
        $widget_files = array(
            'class-widget-header.php',
            'class-widget-hero.php',
            'class-widget-categories.php',
            'class-widget-featured-products.php',
            'class-widget-promotional-banner.php',
            'class-widget-bestsellers.php',
            'class-widget-heritage.php',
            'class-widget-testimonials.php',
            'class-widget-newsletter.php',
            'class-widget-trust-benefits.php',
            'class-widget-faq.php',
            'class-widget-flash-deals.php',
            'class-widget-size-guide.php',
            'class-widget-contact.php',
            'class-widget-single-product.php',
            'class-widget-shop.php',
            'class-widget-cart.php',
            'class-widget-checkout.php',
            'class-widget-my-account.php',
            'class-widget-footer.php',
        );

        foreach ($widget_files as $file) {
            $path = KINBONI_CORE_PATH . 'widgets/' . $file;
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    /**
     * Frontend styles & scripts
     */
    public function frontend_scripts() {
        $swiper_css = file_exists(KINBONI_CORE_PATH . 'assets/vendor/swiper-bundle.min.css')
            ? KINBONI_CORE_URL . 'assets/vendor/swiper-bundle.min.css'
            : 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';

        $swiper_js = file_exists(KINBONI_CORE_PATH . 'assets/vendor/swiper-bundle.min.js')
            ? KINBONI_CORE_URL . 'assets/vendor/swiper-bundle.min.js'
            : 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';

        $lucide_js = file_exists(KINBONI_CORE_PATH . 'assets/vendor/lucide.min.js')
            ? KINBONI_CORE_URL . 'assets/vendor/lucide.min.js'
            : 'https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js';

        if (!wp_style_is('swiper-css', 'registered') && !wp_style_is('swiper-css', 'enqueued')) {
            wp_register_style('swiper-css', $swiper_css, array(), '11.0.0');
        }
        if (!wp_style_is('swiper-css', 'enqueued')) {
            wp_enqueue_style('swiper-css');
        }

        if (!wp_script_is('swiper-js', 'registered') && !wp_script_is('swiper-js', 'enqueued')) {
            wp_register_script('swiper-js', $swiper_js, array(), '11.0.0', true);
        }
        if (!wp_script_is('swiper-js', 'enqueued')) {
            wp_enqueue_script('swiper-js');
        }

        if (!wp_script_is('lucide-icons', 'registered') && !wp_script_is('lucide-icons', 'enqueued')) {
            wp_register_script('lucide-icons', $lucide_js, array(), '0.460.0', true);
        }
        if (!wp_script_is('lucide-icons', 'enqueued')) {
            wp_enqueue_script('lucide-icons');
        }
    }

    /**
     * Editor styles & scripts
     */
    public function editor_scripts() {
        $swiper_css = file_exists(KINBONI_CORE_PATH . 'assets/vendor/swiper-bundle.min.css')
            ? KINBONI_CORE_URL . 'assets/vendor/swiper-bundle.min.css'
            : 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';

        $swiper_js = file_exists(KINBONI_CORE_PATH . 'assets/vendor/swiper-bundle.min.js')
            ? KINBONI_CORE_URL . 'assets/vendor/swiper-bundle.min.js'
            : 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';

        $lucide_js = file_exists(KINBONI_CORE_PATH . 'assets/vendor/lucide.min.js')
            ? KINBONI_CORE_URL . 'assets/vendor/lucide.min.js'
            : 'https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js';

        wp_enqueue_style('swiper-css', $swiper_css, array(), '11.0.0');
        wp_enqueue_script('swiper-js', $swiper_js, array(), '11.0.0', true);
        wp_enqueue_script('lucide-icons', $lucide_js, array(), '0.460.0', true);

        // Ensure Kinboni Atelier category is at the top of the editor panel in JavaScript
        wp_add_inline_script(
            'elementor-editor',
            "(function() {
                function prioritizeKinboniCategory() {
                    if (window.elementor && elementor.config && elementor.config.document && elementor.config.document.panel && elementor.config.document.panel.elements_categories) {
                        var cats = elementor.config.document.panel.elements_categories;
                        if (cats['kinboni-category']) {
                            var kinboni = cats['kinboni-category'];
                            delete cats['kinboni-category'];
                            elementor.config.document.panel.elements_categories = Object.assign({ 'kinboni-category': kinboni }, cats);
                        }
                    }
                }
                window.addEventListener('elementor/init', prioritizeKinboniCategory);
                if (window.elementor) { prioritizeKinboniCategory(); }
            })();"
        );
    }
}

