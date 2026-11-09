<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="checkout-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 md:p-6">
    <div id="checkout-backdrop" class="fixed inset-0 bg-stone-950/75 backdrop-blur-md"></div>
    
    <div class="relative w-full max-w-4xl lg:max-w-5xl max-h-[92vh] flex flex-col bg-white dark:bg-stone-900 rounded-3xl shadow-2xl border border-stone-200 dark:border-stone-800 z-10 animate-slide-up overflow-hidden">
      
      <!-- VIEW 1: 3-FIELD ORDER FORM (2-Column Quick View Style) -->
      <div id="checkout-form-view" class="flex flex-col max-h-[92vh] overflow-y-auto">
        
        <!-- Header (matches Quick View style) -->
        <div class="px-6 py-4 sm:px-8 sm:py-5 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between bg-stone-50/60 dark:bg-stone-950/60 sticky top-0 z-20 backdrop-blur-md flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gold-400/20 text-gold-700 dark:text-gold-400 flex items-center justify-center flex-shrink-0">
              <i data-lucide="zap" class="w-5 h-5 fill-gold-500"></i>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-serif text-xl sm:text-2xl font-semibold text-stone-900 dark:text-white">Confirm Your Order</h3>
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 text-xs font-bold uppercase tracking-wider">Cash on Delivery</span>
              </div>
              <p class="text-sm text-stone-600 dark:text-stone-300 font-normal hidden sm:block">Pay cash when package arrives at your doorstep &bull; Zero advance payment</p>
            </div>
          </div>
          <button id="close-checkout-modal-btn" type="button" class="w-9 h-9 rounded-full flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-white bg-stone-100 dark:bg-stone-800 transition-colors" aria-label="Close modal">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <!-- 2-Column Split Body (matches Quick View Product Modal) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 min-h-0">

          <!-- LEFT COLUMN (lg:col-span-5): Order Items Summary & Pricing Breakdown -->
          <div class="lg:col-span-5 p-6 sm:p-7 bg-stone-50/70 dark:bg-stone-950/70 lg:border-r border-stone-200 dark:border-stone-800 flex flex-col justify-between space-y-6">
            <div class="space-y-4">
              <div class="flex items-center justify-between pb-2 border-b border-stone-200 dark:border-stone-800">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400">Order Items</span>
                <span class="text-[11px] text-gold-700 dark:text-gold-400 font-medium">Bespoke Selection</span>
              </div>

              <!-- Selected Items List -->
              <div id="checkout-items-preview" class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                <!-- Dynamically populated by QuickOrderController.renderItemsPreview() -->
              </div>

              <!-- Privilege Voucher Form -->
              <div class="pt-3 border-t border-stone-200 dark:border-stone-800">
                <label for="order-modal-promo-input" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5">
                  Have a Privilege Voucher?
                </label>
                <div class="flex gap-2">
                  <input 
                    type="text" 
                    id="order-modal-promo-input" 
                    placeholder="Voucher code (try LUXE20)" 
                    class="flex-grow px-3.5 py-2 text-xs uppercase rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-900 dark:text-stone-100 focus:outline-none focus:border-gold-500"
                  />
                  <button 
                    type="button" 
                    id="order-modal-promo-btn"
                    class="px-4 py-2 bg-stone-900 text-white dark:bg-white dark:text-stone-900 text-xs font-bold uppercase rounded-xl hover:bg-gold-500 hover:text-stone-950 transition-colors flex-shrink-0"
                  >
                    Apply
                  </button>
                </div>
                <div id="order-modal-applied-promo" class="mt-2"></div>
              </div>

              <!-- Price Breakdown -->
              <div class="pt-3 border-t border-stone-200 dark:border-stone-800 space-y-2 text-xs">
                <div class="flex justify-between text-stone-600 dark:text-stone-400">
                  <span>Items Subtotal:</span>
                  <span id="order-modal-subtotal" class="font-bold text-stone-900 dark:text-white">৳0</span>
                </div>
                <div id="order-modal-discount-row" class="hidden flex justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                  <span>Privilege Discount:</span>
                  <span id="order-modal-discount">-৳0</span>
                </div>
                <div class="flex justify-between text-stone-600 dark:text-stone-400">
                  <span>Delivery Fee:</span>
                  <span id="order-modal-shipping" class="font-bold text-stone-900 dark:text-white">৳<?php echo esc_html(get_option('kinboni_delivery_fee_inside', 70)); ?></span>
                </div>
                <div class="flex justify-between text-base font-bold text-stone-900 dark:text-white pt-2.5 border-t border-stone-200 dark:border-stone-800">
                  <span>Total Payable:</span>
                  <span id="order-modal-total" class="font-serif text-xl font-bold text-gold-700 dark:text-gold-400">৳0</span>
                </div>
              </div>
            </div>

            <!-- Trust Assurances (Quick View Style) -->
            <div class="p-3.5 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800/80 space-y-2 text-[11px] text-stone-600 dark:text-stone-400">
              <?php if (get_option('kinboni_allow_inspection', 'yes') === 'yes') : ?>
              <div class="flex items-center gap-2 font-medium text-emerald-600 dark:text-emerald-400">
                <i data-lucide="shield-check" class="w-4 h-4 flex-shrink-0"></i>
                <span><?php esc_html_e('Cash on Delivery — Inspect upon receipt', 'kinboni-core'); ?></span>
              </div>
              <?php endif; ?>
              <div class="flex items-center gap-2">
                <i data-lucide="truck" class="w-4 h-4 flex-shrink-0 text-gold-500"></i>
                <span><?php echo esc_html(sprintf(__('White-Glove Courier • %s in Dhaka', 'kinboni-core'), get_option('kinboni_delivery_time_inside', '1 - 2 Business Days'))); ?></span>
              </div>
              <div class="flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4 flex-shrink-0 text-gold-500"></i>
                <span>7-Day Hassle-Free Exchange Policy</span>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN (lg:col-span-7): Fast 3-Field Order Form -->
          <div class="lg:col-span-7 p-6 sm:p-8">
            <div class="mb-5">
              <span class="text-xs font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400">Recipient Details</span>
              <h4 class="font-serif text-lg sm:text-xl font-semibold text-stone-900 dark:text-white mt-0.5">Where should we deliver your order?</h4>
            </div>

            <!-- 3-Field Checkout Form -->
            <form id="quick-order-form" class="space-y-4" novalidate>
              <div style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden;" aria-hidden="true"><input type="text" name="kinboni_hp" value="" tabindex="-1" autocomplete="off"></div>
              
              <!-- Field 1: Full Name -->
              <div>
                <label for="order-customer-name" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5">
                  1. Full Name <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                    <i data-lucide="user" class="w-4 h-4"></i>
                  </span>
                  <input 
                    type="text" 
                    id="order-customer-name" 
                    placeholder="Enter your full name" 
                    class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-900 dark:text-stone-100 text-xs sm:text-sm focus:outline-none focus:border-gold-500 transition-colors shadow-2xs"
                    required
                  />
                </div>
                <p id="order-name-error" class="hidden mt-1 text-xs text-rose-500 font-medium"></p>
              </div>

              <!-- Field 2: Phone Number (11-Digit Active Number) -->
              <div>
                <label for="order-customer-phone" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5">
                  2. Phone Number (Active Mobile) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                    <i data-lucide="phone" class="w-4 h-4"></i>
                  </span>
                  <input 
                    type="tel" 
                    id="order-customer-phone" 
                    placeholder="01XXXXXXXXX (11-digit active number)" 
                    maxlength="14"
                    class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-900 dark:text-stone-100 text-xs sm:text-sm focus:outline-none focus:border-gold-500 transition-colors shadow-2xs"
                    required
                  />
                </div>
                <p id="order-phone-error" class="hidden mt-1 text-xs text-rose-500 font-medium"></p>
              </div>

              <!-- Field 3: Delivery Address -->
              <div>
                <label for="order-customer-address" class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-1.5">
                  3. Complete Delivery Address <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute top-3.5 left-0 pl-3.5 flex items-start pointer-events-none text-stone-400">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                  </span>
                  <textarea 
                    id="order-customer-address" 
                    rows="2" 
                    placeholder="House/Apartment, Road, Area/Thana, District/City" 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-900 dark:text-stone-100 text-xs sm:text-sm focus:outline-none focus:border-gold-500 transition-colors shadow-2xs"
                    required
                  ></textarea>
                </div>
                <p id="order-address-error" class="hidden mt-1 text-xs text-rose-500 font-medium"></p>
              </div>

              <!-- Delivery Zone Selection Cards -->
              <div class="pt-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-800 dark:text-stone-200 mb-2">
                  Select Delivery Zone:
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <label id="zone-label-inside" class="delivery-zone-option flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition-all bg-white dark:bg-stone-900 border-gold-500 bg-gold-50/50 dark:bg-gold-950/20 ring-1 ring-gold-500">
                    <div class="flex items-center gap-2.5">
                      <input type="radio" name="order-delivery-zone" value="inside" checked class="text-gold-500 focus:ring-gold-500 w-4 h-4" />
                      <div>
                        <span class="text-xs font-bold text-stone-900 dark:text-stone-100 block"><?php esc_html_e('Inside Dhaka City', 'kinboni-core'); ?></span>
                        <span class="text-xs text-stone-600 dark:text-stone-300"><?php echo esc_html(get_option('kinboni_delivery_time_inside', '1 - 2 Business Days')); ?></span>
                      </div>
                    </div>
                    <span id="zone-fee-inside" class="text-xs font-bold text-stone-900 dark:text-white px-2 py-0.5 rounded bg-stone-100 dark:bg-stone-800">৳<?php echo esc_html(get_option('kinboni_delivery_fee_inside', 70)); ?></span>
                  </label>

                  <label id="zone-label-outside" class="delivery-zone-option flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition-all bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-700 hover:border-gold-400">
                    <div class="flex items-center gap-2.5">
                      <input type="radio" name="order-delivery-zone" value="outside" class="text-gold-500 focus:ring-gold-500 w-4 h-4" />
                      <div>
                        <span class="text-xs font-bold text-stone-900 dark:text-stone-100 block"><?php esc_html_e('Outside Dhaka City', 'kinboni-core'); ?></span>
                        <span class="text-xs text-stone-600 dark:text-stone-300"><?php echo esc_html(get_option('kinboni_delivery_time_outside', '2 - 4 Business Days')); ?></span>
                      </div>
                    </div>
                    <span id="zone-fee-outside" class="text-xs font-bold text-stone-900 dark:text-white px-2 py-0.5 rounded bg-stone-100 dark:bg-stone-800">৳<?php echo esc_html(get_option('kinboni_delivery_fee_outside', 130)); ?></span>
                  </label>
                </div>
              </div>

              <!-- Submit Order Button -->
              <div class="pt-2">
                <button 
                  type="submit" 
                  id="order-submit-button"
                  class="w-full py-4 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs sm:text-sm font-bold uppercase tracking-widest rounded-xl shadow-lg hover:shadow-gold-500/25 transition-all flex items-center justify-center gap-2 active:scale-[0.99]"
                >
                  <i data-lucide="check-circle" class="w-5 h-5"></i>
                  <span id="order-submit-btn-text">Confirm Order (Cash on Delivery)</span>
                </button>
                <p class="text-sm text-center text-stone-400 mt-2 font-normal">
                  Zero online payment &bull; Complete payment in cash when receiving the package.
                </p>
              </div>

            </form>
          </div>

        </div>

      </div>

      <!-- VIEW 2: ORDER SUCCESS CONFIRMATION RECEIPT -->
      <div id="checkout-success-view" class="hidden p-6 sm:p-8 text-center animate-slide-up">
        
        <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/10">
          <i data-lucide="check-circle-2" class="w-9 h-9"></i>
        </div>

        <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 text-xs font-bold uppercase tracking-wider mb-2">
          Order Successfully Placed!
        </span>

        <h3 class="font-serif text-2xl sm:text-3xl font-medium text-stone-900 dark:text-white mb-1">
          Thank You, <span id="confirmed-customer-name" class="text-gold-700 dark:text-gold-400"></span>!
        </h3>

        <p class="text-sm text-stone-600 dark:text-stone-300 max-w-sm mx-auto mb-6 font-normal">
          Your bespoke order has been accepted. Our concierge team will reach out shortly for dispatch.
        </p>

        <!-- Invoice Receipt Box -->
        <div class="p-4 sm:p-5 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 text-xs text-left mb-6 space-y-2.5">
          <div class="flex justify-between items-center pb-2 border-b border-stone-200 dark:border-stone-800">
            <span class="text-stone-500">Order Reference:</span>
            <span id="confirmed-order-id" class="font-mono font-bold text-stone-900 dark:text-white"></span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-stone-500">Phone Number:</span>
            <span id="confirmed-customer-phone" class="font-semibold text-stone-900 dark:text-stone-100"></span>
          </div>
          <div class="flex justify-between items-start gap-4">
            <span class="text-stone-500 flex-shrink-0">Delivery Address:</span>
            <span id="confirmed-customer-address" class="font-semibold text-stone-900 dark:text-stone-100 text-right"></span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-stone-500">Payment Method:</span>
            <span class="font-bold text-emerald-600 dark:text-emerald-400">Cash on Delivery (COD)</span>
          </div>
          <div class="flex justify-between items-center pt-2 border-t border-stone-200 dark:border-stone-800 text-sm font-bold">
            <span class="text-stone-900 dark:text-white">Total Net Payable:</span>
            <span id="confirmed-order-total" class="text-gold-700 dark:text-gold-400 font-serif text-lg"></span>
          </div>
        </div>

        <!-- Action CTAs -->
        <div class="space-y-3">
          <a 
            id="confirmed-whatsapp-link" 
            href="#" 
            target="_blank" 
            class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center justify-center gap-2"
          >
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span>Send Order Confirmation on WhatsApp</span>
          </a>

          <button 
            id="close-success-view-btn" 
            type="button" 
            class="w-full py-3 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-800 dark:text-stone-200 text-xs font-semibold uppercase tracking-wider rounded-xl transition-colors"
          >
            Continue Shopping
          </button>
        </div>

      </div>

    </div>
  </div>
