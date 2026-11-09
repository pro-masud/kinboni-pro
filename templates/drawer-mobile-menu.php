<?php
/**
 * Kinboni Mobile Menu Slide-Out Drawer Template
 *
 * Provides a responsive, accessible slide-out navigation drawer
 * for mobile and tablet devices with Lucide icons, quick action pills,
 * and dark/light theme switching.
 *
 * @package Kinboni_Core
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Global page URLs
$shop_url       = function_exists('wc_get_page_id') && wc_get_page_id('shop') > 0 ? get_permalink(wc_get_page_id('shop')) : home_url('/shop');
$cart_url       = function_exists('wc_get_page_id') && wc_get_page_id('cart') > 0 ? get_permalink(wc_get_page_id('cart')) : home_url('/cart');
$checkout_url   = function_exists('wc_get_page_id') && wc_get_page_id('checkout') > 0 ? get_permalink(wc_get_page_id('checkout')) : home_url('/checkout');
$account_url    = function_exists('wc_get_page_id') && wc_get_page_id('myaccount') > 0 ? get_permalink(wc_get_page_id('myaccount')) : home_url('/my-account');
$cart_count     = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
?>

<!-- Mobile Menu Backdrop -->
<div id="mobile-menu-backdrop" class="fixed inset-0 z-[9998] bg-stone-950/65 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300"></div>

<!-- Mobile Menu Drawer -->
<aside id="mobile-menu-drawer" class="fixed inset-y-0 left-0 z-[9999] w-full max-w-xs sm:max-w-sm bg-white dark:bg-stone-900 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-out flex flex-col border-r border-stone-200 dark:border-stone-800" aria-label="<?php esc_attr_e('Mobile Navigation Drawer', 'kinboni-core'); ?>">
  
  <!-- Drawer Header -->
  <div class="p-5 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between bg-stone-50/80 dark:bg-stone-950/80">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2.5">
      <span class="w-8 h-8 rounded-full bg-stone-900 dark:bg-gold-400 text-gold-300 dark:text-stone-950 font-serif text-base font-bold grid place-items-center">K</span>
      <div class="leading-none">
        <span class="font-serif text-lg font-bold tracking-[0.2em] text-stone-900 dark:text-white uppercase">KINBONI</span>
        <span class="block text-[10px] uppercase tracking-[0.3em] text-gold-700 dark:text-gold-400 font-semibold mt-0.5">Haute Atelier</span>
      </div>
    </a>
    <button type="button" id="close-mobile-menu-btn" class="w-8 h-8 rounded-full flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-200/60 dark:hover:bg-stone-800/60 transition-colors cursor-pointer" aria-label="<?php esc_attr_e('Close Menu', 'kinboni-core'); ?>">
      <i data-lucide="x" class="w-5 h-5"></i>
    </button>
  </div>

  <!-- Navigation Links Area -->
  <nav class="flex-grow p-5 space-y-1 overflow-y-auto font-sans">
    
    <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-gold-700 dark:text-gold-400 block px-3 py-1 mb-1">
      <?php esc_html_e('Navigation', 'kinboni-core'); ?>
    </span>

    <?php
    $mob_location = has_nav_menu('mobile') ? 'mobile' : (has_nav_menu('primary') ? 'primary' : '');
    $mob_fallback = '';
    if (!$mob_location) {
        $available_menus = wp_get_nav_menus();
        if (!empty($available_menus)) {
            foreach ($available_menus as $m) {
                if ($m->slug === 'main-menu-header' || $m->name === 'main menu header') {
                    $mob_fallback = $m->term_id;
                    break;
                }
            }
            if (!$mob_fallback) {
                $mob_fallback = $available_menus[0]->term_id;
            }
        }
    }

    if ($mob_location || $mob_fallback) {
        $mob_args = array(
            'container'   => false,
            'menu_class'  => 'space-y-1 text-sm font-sans',
            'fallback_cb' => false,
        );
        if (class_exists('Kinboni_Mobile_Nav_Walker')) {
            $mob_args['walker'] = new Kinboni_Mobile_Nav_Walker();
        }
        if ($mob_location) {
            $mob_args['theme_location'] = $mob_location;
        } else {
            $mob_args['menu'] = $mob_fallback;
        }
        wp_nav_menu($mob_args);
    } else {
    ?>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-stone-800 dark:text-stone-200 hover:bg-gold-500/10 hover:text-gold-600 dark:hover:text-gold-400 text-sm font-semibold transition-colors">
      <span class="flex items-center gap-3">
        <i data-lucide="home" class="w-4 h-4 text-stone-500 dark:text-stone-400"></i>
        <span><?php esc_html_e('Home Storefront', 'kinboni-core'); ?></span>
      </span>
      <i data-lucide="chevron-right" class="w-4 h-4 opacity-40"></i>
    </a>

    <a href="<?php echo esc_url($shop_url); ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-stone-800 dark:text-stone-200 hover:bg-gold-500/10 hover:text-gold-600 dark:hover:text-gold-400 text-sm font-semibold transition-colors">
      <span class="flex items-center gap-3">
        <i data-lucide="shopping-bag" class="w-4 h-4 text-stone-500 dark:text-stone-400"></i>
        <span><?php esc_html_e('Shop Catalogue', 'kinboni-core'); ?></span>
      </span>
      <i data-lucide="chevron-right" class="w-4 h-4 opacity-40"></i>
    </a>

    <a href="<?php echo esc_url($cart_url); ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-stone-800 dark:text-stone-200 hover:bg-gold-500/10 hover:text-gold-600 dark:hover:text-gold-400 text-sm font-semibold transition-colors">
      <span class="flex items-center gap-3">
        <i data-lucide="shopping-cart" class="w-4 h-4 text-stone-500 dark:text-stone-400"></i>
        <span><?php esc_html_e('Shopping Cart', 'kinboni-core'); ?></span>
      </span>
      <span class="cart-count-badge px-2 py-0.5 rounded-full bg-gold-400 text-stone-950 font-bold text-xs"><?php echo esc_html($cart_count); ?></span>
    </a>

    <a href="<?php echo esc_url($checkout_url); ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-stone-800 dark:text-stone-200 hover:bg-gold-500/10 hover:text-gold-600 dark:hover:text-gold-400 text-sm font-semibold transition-colors">
      <span class="flex items-center gap-3">
        <i data-lucide="credit-card" class="w-4 h-4 text-stone-500 dark:text-stone-400"></i>
        <span><?php esc_html_e('Checkout', 'kinboni-core'); ?></span>
      </span>
      <i data-lucide="chevron-right" class="w-4 h-4 opacity-40"></i>
    </a>

    <a href="<?php echo esc_url($account_url); ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-stone-800 dark:text-stone-200 hover:bg-gold-500/10 hover:text-gold-600 dark:hover:text-gold-400 text-sm font-semibold transition-colors">
      <span class="flex items-center gap-3">
        <i data-lucide="user" class="w-4 h-4 text-stone-500 dark:text-stone-400"></i>
        <span><?php esc_html_e('My Account Portal', 'kinboni-core'); ?></span>
      </span>
      <i data-lucide="chevron-right" class="w-4 h-4 opacity-40"></i>
    </a>

    <a href="<?php echo esc_url(home_url('/#flash-deals')); ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-stone-800 dark:text-stone-200 hover:bg-gold-500/10 hover:text-gold-600 dark:hover:text-gold-400 text-sm font-semibold transition-colors">
      <span class="flex items-center gap-3">
        <i data-lucide="flame" class="w-4 h-4 text-rose-500"></i>
        <span><?php esc_html_e('Flash Deals', 'kinboni-core'); ?></span>
      </span>
      <span class="px-1.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-bold uppercase leading-none">HOT</span>
    </a>
    <?php } ?>

    <!-- Action Drawer Shortcuts -->
    <div class="pt-4 mt-3 border-t border-stone-200 dark:border-stone-800 space-y-2">
      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-gold-700 dark:text-gold-400 block px-3 py-1">
        <?php esc_html_e('Quick Actions', 'kinboni-core'); ?>
      </span>

      <button type="button" class="open-search-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 text-xs uppercase font-bold tracking-wider transition-colors cursor-pointer">
        <span class="flex items-center gap-3">
          <i data-lucide="search" class="w-4 h-4"></i>
          <span><?php esc_html_e('Search Catalog', 'kinboni-core'); ?></span>
        </span>
        <i data-lucide="arrow-right" class="w-3.5 h-3.5 opacity-40"></i>
      </button>

      <button type="button" class="open-cart-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 text-xs uppercase font-bold tracking-wider transition-colors cursor-pointer">
        <span class="flex items-center gap-3">
          <i data-lucide="shopping-bag" class="w-4 h-4"></i>
          <span><?php esc_html_e('Shopping Bag', 'kinboni-core'); ?></span>
        </span>
        <span class="cart-count-badge px-2 py-0.5 rounded-full bg-gold-400 text-stone-950 font-bold text-xs"><?php echo esc_html($cart_count); ?></span>
      </button>

      <button type="button" class="open-wishlist-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 text-xs uppercase font-bold tracking-wider transition-colors cursor-pointer">
        <span class="flex items-center gap-3">
          <i data-lucide="heart" class="w-4 h-4 text-rose-500"></i>
          <span><?php esc_html_e('Saved Wishlist', 'kinboni-core'); ?></span>
        </span>
        <span class="wishlist-count-badge px-2 py-0.5 rounded-full bg-rose-600 text-white font-bold text-xs">0</span>
      </button>

      <button type="button" class="theme-toggle-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 text-xs uppercase font-bold tracking-wider transition-colors cursor-pointer">
        <span class="flex items-center gap-3">
          <i data-lucide="moon" class="w-4 h-4"></i>
          <span><?php esc_html_e('Theme Mode', 'kinboni-core'); ?></span>
        </span>
        <span class="text-[11px] font-bold text-gold-600 dark:text-gold-400 uppercase tracking-wider"><?php esc_html_e('Toggle', 'kinboni-core'); ?></span>
      </button>
    </div>
  </nav>

  <!-- Drawer Footer -->
  <div class="p-4 border-t border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 text-xs text-stone-500">
    <p class="font-serif italic text-stone-700 dark:text-stone-300">"Shop Smart, Live Better."</p>
    <p class="text-[11px] text-stone-400 mt-1">&copy; <?php echo esc_html(date('Y')); ?> Kinboni E-Commerce</p>
  </div>
</aside>
