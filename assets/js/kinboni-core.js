/**
 * Kinboni Core & Elementor Addons - Frontend Interaction Engine
 * Implements: Fast 3-Field COD Modal, Slide-Over Cart Drawer, Quick View,
 * Live Sales Popup with Web Audio API chime, Wishlist Manager, and Live Search.
 *
 * @package Kinboni_Core
 * @version 1.1.0
 */

(function ($) {
  'use strict';

  // Transparent AJAX Nonce Refresh & Single Retry on HTTP 403 or "-1"
  const origAjax = $.ajax;
  $.ajax = function (url, options) {
    if (typeof url === 'object') {
      options = url;
      url = undefined;
    }
    options = options || {};

    const isKinboni = options.data && (
      (typeof options.data === 'string' && options.data.indexOf('kinboni_') !== -1) ||
      (typeof options.data === 'object' && !(typeof FormData !== 'undefined' && options.data instanceof FormData) && options.data.action && String(options.data.action).indexOf('kinboni_') === 0) ||
      (typeof FormData !== 'undefined' && options.data instanceof FormData && options.data.has('action') && String(options.data.get('action')).indexOf('kinboni_') === 0)
    );

    if (!isKinboni || options._kinboniRetried) {
      return origAjax.call($, url, options);
    }

    const origSuccess = options.success;
    const origError = options.error;

    const retryWithFreshNonce = function (xhr) {
      origAjax.call($, {
        url: (window.kinboniCoreData && kinboniCoreData.ajax_url) || '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: { action: 'kinboni_refresh_nonce' },
        success: function (res) {
          if (res && res.success && res.data && res.data.nonce) {
            const newNonce = res.data.nonce;
            if (window.kinboniCoreData) {
              window.kinboniCoreData.nonce = newNonce;
            }
            const retryOpts = $.extend({}, options);
            retryOpts._kinboniRetried = true;
            retryOpts.success = origSuccess;
            retryOpts.error = origError;

            if (typeof FormData !== 'undefined' && retryOpts.data instanceof FormData) {
              retryOpts.data.set('nonce', newNonce);
            } else if (typeof retryOpts.data === 'string') {
              retryOpts.data = retryOpts.data.replace(/([?&]nonce=)[^&]*/, '$1' + encodeURIComponent(newNonce));
            } else if (typeof retryOpts.data === 'object' && retryOpts.data !== null) {
              retryOpts.data = $.extend({}, retryOpts.data, { nonce: newNonce });
            }
            origAjax.call($, url, retryOpts);
          } else {
            if (origError) origError.call(this, xhr, 'error', 'Nonce refresh failed');
          }
        },
        error: function () {
          if (origError) origError.call(this, xhr, 'error', 'Nonce refresh failed');
        }
      });
    };

    options.success = function (data, textStatus, xhr) {
      if (data === -1 || data === '-1') {
        retryWithFreshNonce(xhr);
        return;
      }
      if (origSuccess) origSuccess.apply(this, arguments);
    };

    options.error = function (xhr, textStatus, errorThrown) {
      const resp = xhr && xhr.responseText ? xhr.responseText.trim() : '';
      if (xhr && (xhr.status === 403 || resp === '-1')) {
        retryWithFreshNonce(xhr);
        return;
      }
      if (origError) origError.apply(this, arguments);
    };

    return origAjax.call($, url, options);
  };

  const Kinboni = {
    esc(str) {
      if (str === null || str === undefined) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    },

    state: {
      currentOrderProduct: null,
      deliveryZone: 'inside', // 'inside' = 70, 'outside' = 130
      shippingFee: 70,
      subtotal: 0,
      promoDiscount: 0,
      wishlist: JSON.parse(localStorage.getItem('kinboni_wishlist') || '[]'),
      liveSales: [],
      liveSaleIndex: 0,
      audioCtx: null
    },

    init() {
      this.initCartDrawer();
      this.initWishlist();
      this.initQuickOrder();
      this.initQuickView();
      this.initSearchModal();
      this.initLiveSales();
      this.refreshIcons();
    },

    refreshIcons() {
      if (window.lucide) {
        window.lucide.createIcons();
      }
    },

    trackAnalyticsEvent(eventName, payload = {}) {
      try {
        const currency = payload.currency || (window.kinboniCoreData && kinboniCoreData.currency_code) || 'BDT';
        if (typeof window.fbq === 'function') {
          window.fbq('track', eventName, Object.assign({ currency: currency }, payload));
        }
        if (typeof window.gtag === 'function') {
          const gaEvent = (eventName === 'AddToCart') ? 'add_to_cart' : ((eventName === 'Purchase') ? 'purchase' : eventName);
          window.gtag('event', gaEvent, Object.assign({ currency: currency }, payload));
        }
      } catch (e) {}
    },

    showToast(message, type = 'success') {
      const container = document.getElementById('toast-container');
      if (!container) return;

      const toast = document.createElement('div');
      const borderColor = type === 'success' ? 'border-gold-500/50' : 'border-rose-500/50';
      const icon = type === 'success' ? 'check-circle' : 'alert-circle';
      const iconColor = type === 'success' ? 'text-gold-500' : 'text-rose-500';

      toast.className = `flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/95 dark:bg-stone-900/95 backdrop-blur-xl border ${borderColor} shadow-2xl text-xs font-semibold text-stone-900 dark:text-white transform transition-all duration-300 translate-y-2 opacity-0 pointer-events-auto`;
      toast.innerHTML = `
        <i data-lucide="${icon}" class="w-4 h-4 ${iconColor} flex-shrink-0"></i>
        <span>${message}</span>
      `;

      container.appendChild(toast);
      this.refreshIcons();

      setTimeout(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
      }, 10);

      setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
      }, 3500);
    },

    // =========================================================================
    // 1. SLIDE-OVER SHOPPING CART DRAWER
    // =========================================================================
    initCartDrawer() {
      const drawer = document.getElementById('cart-drawer');
      const backdrop = document.getElementById('cart-drawer-backdrop');
      const closeBtn = document.getElementById('close-cart-btn');

      this.openCart = () => {
        if (!drawer) return;
        drawer.classList.remove('translate-x-full');
        if (backdrop) {
          backdrop.classList.remove('hidden');
          setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
        }
        document.body.classList.add('overflow-hidden');
        Kinboni.refreshIcons();
      };

      this.closeCart = () => {
        if (!drawer) return;
        drawer.classList.add('translate-x-full');
        if (backdrop) {
          backdrop.classList.add('opacity-0');
          setTimeout(() => backdrop.classList.add('hidden'), 300);
        }
        document.body.classList.remove('overflow-hidden');
      };

      $(document).on('click', '.open-cart-btn', (e) => {
        e.preventDefault();
        this.openCart();
      });

      if (closeBtn) closeBtn.addEventListener('click', this.closeCart);
      if (backdrop) backdrop.addEventListener('click', this.closeCart);

      // Slide-over cart drawer opening on native WooCommerce added_to_cart
      $(document.body).on('added_to_cart', (event, fragments, cart_hash, $button) => {
        $('.added_to_cart').remove();
        if (typeof this.closeQuickView === 'function') {
          this.closeQuickView();
        }
        this.openCart();
        this.refreshIcons();
      });

      // Remove WooCommerce default 'View cart' text button right away on click
      $(document).on('click', '.ajax_add_to_cart, .add_to_cart_button', function () {
        setTimeout(() => $('.added_to_cart').remove(), 50);
        setTimeout(() => $('.added_to_cart').remove(), 250);
        setTimeout(() => $('.added_to_cart').remove(), 600);
      });

      // High-priority capture-phase Add to Bag listener ensures instant AJAX cart addition across all homepage & archive widgets
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.ajax_add_to_cart, .add_to_cart_button');
        if (btn) {
          const pid = btn.dataset.product_id || btn.dataset.productId || btn.getAttribute('data-product_id') || btn.getAttribute('data-product-id');
          if (!pid) return;

          e.preventDefault();
          e.stopPropagation();

          const $btn = $(btn);
          const qty = btn.dataset.quantity || btn.getAttribute('data-quantity') || 1;

          $btn.addClass('loading opacity-75 pointer-events-none');

          $.ajax({
            url: (window.kinboniCoreData && kinboniCoreData.ajax_url) || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
              action: 'kinboni_add_to_cart',
              nonce: (window.kinboniCoreData && kinboniCoreData.nonce) || '',
              product_id: pid,
              quantity: qty
            },
            success: (res) => {
              $btn.removeClass('loading opacity-75 pointer-events-none');
              const data = (res && res.data) ? res.data : res;
              const frags = (data && data.fragments) ? data.fragments : ((res && res.fragments) ? res.fragments : null);
              if (frags) {
                $.each(frags, function (selector, html) {
                  $(selector).replaceWith(html);
                });
              }
              $(document.body).trigger('wc_fragment_refresh');
              $(document.body).trigger('added_to_cart', [frags, (data ? data.cart_hash : ''), $btn]);
              $('.added_to_cart').remove();
              if (typeof Kinboni.closeQuickView === 'function') {
                Kinboni.closeQuickView();
              }
              Kinboni.openCart();
              Kinboni.refreshIcons();
              Kinboni.showToast('Item added to your shopping bag.', 'success');
            },
            error: () => {
              $btn.removeClass('loading opacity-75 pointer-events-none');
              Kinboni.showToast('Could not add to bag. Please try again.', 'error');
            }
          });
        }
      }, true);

      // Drawer quantity steppers
      $(document).on('click', '.btn-drawer-qty-plus, .btn-drawer-qty-minus', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const key = $btn.data('key');
        const qty = parseInt($btn.data('qty'), 10);
        if (key && qty >= 0) {
          $btn.addClass('opacity-50 pointer-events-none');
          $.ajax({
            url: (window.kinboniCoreData && kinboniCoreData.ajax_url) || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
              action: 'kinboni_update_cart_qty',
              nonce: (window.kinboniCoreData && kinboniCoreData.nonce) || '',
              cart_item_key: key,
              quantity: qty
            },
            success: (res) => {
              const data = (res && res.data) ? res.data : res;
              const frags = (data && data.fragments) ? data.fragments : ((res && res.fragments) ? res.fragments : null);
              if (frags) {
                $.each(frags, function (selector, html) {
                  $(selector).replaceWith(html);
                });
              }
              $(document.body).trigger('wc_fragment_refresh');
              Kinboni.refreshIcons();
            },
            complete: () => {
              $btn.removeClass('opacity-50 pointer-events-none');
            }
          });
        }
      });

      // Drawer remove item
      $(document).on('click', '.btn-drawer-remove-item', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const key = $btn.data('key');
        if (key) {
          $btn.closest('.flex.items-center.gap-4').addClass('opacity-40 pointer-events-none');
          $.ajax({
            url: (window.kinboniCoreData && kinboniCoreData.ajax_url) || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
              action: 'kinboni_remove_cart_item',
              nonce: (window.kinboniCoreData && kinboniCoreData.nonce) || '',
              cart_item_key: key
            },
            success: (res) => {
              const data = (res && res.data) ? res.data : res;
              const frags = (data && data.fragments) ? data.fragments : ((res && res.fragments) ? res.fragments : null);
              if (frags) {
                $.each(frags, function (selector, html) {
                  $(selector).replaceWith(html);
                });
              }
              $(document.body).trigger('wc_fragment_refresh');
              Kinboni.refreshIcons();
              Kinboni.showToast('Item removed from bag.', 'info');
            },
            error: () => {
              $btn.closest('.flex.items-center.gap-4').removeClass('opacity-40 pointer-events-none');
              Kinboni.showToast('Could not remove item. Please try again.', 'error');
            }
          });
        }
      });

      // Apply promo inside cart drawer
      const promoForm = document.getElementById('cart-promo-form');
      if (promoForm) {
        promoForm.addEventListener('submit', (e) => {
          e.preventDefault();
          const input = document.getElementById('cart-promo-input');
          const code = input ? input.value.trim() : '';
          if (!code) {
            Kinboni.showToast('Please enter a voucher code.', 'error');
            return;
          }

          $.ajax({
            url: kinboniCoreData.ajax_url,
            type: 'POST',
            data: {
              action: 'kinboni_apply_coupon',
              nonce: kinboniCoreData.nonce,
              coupon_code: code
            },
            success: (res) => {
              if (res.success) {
                Kinboni.showToast(res.data.message, 'success');
                $(document.body).trigger('wc_fragment_refresh');
              } else {
                Kinboni.showToast(res.data.message, 'error');
              }
            }
          });
        });
      }

      // Quick COD trigger from cart footer
      $(document).on('click', '#cart-checkout-btn', (e) => {
        e.preventDefault();
        this.closeCart();
        this.openQuickOrder(null);
      });

      // Refresh icons after WooCommerce fragments update
      $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', () => {
        this.refreshIcons();
      });
    },

    // =========================================================================
    // 2. FAST 3-FIELD CASH ON DELIVERY QUICK ORDER MODAL
    // =========================================================================
    initQuickOrder() {
      const modal = document.getElementById('checkout-modal');
      const backdrop = document.getElementById('checkout-backdrop');
      const closeBtn = document.getElementById('close-checkout-modal-btn');
      const successCloseBtn = document.getElementById('close-success-view-btn');
      const form = document.getElementById('quick-order-form');

      this.openQuickOrder = (productData = null) => {
        if (!modal) return;
        this.state.currentOrderProduct = productData;
        this.state.promoDiscount = 0;
        this.renderQuickOrderSummary();

        // Reset views
        const formView = document.getElementById('checkout-form-view');
        const successView = document.getElementById('checkout-success-view');
        if (formView) formView.classList.remove('hidden');
        if (successView) successView.classList.add('hidden');

        // Reset applied promo
        const appliedPromo = document.getElementById('order-modal-applied-promo');
        if (appliedPromo) appliedPromo.innerHTML = '';
        const discRow = document.getElementById('order-modal-discount-row');
        if (discRow) discRow.classList.add('hidden');

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        this.refreshIcons();
      };

      this.closeQuickOrder = () => {
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      };

      // Trigger buttons from product card or single product page
      $(document).on('click', '.btn-trigger-quick-order', function (e) {
        e.preventDefault();
        const btn = $(this);
        const pid = btn.data('product-id');

        if (pid) {
          const productData = {
            id: pid,
            name: btn.data('product-name'),
            price: parseFloat(btn.data('product-price')) || 0,
            image: btn.data('product-image')
          };
          Kinboni.openQuickOrder(productData);
        } else {
          Kinboni.openQuickOrder(null);
        }
      });

      if (closeBtn) closeBtn.addEventListener('click', this.closeQuickOrder);
      if (backdrop) backdrop.addEventListener('click', this.closeQuickOrder);
      if (successCloseBtn) successCloseBtn.addEventListener('click', this.closeQuickOrder);

      // Delivery Zone selector
      const zoneInside = document.getElementById('zone-label-inside');
      const zoneOutside = document.getElementById('zone-label-outside');

      if (zoneInside) {
        zoneInside.addEventListener('click', () => {
          this.state.deliveryZone = 'inside';
          this.state.shippingFee = parseFloat(kinboniCoreData.fee_inside_dhaka) || 70;
          if (zoneOutside) zoneOutside.classList.remove('active');
          zoneInside.classList.add('active');
          this.updateQuickOrderTotals();
        });
      }

      if (zoneOutside) {
        zoneOutside.addEventListener('click', () => {
          this.state.deliveryZone = 'outside';
          this.state.shippingFee = parseFloat(kinboniCoreData.fee_outside_dhaka) || 130;
          if (zoneInside) zoneInside.classList.remove('active');
          zoneOutside.classList.add('active');
          this.updateQuickOrderTotals();
        });
      }

      // Promo button click handler
      $(document).on('click', '#order-modal-promo-btn', () => {
        this.applyModalPromo();
      });

      // Handle form submission
      if (form) {
        form.addEventListener('submit', (e) => {
          e.preventDefault();
          this.submitQuickOrder();
        });
      }
    },

    applyModalPromo() {
      const input = document.getElementById('order-modal-promo-input');
      const code = input ? input.value.trim().toUpperCase() : '';
      const promoDisplay = document.getElementById('order-modal-applied-promo');
      const discRow = document.getElementById('order-modal-discount-row');
      const discAmount = document.getElementById('order-modal-discount-amount');

      if (!code) {
        this.showToast('Please enter a privilege code.', 'error');
        return;
      }

      const prodId = this.state.currentOrderProduct ? this.state.currentOrderProduct.id : 0;

      $.ajax({
        url: (window.kinboniCoreData && kinboniCoreData.ajax_url) || '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
          action: 'kinboni_validate_promo',
          nonce: (window.kinboniCoreData && kinboniCoreData.nonce) || '',
          promo_code: code,
          product_id: prodId,
          quantity: 1
        },
        success: (res) => {
          if (res && res.success && res.data) {
            const discount = Math.round(res.data.discount || 0);
            this.state.promoDiscount = discount;
            this.state.appliedPromoCode = code;

            if (discRow) discRow.classList.remove('hidden');
            if (discAmount) discAmount.textContent = '-৳' + discount.toLocaleString();

            if (promoDisplay) {
              promoDisplay.innerHTML = `
                <div class="flex items-center justify-between p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                  <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5"></i> Code ${Kinboni.esc(code)} Applied</span>
                  <span>-৳${discount.toLocaleString()}</span>
                </div>
              `;
              this.refreshIcons();
            }

            this.updateQuickOrderTotals();
            this.showToast(res.data.message || `Privilege Code ${code} applied!`, 'success');
          } else {
            this.showToast('Invalid or expired privilege voucher.', 'error');
          }
        },
        error: () => {
          this.showToast('Invalid or expired privilege voucher.', 'error');
        }
      });
    },

    renderQuickOrderSummary() {
      const container = document.getElementById('checkout-items-preview');
      if (!container) return;

      const p = this.state.currentOrderProduct;
      if (p) {
        this.state.subtotal = p.price;
        container.innerHTML = `
          <div class="flex items-center gap-3 p-3 rounded-2xl bg-stone-50 dark:bg-stone-800 border border-stone-200/70 dark:border-stone-700">
            <img src="${p.image}" alt="${Kinboni.esc(p.name)}" class="w-14 h-16 rounded-xl object-cover bg-stone-100 flex-shrink-0" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2256%22 height=%2264%22 viewBox=%220 0 56 64%22%3E%3Crect width=%2256%22 height=%2264%22 fill=%22%23e7e5e4%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a8a29e%22 font-size=%2210%22%3ENo Image%3C/text%3E%3C/svg%3E';this.onerror=null;" />
            <div class="flex-grow min-w-0">
              <h4 class="font-serif text-sm font-semibold text-stone-900 dark:text-white truncate">${Kinboni.esc(p.name)}</h4>
              <p class="text-xs text-stone-500">Qty: 1</p>
              <p class="font-serif text-xs font-bold text-gold-700 dark:text-gold-400">৳${p.price.toLocaleString()}</p>
            </div>
          </div>
        `;
      } else {
        container.innerHTML = `<p class="text-xs text-stone-500 py-2">Ordering items from current shopping bag.</p>`;
      }

      this.updateQuickOrderTotals();
    },

    updateQuickOrderTotals() {
      const subtotalEl = document.getElementById('order-modal-subtotal');
      const shippingEl = document.getElementById('order-modal-shipping');
      const totalEl = document.getElementById('order-modal-total');

      const fee = this.state.shippingFee;
      const subtotal = this.state.subtotal;
      const discount = this.state.promoDiscount || 0;
      const total = Math.max(0, subtotal - discount) + fee;

      if (subtotalEl) subtotalEl.textContent = '৳' + subtotal.toLocaleString();
      if (shippingEl) shippingEl.textContent = '৳' + fee.toLocaleString();
      if (totalEl) totalEl.textContent = '৳' + total.toLocaleString();
    },

    submitQuickOrder() {
      const nameInput = document.getElementById('order-customer-name');
      const phoneInput = document.getElementById('order-customer-phone');
      const addressInput = document.getElementById('order-customer-address');
      const submitBtn = document.getElementById('order-submit-button');
      const btnText = document.getElementById('order-submit-btn-text');

      const name = nameInput ? nameInput.value.trim() : '';
      const phone = phoneInput ? phoneInput.value.trim() : '';
      const address = addressInput ? addressInput.value.trim() : '';

      // Validate
      if (!name) {
        this.showToast('অনুগ্রহ করে আপনার নাম দিন।', 'error');
        if (nameInput) nameInput.focus();
        return;
      }
      if (!phone || !/^01[3-9]\d{8}$/.test(phone.replace(/[^0-9]/g, ''))) {
        this.showToast('সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 01711000000)।', 'error');
        if (phoneInput) phoneInput.focus();
        return;
      }
      if (!address || address.length < 5) {
        this.showToast('সম্পূর্ণ ডেলিভারি ঠিকানা দিন।', 'error');
        if (addressInput) addressInput.focus();
        return;
      }

      // Submit
      if (submitBtn) submitBtn.disabled = true;
      if (btnText) btnText.textContent = 'অর্ডার প্রক্রিয়া করা হচ্ছে...';

      const data = {
        action: 'kinboni_quick_order_submit',
        nonce: kinboniCoreData.nonce,
        name: name,
        phone: phone,
        address: address,
        zone: this.state.deliveryZone,
        product_id: this.state.currentOrderProduct ? this.state.currentOrderProduct.id : 0,
        quantity: 1,
        promo_code: this.state.appliedPromoCode || (document.getElementById('order-modal-promo-input') ? document.getElementById('order-modal-promo-input').value.trim() : ''),
        kinboni_hp: $('#quick-order-form').find('input[name="kinboni_hp"]').val() || ''
      };

      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: data,
        success: (res) => {
          if (submitBtn) submitBtn.disabled = false;
          if (btnText) btnText.textContent = 'Confirm Order (Cash on Delivery)';

          if (res.success) {
            // Show Success View
            const formView = document.getElementById('checkout-form-view');
            const successView = document.getElementById('checkout-success-view');

            if (formView) formView.classList.add('hidden');
            if (successView) successView.classList.remove('hidden');

            // Populate Success Details
            const nameEl = document.getElementById('confirmed-customer-name');
            const orderIdEl = document.getElementById('confirmed-order-id');
            const phoneEl = document.getElementById('confirmed-customer-phone');
            const addressEl = document.getElementById('confirmed-customer-address');
            const totalEl = document.getElementById('confirmed-order-total');
            const whatsappLink = document.getElementById('confirmed-whatsapp-link');

            if (nameEl) nameEl.textContent = res.data.name;
            if (orderIdEl) orderIdEl.textContent = res.data.order_number;
            if (phoneEl) phoneEl.textContent = res.data.phone;
            if (addressEl) addressEl.textContent = res.data.address;
            if (totalEl) totalEl.innerHTML = res.data.order_total;
            if (whatsappLink) {
              if (res.data.whatsapp_url) {
                whatsappLink.href = res.data.whatsapp_url;
                whatsappLink.classList.remove('hidden');
                whatsappLink.style.display = '';
              } else {
                whatsappLink.href = '#';
                whatsappLink.classList.add('hidden');
                whatsappLink.style.display = 'none';
              }
            }

            this.trackAnalyticsEvent('Purchase', {
              transaction_id: res.data.order_id,
              value: parseFloat(res.data.order_total_raw || res.data.total || 0),
              currency: (window.kinboniCoreData && kinboniCoreData.currency_code) || 'BDT'
            });
            this.showToast(res.data.message, 'success');
            $(document.body).trigger('wc_fragment_refresh');
          } else {
            this.showToast(res.data.message || 'Error creating order.', 'error');
          }
        },
        error: () => {
          if (submitBtn) submitBtn.disabled = false;
          if (btnText) btnText.textContent = 'Confirm Order (Cash on Delivery)';
          this.showToast('সার্ভারে সমস্যা হয়েছে। পুনরায় চেষ্টা করুন।', 'error');
        }
      });
    },

    // =========================================================================
    // 3. PRODUCT QUICK VIEW MODAL
    // =========================================================================
    initQuickView() {
      const modal = document.getElementById('quickview-modal');
      const backdrop = document.getElementById('quickview-backdrop');
      const closeBtn = document.getElementById('close-quickview-btn');
      const content = document.getElementById('quickview-content');

      this.openQuickView = (pid) => {
        if (!pid || !modal || !content) return;

        content.innerHTML = `<div class="p-16 text-center text-sm font-serif animate-pulse">Loading Atelier Creation...</div>`;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        $.ajax({
          url: kinboniCoreData.ajax_url,
          type: 'POST',
          data: {
            action: 'kinboni_get_quick_view',
            nonce: kinboniCoreData.nonce,
            product_id: pid
          },
          success: (res) => {
            if (res.success) {
              const p = res.data;
              content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center p-6 sm:p-8">
                  <div class="aspect-[4/5] rounded-2xl overflow-hidden bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800">
                    <img id="qv-main-img" src="${p.image}" alt="${Kinboni.esc(p.name)}" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22500%22 viewBox=%220 0 400 500%22%3E%3Crect width=%22400%22 height=%22500%22 fill=%22%23e7e5e4%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a8a29e%22 font-size=%2218%22%3ENo Image%3C/text%3E%3C/svg%3E';this.onerror=null;" />
                  </div>
                  <div class="space-y-4">
                    <span class="text-xs font-bold uppercase tracking-widest text-gold-700 dark:text-gold-400">${Kinboni.esc(p.category)}</span>
                    <h3 class="font-serif text-2xl font-medium text-stone-900 dark:text-white">${Kinboni.esc(p.name)}</h3>
                    <div class="font-serif text-xl font-bold text-stone-900 dark:text-white">${p.price_html}</div>
                    <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed">${Kinboni.esc(p.short_desc)}</p>
                    ${(p.show_delivery_info || p.show_sizechart) ? `
                    <div class="pt-2 space-y-1.5 text-[11px] text-stone-600 dark:text-stone-400 border-t border-stone-200/70 dark:border-stone-800/70">
                      ${p.show_delivery_info ? `<div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-medium"><i data-lucide="truck" class="w-3.5 h-3.5"></i><span>Express Dispatch: ${Kinboni.esc(p.delivery_time || '1 - 2 Business Days')}</span></div>` : ''}
                      ${p.show_sizechart ? `<div class="flex items-center gap-2"><i data-lucide="ruler" class="w-3.5 h-3.5 text-gold-500"></i><span>Standard Atelier Fit &bull; Complimentary Sizing Adjustment</span></div>` : ''}
                    </div>` : ''}
                    <div class="pt-4 grid grid-cols-2 gap-2">
                      <a href="${p.add_to_cart_url}" class="ajax_add_to_cart add_to_cart_button btn-outline rounded-xl py-3 text-xs text-center font-bold" data-product_id="${p.id}">Add to Bag</a>
                      <button type="button" class="btn-trigger-quick-order btn-primary rounded-xl py-3 text-xs text-center font-bold" data-product-id="${p.id}" data-product-name="${Kinboni.esc(p.name)}" data-product-price="${p.price_raw}" data-product-image="${p.image}">Order COD</button>
                    </div>
                  </div>
                </div>
              `;
              Kinboni.refreshIcons();
            }
          }
        });
      };

      this.closeQuickView = () => {
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      };

      if (closeBtn) closeBtn.addEventListener('click', this.closeQuickView);
      if (backdrop) backdrop.addEventListener('click', this.closeQuickView);

      // Capture listener ensures .btn-open-quickview clicks always fire
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-open-quickview');
        if (btn) {
          e.preventDefault();
          e.stopPropagation();
          const pid = btn.getAttribute('data-product-id');
          if (pid) {
            Kinboni.openQuickView(pid);
          }
        }
      }, true);

      $(document).on('click', '.btn-open-quickview', function (e) {
        e.preventDefault();
        const pid = $(this).data('product-id');
        Kinboni.openQuickView(pid);
      });
    },

    // =========================================================================
    // 4. LIVE SEARCH MODAL
    // =========================================================================
    initSearchModal() {
      const modal = document.getElementById('search-modal');
      const backdrop = document.getElementById('search-backdrop');
      const closeBtn = document.getElementById('close-search-btn');
      const input = document.getElementById('search-input');
      const resultsContainer = document.getElementById('search-results-container');
      const countEl = document.getElementById('search-count');

      this.openSearch = () => {
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        if (input) setTimeout(() => input.focus(), 50);
      };

      this.closeSearch = () => {
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      };

      $(document).on('click', '.open-search-btn', (e) => {
        e.preventDefault();
        this.openSearch();
      });

      if (closeBtn) closeBtn.addEventListener('click', this.closeSearch);
      if (backdrop) backdrop.addEventListener('click', this.closeSearch);

      // Debounced live typing search
      let searchTimeout = null;
      if (input && resultsContainer) {
        input.addEventListener('input', () => {
          clearTimeout(searchTimeout);
          const term = input.value.trim();

          if (term.length < 2) {
            resultsContainer.innerHTML = '';
            if (countEl) countEl.textContent = 'Live Atelier Search';
            return;
          }

          if (countEl) countEl.textContent = 'Searching creations...';
          resultsContainer.innerHTML = `<div class="p-8 text-center text-xs text-stone-500 animate-pulse">Searching luxury archive...</div>`;

          searchTimeout = setTimeout(() => {
            $.ajax({
              url: kinboniCoreData.ajax_url,
              type: 'POST',
              data: {
                action: 'kinboni_ajax_search',
                nonce: kinboniCoreData.nonce,
                term: term
              },
              success: (res) => {
                if (res.success && res.data.results && res.data.results.length > 0) {
                  if (countEl) countEl.textContent = `Found ${res.data.results.length} Creations`;
                  resultsContainer.innerHTML = `
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                      ${res.data.results.map((p) => `
                        <a href="${p.link}" class="flex items-center gap-3 p-3 rounded-2xl border border-stone-200/80 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 hover:border-gold-500 transition-colors group">
                          <img src="${p.image}" alt="${Kinboni.esc(p.name)}" class="w-12 h-14 rounded-xl object-cover bg-stone-200 dark:bg-stone-900 flex-shrink-0" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2256%22 viewBox=%220 0 48 56%22%3E%3Crect width=%2248%22 height=%2256%22 fill=%22%23e7e5e4%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a8a29e%22 font-size=%228%22%3ENo Image%3C/text%3E%3C/svg%3E';this.onerror=null;" />
                          <div class="min-w-0">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-gold-700 dark:text-gold-400 block">${Kinboni.esc(p.category)}</span>
                            <h4 class="font-serif text-sm font-semibold text-stone-900 dark:text-white truncate group-hover:text-gold-600 transition-colors">${Kinboni.esc(p.name)}</h4>
                            <div class="text-xs font-bold text-stone-900 dark:text-white">${p.price_html}</div>
                          </div>
                        </a>
                      `).join('')}
                    </div>
                  `;
                } else {
                  if (countEl) countEl.textContent = 'No Creations Found';
                  resultsContainer.innerHTML = `<div class="p-8 text-center text-xs text-stone-400">No creations matched "${Kinboni.esc(term)}".</div>`;
                }
              }
            });
          }, 300);
        });

        // Quick suggestion pills
        $(document).on('click', '.search-suggestion-tag', function (e) {
          e.preventDefault();
          const query = $(this).data('query');
          if (query && input) {
            input.value = query;
            input.dispatchEvent(new Event('input'));
          }
        });
      }
    },

    // =========================================================================
    // 5. 15-SECOND LIVE SALES SOCIAL PROOF POPUP WITH SYNTHESIZER CHIME
    // =========================================================================
    initLiveSales() {
      const popup = document.getElementById('live-sales-popup');
      if (!popup) return;
      if (window.kinboniCoreData && kinboniCoreData.live_sales_enabled === false) {
        popup.classList.add('hidden');
        return;
      }

      const playChime = () => {
        if (window.kinboniCoreData && kinboniCoreData.live_sales_sound === false) return;
        try {
          const AudioContext = window.AudioContext || window.webkitAudioContext;
          if (!AudioContext) return;
          const ctx = new AudioContext();
          const now = ctx.currentTime;

          [523.25, 659.25, 783.99].forEach((freq, i) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, now + i * 0.12);
            gain.gain.setValueAtTime(0.06, now + i * 0.12);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + i * 0.12 + 0.8);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now + i * 0.12);
            osc.stop(now + i * 0.12 + 0.8);
          });
        } catch (e) {}
      };

      // Fetch live sales data
      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_get_live_sales',
          nonce: kinboniCoreData.nonce
        },
        success: (res) => {
          if (res.success && res.data.sales && res.data.sales.length > 0) {
            this.state.liveSales = res.data.sales;
            this.startLiveSalesLoop(playChime);
          }
        }
      });

      // Dismiss button handler
      $(document).on('click', '#close-live-sale-btn', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.hideLiveSale();
      });

      // Card click opens currently displayed product
      $(document).on('click', '#live-sales-card', () => {
        const item = this.state.currentDisplayedSale || this.state.liveSales[this.state.liveSaleIndex];
        if (item && item.id) {
          this.openQuickView(item.id);
        }
      });
    },

    hideLiveSale() {
      const popup = document.getElementById('live-sales-popup');
      if (popup) {
        popup.classList.add('translate-y-8', 'opacity-0');
        setTimeout(() => popup.classList.add('hidden'), 500);
      }
    },

    startLiveSalesLoop(playChime) {
      const popup = document.getElementById('live-sales-popup');
      const buyerEl = document.getElementById('live-sale-buyer');
      const timeEl = document.getElementById('live-sale-time');
      const nameEl = document.getElementById('live-sale-name');
      const priceEl = document.getElementById('live-sale-price');
      const imgEl = document.getElementById('live-sale-image');
      const quickViewBtn = document.getElementById('live-sale-quickview-btn');

      if (!popup) return;

      const showSale = () => {
        const item = this.state.liveSales[this.state.liveSaleIndex];
        if (!item) return;
        this.state.currentDisplayedSale = item;

        if (buyerEl) buyerEl.textContent = item.customer;
        if (timeEl) timeEl.textContent = item.time || '';
        if (nameEl) nameEl.textContent = item.name;
        if (priceEl) priceEl.innerHTML = item.price;
        if (imgEl) imgEl.src = item.image;
        if (quickViewBtn) quickViewBtn.setAttribute('data-product-id', item.id);

        // Animate in
        popup.classList.remove('hidden', 'translate-y-8', 'opacity-0');
        playChime();

        // Hide after 6 seconds
        setTimeout(() => {
          popup.classList.add('translate-y-8', 'opacity-0');
          setTimeout(() => popup.classList.add('hidden'), 500);
        }, 6000);

        this.state.liveSaleIndex = (this.state.liveSaleIndex + 1) % this.state.liveSales.length;
      };

      const intervalSec = (window.kinboniCoreData && parseInt(kinboniCoreData.live_sales_interval, 10) >= 5)
        ? parseInt(kinboniCoreData.live_sales_interval, 10)
        : 15;
      setTimeout(showSale, 4000);
      setInterval(showSale, intervalSec * 1000);
    },

    // =========================================================================
    // 6. WISHLIST ENGINE
    // =========================================================================
    initWishlist() {
      this.updateWishlistCount();
      this.syncWishlistButtons();

      // Capture listener ensures .btn-toggle-wishlist clicks always execute
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-toggle-wishlist');
        if (btn) {
          e.preventDefault();
          e.stopPropagation();
          const pid = btn.getAttribute('data-product-id');
          if (pid) {
            Kinboni.toggleWishlist(pid, $(btn));
          }
        }
      }, true);

      $(document).on('click', '.btn-toggle-wishlist', function (e) {
        e.preventDefault();
        const pid = $(this).data('product-id');
        Kinboni.toggleWishlist(pid, $(this));
      });

      const drawer = document.getElementById('wishlist-drawer');
      const backdrop = document.getElementById('wishlist-drawer-backdrop');
      const closeBtn = document.getElementById('close-wishlist-btn');

      this.openWishlist = () => {
        if (!drawer) return;
        drawer.classList.remove('translate-x-full');
        if (backdrop) {
          backdrop.classList.remove('hidden');
          setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
        }
        document.body.classList.add('overflow-hidden');
        this.renderWishlistItems();
        Kinboni.refreshIcons();
      };

      this.closeWishlist = () => {
        if (!drawer) return;
        drawer.classList.add('translate-x-full');
        if (backdrop) {
          backdrop.classList.add('opacity-0');
          setTimeout(() => backdrop.classList.add('hidden'), 300);
        }
        document.body.classList.remove('overflow-hidden');
      };

      $(document).on('click', '.open-wishlist-btn', (e) => {
        e.preventDefault();
        this.openWishlist();
      });

      if (closeBtn) closeBtn.addEventListener('click', this.closeWishlist);
      if (backdrop) backdrop.addEventListener('click', this.closeWishlist);

      // Also render on dedicated wishlist page if present
      if (document.getElementById('wishlist-page-container') || document.getElementById('wishlist-page-grid') || document.querySelector('.kinboni-wishlist-page-grid')) {
        this.renderWishlistPageItems();
      }

      // Add All Saved Items to Bag click handler
      $(document).on('click', '.btn-wishlist-move-all-to-bag', (e) => {
        e.preventDefault();
        this.moveAllWishlistToBag();
      });

      // Remove from wishlist click handler
      $(document).on('click', '.btn-remove-wishlist-item', function (e) {
        e.preventDefault();
        const pid = $(this).data('product-id');
        Kinboni.removeFromWishlist(pid);
      });
    },

    syncWishlistButtons() {
      const list = (this.state && this.state.wishlist) ? this.state.wishlist : [];
      document.querySelectorAll('.btn-toggle-wishlist').forEach((btn) => {
        const pid = parseInt(btn.getAttribute('data-product-id'), 10);
        const svg = btn.querySelector('svg');
        const isFav = list.includes(pid);
        if (isFav) {
          btn.classList.add('active', 'text-rose-500');
        } else {
          btn.classList.remove('active', 'text-rose-500');
        }
        if (svg) {
          if (isFav) {
            svg.classList.add('fill-rose-500', 'text-rose-500', 'stroke-rose-500');
            svg.classList.remove('fill-transparent');
            svg.setAttribute('fill', '#f43f5e');
            svg.setAttribute('stroke', '#f43f5e');
          } else {
            svg.classList.remove('fill-rose-500', 'text-rose-500', 'stroke-rose-500');
            svg.classList.add('fill-transparent');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
          }
        }
      });
    },

    toggleWishlist(productId, btn) {
      productId = parseInt(productId, 10);
      let list = this.state.wishlist;
      const idx = list.indexOf(productId);

      if (idx > -1) {
        list.splice(idx, 1);
        this.showToast('Item removed from wishlist.', 'info');
        if (window.kinboniCoreData && kinboniCoreData.ajax_url) {
          $.post(kinboniCoreData.ajax_url, { action: 'kinboni_track_wishlist', nonce: kinboniCoreData.nonce, product_id: productId, wishlist_action: 'remove' });
        }
      } else {
        list.push(productId);
        this.showToast('Added to your wishlist!', 'success');
        if (window.kinboniCoreData && kinboniCoreData.ajax_url) {
          $.post(kinboniCoreData.ajax_url, { action: 'kinboni_track_wishlist', nonce: kinboniCoreData.nonce, product_id: productId, wishlist_action: 'add' });
        }
      }

      this.state.wishlist = list;
      localStorage.setItem('kinboni_wishlist', JSON.stringify(list));
      this.updateWishlistCount();
      this.syncWishlistButtons();
    },

    removeFromWishlist(productId) {
      productId = parseInt(productId, 10);
      let list = this.state.wishlist.filter(id => id !== productId);
      this.state.wishlist = list;
      localStorage.setItem('kinboni_wishlist', JSON.stringify(list));
      this.updateWishlistCount();
      this.renderWishlistItems();
      this.renderWishlistPageItems();
      this.showToast('Item removed from wishlist.', 'info');
      if (window.kinboniCoreData && kinboniCoreData.ajax_url) {
        $.post(kinboniCoreData.ajax_url, { action: 'kinboni_track_wishlist', nonce: kinboniCoreData.nonce, product_id: productId, wishlist_action: 'remove' });
      }
    },

    renderWishlistItems() {
      const container = document.getElementById('wishlist-items-container');
      const emptyState = document.getElementById('wishlist-empty-state');
      if (!container) return;

      const list = this.state.wishlist;
      if (!list || list.length === 0) {
        container.innerHTML = '';
        if (emptyState) emptyState.classList.remove('hidden');
        return;
      }

      if (emptyState) emptyState.classList.add('hidden');
      container.innerHTML = `<div class="p-8 text-center text-xs text-stone-500 animate-pulse">Loading saved creations...</div>`;

      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_get_wishlist_items',
          nonce: kinboniCoreData.nonce,
          product_ids: list
        },
        success: (res) => {
          if (res.success && res.data.items && res.data.items.length > 0) {
            container.innerHTML = res.data.items.map(p => `
              <div class="flex items-center gap-3 p-3 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60">
                <img src="${p.image}" alt="${Kinboni.esc(p.name)}" class="w-14 h-16 rounded-xl object-cover bg-stone-100 flex-shrink-0" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2256%22 height=%2264%22 viewBox=%220 0 56 64%22%3E%3Crect width=%2256%22 height=%2264%22 fill=%22%23e7e5e4%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a8a29e%22 font-size=%2210%22%3ENo Image%3C/text%3E%3C/svg%3E';this.onerror=null;" />
                <div class="flex-grow min-w-0">
                  <h4 class="font-serif text-sm font-semibold text-stone-900 dark:text-white truncate">${Kinboni.esc(p.name)}</h4>
                  <div class="font-serif text-xs font-bold text-gold-700 dark:text-gold-400">${p.price_html || ('৳' + Number(p.price).toLocaleString())}</div>
                  <div class="pt-1.5 flex items-center gap-2">
                    <a href="${p.cart_url}" class="ajax_add_to_cart add_to_cart_button px-3 py-1 bg-gold-400 hover:bg-gold-500 text-stone-950 font-bold uppercase text-[10px] rounded-lg tracking-wider" data-product_id="${p.id}">Add to Bag</a>
                    <button type="button" class="btn-open-quickview text-stone-400 hover:text-gold-500 text-xs p-1" data-product-id="${p.id}" title="Quick View">
                      <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" class="btn-remove-wishlist-item text-stone-400 hover:text-rose-500 text-xs p-1" data-product-id="${p.id}">
                      <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                  </div>
                </div>
              </div>
            `).join('');
            Kinboni.refreshIcons();
          } else {
            container.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
          }
        }
      });
    },

    moveAllWishlistToBag() {
      const list = this.state.wishlist;
      if (!list || list.length === 0) {
        this.showToast('No items in your wishlist to move.', 'info');
        return;
      }

      const $btn = $('.btn-wishlist-move-all-to-bag');
      const origText = $btn.first().text();
      $btn.prop('disabled', true).text('Moving to Bag...');

      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_move_all_wishlist_to_bag',
          nonce: kinboniCoreData.nonce,
          product_ids: list
        },
        success: (res) => {
          if (res.success) {
            this.state.wishlist = [];
            localStorage.setItem('kinboni_wishlist', JSON.stringify([]));
            this.updateWishlistCount();
            this.renderWishlistItems();
            this.renderWishlistPageItems();
            this.showToast('All saved creations moved to your Bag!', 'success');
            if (typeof this.openCart === 'function') {
              this.openCart();
            }
            $(document.body).trigger('wc_fragment_refresh');
          } else {
            this.showToast(res.data && res.data.message ? res.data.message : 'Could not move items to bag.', 'error');
          }
        },
        error: () => {
          this.showToast('Server error while moving creations to bag.', 'error');
        },
        complete: () => {
          $btn.prop('disabled', false).text(origText);
        }
      });
    },

    renderWishlistPageItems() {
      const grid = document.getElementById('wishlist-page-grid') || 
                   document.querySelector('.kinboni-wishlist-page-grid') || 
                   document.getElementById('wishlist-page-container');
      if (!grid) return;

      const isEditMode = window.elementorFrontend && typeof window.elementorFrontend.isEditMode === 'function' && window.elementorFrontend.isEditMode();
      const list = this.state.wishlist || [];

      // In Elementor edit mode with no wishlist items saved locally, keep the rich preview cards visible so user can customize styling
      if (isEditMode && list.length === 0) {
        Kinboni.refreshIcons();
        return;
      }

      const shopUrl = grid.getAttribute('data-shop-url') || (kinboniCoreData.ajax_url.replace('/wp-admin/admin-ajax.php', '') + '/shop');
      const emptyTitle = grid.getAttribute('data-empty-title') || 'Your Wishlist is Empty';
      const emptyDesc = grid.getAttribute('data-empty-desc') || 'Tap the heart icon on any creation to bookmark it in your personal archive.';
      const emptyBtn = grid.getAttribute('data-empty-btn') || 'Explore Shop Archive';

      if (list.length === 0) {
        grid.className = 'kinboni-wishlist-page-grid block';
        grid.innerHTML = `
          <div class="text-center py-16 sm:py-20 bg-white/95 dark:bg-stone-900/90 rounded-3xl border border-stone-200/80 dark:border-stone-800 p-8 space-y-4 shadow-sm max-w-xl mx-auto">
            <div class="w-16 h-16 rounded-full bg-gold-100 dark:bg-gold-950/40 text-gold-600 dark:text-gold-400 flex items-center justify-center mx-auto mb-2">
              <i data-lucide="heart" class="w-8 h-8 opacity-60"></i>
            </div>
            <h3 class="font-serif text-2xl sm:text-3xl font-medium text-stone-900 dark:text-white">${emptyTitle}</h3>
            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 max-w-md mx-auto leading-relaxed">${emptyDesc}</p>
            <div class="pt-3">
              <a href="${shopUrl}" class="inline-flex px-8 py-3.5 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95">${emptyBtn}</a>
            </div>
          </div>
        `;
        Kinboni.refreshIcons();
        return;
      }

      grid.className = 'kinboni-wishlist-page-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6';
      grid.innerHTML = `<div class="col-span-full py-16 text-center text-xs text-stone-500 animate-pulse"><i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-2 text-gold-400 animate-spin"></i><span>Loading saved creations...</span></div>`;
      Kinboni.refreshIcons();

      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_get_wishlist_items',
          nonce: kinboniCoreData.nonce,
          product_ids: list
        },
        success: (res) => {
          if (res.success && res.data.items && res.data.items.length > 0) {
            grid.innerHTML = res.data.items.map(p => `
              <div class="kinboni-product-card group relative flex flex-col bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl overflow-hidden shadow-sm hover:border-gold-500/40 transition-all duration-300">
                <div class="relative w-full aspect-[4/5] bg-stone-100 dark:bg-stone-950 overflow-hidden">
                  <img src="${p.image}" alt="${Kinboni.esc(p.name)}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22500%22 viewBox=%220 0 400 500%22%3E%3Crect width=%22400%22 height=%22500%22 fill=%22%23e7e5e4%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a8a29e%22 font-size=%2218%22%3ENo Image%3C/text%3E%3C/svg%3E';this.onerror=null;" />
                  <div class="absolute top-3 right-3 z-10">
                    <button type="button" class="btn-remove-wishlist-item w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-rose-500 shadow-md hover:bg-rose-500 hover:text-white transition-colors" data-product-id="${p.id}" title="Remove from wishlist">
                      <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                  </div>
                  ${p.discount_pct ? `
                  <div class="absolute top-3 left-3 z-10">
                    <span class="inline-flex px-2.5 py-1 text-[11px] font-bold tracking-wider rounded-full bg-rose-600 text-white shadow-md">
                      -${p.discount_pct}%
                    </span>
                  </div>` : ''}
                </div>
                <div class="p-4 sm:p-5 flex flex-col flex-grow">
                  <span class="text-xs uppercase tracking-wider font-bold text-gold-700 dark:text-gold-400 mb-1">${Kinboni.esc(p.category || 'Atelier Collection')}</span>
                  <h3 class="font-serif text-lg font-bold text-stone-900 dark:text-white leading-snug truncate mb-2">
                    <a href="${p.link}" class="hover:text-gold-600 dark:hover:text-gold-400 transition-colors">${Kinboni.esc(p.name)}</a>
                  </h3>
                  <div class="flex items-baseline gap-2 mb-3">
                    ${p.price_html ? `<div class="kinboni-card-price text-stone-900 dark:text-white font-serif text-lg font-bold">${p.price_html}</div>` : `
                      <span class="font-serif text-xl font-bold text-stone-950 dark:text-white">৳${Number(p.price).toLocaleString()}</span>
                      ${p.original_price ? `<span class="text-xs text-stone-400 line-through">৳${Number(p.original_price).toLocaleString()}</span>` : ''}
                    `}
                  </div>
                  <div class="grid grid-cols-2 gap-2 mt-auto pt-3 border-t border-stone-100 dark:border-stone-800/80">
                    <a href="${p.cart_url}" class="ajax_add_to_cart add_to_cart_button py-2.5 px-3 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-wider rounded-xl text-center flex items-center justify-center gap-1.5 transition-all shadow-sm active:scale-95" data-product_id="${p.id}">
                      Add to Bag
                    </a>
                    <button type="button" class="btn-open-quickview py-2.5 px-3 border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:text-gold-600 hover:border-gold-500 text-xs font-bold uppercase tracking-wider rounded-xl text-center transition-colors flex items-center justify-center gap-1.5" data-product-id="${p.id}">
                      <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                      Quick View
                    </button>
                  </div>
                  <div class="mt-2">
                    <button type="button" class="btn-remove-wishlist-item w-full py-1.5 text-xs text-stone-400 hover:text-rose-500 transition-colors flex items-center justify-center gap-1" data-product-id="${p.id}">
                      <i data-lucide="trash-2" class="w-3 h-3"></i>
                      Remove from Wishlist
                    </button>
                  </div>
                </div>
              </div>
            `).join('');
            Kinboni.refreshIcons();
          } else {
            grid.className = 'kinboni-wishlist-page-grid block';
            grid.innerHTML = `
              <div class="text-center py-16 sm:py-20 bg-white/95 dark:bg-stone-900/90 rounded-3xl border border-stone-200/80 dark:border-stone-800 p-8 space-y-4 shadow-sm max-w-xl mx-auto">
                <div class="w-16 h-16 rounded-full bg-gold-100 dark:bg-gold-950/40 text-gold-600 dark:text-gold-400 flex items-center justify-center mx-auto mb-2">
                  <i data-lucide="heart" class="w-8 h-8 opacity-60"></i>
                </div>
                <h3 class="font-serif text-2xl sm:text-3xl font-medium text-stone-900 dark:text-white">${Kinboni.esc(emptyTitle)}</h3>
                <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 max-w-md mx-auto leading-relaxed">${Kinboni.esc(emptyDesc)}</p>
                <div class="pt-3">
                  <a href="${shopUrl}" class="inline-flex px-8 py-3.5 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95">${Kinboni.esc(emptyBtn)}</a>
                </div>
              </div>
            `;
            Kinboni.refreshIcons();
          }
        },
        error: () => {
          grid.innerHTML = `<div class="col-span-full py-12 text-center text-xs text-rose-500">Failed to load saved items. Please try refreshing.</div>`;
        }
      });
    },

    updateWishlistCount() {
      const count = this.state.wishlist.length;
      const badges = document.querySelectorAll('.wishlist-count-badge');
      badges.forEach((b) => {
        b.textContent = count;
        if (count > 0) {
          b.classList.remove('scale-0');
          b.classList.add('scale-100');
        } else {
          b.classList.remove('scale-100');
          b.classList.add('scale-0');
        }
      });
    }
  };

  // Expose global prototype controller aliases for seamless backward compatibility
  window.RouterController = window.RouterController || {
    navigate: function (route) {
      if (route === 'home') {
        window.location.href = (window.kinboniCoreData && kinboniCoreData.home_url) || '/';
      } else if (route === 'shop') {
        window.location.href = (window.kinboniCoreData && kinboniCoreData.shop_url) || '/shop/';
      } else if (route === 'checkout') {
        window.location.href = (window.kinboniCoreData && kinboniCoreData.checkout_url) || '/checkout/';
      } else if (route === 'cart') {
        window.location.href = (window.kinboniCoreData && kinboniCoreData.cart_url) || '/cart/';
      } else {
        window.location.href = '/' + route + '/';
      }
    },
    openProduct: function (productIdOrUrl) {
      if (typeof productIdOrUrl === 'string' && productIdOrUrl.indexOf('/') !== -1) {
        window.location.href = productIdOrUrl;
      } else if (productIdOrUrl) {
        const base = (window.kinboniCoreData && kinboniCoreData.home_url) || '/';
        window.location.href = base + '?p=' + productIdOrUrl;
      }
    }
  };

  window.CartController = {
    open: () => Kinboni.openCart && Kinboni.openCart(),
    close: () => Kinboni.closeCart && Kinboni.closeCart(),
    clear: () => window.CartPageController && window.CartPageController.clearCart ? window.CartPageController.clearCart() : (Kinboni.clearCart && Kinboni.clearCart()),
    render: () => window.CartPageController && window.CartPageController.fetchAndUpdate ? window.CartPageController.fetchAndUpdate() : (Kinboni.refreshCart && Kinboni.refreshCart()),
    add: (id, qty) => Kinboni.addToCart && Kinboni.addToCart(id, qty || 1),
    updateQuantity: (idOrKey, delta) => window.CartPageController && window.CartPageController.handleQuantityChange && window.CartPageController.handleQuantityChange(idOrKey, delta),
    remove: (idOrKey) => window.CartPageController && window.CartPageController.handleRemove && window.CartPageController.handleRemove(idOrKey),
    applyPromo: (code) => window.CartPageController && window.CartPageController.applyCoupon && window.CartPageController.applyCoupon(code),
    removePromo: (code) => window.CartPageController && window.CartPageController.removeCoupon && window.CartPageController.removeCoupon(code)
  };

  window.Kinboni = Kinboni;

  window.WishlistController = {
    open: () => Kinboni.openWishlist && Kinboni.openWishlist(),
    close: () => Kinboni.closeWishlist && Kinboni.closeWishlist(),
    toggle: (id) => Kinboni.toggleWishlist(id)
  };

  window.WishlistPageController = {
    render: () => Kinboni.renderWishlistPageItems && Kinboni.renderWishlistPageItems(),
    moveAllToBag: () => Kinboni.moveAllWishlistToBag && Kinboni.moveAllWishlistToBag()
  };

  window.QuickOrderController = {
    open: (productData) => Kinboni.openQuickOrder && Kinboni.openQuickOrder(productData),
    close: () => Kinboni.closeQuickOrder && Kinboni.closeQuickOrder(),
    applyModalPromo: () => Kinboni.applyModalPromo && Kinboni.applyModalPromo()
  };

  window.QuickViewController = {
    open: (id) => Kinboni.openQuickView && Kinboni.openQuickView(id),
    close: () => Kinboni.closeQuickView && Kinboni.closeQuickView()
  };

  window.LiveSalesController = {
    hide: () => Kinboni.hideLiveSale && Kinboni.hideLiveSale()
  };

  window.copyPromoCode = window.copyPromoCode || function (code) {
    if (!code) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(code).then(function () {
        Kinboni.showToast('Privilege code "' + code + '" copied to clipboard!', 'success');
      }).catch(function () {
        Kinboni.showToast('Privilege code: ' + code, 'info');
      });
    } else {
      Kinboni.showToast('Privilege code: ' + code, 'info');
    }
  };

  // =========================================================================
  // 7. HERO BANNER SLIDER & SHOWCASE CONTROLLER
  // =========================================================================
  const HeroBannerSliderController = {
    swipers: [],

    init(root) {
      if (typeof Swiper === 'undefined') return;

      const container = root || document;
      const swiperEls = container.querySelectorAll ? container.querySelectorAll('.hero-banner-swiper') : document.querySelectorAll('.hero-banner-swiper');

      swiperEls.forEach((el) => {
        if (el.swiper) {
          try { el.swiper.destroy(true, true); } catch (e) {}
        }

        const isAutoplay = el.getAttribute('data-autoplay') !== 'false';
        const delay = parseInt(el.getAttribute('data-delay'), 10) || 4500;
        const isLoop = el.getAttribute('data-loop') !== 'false';
        const speed = parseInt(el.getAttribute('data-speed'), 10) || 600;
        const effect = el.getAttribute('data-effect') || 'slide';

        const parentBox = el.closest('.hero-slider-box') || el.parentElement;
        const prevBtn = parentBox ? parentBox.querySelector('.hero-banner-prev') : null;
        const nextBtn = parentBox ? parentBox.querySelector('.hero-banner-next') : null;
        const paginationEl = parentBox ? parentBox.querySelector('.hero-banner-pagination') : el.querySelector('.swiper-pagination');

        try {
          const sw = new Swiper(el, {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: isLoop,
            speed: speed,
            effect: effect,
            grabCursor: true,
            autoHeight: false,
            watchOverflow: true,
            autoplay: isAutoplay ? {
              delay: delay,
              disableOnInteraction: false,
              pauseOnMouseEnter: true
            } : false,
            pagination: paginationEl ? {
              el: paginationEl,
              clickable: true
            } : false,
            navigation: (prevBtn && nextBtn) ? {
              prevEl: prevBtn,
              nextEl: nextBtn
            } : false
          });
          this.swipers.push(sw);
        } catch (err) {
          console.warn('Hero banner swiper init error:', err);
        }
      });

      // Recalculate layout on viewport/orientation change to prevent gaps
      if (!this._resizeAttached) {
        this._resizeAttached = true;
        window.addEventListener('resize', () => {
          this.swipers.forEach((sw) => {
            if (sw && typeof sw.update === 'function') {
              sw.update();
            }
          });
        });
      }

      // Backward compatibility for legacy triple-slider containers if present
      const legacy1 = container.querySelector ? container.querySelector('.hero-swiper-1') : null;
      if (legacy1 && !legacy1.swiper) {
        try {
          new Swiper(legacy1, { slidesPerView: 1, loop: true, autoplay: { delay: 4200 } });
        } catch (e) {}
      }

      if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
      }
      if (window.Kinboni && typeof window.Kinboni.syncWishlistButtons === 'function') {
        window.Kinboni.syncWishlistButtons();
      }
    },

    slideIndividual(idx, dir) {
      if (this.swipers && this.swipers[0]) {
        if (dir === 'prev') {
          this.swipers[0].slidePrev();
        } else {
          this.swipers[0].slideNext();
        }
      }
    }
  };

  window.HeroBannerSliderController = HeroBannerSliderController;
  window.HeroTripleSliderController = HeroBannerSliderController;

  $(document).on('click', '[data-hero-nav]', function (e) {
    e.preventDefault();
    const idx = parseInt($(this).data('slider-idx'), 10);
    const dir = $(this).data('slider-dir');
    HeroBannerSliderController.slideIndividual(idx, dir);
  });

  // Category card trigger support
  $(document).on('click', '.category-card-trigger', function (e) {
    const slug = $(this).data('category');
    const href = $(this).attr('href');
    if (window.ShopPageController && typeof window.ShopPageController.setCategory === 'function') {
      if (!href || href === '#' || href === 'javascript:void(0);') {
        e.preventDefault();
        window.ShopPageController.setCategory(slug);
      }
    }
  });

  // Promo Code Click-to-Copy Handler
  function copyPromoCode(code, btnEl) {
    code = code || 'LUXE20';
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(code).then(function () {
        handleCopySuccess(code, btnEl);
      }).catch(function () {
        fallbackCopyText(code, btnEl);
      });
    } else {
      fallbackCopyText(code, btnEl);
    }
  }

  function fallbackCopyText(text, btnEl) {
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    try {
      document.execCommand('copy');
      handleCopySuccess(text, btnEl);
    } catch (err) {
      if (typeof window.showToast === 'function') {
        window.showToast('Code: ' + text, 'info');
      }
    }
    document.body.removeChild(tempInput);
  }

  function handleCopySuccess(code, btnEl) {
    if (btnEl) {
      const $btn = $(btnEl);
      const originalText = $btn.find('.promo-code-text').text();
      $btn.find('.promo-code-text').text('COPIED!');
      $btn.addClass('border-emerald-500 text-emerald-400');

      setTimeout(function () {
        $btn.find('.promo-code-text').text(originalText);
        $btn.removeClass('border-emerald-500 text-emerald-400');
      }, 2000);
    }

    if (typeof window.showToast === 'function') {
      window.showToast('Privilege code "' + code + '" copied! Use at checkout for discount.', 'success');
    }
  }

  window.copyPromoCode = copyPromoCode;

  $(document).on('click', '.btn-copy-promo-code', function (e) {
    e.preventDefault();
    const code = $(this).data('code') || 'LUXE20';
    copyPromoCode(code, this);
  });

  // Best Sellers Filter Handler
  function filterBestSellers(filter, context) {
    filter = filter ? String(filter).trim().toLowerCase() : 'all';
    const $scope = context ? $(context).closest('#bestsellers, .kinboni-bestsellers-section') : $('#bestsellers, .kinboni-bestsellers-section');
    if (!$scope.length) return;

    $scope.each(function () {
      const $sec = $(this);
      const $btns = $sec.find('.bestseller-filter-btn');
      const $cards = $sec.find('.kinboni-bestseller-card');

      $btns.removeClass('bg-stone-900 text-white dark:bg-gold-400 dark:text-stone-950 border-transparent shadow-sm')
        .addClass('bg-transparent text-stone-700 dark:text-stone-300 border-stone-300 dark:border-stone-700');

      $btns.filter(function () {
        return String($(this).data('filter')).trim().toLowerCase() === filter;
      })
        .addClass('bg-stone-900 text-white dark:bg-gold-400 dark:text-stone-950 border-transparent shadow-sm')
        .removeClass('bg-transparent text-stone-700 dark:text-stone-300 border-stone-300 dark:border-stone-700');

      let visibleCount = 0;

      $cards.each(function () {
        const catAttr = $(this).attr('data-category') || $(this).data('category') || '';
        const catsJson = $(this).attr('data-categories');
        const isSale = $(this).data('is-sale');
        let show = false;

        if (filter === 'all') {
          show = true;
        } else if (filter === 'sale') {
          show = (isSale === true || isSale === 'true');
        } else {
          let list = [];
          if (catsJson) {
            try {
              const parsed = typeof catsJson === 'string' ? JSON.parse(catsJson) : catsJson;
              if (Array.isArray(parsed)) {
                list = parsed.map(c => String(c).trim().toLowerCase());
              }
            } catch (e) {}
          }
          if (list.length === 0 && catAttr) {
            list = String(catAttr).toLowerCase().split(/[,\s]+/);
          }
          show = list.includes(filter);
        }

        if (show) {
          visibleCount++;
          $(this).stop(true, true).fadeIn(250);
        } else {
          $(this).stop(true, true).hide();
        }
      });

      // Handle empty state if no products match this category filter
      let $emptyMsg = $sec.find('.bestseller-empty-filter-msg');
      if (visibleCount === 0) {
        if (!$emptyMsg.length) {
          $emptyMsg = $('<div class="bestseller-empty-filter-msg col-span-full py-16 text-center text-xs text-stone-500 dark:text-stone-400 bg-stone-50/50 dark:bg-stone-900/50 rounded-2xl border border-stone-200/60 dark:border-stone-800">No creations found in this category.</div>');
          const $grid = $sec.find('#bestsellers-grid, .grid');
          if ($grid.length) {
            $grid.append($emptyMsg);
          } else {
            $sec.append($emptyMsg);
          }
        }
        $emptyMsg.show();
      } else {
        if ($emptyMsg.length) {
          $emptyMsg.hide();
        }
      }
    });

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }

  window.ProductRenderer = window.ProductRenderer || {};
  window.ProductRenderer.renderBestSellers = function (filter) {
    filterBestSellers(filter);
  };

  $(document).on('click', '.bestseller-filter-btn', function (e) {
    e.preventDefault();
    const filter = $(this).data('filter');
    filterBestSellers(filter, this);
  });

  // Testimonials Slider Controller
  const TestimonialSliderController = {
    init(scope) {
      if (typeof Swiper === 'undefined') return;
      const $root = scope ? $(scope) : $(document);
      const swiperEls = $root.find('.testimonial-swiper');
      if (!swiperEls.length) return;

      swiperEls.each(function () {
        const swiperEl = this;
        const $container = $(swiperEl).closest('.kinboni-testimonials-section, section');
        const isAutoplay = $container.data('autoplay') !== false && $container.data('autoplay') !== 'false';
        const delay = parseInt($container.data('delay'), 10) || 4500;
        const isLoop = $container.data('loop') !== false && $container.data('loop') !== 'false';
        const speed = parseInt($container.data('speed'), 10) || 750;

        const nextBtn = $container.find('.testimonial-next-btn, #testimonial-next-btn')[0];
        const prevBtn = $container.find('.testimonial-prev-btn, #testimonial-prev-btn')[0];
        const paginationEl = $(swiperEl).find('.swiper-pagination')[0];

        if (swiperEl.swiper) {
          swiperEl.swiper.destroy(true, true);
        }

        new Swiper(swiperEl, {
          slidesPerView: 1,
          spaceBetween: 24,
          loop: isLoop,
          speed: speed,
          grabCursor: true,
          autoplay: isAutoplay ? {
            delay: delay,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
          } : false,
          pagination: paginationEl ? {
            el: paginationEl,
            clickable: true,
          } : false,
          navigation: {
            nextEl: nextBtn,
            prevEl: prevBtn,
          },
          breakpoints: {
            640: {
              slidesPerView: 1.5,
              spaceBetween: 24,
            },
            768: {
              slidesPerView: 2,
              spaceBetween: 28,
            },
            1024: {
              slidesPerView: 3,
              spaceBetween: 32,
            },
          },
          on: {
            init: function () {
              if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
              }
            }
          }
        });
      });
    }
  };

  window.TestimonialSliderController = TestimonialSliderController;

  // Newsletter Form Handler
  $(document).on('submit', '#newsletter-form, .kinboni-newsletter-form', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $input = $form.find('input[type="email"], #newsletter-email');
    const $feedback = $form.find('#newsletter-feedback, .newsletter-feedback');
    const email = $input.val() ? $input.val().trim() : '';
    const honeypot = $form.find('input[name="kinboni_hp"]').val() || '';

    const customError = $form.data('error') || 'Please enter a valid email address.';
    const customSuccess = $form.data('success') || '✨ Welcome to The Kinboni Circle! Your VIP invitation and private 20% privilege code (<strong>LUXE20</strong>) have been delivered.';

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      if ($feedback.length) {
        $feedback.removeClass('hidden').attr('class', 'newsletter-feedback mt-3 text-xs text-rose-500 font-medium block animate-fade-in');
        $feedback.text(customError);
      }
      return;
    }

    const $btn = $form.find('button[type="submit"]');
    $btn.prop('disabled', true);

    $.ajax({
      url: (window.kinboniCoreData && kinboniCoreData.ajax_url) || '/wp-admin/admin-ajax.php',
      type: 'POST',
      data: {
        action: 'kinboni_subscribe_newsletter',
        nonce: (window.kinboniCoreData && kinboniCoreData.nonce) || '',
        email: email,
        kinboni_hp: honeypot
      },
      complete: function () {
        $btn.prop('disabled', false);
      },
      success: function (res) {
        if ($feedback.length) {
          $feedback.removeClass('hidden').attr('class', 'newsletter-feedback mt-3 text-xs text-emerald-500 dark:text-emerald-400 font-semibold block animate-fade-in');
          const msg = (res && res.data && res.data.message) ? res.data.message : customSuccess;
          $feedback.html(msg);
        }
        $input.val('');

        if (window.KinboniCore && typeof window.KinboniCore.showToast === 'function') {
          window.KinboniCore.showToast('Subscribed to Kinboni VIP Archive', 'success');
        } else if (typeof window.showToast === 'function') {
          window.showToast('Subscribed to Kinboni VIP Archive', 'success');
        }
      },
      error: function () {
        if ($feedback.length) {
          $feedback.removeClass('hidden').attr('class', 'newsletter-feedback mt-3 text-xs text-rose-500 font-medium block animate-fade-in');
          $feedback.text('Subscription request could not be processed. Please try again.');
        }
      }
    });
  });

  // FAQ Accordion Handler
  $(document).on('click', '.kinboni-faq-btn', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const $item = $btn.closest('.kinboni-faq-item');
    const $section = $btn.closest('.kinboni-faq-section');
    const $accordion = $item.closest('.kinboni-faq-accordion');
    const $answer = $item.find('.kinboni-faq-answer');
    const behavior = $section.data('behavior') || 'single';
    const isCurrentlyActive = $item.hasClass('is-active');

    if (behavior === 'single') {
      $accordion.find('.kinboni-faq-item').each(function () {
        const $other = $(this);
        if (!$other.is($item) && $other.hasClass('is-active')) {
          $other.removeClass('is-active border-gold-500 shadow-md').addClass('border-stone-200/80 dark:border-stone-800');
          $other.find('.kinboni-faq-btn').attr('aria-expanded', 'false');
          $other.find('.kinboni-faq-answer').slideUp(200, function () {
            $(this).addClass('hidden').removeAttr('style');
          });
          $other.find('.kinboni-faq-icon-box').html('<i data-lucide="plus" class="w-4 h-4"></i>');
        }
      });
    }

    if (isCurrentlyActive) {
      $item.removeClass('is-active border-gold-500 shadow-md').addClass('border-stone-200/80 dark:border-stone-800');
      $btn.attr('aria-expanded', 'false');
      $answer.slideUp(200, function () {
        $(this).addClass('hidden').removeAttr('style');
      });
      $item.find('.kinboni-faq-icon-box').html('<i data-lucide="plus" class="w-4 h-4"></i>');
    } else {
      $item.addClass('is-active border-gold-500 shadow-md').removeClass('border-stone-200/80 dark:border-stone-800');
      $btn.attr('aria-expanded', 'true');
      $answer.removeClass('hidden').hide().slideDown(200);
      $item.find('.kinboni-faq-icon-box').html('<i data-lucide="minus" class="w-4 h-4"></i>');
    }

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  });

  // Flash Deals Countdown Controller
  const FlashCountdownController = {
    intervals: [],

    init(scope) {
      const $root = scope ? $(scope) : $(document);
      const $sections = $root.find('.kinboni-flash-deals-section');
      if (!$sections.length && $root.hasClass('kinboni-flash-deals-section')) {
        this.startTimer($root);
        return;
      }

      $sections.each((i, el) => {
        this.startTimer($(el));
      });
    },

    startTimer($section) {
      const type = $section.data('countdown-type') || 'evergreen';
      const targetDateStr = $section.data('target-date');
      let totalSeconds = parseInt($section.data('evergreen-seconds'), 10) || (2 * 86400 + 14 * 3600 + 38 * 60 + 45);

      if (type === 'target_date' && targetDateStr) {
        const targetMs = new Date(targetDateStr).getTime();
        totalSeconds = Math.max(0, Math.floor((targetMs - Date.now()) / 1000));
      } else {
        const storageKey = 'kinboni_evergreen_end_' + ($section.attr('id') || 'default');
        const savedEnd = localStorage.getItem(storageKey);
        const now = Date.now();
        if (savedEnd && parseInt(savedEnd, 10) > now) {
          totalSeconds = Math.floor((parseInt(savedEnd, 10) - now) / 1000);
        } else {
          localStorage.setItem(storageKey, (now + totalSeconds * 1000).toString());
        }
      }

      const updateDisplay = () => {
        if (totalSeconds <= 0) {
          totalSeconds = 0;
        }

        const d = Math.floor(totalSeconds / 86400);
        const h = Math.floor((totalSeconds % 86400) / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;

        $section.find('.flash-cd-days').text(String(d).padStart(2, '0'));
        $section.find('.flash-cd-hours').text(String(h).padStart(2, '0'));
        $section.find('.flash-cd-mins').text(String(m).padStart(2, '0'));
        $section.find('.flash-cd-secs').text(String(s).padStart(2, '0'));
      };

      updateDisplay();

      const timerId = setInterval(() => {
        if (totalSeconds > 0) {
          totalSeconds--;
          updateDisplay();
        }
      }, 1000);

      this.intervals.push(timerId);
    }
  };

  window.FlashCountdownController = FlashCountdownController;

  // Promo Code Actions
  $(document).on('click', '.btn-copy-promo-code', function (e) {
    e.preventDefault();
    const code = $(this).data('code') || 'LUXE20';
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(code).then(() => {
        if (window.KinboniCore && typeof window.KinboniCore.showToast === 'function') {
          window.KinboniCore.showToast(`Privilege code "${code}" copied! Use at checkout for discount.`, 'success');
        } else if (typeof window.showToast === 'function') {
          window.showToast(`Privilege code "${code}" copied! Use at checkout for discount.`, 'success');
        }
      }).catch(() => {
        if (typeof window.showToast === 'function') window.showToast(`Code: ${code}`, 'info');
      });
    } else {
      if (typeof window.showToast === 'function') window.showToast(`Code: ${code}`, 'info');
    }
  });

  $(document).on('click', '.btn-apply-promo-voucher', function (e) {
    e.preventDefault();
    const code = $(this).data('code') || 'LUXE20';
    sessionStorage.setItem('kinboni_applied_promo', code);
    if (window.KinboniCore && typeof window.KinboniCore.showToast === 'function') {
      window.KinboniCore.showToast(`20% Voucher (${code}) auto-applied to your Bag!`, 'success');
    } else if (typeof window.showToast === 'function') {
      window.showToast(`20% Voucher (${code}) auto-applied to your Bag!`, 'success');
    }
  });

  // Size Guide Modal Controller
  const SizeGuideController = {
    open(modalId) {
      const id = modalId || 'size-guide-modal';
      const m = document.getElementById(id);
      if (m) {
        m.classList.remove('hidden');
        m.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      }
    },
    close(modalId) {
      const id = modalId || 'size-guide-modal';
      const m = document.getElementById(id);
      if (m) {
        m.classList.add('hidden');
        m.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
      }
    }
  };

  window.SizeGuideController = SizeGuideController;

  $(document).on('click', '.btn-open-size-guide, [data-open-modal="size-guide"]', function (e) {
    e.preventDefault();
    const target = $(this).data('modal-target') || 'size-guide-modal';
    SizeGuideController.open(target);
  });

  $(document).on('click', '.btn-close-size-guide, .size-guide-backdrop', function (e) {
    e.preventDefault();
    const $modal = $(this).closest('.kinboni-size-guide-modal');
    const target = $modal.attr('id') || 'size-guide-modal';
    SizeGuideController.close(target);
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') {
      $('.kinboni-size-guide-modal:not(.hidden)').each(function () {
        SizeGuideController.close($(this).attr('id'));
      });
    }
  });

  // =========================================================================
  // SINGLE PRODUCT ATELIER SHOWCASE CONTROLLER
  // =========================================================================
  const SingleProductController = {
    quantity: 1,
    monogramText: '',
    monogramFont: 'serif',

    init(root) {
      const scope = root || document;

      // Track visited product into recently viewed localStorage
      const productMain = scope.querySelector ? scope.querySelector('#page-view-product') : document.getElementById('page-view-product');
      if (productMain) {
        const pid = productMain.getAttribute('data-product-id');
        if (pid) {
          try {
            let recent = JSON.parse(localStorage.getItem('kinboni_recently_viewed') || '[]');
            recent = [pid, ...recent.filter(id => String(id) !== String(pid))].slice(0, 8);
            localStorage.setItem('kinboni_recently_viewed', JSON.stringify(recent));
          } catch (e) {}
        }
      }

      // Review submission handler
      const reviewForm = scope.querySelector ? scope.querySelector('#sp-review-form') : document.getElementById('sp-review-form');
      if (reviewForm && !reviewForm.dataset.listenerAttached) {
        reviewForm.dataset.listenerAttached = 'true';
        reviewForm.addEventListener('submit', (e) => {
          e.preventDefault();
          this.submitReview(reviewForm);
        });
      }
    },

    switchTab(tabName) {
      document.querySelectorAll('.sp-tab-btn').forEach(btn => {
        const t = btn.getAttribute('data-sp-tab');
        if (t === tabName) {
          btn.className = 'sp-tab-btn px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-widest transition-all bg-stone-900 text-white dark:bg-gold-400 dark:text-stone-950 cursor-pointer';
        } else {
          btn.className = 'sp-tab-btn px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-widest transition-all text-stone-600 dark:text-stone-400 hover:text-gold-600 cursor-pointer';
        }
      });

      ['description', 'specs', 'reviews'].forEach(t => {
        const panel = document.getElementById(`sp-tab-panel-${t}`);
        if (panel) panel.classList.toggle('hidden', t !== tabName);
      });
      if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
      }
      if (window.Kinboni && typeof window.Kinboni.syncWishlistButtons === 'function') {
        window.Kinboni.syncWishlistButtons();
      }
    },

    switchMainImage(url, btnEl) {
      const img = document.getElementById('sp-main-gallery-image');
      if (img && url) {
        img.src = url;
      }
      document.querySelectorAll('.sp-gallery-thumb').forEach(b => {
        b.classList.remove('border-gold-500', 'ring-2', 'ring-gold-500/30');
        b.classList.add('border-stone-200', 'dark:border-stone-800', 'opacity-70');
      });
      if (btnEl) {
        btnEl.classList.add('border-gold-500', 'ring-2', 'ring-gold-500/30');
        btnEl.classList.remove('border-stone-200', 'dark:border-stone-800', 'opacity-70');
      }
    },

    changeQty(delta) {
      this.quantity = Math.max(1, this.quantity + delta);
      const display = document.getElementById('sp-qty-display');
      if (display) display.textContent = this.quantity;

      const cartBtn = document.getElementById('sp-add-to-cart-btn');
      if (cartBtn) cartBtn.setAttribute('data-quantity', this.quantity);

      const quickBtn = document.getElementById('sp-quick-order-btn');
      if (quickBtn) quickBtn.setAttribute('data-product-quantity', this.quantity);
    },

    updateMonogram(val) {
      this.monogramText = (val || '').toUpperCase().trim();
      const prev = document.getElementById('sp-monogram-preview');
      if (prev) {
        prev.textContent = this.monogramText || 'K • A';
      }
    },

    updateMonogramFont(style) {
      this.monogramFont = style;
      const prev = document.getElementById('sp-monogram-preview');
      if (prev) {
        prev.className = style === 'italic'
          ? 'font-serif italic text-sm font-bold tracking-[0.2em] text-gold-400'
          : 'font-serif text-sm font-bold tracking-[0.25em] text-gold-400';
      }
    },

    submitReview(form) {
      const productId = form.getAttribute('data-product-id');
      const author = $('#sp-review-author').val() ? $('#sp-review-author').val().trim() : '';
      const city = $('#sp-review-city').val() ? $('#sp-review-city').val().trim() : '';
      const rating = $('#sp-review-rating').val() ? parseInt($('#sp-review-rating').val(), 10) : 5;
      const comment = $('#sp-review-comment').val() ? $('#sp-review-comment').val().trim() : '';
      const submitBtn = $('#sp-review-submit-btn');

      if (!author || !comment) {
        Kinboni.showToast('Please enter your name and review impressions.', 'error');
        return;
      }

      submitBtn.prop('disabled', true).addClass('opacity-60');

      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_submit_review',
          nonce: kinboniCoreData.nonce,
          product_id: productId,
          author: author,
          city: city,
          rating: rating,
          comment: comment,
          kinboni_hp: $(form).find('input[name="kinboni_hp"]').val() || ''
        },
        success: (res) => {
          submitBtn.prop('disabled', false).removeClass('opacity-60');
          if (res.success) {
            Kinboni.showToast(res.data.message || 'Your review has been recorded!', 'success');
            form.reset();

            // Prepend new review card if approved
            const r = res.data.review;
            if (r && (r.approved === 1 || r.approved === undefined)) {
              // Hide empty message if present
              $('#sp-no-reviews-msg').remove();

              let starsHtml = '';
              for (let i = 1; i <= 5; i++) {
                const active = i <= r.rating;
                starsHtml += `<svg class="w-3.5 h-3.5 ${active ? 'text-amber-400 fill-amber-400' : 'text-stone-300 dark:text-stone-700'}" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>`;
              }

              const reviewHtml = `
                <div class="p-4 rounded-2xl bg-stone-50/80 dark:bg-stone-950/80 border border-stone-200/80 dark:border-stone-800 space-y-2">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <span class="font-serif font-semibold text-sm text-stone-900 dark:text-white">${Kinboni.esc(r.author)}</span>
                      <span class="px-2 py-0.5 rounded-full bg-gold-500/15 text-gold-700 dark:text-gold-400 text-[10px] font-bold uppercase tracking-wider">${Kinboni.esc(r.city)}</span>
                    </div>
                    <span class="text-[11px] text-stone-400">${Kinboni.esc(r.date)}</span>
                  </div>
                  <div class="flex items-center gap-1">${starsHtml}</div>
                  <p class="text-xs text-stone-600 dark:text-stone-300 leading-relaxed">${Kinboni.esc(r.comment)}</p>
                </div>
              `;
              $('#sp-reviews-list').prepend(reviewHtml);
            }
          } else {
            Kinboni.showToast(res.data.message || 'Error recording review.', 'error');
          }
        },
        error: () => {
          submitBtn.prop('disabled', false).removeClass('opacity-60');
          Kinboni.showToast('Network error submitting review.', 'error');
        }
      });
    }
  };

  window.SingleProductController = SingleProductController;
  window.KinboniSingleProduct = SingleProductController;

  // Single Product Add to Bag AJAX Click Handler
  $(document).on('click', '#sp-add-to-cart-btn', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const productId = $btn.data('product_id') || $btn.attr('data-product_id');
    const quantity = SingleProductController.quantity || 1;
    const monogram = SingleProductController.monogramText || '';

    $btn.addClass('opacity-75');

    $.ajax({
      url: kinboniCoreData.ajax_url,
      type: 'POST',
      data: {
        action: 'kinboni_add_to_cart',
        nonce: kinboniCoreData.nonce,
        product_id: productId,
        quantity: quantity,
        monogram: monogram
      },
      success: (res) => {
        $btn.removeClass('opacity-75');
        if (res && res.success) {
          Kinboni.trackAnalyticsEvent('AddToCart', {
            content_ids: [String(productId)],
            content_type: 'product',
            quantity: quantity,
            currency: (window.kinboniCoreData && kinboniCoreData.currency_code) || 'BDT'
          });
          Kinboni.showToast(res.data && res.data.message ? res.data.message : 'Added to your shopping bag!', 'success');
          $(document.body).trigger('wc_fragment_refresh');
          Kinboni.openCart();
        } else {
          $.ajax({
            url: '?wc-ajax=add_to_cart',
            type: 'POST',
            data: { product_id: productId, quantity: quantity },
            success: () => {
              Kinboni.showToast('Added to your shopping bag!', 'success');
              $(document.body).trigger('wc_fragment_refresh');
              Kinboni.openCart();
            }
          });
        }
      },
      error: () => {
        $btn.removeClass('opacity-75');
        $.ajax({
          url: '?wc-ajax=add_to_cart',
          type: 'POST',
          data: { product_id: productId, quantity: quantity },
          success: () => {
            Kinboni.showToast('Added to your shopping bag!', 'success');
            $(document.body).trigger('wc_fragment_refresh');
            Kinboni.openCart();
          }
        });
      }
    });
  });

  // =========================================================================
  // SHOP CATALOGUE & ARCHIVE CONTROLLER
  // =========================================================================
  const ShopPageController = {
    products: [],
    categories: [],
    category: 'all',
    maxPrice: 30000,
    searchQuery: '',
    sortBy: 'menu_order',
    onlySale: false,
    onlyBestSeller: false,
    viewMode: 'grid',
    currentPage: 1,
    perPage: 12,

    init(root) {
      const scope = root || document;
      const shopMain = scope.querySelector ? scope.querySelector('#page-view-shop') : document.getElementById('page-view-shop');
      if (!shopMain) return;

      // Extract JSON data
      const prodDataEl = scope.querySelector ? scope.querySelector('#kinboni-shop-products-data') : document.getElementById('kinboni-shop-products-data');
      const catDataEl = scope.querySelector ? scope.querySelector('#kinboni-shop-categories-data') : document.getElementById('kinboni-shop-categories-data');

      if (prodDataEl) {
        try {
          this.products = JSON.parse(prodDataEl.textContent.trim()) || [];
        } catch (e) {
          this.products = [];
        }
      }

      if (catDataEl) {
        try {
          this.categories = JSON.parse(catDataEl.textContent.trim()) || [];
        } catch (e) {
          this.categories = [];
        }
      }

      if (shopMain.dataset.perPage) {
        this.perPage = parseInt(shopMain.dataset.perPage, 10) || 12;
      }
      if (shopMain.dataset.defaultView) {
        this.viewMode = shopMain.dataset.defaultView;
      }

      // Calculate max price from products
      if (this.products.length > 0) {
        const highestPrice = Math.max(...this.products.map(p => p.price || 0));
        this.maxPrice = Math.max(highestPrice, 30000);
      }

      // URL parameters check
      try {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('category')) {
          this.category = urlParams.get('category');
        }
        if (urlParams.get('s')) {
          this.searchQuery = urlParams.get('s');
          const searchInput = document.getElementById('shop-search-input');
          if (searchInput) searchInput.value = this.searchQuery;
        }
        if (urlParams.get('max_price')) {
          this.maxPrice = parseInt(urlParams.get('max_price'), 10) || this.maxPrice;
        }
      } catch (e) {}

      // Binds
      const searchInput = document.getElementById('shop-search-input');
      if (searchInput && !searchInput.dataset.listenerAttached) {
        searchInput.dataset.listenerAttached = 'true';
        searchInput.addEventListener('input', (e) => {
          this.searchQuery = e.target.value.trim();
          this.currentPage = 1;
          const clearBtn = document.getElementById('shop-search-clear-btn');
          if (clearBtn) clearBtn.classList.toggle('hidden', !this.searchQuery);
          this.render();
        });
      }

      const priceRange = document.getElementById('shop-price-range');
      if (priceRange && !priceRange.dataset.listenerAttached) {
        priceRange.dataset.listenerAttached = 'true';
        priceRange.addEventListener('input', (e) => {
          this.maxPrice = parseInt(e.target.value, 10) || 30000;
          this.currentPage = 1;
          const disp = document.getElementById('shop-price-display');
          if (disp) disp.textContent = 'Up to ৳' + Number(this.maxPrice).toLocaleString();
          this.render();
        });
      }

      const sortSelect = document.getElementById('shop-sort-select');
      if (sortSelect && !sortSelect.dataset.listenerAttached) {
        sortSelect.dataset.listenerAttached = 'true';
        sortSelect.addEventListener('change', (e) => {
          this.sortBy = e.target.value;
          this.render();
        });
      }

      const onSaleCheckbox = document.getElementById('shop-filter-onsale');
      if (onSaleCheckbox && !onSaleCheckbox.dataset.listenerAttached) {
        onSaleCheckbox.dataset.listenerAttached = 'true';
        onSaleCheckbox.addEventListener('change', (e) => {
          this.onlySale = e.target.checked;
          this.currentPage = 1;
          this.render();
        });
      }

      const bestSellerCheckbox = document.getElementById('shop-filter-bestseller');
      if (bestSellerCheckbox && !bestSellerCheckbox.dataset.listenerAttached) {
        bestSellerCheckbox.dataset.listenerAttached = 'true';
        bestSellerCheckbox.addEventListener('change', (e) => {
          this.onlyBestSeller = e.target.checked;
          this.currentPage = 1;
          this.render();
        });
      }

      this.render();
    },

    clearSearch() {
      this.searchQuery = '';
      const searchInput = document.getElementById('shop-search-input');
      if (searchInput) searchInput.value = '';
      const clearBtn = document.getElementById('shop-search-clear-btn');
      if (clearBtn) clearBtn.classList.add('hidden');
      this.render();
    },

    setCategory(cat) {
      this.category = cat || 'all';
      this.currentPage = 1;
      this.render();
    },

    setPerPage(count) {
      this.perPage = Number(count) || 12;
      this.currentPage = 1;
      this.render();
    },

    setViewMode(mode) {
      this.viewMode = mode;
      const gridBtn = document.getElementById('shop-view-grid-btn');
      const listBtn = document.getElementById('shop-view-list-btn');
      if (gridBtn && listBtn) {
        if (mode === 'grid') {
          gridBtn.className = 'p-2 bg-gold-400 text-stone-950 transition-colors cursor-pointer';
          listBtn.className = 'p-2 text-stone-500 hover:text-stone-900 dark:hover:text-white transition-colors cursor-pointer';
        } else {
          listBtn.className = 'p-2 bg-gold-400 text-stone-950 transition-colors cursor-pointer';
          gridBtn.className = 'p-2 text-stone-500 hover:text-stone-900 dark:hover:text-white transition-colors cursor-pointer';
        }
      }
      this.render();
    },

    resetFilters() {
      this.category = 'all';
      if (this.products.length > 0) {
        const highestPrice = Math.max(...this.products.map(p => p.price || 0));
        this.maxPrice = Math.max(highestPrice, 30000);
      } else {
        this.maxPrice = 30000;
      }
      this.searchQuery = '';
      this.sortBy = 'menu_order';
      this.onlySale = false;
      this.onlyBestSeller = false;
      this.currentPage = 1;

      const searchInput = document.getElementById('shop-search-input');
      if (searchInput) searchInput.value = '';
      const clearBtn = document.getElementById('shop-search-clear-btn');
      if (clearBtn) clearBtn.classList.add('hidden');
      const priceRange = document.getElementById('shop-price-range');
      if (priceRange) priceRange.value = this.maxPrice;
      const disp = document.getElementById('shop-price-display');
      if (disp) disp.textContent = 'Up to ৳' + Number(this.maxPrice).toLocaleString();
      const sortSelect = document.getElementById('shop-sort-select');
      if (sortSelect) sortSelect.value = 'menu_order';
      const onSaleCheckbox = document.getElementById('shop-filter-onsale');
      if (onSaleCheckbox) onSaleCheckbox.checked = false;
      const bestSellerCheckbox = document.getElementById('shop-filter-bestseller');
      if (bestSellerCheckbox) bestSellerCheckbox.checked = false;

      this.render();
      Kinboni.showToast('Shop filters reset to default.', 'info');
    },

    createGridCard(p) {
      const isWishlisted = Kinboni.state && Kinboni.state.wishlist ? Kinboni.state.wishlist.includes(p.id) : false;
      const discountPct = (p.originalPrice && p.originalPrice > p.price) ? Math.round(((p.originalPrice - p.price) / p.originalPrice) * 100) : 0;

      return `
        <div class="kinboni-product-card group relative flex flex-col bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-luxury dark:hover:shadow-luxury-dark hover:border-gold-500/40 dark:hover:border-gold-500/40 transition-all duration-500 cursor-pointer" data-product-id="${p.id}">
          <div class="relative w-full aspect-[4/5] bg-stone-100 dark:bg-stone-950 overflow-hidden cursor-pointer" onclick="if (!event.target.closest('button, a')) window.location.href='${p.link}'">
            <img src="${p.image}" alt="${Kinboni.esc(p.name)}" loading="lazy" class="w-full h-full object-cover object-center transition-all duration-700 ease-out group-hover:scale-105" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22500%22 viewBox=%220 0 400 500%22%3E%3Crect width=%22400%22 height=%22500%22 fill=%22%23e7e5e4%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a8a29e%22 font-size=%2218%22%3ENo Image%3C/text%3E%3C/svg%3E';this.onerror=null;" />
            ${p.hoverImage && p.hoverImage !== p.image ? `<img src="${p.hoverImage}" alt="${Kinboni.esc(p.name)} alternate angle" loading="lazy" class="absolute inset-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-700 ease-out group-hover:opacity-100 group-hover:scale-105 pointer-events-none" />` : ''}

            ${(p.badge || discountPct > 0) ? `
            <div class="absolute top-3 left-3 right-20 flex flex-row flex-wrap items-center gap-1.5 z-10 pointer-events-none">
              ${p.badge ? `<span class="w-fit flex-shrink-0 inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] rounded-full bg-stone-950/90 text-stone-100 dark:bg-white/95 dark:text-stone-950 shadow-md backdrop-blur-sm border border-white/10 dark:border-stone-900/10">${Kinboni.esc(p.badge)}</span>` : ''}
              ${discountPct > 0 ? `<span class="w-fit flex-shrink-0 inline-flex items-center px-2 py-0.5 text-[10px] font-bold tracking-wider rounded-full bg-rose-600 text-white shadow-sm">-${discountPct}%</span>` : ''}
            </div>` : ''}

            <div class="absolute top-3 right-3 z-10 flex flex-col gap-2">
              <button type="button" class="btn-toggle-wishlist w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-stone-700 dark:text-stone-200 hover:text-rose-500 hover:border-rose-400 dark:hover:border-rose-400 hover:scale-110 active:scale-90 transition-all shadow-md group/heart cursor-pointer ${isWishlisted ? 'active text-rose-500' : ''}" data-product-id="${p.id}" aria-label="Save to Wishlist">
                <svg class="w-4 h-4 transition-transform group-hover/heart:scale-110 ${isWishlisted ? 'fill-rose-500 text-rose-500 stroke-rose-500' : ''}" viewBox="0 0 24 24" fill="${isWishlisted ? '#f43f5e' : 'none'}" stroke="${isWishlisted ? '#f43f5e' : 'currentColor'}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
              <button type="button" class="btn-open-quickview w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-stone-700 dark:text-stone-200 hover:bg-gold-400 hover:text-stone-950 hover:border-gold-400 dark:hover:bg-gold-400 dark:hover:text-stone-950 hover:scale-110 active:scale-90 transition-all shadow-md cursor-pointer" data-product-id="${p.id}" aria-label="Quick View">
                <i data-lucide="eye" class="w-4 h-4"></i>
              </button>
            </div>
          </div>

          <div class="p-4 sm:p-5 flex flex-col flex-grow">
            <div class="flex items-center justify-between gap-2 mb-2 min-w-0">
              <span class="text-xs uppercase tracking-wider font-bold text-gold-700 dark:text-gold-400 truncate">${Kinboni.esc(p.categoryLabel)}</span>
              ${p.rating > 0 ? `
              <div class="flex items-center gap-1 text-[11px] font-semibold text-stone-700 dark:text-stone-300 flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span>${Number(p.rating).toFixed(1)}</span>
                <span class="text-stone-400 dark:text-stone-500 font-normal">(${p.reviewsCount})</span>
              </div>` : ''}
            </div>

            <h3 class="kinboni-product-card-title font-serif text-xl sm:text-2xl font-bold text-stone-900 dark:text-white leading-snug truncate mb-2 group-hover:text-gold-600 dark:group-hover:text-gold-400 transition-colors">
              <a href="${p.link}">${Kinboni.esc(p.name)}</a>
            </h3>

            <div class="flex items-baseline gap-2 mb-3">
              <span class="font-serif text-xl sm:text-2xl font-bold text-stone-950 dark:text-white">৳${Number(p.price).toLocaleString()}</span>
              ${p.originalPrice > p.price ? `
              <span class="text-xs text-stone-400 dark:text-stone-500 line-through font-normal">৳${Number(p.originalPrice).toLocaleString()}</span>
              <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 ml-auto">Save ৳${Number(p.originalPrice - p.price).toLocaleString()}</span>` : ''}
            </div>

            <div class="flex items-center gap-2 mt-auto pt-3 border-t border-stone-100 dark:border-stone-800/80">
              <a href="${p.add_to_cart_url || p.link}" data-quantity="1" data-product_id="${p.id}" class="ajax_add_to_cart add_to_cart_button flex-1 py-2.5 px-3 rounded-xl bg-stone-200/80 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-stone-900 dark:text-stone-100 text-xs sm:text-[13px] font-bold transition-all duration-200 flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 flex-shrink-0 text-stone-600 dark:text-stone-300"></i>
                <span class="whitespace-nowrap">Add to Bag</span>
              </a>

              <button type="button" class="btn-trigger-quick-order flex-1 py-2.5 px-3 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs sm:text-[13px] font-bold transition-all duration-200 flex items-center justify-center gap-1.5 shadow-sm hover:shadow-gold-500/25 active:scale-95 cursor-pointer" data-product-id="${p.id}" data-product-name="${Kinboni.esc(p.name)}" data-product-price="${p.price}" data-product-image="${p.image}">
                <i data-lucide="zap" class="w-3.5 h-3.5 fill-stone-950 flex-shrink-0"></i>
                <span class="whitespace-nowrap">Order Now</span>
              </button>
            </div>
          </div>
        </div>
      `;
    },

    createListCard(p) {
      const isWishlisted = Kinboni.state && Kinboni.state.wishlist ? Kinboni.state.wishlist.includes(p.id) : false;
      const specsHtml = p.specs && p.specs.length > 0
        ? p.specs.map(s => `<span class="px-2.5 py-1 rounded-lg bg-stone-100 dark:bg-stone-800 text-xs text-stone-600 dark:text-stone-300">${Kinboni.esc(s)}</span>`).join('')
        : '';

      return `
        <div class="kinboni-product-list-card group flex flex-col sm:flex-row bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 rounded-2xl overflow-hidden shadow-sm hover:border-gold-500/40 transition-all duration-300 cursor-pointer" data-product-id="${p.id}" onclick="if (!event.target.closest('button, a')) window.location.href='${p.link}'">
          <div class="relative w-full sm:w-60 aspect-[4/5] sm:aspect-auto bg-stone-100 dark:bg-stone-950 flex-shrink-0 overflow-hidden cursor-pointer">
            <img src="${p.image}" alt="${Kinboni.esc(p.name)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22500%22 viewBox=%220 0 400 500%22%3E%3Crect width=%22400%22 height=%22500%22 fill=%22%23e7e5e4%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a8a29e%22 font-size=%2218%22%3ENo Image%3C/text%3E%3C/svg%3E';this.onerror=null;" />
            ${p.badge ? `<span class="absolute top-3 left-3 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full bg-stone-950/90 text-white">${Kinboni.esc(p.badge)}</span>` : ''}
            <div class="absolute top-3 right-3 z-10 flex flex-col gap-2">
              <button type="button" class="btn-toggle-wishlist w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-stone-700 dark:text-stone-200 hover:text-rose-500 hover:border-rose-400 dark:hover:border-rose-400 hover:scale-110 active:scale-90 transition-all shadow-md group/heart cursor-pointer ${isWishlisted ? 'active text-rose-500' : ''}" data-product-id="${p.id}" aria-label="Save to Wishlist">
                <svg class="w-4 h-4 transition-transform group-hover/heart:scale-110 ${isWishlisted ? 'fill-rose-500 text-rose-500 stroke-rose-500' : ''}" viewBox="0 0 24 24" fill="${isWishlisted ? '#f43f5e' : 'none'}" stroke="${isWishlisted ? '#f43f5e' : 'currentColor'}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
              <button type="button" class="btn-open-quickview w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-stone-700 dark:text-stone-200 hover:bg-gold-400 hover:text-stone-950 transition-all shadow-md cursor-pointer" data-product-id="${p.id}">
                <i data-lucide="eye" class="w-4 h-4"></i>
              </button>
            </div>
          </div>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
              <button type="button" class="btn-open-quickview w-9 h-9 rounded-full bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border border-stone-200/80 dark:border-stone-700/80 flex items-center justify-center text-stone-700 dark:text-stone-200 hover:bg-gold-400 hover:text-stone-950 transition-all shadow-md cursor-pointer" data-product-id="${p.id}">
                <i data-lucide="eye" class="w-4 h-4"></i>
              </button>
            </div>
          </div>
          <div class="p-5 sm:p-6 flex flex-col justify-between flex-grow">
            <div>
              <div class="flex items-center justify-between gap-2 mb-1.5">
                <span class="text-[11px] uppercase tracking-wider font-bold text-gold-700 dark:text-gold-400">${Kinboni.esc(p.categoryLabel)}</span>
                ${p.rating > 0 ? `
                <div class="flex items-center gap-1 text-[11px] font-semibold text-stone-700 dark:text-stone-300 flex-shrink-0">
                  <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                  <span>${Number(p.rating).toFixed(1)}</span>
                  <span class="text-stone-400 dark:text-stone-500 font-normal">(${p.reviewsCount})</span>
                </div>` : ''}
              </div>
              <h3 class="font-serif text-xl sm:text-2xl font-medium text-stone-900 dark:text-white mb-2 group-hover:text-gold-600 transition-colors">
                <a href="${p.link}">${Kinboni.esc(p.name)}</a>
              </h3>
              <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed mb-4 font-normal line-clamp-2">${Kinboni.esc(p.description)}</p>
              ${specsHtml ? `<div class="flex flex-wrap gap-1.5 mb-4">${specsHtml}</div>` : ''}
            </div>
            <div class="pt-4 border-t border-stone-200/70 dark:border-stone-800 flex flex-wrap items-center justify-between gap-4">
              <div class="flex items-baseline gap-2">
                <span class="font-serif text-xl font-bold text-stone-900 dark:text-white">৳${Number(p.price).toLocaleString()}</span>
                ${p.originalPrice > p.price ? `<span class="text-xs text-stone-400 line-through">৳${Number(p.originalPrice).toLocaleString()}</span>` : ''}
              </div>
              <div class="flex items-center gap-2">
                <a href="${p.add_to_cart_url || p.link}" data-quantity="1" data-product_id="${p.id}" class="ajax_add_to_cart add_to_cart_button px-4 py-2.5 rounded-xl bg-stone-900 text-white dark:bg-stone-800 text-xs font-semibold hover:bg-gold-500 hover:text-stone-950 transition-colors flex items-center gap-1.5 cursor-pointer" onclick="event.stopPropagation();">
                  <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                  <span>Add to Bag</span>
                </a>
                <button type="button" class="btn-trigger-quick-order px-4 py-2.5 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer" data-product-id="${p.id}" data-product-name="${Kinboni.esc(p.name)}" data-product-price="${p.price}" data-product-image="${p.image}" onclick="event.stopPropagation();">
                  <i data-lucide="zap" class="w-3.5 h-3.5 fill-stone-950"></i>
                  <span>Order Now</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
    },

    renderActiveFilterChips() {
      const activeEl = document.getElementById('shop-active-filters');
      if (!activeEl) return;

      const chips = [];
      if (this.category !== 'all') {
        const cObj = this.categories.find(c => c.id === this.category);
        const cLabel = cObj ? cObj.label : this.category;
        chips.push(`<button type="button" onclick="ShopPageController.setCategory('all')" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gold-400/20 border border-gold-500/40 text-xs font-semibold text-stone-900 dark:text-gold-300 cursor-pointer"><span>Category: ${cLabel}</span><i data-lucide="x" class="w-3 h-3"></i></button>`);
      }
      if (this.maxPrice < 30000) {
        chips.push(`<button type="button" onclick="document.getElementById('shop-price-range').value='30000'; ShopPageController.maxPrice=30000; document.getElementById('shop-price-display').textContent='Up to ৳'+Number(30000).toLocaleString(); ShopPageController.render();" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gold-400/20 border border-gold-500/40 text-xs font-semibold text-stone-900 dark:text-gold-300 cursor-pointer"><span>Max: ৳${Number(this.maxPrice).toLocaleString()}</span><i data-lucide="x" class="w-3 h-3"></i></button>`);
      }
      if (this.onlySale) {
        chips.push(`<button type="button" onclick="document.getElementById('shop-filter-onsale').checked=false; ShopPageController.onlySale=false; ShopPageController.render();" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/15 border border-rose-500/30 text-xs font-semibold text-rose-600 dark:text-rose-300 cursor-pointer"><span>On Private Sale</span><i data-lucide="x" class="w-3 h-3"></i></button>`);
      }
      if (this.onlyBestSeller) {
        chips.push(`<button type="button" onclick="document.getElementById('shop-filter-bestseller').checked=false; ShopPageController.onlyBestSeller=false; ShopPageController.render();" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gold-400/20 border border-gold-500/40 text-xs font-semibold text-stone-900 dark:text-gold-300 cursor-pointer"><span>Best Sellers Only</span><i data-lucide="x" class="w-3 h-3"></i></button>`);
      }
      if (this.searchQuery) {
        chips.push(`<button type="button" onclick="ShopPageController.clearSearch()" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-200 dark:bg-stone-800 text-xs font-semibold text-stone-800 dark:text-stone-200 cursor-pointer"><span>Search: "${this.searchQuery}"</span><i data-lucide="x" class="w-3 h-3"></i></button>`);
      }

      if (chips.length > 0) {
        activeEl.className = 'flex items-center flex-wrap gap-2 py-1';
        activeEl.innerHTML = `<span class="text-[11px] font-bold uppercase tracking-wider text-stone-400">Active Filters:</span>` + chips.join('') + `<button type="button" onclick="ShopPageController.resetFilters()" class="text-xs font-bold text-rose-500 hover:underline ml-2 cursor-pointer">Clear All</button>`;
      } else {
        activeEl.className = 'hidden';
        activeEl.innerHTML = '';
      }
    },

    render() {
      this.renderActiveFilterChips();
      const container = document.getElementById('shop-products-container');
      const resultCountEl = document.getElementById('shop-result-count');
      const paginationEl = document.getElementById('shop-pagination');
      if (!container) return;

      // Update sidebar category active state
      document.querySelectorAll('#shop-category-list button').forEach(btn => {
        const catId = btn.getAttribute('data-cat-id');
        const countSpan = btn.querySelector('span:last-child');
        if (catId === this.category) {
          btn.className = 'w-full flex items-center justify-between px-3 py-2 rounded-xl transition-all bg-gold-400 text-stone-950 font-bold shadow-sm cursor-pointer';
          if (countSpan) countSpan.className = 'text-xs px-2 py-0.5 rounded-full bg-stone-950 text-gold-400';
        } else {
          btn.className = 'w-full flex items-center justify-between px-3 py-2 rounded-xl transition-all text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 cursor-pointer';
          if (countSpan) countSpan.className = 'text-xs px-2 py-0.5 rounded-full bg-stone-200/70 dark:bg-stone-800 text-stone-600 dark:text-stone-400';
        }
      });

      // Update top pills active state
      document.querySelectorAll('#shop-top-category-pills button').forEach(btn => {
        const catId = btn.getAttribute('data-cat-pill');
        if (catId === this.category) {
          btn.className = 'px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all border bg-stone-900 text-white dark:bg-gold-400 dark:text-stone-950 border-transparent shadow-sm cursor-pointer';
        } else {
          btn.className = 'px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all border bg-white/90 dark:bg-stone-900/90 text-stone-700 dark:text-stone-300 border-stone-200/80 dark:border-stone-800 hover:border-gold-500 cursor-pointer';
        }
      });

      let filtered = this.products.filter(p => {
        if (this.category !== 'all' && p.category !== this.category) return false;
        if (p.price > this.maxPrice) return false;
        if (this.onlySale && !p.onSale) return false;
        if (this.onlyBestSeller && !p.bestSeller) return false;
        if (this.searchQuery) {
          const q = this.searchQuery.toLowerCase();
          const match = (p.name && p.name.toLowerCase().includes(q)) ||
                        (p.categoryLabel && p.categoryLabel.toLowerCase().includes(q)) ||
                        (p.description && p.description.toLowerCase().includes(q));
          if (!match) return false;
        }
        return true;
      });

      // Sorting
      if (this.sortBy === 'popularity') {
        filtered.sort((a, b) => (b.reviewsCount || 0) - (a.reviewsCount || 0));
      } else if (this.sortBy === 'rating') {
        filtered.sort((a, b) => (b.rating || 0) - (a.rating || 0));
      } else if (this.sortBy === 'price') {
        filtered.sort((a, b) => (a.price || 0) - (b.price || 0));
      } else if (this.sortBy === 'price-desc') {
        filtered.sort((a, b) => (b.price || 0) - (a.price || 0));
      } else {
        filtered.sort((a, b) => a.id - b.id);
      }

      const total = filtered.length;
      const totalPages = Math.max(1, Math.ceil(total / this.perPage));
      if (this.currentPage > totalPages) this.currentPage = 1;

      const startIdx = (this.currentPage - 1) * this.perPage;
      const pageItems = filtered.slice(startIdx, startIdx + this.perPage);

      if (resultCountEl) {
        resultCountEl.textContent = total === 0
          ? 'Showing 0 results'
          : (total <= this.perPage ? `Showing all ${total} results` : `Showing ${startIdx + 1}–${Math.min(startIdx + this.perPage, total)} of ${total} results`);
      }

      if (total === 0) {
        container.className = 'block';
        container.innerHTML = `
          <div class="p-12 text-center bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800">
            <i data-lucide="package-search" class="w-12 h-12 mx-auto text-stone-400 mb-3 opacity-50"></i>
            <h3 class="font-serif text-2xl font-medium text-stone-900 dark:text-white mb-2">No Matching Creations Found</h3>
            <p class="text-xs text-stone-500 max-w-sm mx-auto mb-6">No creations matched your current filter combination. Reset filters to view all handcrafted atelier pieces.</p>
            <button type="button" onclick="ShopPageController.resetFilters()" class="px-5 py-2.5 bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-wider rounded-xl transition-all cursor-pointer">Reset All Filters</button>
          </div>
        `;
      } else if (this.viewMode === 'list') {
        container.className = 'space-y-4';
        container.innerHTML = pageItems.map(p => this.createListCard(p)).join('');
      } else {
        container.className = 'products grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6';
        container.innerHTML = pageItems.map(p => this.createGridCard(p)).join('');
      }

      if (paginationEl) {
        let btns = '';
        if (totalPages > 1) {
          for (let i = 1; i <= totalPages; i++) {
            const active = i === this.currentPage;
            btns += `<button type="button" onclick="ShopPageController.goToPage(${i})" class="w-9 h-9 rounded-xl text-xs font-bold transition-all ${active ? 'bg-gold-400 text-stone-950 shadow-sm' : 'bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-700 dark:text-stone-300 hover:border-gold-500'} cursor-pointer">${i}</button>`;
          }
        }
        paginationEl.innerHTML = `
          <div class="flex items-center gap-2 text-xs text-stone-500">
            <span>Page ${this.currentPage} of ${totalPages} (${total} creations)</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-[11px] text-stone-400 mr-1">Show:</span>
            <button type="button" onclick="ShopPageController.setPerPage(12)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all ${this.perPage === 12 ? 'bg-gold-400 text-stone-950' : 'bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400'} cursor-pointer">12 / Page</button>
            <button type="button" onclick="ShopPageController.setPerPage(6)" class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all ${this.perPage === 6 ? 'bg-gold-400 text-stone-950' : 'bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-600 dark:text-stone-400'} cursor-pointer">6 / Page</button>
            ${btns}
          </div>
        `;
      }

      if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
      }
      if (window.Kinboni && typeof window.Kinboni.syncWishlistButtons === 'function') {
        window.Kinboni.syncWishlistButtons();
      }
    },

    goToPage(page) {
      this.currentPage = page;
      this.render();
      const shopMain = document.getElementById('page-view-shop');
      if (shopMain) {
        shopMain.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  };

  window.ShopPageController = ShopPageController;

  // =========================================================================
  // SHOPPING BAG & CART PAGE CONTROLLER
  // =========================================================================
  const CartPageController = {
    zone: 'inside',
    cartData: null,

    init(scope) {
      const container = (scope && scope.querySelector) ? scope.querySelector('#page-view-cart') : document.getElementById('page-view-cart');
      if (!container) return;

      const initialScript = document.getElementById('kinboni-cart-initial-data');
      if (initialScript) {
        try {
          this.cartData = JSON.parse(initialScript.textContent);
        } catch (e) {
          this.cartData = null;
        }
      }

      this.bindEvents(container);
    },

    bindEvents(container) {
      $(document).off('submit', '#cart-page-coupon-form').on('submit', '#cart-page-coupon-form', (e) => {
        e.preventDefault();
        const input = document.getElementById('cart-page-coupon-input');
        const code = input ? input.value.trim() : '';
        if (code) {
          this.applyCoupon(code);
          if (input) input.value = '';
        } else {
          Kinboni.showToast('Please enter a privilege code.', 'error');
        }
      });

      $(document).off('click', '.btn-cart-page-qty-plus, .btn-cart-page-qty-minus').on('click', '.btn-cart-page-qty-plus, .btn-cart-page-qty-minus', function (e) {
        e.preventDefault();
        const key = $(this).data('key');
        const qty = parseInt($(this).data('qty'), 10);
        if (key && qty >= 0) {
          CartPageController.updateQty(key, qty);
        }
      });

      $(document).off('click', '.btn-cart-page-remove').on('click', '.btn-cart-page-remove', function (e) {
        e.preventDefault();
        const key = $(this).data('key');
        if (key) {
          CartPageController.removeItem(key);
        }
      });

      $(document).off('click', '.btn-cart-remove-coupon').on('click', '.btn-cart-remove-coupon', function (e) {
        e.preventDefault();
        const coupon = $(this).data('coupon');
        if (coupon) {
          CartPageController.removeCoupon(coupon);
        }
      });

      $(document).off('change', 'input[name="cart-page-zone"]').on('change', 'input[name="cart-page-zone"]', function () {
        CartPageController.setZone($(this).val());
      });

      $(document.body).off('wc_fragments_refreshed.cartpage').on('wc_fragments_refreshed.cartpage', () => {
        this.fetchAndUpdate();
      });
    },

    setZone(z) {
      this.zone = z;
      if (this.cartData) {
        this.renderTotals();
      } else {
        this.fetchAndUpdate();
      }
    },

    updateQty(key, qty) {
      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_update_cart_qty',
          nonce: kinboniCoreData.nonce,
          cart_item_key: key,
          quantity: qty
        },
        success: () => {
          this.fetchAndUpdate();
          $(document.body).trigger('wc_fragment_refresh');
        }
      });
    },

    removeItem(key) {
      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_remove_cart_item',
          nonce: kinboniCoreData.nonce,
          cart_item_key: key
        },
        success: () => {
          Kinboni.showToast('Item removed from shopping bag.', 'info');
          this.fetchAndUpdate();
          $(document.body).trigger('wc_fragment_refresh');
        }
      });
    },

    clearCart() {
      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_clear_cart',
          nonce: kinboniCoreData.nonce
        },
        success: () => {
          Kinboni.showToast('Shopping bag cleared.', 'info');
          this.fetchAndUpdate();
          $(document.body).trigger('wc_fragment_refresh');
        }
      });
    },

    applyCoupon(code) {
      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_apply_coupon',
          nonce: kinboniCoreData.nonce,
          coupon_code: code
        },
        success: (res) => {
          if (res.success) {
            Kinboni.showToast(res.data.message || 'Privilege code applied!', 'success');
            this.fetchAndUpdate();
            $(document.body).trigger('wc_fragment_refresh');
          } else {
            Kinboni.showToast(res.data.message || 'Invalid privilege code.', 'error');
          }
        }
      });
    },

    removeCoupon(code) {
      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_remove_coupon',
          nonce: kinboniCoreData.nonce,
          coupon_code: code
        },
        success: () => {
          Kinboni.showToast('Privilege code removed.', 'info');
          this.fetchAndUpdate();
          $(document.body).trigger('wc_fragment_refresh');
        }
      });
    },

    fetchAndUpdate() {
      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_get_cart_data',
          nonce: kinboniCoreData.nonce
        },
        success: (res) => {
          if (res.success && res.data) {
            this.cartData = res.data;
            this.render();
          }
        }
      });
    },

    handleQuantityChange(idOrKey, delta) {
      if (!this.cartData || !this.cartData.items) return;
      const item = this.cartData.items.find(i => i.key === idOrKey || i.id === idOrKey);
      if (item) {
        const newQty = Math.max(0, item.quantity + delta);
        this.updateQty(item.key, newQty);
      }
    },

    handleRemove(idOrKey) {
      if (!this.cartData || !this.cartData.items) return;
      const item = this.cartData.items.find(i => i.key === idOrKey || i.id === idOrKey);
      if (item) {
        this.removeItem(item.key);
      }
    },

    renderTotals() {
      if (!this.cartData) return;
      const d = this.cartData;
      const subtotal = d.subtotal || 0;
      const discount = d.discount || 0;
      const threshold = d.threshold || (window.kinboniCoreData && kinboniCoreData.free_shipping_threshold) || 2500;
      const feeInside = d.fee_inside || (window.kinboniCoreData && kinboniCoreData.fee_inside_dhaka) || 60;
      const feeOutside = d.fee_outside || (window.kinboniCoreData && kinboniCoreData.fee_outside_dhaka) || 120;

      const isFree = subtotal >= threshold;
      const shippingFee = isFree ? 0 : (this.zone === 'outside' ? feeOutside : feeInside);
      const finalTotal = Math.max(0, subtotal - discount + shippingFee);

      const sym = d.currency_symbol || (window.kinboniCoreData && kinboniCoreData.currency_symbol) || '৳';

      const subtotalEl = document.getElementById('cart-page-subtotal');
      if (subtotalEl) subtotalEl.innerHTML = d.subtotal_formatted || `${sym}${Number(subtotal).toLocaleString()}`;

      const discountRow = document.getElementById('cart-page-discount-row');
      const discountEl = document.getElementById('cart-page-discount');
      if (discountRow && discountEl) {
        if (discount > 0) {
          discountRow.classList.remove('hidden');
          discountRow.classList.add('flex');
          discountEl.innerHTML = `-${d.discount_formatted || (sym + Number(discount).toLocaleString())}`;
        } else {
          discountRow.classList.add('hidden');
          discountRow.classList.remove('flex');
        }
      }

      const feeInsideEl = document.getElementById('cart-page-fee-inside');
      if (feeInsideEl) feeInsideEl.innerHTML = isFree ? 'Free' : `${sym}${Number(feeInside).toLocaleString()}`;

      const feeOutsideEl = document.getElementById('cart-page-fee-outside');
      if (feeOutsideEl) feeOutsideEl.innerHTML = isFree ? 'Free' : `${sym}${Number(feeOutside).toLocaleString()}`;

      const totalEl = document.getElementById('cart-page-total');
      if (totalEl) totalEl.innerHTML = `${sym}${Number(finalTotal).toLocaleString()}`;

      const shippingBar = document.getElementById('cart-page-shipping-bar');
      const shippingText = document.getElementById('cart-page-shipping-text');
      if (shippingBar && shippingText) {
        const pct = Math.min(100, Math.round((subtotal / threshold) * 100));
        shippingBar.style.width = `${pct}%`;
        if (isFree) {
          shippingText.innerHTML = `🎉 <strong class="text-emerald-600 dark:text-emerald-400">Complimentary White-Glove Express Shipping Unlocked!</strong>`;
        } else {
          const diff = Math.max(0, threshold - subtotal);
          shippingText.innerHTML = `Add <strong>${sym}${Number(diff).toLocaleString()}</strong> more to unlock Complimentary White-Glove Express Shipping`;
        }
      }
    },

    render() {
      if (!this.cartData) return;
      const d = this.cartData;

      const emptyEl = document.getElementById('cart-page-empty');
      const contentEl = document.getElementById('cart-page-content');
      const tbody = document.getElementById('cart-page-table-body');
      const promoContainer = document.getElementById('cart-page-applied-promo');
      const crossSellGrid = document.getElementById('cart-cross-sell-grid');

      if (!d.items || d.items.length === 0) {
        if (emptyEl) emptyEl.classList.remove('hidden');
        if (contentEl) contentEl.classList.add('hidden');
        const shippingBar = document.getElementById('cart-page-shipping-bar');
        const shippingText = document.getElementById('cart-page-shipping-text');
        if (shippingBar) shippingBar.style.width = '0%';
        if (shippingText) {
          const sym = d.currency_symbol || '৳';
          shippingText.innerHTML = `Add <strong>${sym}${Number(d.threshold || 2500).toLocaleString()}</strong> more to unlock Complimentary White-Glove Express Shipping`;
        }
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
        return;
      }

      if (emptyEl) emptyEl.classList.add('hidden');
      if (contentEl) contentEl.classList.remove('hidden');

      if (tbody) {
        tbody.innerHTML = d.items.map(item => `
          <tr class="woocommerce-cart-form__cart-item cart_item hover:bg-stone-50/50 dark:hover:bg-stone-950/40 transition-colors" data-cart-item-key="${item.key}">
            <td class="py-4 px-5">
              <div class="flex items-center gap-4">
                <a href="${item.url}">
                  <img 
                    src="${item.image}" 
                    alt="${Kinboni.esc(item.name)}" 
                    class="w-16 h-20 object-cover rounded-xl bg-stone-100 dark:bg-stone-800 cursor-pointer flex-shrink-0" 
                  />
                </a>
                <div>
                  <span class="text-xs font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400 block">${Kinboni.esc(item.category)}</span>
                  <a href="${item.url}" class="font-serif text-base font-medium text-stone-900 dark:text-white hover:text-gold-600 transition-colors">
                    ${Kinboni.esc(item.name)}
                  </a>
                  ${item.monogram ? `
                    <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-gold-700 dark:text-gold-400">
                      <i data-lucide="sparkles" class="w-3 h-3"></i>
                      <span>Foil Monogram: "${Kinboni.esc(item.monogram)}"</span>
                    </div>
                  ` : ''}
                </div>
              </div>
            </td>
            <td class="py-4 px-4 font-semibold text-stone-700 dark:text-stone-300">
              ${item.price_formatted}
            </td>
            <td class="py-4 px-4">
              <div class="inline-flex items-center border border-stone-200 dark:border-stone-700 rounded-xl bg-stone-50 dark:bg-stone-950">
                <button type="button" class="btn-cart-page-qty-minus w-8 h-8 flex items-center justify-center text-stone-600 dark:text-stone-400 hover:text-gold-500 cursor-pointer" data-key="${item.key}" data-id="${item.id}" data-qty="${item.quantity - 1}" aria-label="Decrease">
                  <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                </button>
                <span class="w-8 text-center text-xs font-bold text-stone-900 dark:text-white">${item.quantity}</span>
                <button type="button" class="btn-cart-page-qty-plus w-8 h-8 flex items-center justify-center text-stone-600 dark:text-stone-400 hover:text-gold-500 cursor-pointer" data-key="${item.key}" data-id="${item.id}" data-qty="${item.quantity + 1}" aria-label="Increase">
                  <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                </button>
              </div>
            </td>
            <td class="py-4 px-5 text-right font-serif text-base font-bold text-stone-900 dark:text-white">
              ${item.subtotal_formatted}
            </td>
            <td class="py-4 px-4 text-center">
              <button type="button" class="btn-cart-page-remove p-2 text-stone-400 hover:text-rose-500 transition-colors cursor-pointer" data-key="${item.key}" data-id="${item.id}" aria-label="Remove item">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
            </td>
          </tr>
        `).join('');
      }

      if (promoContainer) {
        if (d.coupons && d.coupons.length > 0) {
          promoContainer.innerHTML = d.coupons.map(cpn => `
            <div class="flex items-center justify-between p-3 rounded-xl bg-gold-50 dark:bg-gold-950/40 border border-gold-300 dark:border-gold-800 text-xs mb-2">
              <span class="font-semibold text-gold-700 dark:text-gold-300">Active Code: <strong>${Kinboni.esc(cpn.code)}</strong></span>
              <button type="button" class="btn-cart-remove-coupon text-stone-400 hover:text-rose-500 cursor-pointer" data-coupon="${Kinboni.esc(cpn.code)}">
                <i data-lucide="x" class="w-4 h-4"></i>
              </button>
            </div>
          `).join('');
        } else {
          promoContainer.innerHTML = '';
        }
      }

      if (crossSellGrid && d.cross_sells && d.cross_sells.length > 0) {
        crossSellGrid.innerHTML = d.cross_sells.map(cs => `
          <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-stone-50 dark:bg-stone-950 border border-stone-200/80 dark:border-stone-800">
            <div class="flex items-center gap-3 min-w-0">
              <img src="${cs.image}" alt="${Kinboni.esc(cs.name)}" class="w-14 h-16 object-cover rounded-xl flex-shrink-0" />
              <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gold-700 dark:text-gold-400 block">${Kinboni.esc(cs.category)}</span>
                <h4 class="font-serif text-xs font-semibold text-stone-900 dark:text-white truncate">${Kinboni.esc(cs.name)}</h4>
                <span class="text-xs font-bold text-stone-900 dark:text-white">${cs.price_formatted}</span>
              </div>
            </div>
                <h4 class="font-serif text-xs font-semibold text-stone-900 dark:text-white truncate">${cs.name}</h4>
                <span class="text-xs font-bold text-stone-900 dark:text-white">${cs.price_formatted}</span>
              </div>
            </div>
            <button type="button" class="btn-cart-cross-sell-add px-3 py-2 rounded-xl bg-gold-400 hover:bg-gold-500 text-stone-950 text-xs font-bold uppercase tracking-wider flex-shrink-0 cursor-pointer" data-id="${cs.id}" onclick="if(window.CartController && window.CartController.add){CartController.add(${cs.id}, 1);}else if(window.KinboniCore){KinboniCore.addToCart(${cs.id}, 1);}">
              + Add
            </button>
          </div>
        `).join('');
      }

      this.renderTotals();

      if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
      }
      if (window.Kinboni && typeof window.Kinboni.syncWishlistButtons === 'function') {
        window.Kinboni.syncWishlistButtons();
      }
    }
  };

  window.CartPageController = CartPageController;

  // =========================================================================
  // ATELIER CHECKOUT PAGE CONTROLLER
  // =========================================================================
  const CheckoutPageController = {
    zone: 'inside',
    cartData: null,

    init(scope) {
      const container = (scope && scope.querySelector) ? scope.querySelector('#page-view-checkout') : document.getElementById('page-view-checkout');
      if (!container) return;

      this.bindEvents(container);
      this.fetchAndUpdate();
    },

    bindEvents(container) {
      $(document).off('submit', '#wc-full-checkout-form').on('submit', '#wc-full-checkout-form', (e) => {
        e.preventDefault();
        this.submitOrder();
      });

      $(document).off('change', '#wc-billing-city').on('change', '#wc-billing-city', function () {
        CheckoutPageController.onCityChange($(this).val());
      });

      $(document).off('change', 'input[name="wc_payment_method"]').on('change', 'input[name="wc_payment_method"]', function () {
        CheckoutPageController.togglePaymentFields($(this).val());
      });

      $(document.body).off('wc_fragments_refreshed.checkoutpage').on('wc_fragments_refreshed.checkoutpage', () => {
        this.fetchAndUpdate();
      });
    },

    onCityChange(val) {
      this.zone = (val === 'outside') ? 'outside' : 'inside';
      this.render();
    },

    togglePaymentFields(method) {
      const bkashBox = document.getElementById('wc-bkash-fields');
      if (bkashBox) {
        if (method && (method.indexOf('bKash') !== -1 || method.indexOf('Nagad') !== -1)) {
          bkashBox.classList.remove('hidden');
        } else {
          bkashBox.classList.add('hidden');
        }
      }
    },

    applyCoupon() {
      const input = document.getElementById('wc-checkout-coupon-input');
      const code = input ? input.value.trim() : '';
      if (!code) {
        Kinboni.showToast('Please enter a voucher code.', 'error');
        return;
      }

      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_apply_coupon',
          nonce: kinboniCoreData.nonce,
          coupon_code: code
        },
        success: (res) => {
          if (res.success) {
            Kinboni.showToast(res.data.message || 'Privilege voucher applied!', 'success');
            if (input) input.value = '';
            this.fetchAndUpdate();
            $(document.body).trigger('wc_fragment_refresh');
          } else {
            Kinboni.showToast(res.data.message || 'Invalid privilege voucher.', 'error');
          }
        }
      });
    },

    fetchAndUpdate() {
      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_get_cart_data',
          nonce: kinboniCoreData.nonce
        },
        success: (res) => {
          if (res.success && res.data) {
            this.cartData = res.data;
            this.render();
          }
        }
      });
    },

    render() {
      const itemsContainer = document.getElementById('wc-checkout-order-items');
      const subtotalEl = document.getElementById('wc-checkout-subtotal');
      const discountRow = document.getElementById('wc-checkout-discount-row');
      const discountEl = document.getElementById('wc-checkout-discount');
      const shippingEl = document.getElementById('wc-checkout-shipping');
      const totalEl = document.getElementById('wc-checkout-total');

      if (!itemsContainer) return;

      const d = this.cartData;
      if (!d || !d.items || d.items.length === 0) {
        const shopUrl = (window.kinboniCoreData && kinboniCoreData.shop_url) || '/shop/';
        itemsContainer.innerHTML = `
          <div class="py-6 text-center text-xs text-stone-500">
            Your bag is currently empty. <a href="${shopUrl}" onclick="if(window.RouterController){RouterController.navigate('shop'); return false;}" class="text-gold-600 font-bold underline">Browse Creations</a>
          </div>
        `;
        if (subtotalEl) subtotalEl.textContent = '৳0';
        if (discountRow) {
          discountRow.classList.add('hidden');
          discountRow.classList.remove('flex');
        }
        if (shippingEl) shippingEl.textContent = '৳0';
        if (totalEl) totalEl.textContent = '৳0';
        return;
      }

      itemsContainer.innerHTML = d.items.map(item => `
        <div class="py-3 flex items-center justify-between gap-3 text-xs">
          <div class="flex items-center gap-3 min-w-0">
            <img src="${item.image}" alt="${Kinboni.esc(item.name)}" class="w-12 h-14 object-cover rounded-lg flex-shrink-0" />
            <div class="min-w-0">
              <h4 class="font-serif text-sm font-medium text-stone-900 dark:text-white truncate">${Kinboni.esc(item.name)}</h4>
              <span class="text-stone-500">Qty: ${item.quantity}</span>
              ${item.monogram ? `<span class="block text-[11px] text-gold-700 dark:text-gold-400 font-semibold">Monogram: "${Kinboni.esc(item.monogram)}"</span>` : ''}
            </div>
          </div>
          <span class="font-bold text-stone-900 dark:text-white flex-shrink-0">${item.subtotal_formatted}</span>
        </div>
      `).join('');

      const subtotal = d.subtotal || 0;
      const discount = d.discount || 0;
      const threshold = d.threshold || (window.kinboniCoreData && kinboniCoreData.free_shipping_threshold) || 2500;
      const feeInside = d.fee_inside || (window.kinboniCoreData && kinboniCoreData.fee_inside_dhaka) || 60;
      const feeOutside = d.fee_outside || (window.kinboniCoreData && kinboniCoreData.fee_outside_dhaka) || 120;

      const isFree = subtotal >= threshold;
      const shippingFee = isFree ? 0 : (this.zone === 'outside' ? feeOutside : feeInside);
      const totalPayable = Math.max(0, subtotal - discount + shippingFee);
      const sym = d.currency_symbol || (window.kinboniCoreData && kinboniCoreData.currency_symbol) || '৳';

      if (subtotalEl) subtotalEl.innerHTML = d.subtotal_formatted || `${sym}${Number(subtotal).toLocaleString()}`;

      if (discountRow && discountEl) {
        if (discount > 0) {
          discountRow.classList.remove('hidden');
          discountRow.classList.add('flex');
          discountEl.innerHTML = `-${d.discount_formatted || (sym + Number(discount).toLocaleString())}`;
        } else {
          discountRow.classList.add('hidden');
          discountRow.classList.remove('flex');
        }
      }

      if (shippingEl) shippingEl.textContent = isFree ? 'Free (Complimentary)' : `${sym}${Number(shippingFee).toLocaleString()}`;
      if (totalEl) totalEl.textContent = `${sym}${Number(totalPayable).toLocaleString()}`;

      if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
      }
      if (window.Kinboni && typeof window.Kinboni.syncWishlistButtons === 'function') {
        window.Kinboni.syncWishlistButtons();
      }
    },

    submitOrder() {
      if (!this.cartData || !this.cartData.items || this.cartData.items.length === 0) {
        Kinboni.showToast('Your shopping bag is empty! Please add a creation first.', 'error');
        return;
      }

      const firstName = $('#wc-billing-first-name').val() ? $('#wc-billing-first-name').val().trim() : '';
      const lastName = $('#wc-billing-last-name').val() ? $('#wc-billing-last-name').val().trim() : '';
      const phone = $('#wc-billing-phone').val() ? $('#wc-billing-phone').val().trim() : '';
      const email = $('#wc-billing-email').val() ? $('#wc-billing-email').val().trim() : '';
      const city = $('#wc-billing-city').val() || 'inside';
      const postcode = $('#wc-billing-postcode').val() ? $('#wc-billing-postcode').val().trim() : '';
      const address = $('#wc-billing-address').val() ? $('#wc-billing-address').val().trim() : '';
      const giftWrap = $('#wc-gift-wrap-checkbox').is(':checked') ? 1 : 0;
      const orderNotes = $('#wc-order-notes').val() ? $('#wc-order-notes').val().trim() : '';
      const paymentMethod = $('input[name="wc_payment_method"]:checked').val() || 'Cash on Delivery (COD)';
      const trxId = $('#wc-bkash-trx').val() ? $('#wc-bkash-trx').val().trim() : '';
      const errEl = document.getElementById('wc-checkout-error');

      const cleanPhone = phone.replace(/[\s-]/g, '');
      const phoneRegex = /^(?:\+88|88)?01[3-9]\d{8}$/;

      if (!firstName || !phoneRegex.test(cleanPhone) || !address) {
        if (errEl) {
          errEl.textContent = 'Please complete your First Name, valid 11-digit Bangladeshi Mobile Number (01XXXXXXXXX), and Street Address.';
          errEl.classList.remove('hidden');
        }
        Kinboni.showToast('Please complete all required fields.', 'error');
        return;
      }

      if (paymentMethod.indexOf('bKash') !== -1 && !trxId) {
        if (errEl) {
          errEl.textContent = 'Please enter your bKash / Nagad Transaction ID (TrxID).';
          errEl.classList.remove('hidden');
        }
        Kinboni.showToast('Please enter your TrxID for mobile payment verification.', 'error');
        return;
      }

      if (errEl) errEl.classList.add('hidden');

      const submitBtn = document.getElementById('wc-place-order-btn');
      const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i><span>Securing Order...</span>`;
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      }

      $.ajax({
        url: kinboniCoreData.ajax_url,
        type: 'POST',
        data: {
          action: 'kinboni_checkout_submit',
          nonce: kinboniCoreData.nonce,
          first_name: firstName,
          last_name: lastName,
          phone: cleanPhone,
          email: email,
          city: city,
          postcode: postcode,
          address: address,
          gift_wrap: giftWrap,
          order_notes: orderNotes,
          payment_method: paymentMethod,
          trx_id: trxId,
          kinboni_hp: $('#wc-full-checkout-form').find('input[name="kinboni_hp"]').val() || ''
        },
        success: (res) => {
          if (res.success && res.data) {
            const data = res.data;
            const tyId = document.getElementById('wc-ty-order-id');
            const tyDate = document.getElementById('wc-ty-date');
            const tyTotal = document.getElementById('wc-ty-total');
            const tyMethod = document.getElementById('wc-ty-method');

            if (tyId) tyId.textContent = data.order_number;
            if (tyDate) tyDate.textContent = data.date;
            if (tyTotal) tyTotal.innerHTML = data.total_formatted;
            if (tyMethod) tyMethod.textContent = data.payment_method;

            const formContainer = document.getElementById('wc-checkout-form-container');
            const tyContainer = document.getElementById('wc-checkout-thankyou-container');
            if (formContainer) formContainer.classList.add('hidden');
            if (tyContainer) tyContainer.classList.remove('hidden');

            Kinboni.trackAnalyticsEvent('Purchase', {
              transaction_id: data.order_id,
              value: parseFloat(data.total || 0),
              currency: (window.kinboniCoreData && kinboniCoreData.currency_code) || 'BDT'
            });
            Kinboni.showToast(data.message || `Order ${data.order_number} confirmed!`, 'success');
            $(document.body).trigger('wc_fragment_refresh');
            window.scrollTo({ top: 0, behavior: 'smooth' });
          } else {
            if (errEl) {
              errEl.textContent = (res.data && res.data.message) || 'Could not place order. Please try again.';
              errEl.classList.remove('hidden');
            }
            Kinboni.showToast((res.data && res.data.message) || 'Error placing order.', 'error');
          }
        },
        error: () => {
          if (errEl) {
            errEl.textContent = 'Server communication error. Please try again.';
            errEl.classList.remove('hidden');
          }
          Kinboni.showToast('Network error while placing order.', 'error');
        },
        complete: () => {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
              window.lucide.createIcons();
            }
          }
        }
      });
    }
  };

  window.CheckoutPageController = CheckoutPageController;

  // =========================================================================
  // PATRON CONCIERGE & MY ACCOUNT CONTROLLER
  // =========================================================================
  const AccountPageController = {
    isLoggedIn: false,
    profile: {
      name: '',
      email: '',
      phone: '',
      address: ''
    },
    orders: [],

    init(scope) {
      const container = (scope && scope.querySelector) ? scope.querySelector('#page-view-my-account') : document.getElementById('page-view-my-account');
      if (!container) return;

      const initDataScript = container.querySelector('#kinboni-account-initial-data') || document.getElementById('kinboni-account-initial-data');
      if (initDataScript && initDataScript.textContent) {
        try {
          const parsed = JSON.parse(initDataScript.textContent.trim());
          if (parsed) {
            this.isLoggedIn = !!parsed.is_logged_in;
            if (parsed.profile) {
              this.profile = Object.assign(this.profile, parsed.profile);
            }
            if (Array.isArray(parsed.orders)) {
              this.orders = parsed.orders;
            }
          }
        } catch (e) {
          console.warn('Kinboni: Failed to parse initial account data', e);
        }
      }

      try {
        const urlParams = new URLSearchParams(window.location.search);
        const requestedTab = urlParams.get('tab');
        if (requestedTab) {
          this.switchTab(requestedTab, container);
        }
      } catch (e) {}

      this.updateWishlistCount(container);
      this.bindEvents(container);

      if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
      }
      if (window.Kinboni && typeof window.Kinboni.syncWishlistButtons === 'function') {
        window.Kinboni.syncWishlistButtons();
      }
    },

    updateWishlistCount(container) {
      const root = container || document;
      const wishStat = root.querySelector('#wc-acc-stat-wishlist');
      if (wishStat && Kinboni && Array.isArray(Kinboni.wishlist)) {
        wishStat.textContent = Kinboni.wishlist.length;
      }
    },

    switchTab(tabName, container) {
      const root = container || document.getElementById('page-view-my-account') || document;
      if (!root) return;

      const dashWrap = root.querySelector('#wc-account-dashboard-wrap');
      const authWrap = root.querySelector('#wc-account-auth-wrap');
      if (dashWrap) dashWrap.classList.remove('hidden');
      if (authWrap) authWrap.classList.add('hidden');

      const navButtons = root.querySelectorAll('.wc-acc-nav-btn');
      navButtons.forEach(btn => {
        const btnTab = btn.getAttribute('data-acc-tab');
        if (btnTab === tabName) {
          btn.classList.remove('text-stone-700', 'dark:text-stone-300', 'hover:bg-stone-100', 'dark:hover:bg-stone-800');
          btn.classList.add('bg-stone-900', 'text-white', 'dark:bg-gold-400', 'dark:text-stone-950');
        } else {
          btn.classList.remove('bg-stone-900', 'text-white', 'dark:bg-gold-400', 'dark:text-stone-950');
          btn.classList.add('text-stone-700', 'dark:text-stone-300', 'hover:bg-stone-100', 'dark:hover:bg-stone-800');
        }
      });

      const panels = root.querySelectorAll('.wc-acc-panel');
      panels.forEach(p => p.classList.add('hidden'));

      const targetPanel = root.querySelector('#wc-acc-panel-' + tabName);
      if (targetPanel) {
        targetPanel.classList.remove('hidden');
      }

      if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
      }
      if (window.Kinboni && typeof window.Kinboni.syncWishlistButtons === 'function') {
        window.Kinboni.syncWishlistButtons();
      }
    },

    toggleAuthView(showAuth, container) {
      const root = container || document.getElementById('page-view-my-account') || document;
      if (!root) return;

      const dashWrap = root.querySelector('#wc-account-dashboard-wrap');
      const authWrap = root.querySelector('#wc-account-auth-wrap');

      if (showAuth) {
        if (dashWrap) dashWrap.classList.add('hidden');
        if (authWrap) authWrap.classList.remove('hidden');
      } else {
        if (dashWrap) dashWrap.classList.remove('hidden');
        if (authWrap) authWrap.classList.add('hidden');
      }

      if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
      }
      if (window.Kinboni && typeof window.Kinboni.syncWishlistButtons === 'function') {
        window.Kinboni.syncWishlistButtons();
      }
    },

    openOrdersView() {
      const accView = document.getElementById('page-view-my-account');
      if (accView) {
        this.switchTab('orders', accView);
        accView.scrollIntoView({ behavior: 'smooth' });
      } else {
        const dest = (window.kinboniCoreData && kinboniCoreData.myaccount_url) ? (kinboniCoreData.myaccount_url + '?tab=orders') : '/my-account/?tab=orders';
        window.location.href = dest;
      }
    },

    recordOrder(orderObj) {
      if (!orderObj) return;
      this.orders.unshift(orderObj);

      const ordersTbody = document.getElementById('wc-acc-orders-tbody');
      const ordersStat = document.getElementById('wc-acc-stat-orders');
      if (ordersStat) {
        ordersStat.textContent = this.orders.length;
      }

      if (ordersTbody) {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-stone-50/50 dark:hover:bg-stone-800/30 transition-colors';
        tr.innerHTML = `
          <td class="py-4 font-mono font-bold text-stone-900 dark:text-white">${Kinboni.esc(orderObj.id || '#KB-NEW')}</td>
          <td class="py-4 text-stone-600 dark:text-stone-400">${Kinboni.esc(orderObj.date || 'Today')}</td>
          <td class="py-4">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> ${Kinboni.esc(orderObj.status || 'Processing')}
            </span>
          </td>
          <td class="py-4 font-bold text-stone-900 dark:text-white">${orderObj.total_formatted || ('৳' + (orderObj.total || '0'))}</td>
          <td class="py-4 text-right">
            <a href="${orderObj.view_url || '#'}" class="text-xs font-bold text-gold-600 dark:text-gold-400 hover:underline">View Status</a>
          </td>
        `;
        if (ordersTbody.firstChild) {
          ordersTbody.insertBefore(tr, ordersTbody.firstChild);
        } else {
          ordersTbody.appendChild(tr);
        }
      }
    },

    bindEvents(container) {
      const self = this;
      const root = $(container || '#page-view-my-account');

      // Edit Profile Form
      root.off('submit', '#wc-acc-details-form').on('submit', '#wc-acc-details-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const origText = submitBtn.text();

        const nameVal = $('#wc-acc-input-name').val().trim();
        const emailVal = $('#wc-acc-input-email').val().trim();
        const phoneVal = $('#wc-acc-input-phone').val().trim();
        const addrVal = $('#wc-acc-input-address').val().trim();
        const passVal = $('#wc-acc-input-password').val();
        const curPassVal = $('#wc-acc-input-current-password').val() || '';

        if (!nameVal || !emailVal) {
          Kinboni.showToast('Please fill in required fields.', 'error');
          return;
        }

        submitBtn.prop('disabled', true).text('Saving Changes...');

        $.ajax({
          url: (window.kinboniCoreData && kinboniCoreData.ajax_url) || '/wp-admin/admin-ajax.php',
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'kinboni_update_profile',
            nonce: (window.kinboniCoreData && kinboniCoreData.nonce) || '',
            name: nameVal,
            email: emailVal,
            phone: phoneVal,
            address: addrVal,
            password: passVal,
            current_password: curPassVal
          },
          success: function (res) {
            submitBtn.prop('disabled', false).text(origText);
            if (res && res.success) {
              self.profile.name = nameVal;
              self.profile.email = emailVal;
              self.profile.phone = phoneVal;
              self.profile.address = addrVal;

              $('#wc-acc-greeting-name').text(nameVal);
              const addressDisplay = `${Kinboni.esc(nameVal)}<br/>${Kinboni.esc(addrVal)}<br/>Phone: ${Kinboni.esc(phoneVal)}`;
              $('#wc-acc-address-display').html(addressDisplay);

              Kinboni.showToast(res.data && res.data.message ? res.data.message : 'Patron profile updated successfully!', 'success');
              $('#wc-acc-input-password').val('');
              $('#wc-acc-input-current-password').val('');
            } else {
              const msg = res && res.data && res.data.message ? res.data.message : 'Failed to update profile.';
              Kinboni.showToast(msg, 'error');
            }
          },
          error: function () {
            submitBtn.prop('disabled', false).text(origText);
            Kinboni.showToast('Network error while updating profile.', 'error');
          }
        });
      });

      // Sign In Form
      root.off('submit', '#wc-login-form').on('submit', '#wc-login-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const origText = submitBtn.text();

        const username = $('#wc-login-email').val().trim();
        const password = $('#wc-login-pass').val();

        if (!username || !password) {
          Kinboni.showToast('Please enter both identifier and password.', 'error');
          return;
        }

        submitBtn.prop('disabled', true).text('Signing In...');

        $.ajax({
          url: (window.kinboniCoreData && kinboniCoreData.ajax_url) || '/wp-admin/admin-ajax.php',
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'kinboni_login',
            nonce: (window.kinboniCoreData && kinboniCoreData.nonce) || '',
            username: username,
            password: password
          },
          success: function (res) {
            submitBtn.prop('disabled', false).text(origText);
            if (res && res.success) {
              Kinboni.showToast('Welcome back! Signed in successfully.', 'success');
              setTimeout(function () {
                window.location.reload();
              }, 600);
            } else {
              const msg = res && res.data && res.data.message ? res.data.message : 'Invalid credentials. Please try again.';
              Kinboni.showToast(msg, 'error');
            }
          },
          error: function () {
            submitBtn.prop('disabled', false).text(origText);
            Kinboni.showToast('Network error while signing in.', 'error');
          }
        });
      });

      // Register Form
      root.off('submit', '#wc-register-form').on('submit', '#wc-register-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const origText = submitBtn.text();

        const name = $('#wc-reg-name').val().trim();
        const email = $('#wc-reg-email').val().trim();
        const phone = $('#wc-reg-phone').val().trim();
        const password = $('#wc-reg-password').val();

        if (!name || !email || !password) {
          Kinboni.showToast('Please fill in all required fields.', 'error');
          return;
        }

        submitBtn.prop('disabled', true).text('Registering Membership...');

        $.ajax({
          url: (window.kinboniCoreData && kinboniCoreData.ajax_url) || '/wp-admin/admin-ajax.php',
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'kinboni_register',
            nonce: (window.kinboniCoreData && kinboniCoreData.nonce) || '',
            name: name,
            email: email,
            phone: phone,
            password: password
          },
          success: function (res) {
            submitBtn.prop('disabled', false).text(origText);
            if (res && res.success) {
              Kinboni.showToast('Patron account created! Welcome to Kinboni.', 'success');
              setTimeout(function () {
                window.location.reload();
              }, 600);
            } else {
              const msg = res && res.data && res.data.message ? res.data.message : 'Registration failed. Please check inputs.';
              Kinboni.showToast(msg, 'error');
            }
          },
          error: function () {
            submitBtn.prop('disabled', false).text(origText);
            Kinboni.showToast('Network error while creating account.', 'error');
          }
        });
      });
    }
  };

  window.AccountPageController = AccountPageController;

  // DOM READY
  $(document).ready(function () {
    Kinboni.init();
    window.KinboniCore = Kinboni;
    AccountPageController.init();
    CheckoutPageController.init();
    CartPageController.init();
    ShopPageController.init();
    SingleProductController.init();
    HeroTripleSliderController.init();
    TestimonialSliderController.init();
    FlashCountdownController.init();

    // Initial Best Sellers filter trigger
    $('.kinboni-bestsellers-section, #bestsellers').each(function () {
      const activeFilter = $(this).find('.bestseller-filter-btn.bg-stone-900, .bestseller-filter-btn.dark\\:bg-gold-400').data('filter') || 'all';
      filterBestSellers(activeFilter, this);
    });

    // Built-in Salon Contact Form Submission
    $(document).on('submit', '#wc-contact-form', function (e) {
      e.preventDefault();
      const cname = $('#wc-contact-name').val() ? $('#wc-contact-name').val().trim() : 'Patron';
      this.reset();
      Kinboni.showToast('Thank you, ' + cname + '! Your salon inquiry has been dispatched to our Gulshan-2 Concierge.', 'success');
    });
  });

  // NavMenuController for Header Pages Mega Dropdown
  const NavMenuController = {
    isOpen: false,
    suppressHover: false,

    init() {
      $(document).on('click', (e) => {
        const grp = document.getElementById('pages-dropdown-group');
        if (this.isOpen && grp && !grp.contains(e.target)) {
          this.closeDropdown(false);
        }
      });
      $(window).on('keydown', (e) => {
        if (e.key === 'Escape' && this.isOpen) {
          this.closeDropdown(false);
        }
      });
    },

    openDropdown() {
      if (this.suppressHover) return;
      this.isOpen = true;
      const panel = document.getElementById('pages-dropdown-panel');
      const chev = document.getElementById('pages-dropdown-chevron');
      const btn = document.getElementById('pages-dropdown-btn');
      if (panel) {
        panel.classList.remove('opacity-0', 'invisible', 'translate-y-2', 'pointer-events-none');
        panel.classList.add('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
      }
      if (chev) chev.classList.add('rotate-180');
      if (btn) btn.setAttribute('aria-expanded', 'true');
    },

    closeDropdown(lockHover = false) {
      this.isOpen = false;
      const panel = document.getElementById('pages-dropdown-panel');
      const chev = document.getElementById('pages-dropdown-chevron');
      const btn = document.getElementById('pages-dropdown-btn');
      if (panel) {
        panel.classList.add('opacity-0', 'invisible', 'translate-y-2', 'pointer-events-none');
        panel.classList.remove('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
      }
      if (chev) chev.classList.remove('rotate-180');
      if (btn) btn.setAttribute('aria-expanded', 'false');
      if (lockHover) {
        this.suppressHover = true;
        setTimeout(() => { this.suppressHover = false; }, 350);
      }
    },

    toggleDropdown(e) {
      if (e) e.stopPropagation();
      if (this.isOpen) {
        this.closeDropdown(false);
      } else {
        this.suppressHover = false;
        this.openDropdown();
      }
    }
  };

  window.NavMenuController = NavMenuController;
  NavMenuController.init();

  // Mobile menu drawer open/close
  $(document).on('click', '#mobile-menu-btn, .open-mobile-menu-btn, .mobile-menu-toggle-btn', function (e) {
    e.preventDefault();
    const drawer = document.getElementById('mobile-menu-drawer');
    const backdrop = document.getElementById('mobile-menu-backdrop');
    if (drawer) {
      drawer.classList.remove('-translate-x-full');
      drawer.classList.remove('hidden');
    }
    if (backdrop) {
      backdrop.classList.remove('hidden');
      setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
    }
    document.body.classList.add('overflow-hidden');
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  });

  $(document).on('click', '#close-mobile-menu-btn, #mobile-menu-backdrop, .close-mobile-menu-btn, #mobile-menu-drawer a', function (e) {
    const drawer = document.getElementById('mobile-menu-drawer');
    const backdrop = document.getElementById('mobile-menu-backdrop');
    if (drawer) drawer.classList.add('-translate-x-full');
    if (backdrop) {
      backdrop.classList.add('opacity-0');
      setTimeout(() => backdrop.classList.add('hidden'), 300);
    }
    document.body.classList.remove('overflow-hidden');
  });

  // Close mobile drawer on Escape key
  $(document).on('keydown', function (e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
      const drawer = document.getElementById('mobile-menu-drawer');
      const backdrop = document.getElementById('mobile-menu-backdrop');
      if (drawer && !drawer.classList.contains('-translate-x-full')) {
        drawer.classList.add('-translate-x-full');
        if (backdrop) {
          backdrop.classList.add('opacity-0');
          setTimeout(() => backdrop.classList.add('hidden'), 300);
        }
        document.body.classList.remove('overflow-hidden');
      }
    }
  });

  // Apply Default Theme Mode from Storefront Settings if user has not manually toggled yet
  (function applyDefaultThemeMode() {
    try {
      const savedTheme = localStorage.getItem('kinboni_theme');
      const defaultMode = (window.kinboniCoreData && kinboniCoreData.theme_mode_default) || 'system';
      if (!savedTheme) {
        if (defaultMode === 'dark') {
          document.documentElement.classList.add('dark');
        } else if (defaultMode === 'light') {
          document.documentElement.classList.remove('dark');
        }
      }
    } catch (e) {}
  })();

  // Live Guest Contact Capture for Abandoned Cart Recovery
  let guestCaptureTimer = null;
  $(document).on('change blur', '#wc-billing-phone, #wc-billing-email, #wc-billing-first-name, #order-customer-phone, #order-customer-name', function () {
    clearTimeout(guestCaptureTimer);
    guestCaptureTimer = setTimeout(function () {
      const phone = ($('#wc-billing-phone').val() || $('#order-customer-phone').val() || '').trim();
      const email = ($('#wc-billing-email').val() || '').trim();
      const firstName = ($('#wc-billing-first-name').val() || $('#order-customer-name').val() || '').trim();
      const lastName = ($('#wc-billing-last-name').val() || '').trim();
      const name = (firstName + ' ' + lastName).trim();
      if ((phone.length >= 11 || email.indexOf('@') > 0) && window.kinboniCoreData && kinboniCoreData.ajax_url) {
        $.post(kinboniCoreData.ajax_url, {
          action: 'kinboni_capture_guest_cart',
          nonce: kinboniCoreData.nonce,
          phone: phone,
          email: email,
          name: name
        });
      }
    }, 600);
  });

  // Dark/Light Theme Toggle Handler
  $(document).on('click', '.theme-toggle-btn, #theme-toggle-btn', function (e) {
    e.preventDefault();
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('kinboni_theme', isDark ? 'dark' : 'light');
    const sunIcons = document.querySelectorAll('.theme-sun-icon, .theme-icon-sun');
    const moonIcons = document.querySelectorAll('.theme-moon-icon, .theme-icon-moon');
    sunIcons.forEach(el => el.classList.toggle('hidden', !isDark));
    moonIcons.forEach(el => el.classList.toggle('hidden', isDark));
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  });

  // Elementor Frontend Ready Hook
  $(window).on('elementor/frontend/init', function () {
    if (window.elementorFrontend && window.elementorFrontend.hooks) {

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_header.default', function ($scope) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });
      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_hero_triple_slider.default', function ($scope) {
        HeroTripleSliderController.init($scope[0]);
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_featured_products.default', function ($scope) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_categories_grid.default', function ($scope) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_promotional_banner.default', function ($scope) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_bestsellers.default', function ($scope) {
        const activeFilter = $scope.find('.bestseller-filter-btn.bg-stone-900, .bestseller-filter-btn.dark\\:bg-gold-400').data('filter') || 'all';
        filterBestSellers(activeFilter, $scope[0]);
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_heritage.default', function ($scope) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_testimonials.default', function ($scope) {
        TestimonialSliderController.init($scope[0]);
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_newsletter.default', function ($scope) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_trust_benefits.default', function ($scope) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_faq.default', function ($scope) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_flash_deals.default', function ($scope) {
        FlashCountdownController.init($scope[0]);
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_size_guide.default', function ($scope) {
        Kinboni.renderWishlistPageItems();
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_contact.default', function ($scope) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_single_product.default', function ($scope) {
        SingleProductController.init($scope[0]);
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_shop.default', function ($scope) {
        ShopPageController.init($scope[0]);
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_cart.default', function ($scope) {
        CartPageController.init($scope[0]);
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_checkout.default', function ($scope) {
        CheckoutPageController.init($scope[0]);
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_my_account.default', function ($scope) {
        AccountPageController.init($scope[0]);
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });

      elementorFrontend.hooks.addAction('frontend/element_ready/kinboni_footer.default', function ($scope) {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
          window.lucide.createIcons();
        }
      });
    }
  });

})(jQuery);
