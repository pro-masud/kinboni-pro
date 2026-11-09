<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- 4. SEARCH MODAL OVERLAY -->
  <div id="search-modal" class="fixed inset-0 z-50 hidden flex flex-col justify-start p-4 sm:p-6 md:p-12" style="display: none;">
    <div id="search-backdrop" class="fixed inset-0 bg-stone-950/80 backdrop-blur-md cursor-pointer"></div>
    
    <div class="relative w-full max-w-3xl mx-auto bg-white dark:bg-stone-900 rounded-2xl shadow-2xl border border-stone-200 dark:border-stone-800 overflow-hidden z-10 animate-slide-down">
      
      <!-- Search Input Header -->
      <form action="<?php echo esc_url(home_url('/')); ?>" method="get" class="p-5 border-b border-stone-200 dark:border-stone-800 flex items-center gap-3 m-0">
        <input type="hidden" name="post_type" value="product" />
        <svg class="w-5 h-5 text-gold-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"></path></svg>
        <input 
          type="text" 
          name="s"
          id="search-input" 
          placeholder="<?php esc_attr_e('Search by timepiece, leather, fragrance, gold, silk...', 'kinboni-core'); ?>" 
          aria-label="<?php esc_attr_e('Search Kinboni Atelier creations', 'kinboni-core'); ?>"
          autocomplete="off"
          class="flex-grow bg-transparent text-stone-900 dark:text-stone-100 placeholder:text-stone-400 text-sm sm:text-base focus:outline-none"
        />
        <button type="button" id="close-search-btn" class="w-8 h-8 rounded-full flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors cursor-pointer" aria-label="<?php esc_attr_e('Close search', 'kinboni-core'); ?>">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </form>

      <!-- Quick Search Recommendations -->
      <div class="px-5 py-3 bg-stone-50 dark:bg-stone-950 border-b border-stone-200 dark:border-stone-800 flex items-center gap-2 flex-wrap text-xs">
        <span class="text-stone-500 font-medium">Suggestions:</span>
        <button type="button" class="search-suggestion-tag px-3 py-1 rounded-full bg-stone-200/80 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-gold-500 hover:text-stone-950 transition-colors font-medium" data-query="Timepiece">Timepieces</button>
        <button type="button" class="search-suggestion-tag px-3 py-1 rounded-full bg-stone-200/80 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-gold-500 hover:text-stone-950 transition-colors font-medium" data-query="Leather">Leather Goods</button>
        <button type="button" class="search-suggestion-tag px-3 py-1 rounded-full bg-stone-200/80 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-gold-500 hover:text-stone-950 transition-colors font-medium" data-query="Oud">Oud & Fragrance</button>
        <button type="button" class="search-suggestion-tag px-3 py-1 rounded-full bg-stone-200/80 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-gold-500 hover:text-stone-950 transition-colors font-medium" data-query="Silk">Silk & Cashmere</button>
      </div>

      <!-- Search Results Area -->
      <div class="p-6 max-h-[60vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <span id="search-count" class="text-xs uppercase font-bold tracking-wider text-stone-500">Live Atelier Search</span>
        </div>
        <div id="search-results-container">
          <!-- Dynamically filled by SearchController -->
        </div>
      </div>

    </div>
  </div>
