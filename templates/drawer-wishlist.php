<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- 2. WISHLIST SLIDE-OUT DRAWER -->
  <div id="wishlist-drawer-backdrop" class="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300"></div>
  <aside id="wishlist-drawer" class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-stone-900 shadow-2xl transform translate-x-full transition-transform duration-300 ease-out flex flex-col border-l border-stone-200 dark:border-stone-800">
    
    <div class="p-5 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i data-lucide="heart" class="w-5 h-5 text-rose-500"></i>
        <h3 class="font-serif text-lg font-medium text-stone-900 dark:text-white">Your Saved Wishlist</h3>
      </div>
      <button type="button" id="close-wishlist-btn" class="w-8 h-8 rounded-full flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors" aria-label="Close wishlist">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Wishlist Items -->
    <div id="wishlist-items-container" class="flex-grow overflow-y-auto p-5 space-y-4">
      <!-- Injected by WishlistController.render() -->
    </div>

    <!-- Empty Wishlist State -->
    <div id="wishlist-empty-state" class="hidden flex-grow flex flex-col items-center justify-center p-8 text-center">
      <div class="w-16 h-16 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-400 mb-4">
        <i data-lucide="heart" class="w-8 h-8"></i>
      </div>
      <h4 class="font-serif text-lg font-medium text-stone-900 dark:text-white mb-2">No Saved Creations</h4>
      <p class="text-sm text-stone-600 dark:text-stone-300 max-w-xs mb-6 font-normal">
        Click the heart icon on any creation to bookmark it for later consideration.
      </p>
      <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop')); ?>" class="px-6 py-3 rounded-xl bg-stone-900 text-white dark:bg-white dark:text-stone-900 text-xs font-bold uppercase tracking-widest transition-all">
        Browse Catalogue
      </a>
    </div>
  </aside>
