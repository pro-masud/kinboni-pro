<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- 1. CART SLIDE-OUT DRAWER -->
  <div id="cart-drawer-backdrop" class="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300"></div>
  <aside id="cart-drawer" class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-stone-900 shadow-2xl transform translate-x-full transition-transform duration-300 ease-out flex flex-col border-l border-stone-200 dark:border-stone-800">
    
    <!-- Drawer Header -->
    <div class="p-5 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i data-lucide="shopping-bag" class="w-5 h-5 text-gold-700 dark:text-gold-400"></i>
        <h3 class="font-serif text-lg font-medium text-stone-900 dark:text-white">Shopping Bag</h3>
      </div>
      <button type="button" id="close-cart-btn" class="w-8 h-8 rounded-full flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors" aria-label="Close cart">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <?php
    $cart_subtotal_raw  = (function_exists('WC') && WC()->cart) ? (float) WC()->cart->get_subtotal() : 0;
    $cart_subtotal_html = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_subtotal() : '৳0';
    $cart_total_html    = (function_exists('WC') && WC()->cart) ? WC()->cart->get_total() : '৳0';
    $shipping_threshold = (float) get_option('kinboni_free_shipping_threshold', 2500);
    ?>

    <!-- Scrollable Items Container -->
    <div id="cart-items-container" class="flex-grow overflow-y-auto p-5 space-y-4">
      <?php
      if (class_exists('Kinboni_Ajax_Cart')) {
          Kinboni_Ajax_Cart::instance()->render_drawer_items();
      }
      ?>
    </div>

    <!-- Empty State -->
    <div id="cart-empty-state" class="hidden flex-grow flex flex-col items-center justify-center p-8 text-center">
      <div class="w-16 h-16 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-400 mb-4">
        <i data-lucide="shopping-bag" class="w-8 h-8"></i>
      </div>
      <h4 class="font-serif text-lg font-medium text-stone-900 dark:text-white mb-2">Your Bag is Empty</h4>
      <p class="text-sm text-stone-600 dark:text-stone-300 max-w-xs mb-6 font-normal">
        Explore our curated horology and artisanal leather collections to begin your order.
      </p>
      <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop')); ?>" class="px-6 py-3 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest transition-all">
        Explore Creations
      </a>
    </div>

    <!-- Drawer Footer (Subtotal & Checkout) -->
    <div id="cart-footer" class="p-5 border-t border-stone-200 dark:border-stone-800 bg-stone-50/50 dark:bg-stone-950/50 space-y-4">

      <!-- Price Breakdown -->
      <div class="space-y-1.5 text-xs">
        <div class="flex justify-between text-stone-600 dark:text-stone-400">
          <span>Subtotal</span>
          <span id="cart-subtotal" class="font-semibold text-stone-900 dark:text-white"><?php echo $cart_subtotal_html; ?></span>
        </div>
        <div id="cart-discount-row" class="hidden flex justify-between text-emerald-600 dark:text-emerald-400 font-semibold">
          <span>Privilege Discount</span>
          <span id="cart-discount-amount">-৳0</span>
        </div>
        <div class="flex justify-between text-stone-600 dark:text-stone-400">
          <span>Express White-Glove Shipping</span>
          <span id="cart-shipping-status" class="font-semibold <?php echo ($shipping_threshold <= 0 || $cart_subtotal_raw >= $shipping_threshold) ? 'text-emerald-600 dark:text-emerald-400' : 'text-stone-700 dark:text-stone-300'; ?>">
            <?php echo ($shipping_threshold <= 0 || $cart_subtotal_raw >= $shipping_threshold) ? esc_html__('COMPLIMENTARY', 'kinboni-core') : esc_html__('Calculated at Checkout', 'kinboni-core'); ?>
          </span>
        </div>
        <div class="flex justify-between text-base font-bold text-stone-900 dark:text-white pt-2 border-t border-stone-200 dark:border-stone-800">
          <span>Estimated Total</span>
          <span id="cart-total" class="font-serif font-bold text-lg text-gold-700 dark:text-gold-400"><?php echo $cart_total_html; ?></span>
        </div>
      </div>

      <!-- Checkout CTA & Full Page Links -->
      <div class="space-y-2">
        <button 
          id="cart-checkout-btn" 
          type="button" 
          class="w-full py-3.5 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-[0.15em] rounded-xl shadow-lg hover:shadow-gold-500/25 transition-all text-center flex items-center justify-center gap-2 active:scale-95"
        >
          <i data-lucide="zap" class="w-4 h-4 fill-stone-950"></i>
          <span>Quick COD Checkout Modal</span>
        </button>
        <div class="grid grid-cols-2 gap-2">
          <a 
            href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart')); ?>"
            class="w-full py-2.5 px-3 rounded-xl border border-stone-300 dark:border-stone-700 hover:border-gold-500 text-stone-800 dark:text-stone-200 text-[11px] font-bold uppercase tracking-wider transition-all flex items-center justify-center gap-1.5"
          >
            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
            <span>View Cart Page</span>
          </a>
          <a 
            href="<?php echo esc_url(function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout')); ?>"
            class="w-full py-2.5 px-3 rounded-xl bg-stone-900 text-white dark:bg-stone-100 dark:text-stone-950 hover:bg-gold-500 hover:text-stone-950 text-[11px] font-bold uppercase tracking-wider transition-all flex items-center justify-center gap-1.5"
          >
            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
            <span>Checkout Page</span>
          </a>
        </div>
      </div>

    </div>
  </aside>
