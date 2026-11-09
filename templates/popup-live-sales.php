<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="live-sales-popup" class="fixed bottom-20 left-3 right-3 sm:left-auto sm:right-6 z-40 sm:max-w-[360px] transform translate-y-8 opacity-0 pointer-events-none transition-all duration-500 ease-out">
  <div id="live-sales-card" class="relative flex items-center gap-3.5 p-3 sm:p-3.5 rounded-2xl bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-gold-500/40 dark:border-gold-500/40 shadow-2xl hover:border-gold-500 transition-all cursor-pointer group pointer-events-auto" title="<?php esc_attr_e('View Product Details', 'kinboni-core'); ?>">
    
    <!-- Thumbnail Image with Gold Star Spotlight Badge -->
    <div class="relative w-14 h-16 rounded-xl overflow-hidden bg-stone-100 dark:bg-stone-950 flex-shrink-0 border border-stone-200/60 dark:border-stone-800 shadow-sm">
      <img id="live-sale-image" loading="lazy" src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=400&q=80" alt="<?php esc_attr_e('Popular creation spotlight', 'kinboni-core'); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
      <span class="absolute top-1 left-1 w-2 h-2 rounded-full bg-gold-400 animate-pulse"></span>
    </div>

    <!-- Details -->
    <div class="flex-grow min-w-0 pr-8">
      <div class="flex items-center justify-between gap-1.5 text-xs text-gold-700 dark:text-gold-400 font-bold uppercase tracking-wider">
        <div class="flex items-center gap-1.5 min-w-0">
          <i data-lucide="sparkles" class="w-3 h-3 text-gold-500 flex-shrink-0"></i>
          <span id="live-sale-buyer" class="truncate"><?php esc_html_e('Recent Acquisition', 'kinboni-core'); ?></span>
        </div>
        <span id="live-sale-time" class="text-[10px] font-medium text-stone-400 dark:text-stone-500 flex-shrink-0 lowercase"></span>
      </div>
      <h4 id="live-sale-name" class="font-serif text-sm sm:text-base font-semibold text-stone-900 dark:text-white truncate group-hover:text-gold-600 dark:group-hover:text-gold-400 transition-colors mt-0.5">
        Chronos Royale Automatic
      </h4>
      <div class="flex items-center justify-between gap-2 mt-1">
        <div class="flex items-baseline gap-1.5">
          <span id="live-sale-price" class="text-xs font-bold text-stone-900 dark:text-white">৳18,500</span>
        </div>
        <div class="flex items-center gap-1.5">
          <button type="button" id="live-sale-quickview-btn" class="btn-open-quickview px-2 py-0.5 rounded-lg bg-stone-100 dark:bg-stone-800 hover:bg-gold-400 hover:text-stone-950 text-xs font-bold uppercase tracking-wider flex items-center gap-1 transition-colors" title="<?php esc_attr_e('Quick View', 'kinboni-core'); ?>">
            <i data-lucide="eye" class="w-3 h-3"></i>
            <span><?php esc_html_e('Quick View', 'kinboni-core'); ?></span>
          </button>
        </div>
      </div>
    </div>

    <!-- Dismiss Button -->
    <div class="absolute top-2 right-2">
      <button id="close-live-sale-btn" type="button" class="w-6 h-6 rounded-full flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors" aria-label="<?php esc_attr_e('Dismiss spotlight popup', 'kinboni-core'); ?>">
        <i data-lucide="x" class="w-3.5 h-3.5"></i>
      </button>
    </div>
  </div>
</div>
