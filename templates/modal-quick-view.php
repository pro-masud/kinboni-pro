<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- 3. PRODUCT QUICK VIEW MODAL -->
  <div id="quickview-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div id="quickview-backdrop" class="fixed inset-0 bg-stone-950/70 backdrop-blur-md"></div>
    
    <div class="relative w-full max-w-4xl max-h-[92vh] overflow-y-auto bg-white dark:bg-stone-900 rounded-3xl shadow-2xl border border-stone-200 dark:border-stone-800 p-6 sm:p-8 z-10 animate-slide-up">
      <button type="button" id="close-quickview-btn" class="absolute top-4 right-4 w-9 h-9 rounded-full text-stone-400 hover:text-stone-900 dark:hover:text-white bg-stone-100 dark:bg-stone-800 flex items-center justify-center transition-colors" aria-label="Close modal">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
      <div id="quickview-content">
        <!-- Injected by QuickViewController.open(id) -->
      </div>
    </div>
  </div>
