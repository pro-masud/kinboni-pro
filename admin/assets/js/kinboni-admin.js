/**
 * Kinboni Pro Luxury Admin Interaction Engine
 *
 * Implements: Interactive Canvas Charting, Dynamic Date Range Filtering via AJAX,
 * and Live Order Notification Polling.
 *
 * @package Kinboni_Core
 * @version 1.0.0
 */

(function ($) {
    'use strict';

    const KinboniAdmin = {
        state: {
            activeRange: '30d',
            activeChartView: 'revenue', // 'revenue', 'orders', 'visitors'
            chartData: null,
            canvas: null,
            ctx: null,
            audioCtx: null
        },

        init() {
            if ($('#kinboni-overview-app').length) {
                this.initOverview();
            }
            if ($('#kinboni-orders-app').length) {
                this.initOrders();
            }
            if ($('#kinboni-products-app').length) {
                this.initProducts();
            }
            if ($('#kinboni-customers-app').length) {
                this.initCustomers();
            }
            if ($('#kinboni-marketing-app').length) {
                this.initMarketing();
            }
            if ($('#kinboni-analytics-app').length) {
                this.initAnalytics();
            }
            if ($('#kinboni-delivery-app').length) {
                this.initDelivery();
            }
            if ($('#kinboni-notifications-app').length) {
                this.initNotifications();
            }
            if ($('#kinboni-storefront-app').length) {
                this.initStorefront();
            }
            if ($('#kinboni-reports-app').length) {
                this.initReports();
            }
            if ($('#kinboni-system-app').length) {
                this.initSystem();
            }
            this.initLiveOrderPoller();
        },

        // =====================================================================
        // 1. OVERVIEW DASHBOARD CONTROLLER
        // =====================================================================
        initOverview() {
            if (window.kinboniOverviewInit) {
                this.state.chartData = window.kinboniOverviewInit;
            }

            this.initCanvasChart();
            this.bindEvents();
        },

        bindEvents() {
            const self = this;

            // Date Range Switcher Pills
            $(document).on('click', '#overview-range-selector .kinboni-pill', function (e) {
                e.preventDefault();
                const range = $(this).data('range');
                if (range && range !== self.state.activeRange) {
                    $('#overview-range-selector .kinboni-pill').removeClass('is-active');
                    $(this).addClass('is-active');
                    self.fetchOverviewData(range);
                }
            });

            // Manual Refresh Button
            $(document).on('click', '#btn-refresh-overview', function (e) {
                e.preventDefault();
                const $btn = $(this);
                $btn.find('.dashicons').addClass('spin');
                self.fetchOverviewData(self.state.activeRange, function () {
                    $btn.find('.dashicons').removeClass('spin');
                });
            });

            // Chart Tab View Switcher (Revenue vs Orders vs Visitors)
            $(document).on('click', '.chart-toggle-tabs .chart-tab', function (e) {
                e.preventDefault();
                const view = $(this).data('view');
                if (view && view !== self.state.activeChartView) {
                    $('.chart-toggle-tabs .chart-tab').removeClass('is-active');
                    $(this).addClass('is-active');
                    self.state.activeChartView = view;
                    self.drawChart();
                }
            });

            // Window resize handler
            $(window).on('resize', function () {
                self.resizeCanvas();
                self.drawChart();
            });
        },

        /**
         * Fetch Dashboard Metrics via AJAX
         */
        fetchOverviewData(range, callback) {
            const self = this;
            self.state.activeRange = range;

            $.ajax({
                url: kinboniAdminData.ajax_url,
                type: 'POST',
                data: {
                    action: 'kinboni_get_overview_stats',
                    nonce: kinboniAdminData.nonce,
                    range: range
                },
                beforeSend() {
                    $('#kinboni-overview-app').css('opacity', '0.7');
                },
                success(res) {
                    $('#kinboni-overview-app').css('opacity', '1');
                    if (res && res.success && res.data) {
                        self.updateDashboardUI(res.data);
                    }
                    if (typeof callback === 'function') callback();
                },
                error() {
                    $('#kinboni-overview-app').css('opacity', '1');
                    if (typeof callback === 'function') callback();
                }
            });
        },

        /**
         * Update UI Elements with Returned Data
         */
        updateDashboardUI(data) {
            const c = data.currency || '৳';
            const s = data.sales_metrics;
            const v = data.visitor_stats;

            // Update KPI metric numbers
            $('#metric-revenue').text(c + this.formatNumber(s.total_revenue, 2));
            $('#metric-orders').text(this.formatNumber(s.total_orders, 0));
            $('#metric-aov').text(c + this.formatNumber(s.aov, 2));
            $('#metric-visitors').text(this.formatNumber(v.unique_visitors, 0));
            $('#metric-views').text(this.formatNumber(v.total_views, 0));

            // Update state chart data
            this.state.chartData = {
                labels: s.chart_labels,
                revenues: s.chart_revenues,
                orders: s.chart_orders,
                visitorLabels: v.chart_labels,
                visitorViews: v.chart_views,
                currency: c
            };

            // Redraw Chart
            this.drawChart();
        },

        formatNumber(num, decimals) {
            return parseFloat(num || 0).toLocaleString('en-US', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        },

        // =====================================================================
        // 2. HIGH-PERFORMANCE LUXURY CANVAS CHART ENGINE
        // =====================================================================
        initCanvasChart() {
            this.state.canvas = document.getElementById('kinboniSalesChart');
            if (!this.state.canvas) return;

            this.state.ctx = this.state.canvas.getContext('2d');
            this.resizeCanvas();
            this.drawChart();
        },

        resizeCanvas() {
            if (!this.state.canvas) return;
            const container = this.state.canvas.parentElement;
            const dpr = window.devicePixelRatio || 1;
            const width = container.clientWidth;
            const height = container.clientHeight || 320;

            this.state.canvas.width = width * dpr;
            this.state.canvas.height = height * dpr;
            this.state.canvas.style.width = width + 'px';
            this.state.canvas.style.height = height + 'px';
            this.state.ctx.scale(dpr, dpr);
        },

        drawChart() {
            if (!this.state.ctx || !this.state.chartData) return;

            const ctx = this.state.ctx;
            const container = this.state.canvas.parentElement;
            const width = container.clientWidth;
            const height = container.clientHeight || 320;

            ctx.clearRect(0, 0, width, height);

            const isRev = this.state.activeChartView === 'revenue';
            const isOrd = this.state.activeChartView === 'orders';
            const isVis = this.state.activeChartView === 'visitors';

            let labels = this.state.chartData.labels || [];
            let values = isRev ? (this.state.chartData.revenues || []) :
                         isOrd ? (this.state.chartData.orders || []) :
                         (this.state.chartData.visitorViews || []);

            if (isVis && this.state.chartData.visitorLabels && this.state.chartData.visitorLabels.length) {
                labels = this.state.chartData.visitorLabels;
            }

            if (!values.length || !labels.length) {
                // Empty state message on canvas
                ctx.fillStyle = '#78716C';
                ctx.font = '13px sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('No recorded activity for this range.', width / 2, height / 2);
                return;
            }

            const paddingLeft = 60;
            const paddingRight = 20;
            const paddingTop = 30;
            const paddingBottom = 40;

            const chartW = width - paddingLeft - paddingRight;
            const chartH = height - paddingTop - paddingBottom;

            // Find Max Value
            let maxVal = Math.max(...values, 1);
            maxVal = Math.ceil(maxVal * 1.15); // Add headroom

            // Draw Subtle Grid Lines & Y-Axis Labels
            const gridLines = 4;
            ctx.strokeStyle = '#E8E6DF';
            ctx.fillStyle = '#78716C';
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'right';
            ctx.lineWidth = 1;

            const cSymbol = this.state.chartData.currency || '৳';

            for (let i = 0; i <= gridLines; i++) {
                const y = paddingTop + (chartH / gridLines) * i;
                const val = Math.round(maxVal - (maxVal / gridLines) * i);
                const labelText = isRev ? (cSymbol + val.toLocaleString()) : val.toLocaleString();

                ctx.beginPath();
                ctx.moveTo(paddingLeft, y);
                ctx.lineTo(width - paddingRight, y);
                ctx.stroke();

                ctx.fillText(labelText, paddingLeft - 10, y + 4);
            }

            // Calculate Data Points Coordinates
            const stepX = chartW / (values.length > 1 ? (values.length - 1) : 1);
            const points = [];

            for (let i = 0; i < values.length; i++) {
                const x = paddingLeft + (i * stepX);
                const y = paddingTop + chartH - ((values[i] / maxVal) * chartH);
                points.push({ x, y, val: values[i], label: labels[i] });
            }

            // Draw Smooth Golden Gradient Fill
            const gradient = ctx.createLinearGradient(0, paddingTop, 0, paddingTop + chartH);
            gradient.addColorStop(0, 'rgba(212, 175, 55, 0.28)');
            gradient.addColorStop(1, 'rgba(212, 175, 55, 0.00)');

            ctx.beginPath();
            ctx.moveTo(points[0].x, paddingTop + chartH);
            ctx.lineTo(points[0].x, points[0].y);

            for (let i = 0; i < points.length - 1; i++) {
                const p0 = points[i];
                const p1 = points[i + 1];
                const cpx = (p0.x + p1.x) / 2;
                ctx.bezierCurveTo(cpx, p0.y, cpx, p1.y, p1.x, p1.y);
            }

            ctx.lineTo(points[points.length - 1].x, paddingTop + chartH);
            ctx.closePath();
            ctx.fillStyle = gradient;
            ctx.fill();

            // Draw Elegant Golden Stroke
            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            for (let i = 0; i < points.length - 1; i++) {
                const p0 = points[i];
                const p1 = points[i + 1];
                const cpx = (p0.x + p1.x) / 2;
                ctx.bezierCurveTo(cpx, p0.y, cpx, p1.y, p1.x, p1.y);
            }
            ctx.strokeStyle = '#D4AF37';
            ctx.lineWidth = 3;
            ctx.lineJoin = 'round';
            ctx.stroke();

            // Draw Data Dots & X-Axis Labels (skip some labels if too crowded)
            const labelStep = Math.ceil(labels.length / 8);
            ctx.textAlign = 'center';
            ctx.fillStyle = '#1C1917';

            for (let i = 0; i < points.length; i++) {
                const p = points[i];

                // Golden Node Dot
                ctx.beginPath();
                ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
                ctx.fillStyle = '#FFFFFF';
                ctx.fill();
                ctx.lineWidth = 2.5;
                ctx.strokeStyle = '#B89628';
                ctx.stroke();

                // X Label
                if (i % labelStep === 0 || i === points.length - 1) {
                    ctx.fillStyle = '#78716C';
                    ctx.font = '10px sans-serif';
                    ctx.fillText(p.label, p.x, height - 12);
                }
            }
        },

        // =====================================================================
        // 2. ORDERS MANAGEMENT CONTROLLER
        // =====================================================================
        initOrders() {
            const self = this;

            // Check All Orders Checkbox
            $('#check-all-orders').on('change', function () {
                $('.order-cb').prop('checked', $(this).prop('checked'));
            });

            // Inline Status Change Dropdown
            $(document).on('change', '.select-inline-status', function () {
                const $select = $(this);
                const orderId = $select.data('order-id');
                const newStatus = $select.val();

                $select.css('opacity', '0.5');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_update_order_status',
                        nonce: kinboniAdminData.nonce,
                        order_id: orderId,
                        status: newStatus
                    },
                    success(res) {
                        $select.css('opacity', '1');
                        if (res && res.success) {
                            $select.css('border-color', '#10B981');
                            setTimeout(() => $select.css('border-color', '#E8E6DF'), 1500);
                        } else {
                            alert(res.data ? res.data.message : 'Error updating status');
                        }
                    },
                    error() {
                        $select.css('opacity', '1');
                        alert('Server error updating order status');
                    }
                });
            });

            // Manual Suspicious Flag Toggle
            $(document).on('click', '.btn-toggle-flag', function (e) {
                e.preventDefault();
                const $btn = $(this);
                const orderId = $btn.data('order-id');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_toggle_order_flag',
                        nonce: kinboniAdminData.nonce,
                        order_id: orderId
                    },
                    success(res) {
                        if (res && res.success) {
                            if (res.data.is_flagged) {
                                $btn.addClass('is-flagged').attr('title', 'Unflag order');
                                $(`#order-row-${orderId}`).addClass('row-suspicious');
                            } else {
                                $btn.removeClass('is-flagged').attr('title', 'Flag order as suspicious for review');
                                if (!$(`#order-row-${orderId} .badge-risk-warning`).length) {
                                    $(`#order-row-${orderId}`).removeClass('row-suspicious');
                                }
                            }
                        }
                    }
                });
            });

            // Bulk Actions
            $('#btn-apply-bulk-action').on('click', function () {
                const action = $('#bulk-order-action-select').val();
                if (!action) {
                    alert('Please select an action from the dropdown.');
                    return;
                }

                const selectedIds = [];
                $('.order-cb:checked').each(function () {
                    selectedIds.push($(this).val());
                });

                if (!selectedIds.length) {
                    alert('Please select at least one order using the checkboxes.');
                    return;
                }

                if (!confirm(`Apply this bulk action to ${selectedIds.length} selected orders?`)) {
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).text('Applying...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_bulk_order_action',
                        nonce: kinboniAdminData.nonce,
                        order_ids: selectedIds,
                        bulk_action: action
                    },
                    success(res) {
                        $btn.prop('disabled', false).text('Apply');
                        if (res && res.success) {
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error executing bulk action');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).text('Apply');
                        alert('Server error executing bulk action');
                    }
                });
            });

            // Open Order Details Slide-Over Modal
            $(document).on('click', '.btn-view-order-details', function (e) {
                e.preventDefault();
                const orderId = $(this).data('order-id');
                self.openOrderModal(orderId);
            });

            // Close Modal
            $('#btn-close-order-modal, .kinboni-modal-backdrop').on('click', function () {
                $('#kinboni-order-modal').addClass('hidden');
            });

            // Add Private Admin Note inside Modal
            $('#btn-submit-order-note').on('click', function () {
                const $input = $('#input-new-order-note');
                const note = $input.val().trim();
                const orderId = $(this).data('order-id');

                if (!note) {
                    alert('Please enter a note before submitting.');
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).text('Adding Note...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_add_order_note',
                        nonce: kinboniAdminData.nonce,
                        order_id: orderId,
                        note: note
                    },
                    success(res) {
                        $btn.prop('disabled', false).text('Add Private Note');
                        if (res && res.success) {
                            $input.val('');
                            // Prepend note to modal timeline
                            const noteHtml = `
                                <div class="note-bubble">
                                    <div class="note-content">${res.data.note}</div>
                                    <div class="note-meta">${res.data.date} &bull; by ${res.data.author}</div>
                                </div>
                            `;
                            $('#modal-notes-list').prepend(noteHtml);
                        } else {
                            alert(res.data ? res.data.message : 'Error adding note');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).text('Add Private Note');
                        alert('Server error adding note');
                    }
                });
            });

            // Flag toggle inside modal
            $('#modal-toggle-flag-btn').on('click', function () {
                const orderId = $(this).data('order-id');
                const $btn = $(this);

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_toggle_order_flag',
                        nonce: kinboniAdminData.nonce,
                        order_id: orderId
                    },
                    success(res) {
                        if (res && res.success) {
                            if (res.data.is_flagged) {
                                $btn.addClass('is-flagged').text('Remove Warning Flag');
                                $(`#order-row-${orderId}`).addClass('row-suspicious');
                                $(`#order-row-${orderId} .btn-toggle-flag`).addClass('is-flagged');
                            } else {
                                $btn.removeClass('is-flagged').text('Flag as Suspicious');
                                $(`#order-row-${orderId} .btn-toggle-flag`).removeClass('is-flagged');
                                if (!$(`#order-row-${orderId} .badge-risk-warning`).length) {
                                    $(`#order-row-${orderId}`).removeClass('row-suspicious');
                                }
                            }
                        }
                    }
                });
            });
        },

        openOrderModal(orderId) {
            const $modal = $('#kinboni-order-modal');
            $modal.removeClass('hidden');

            // Reset modal data
            $('#modal-order-title').text(`Order #${orderId}`);
            $('#modal-line-items').html('<p style="font-size:12px;color:#78716C;">Loading line items...</p>');
            $('#modal-notes-list').html('<p style="font-size:12px;color:#78716C;">Loading notes...</p>');
            $('#modal-suspicious-alert').addClass('hidden');
            $('#btn-submit-order-note').data('order-id', orderId);
            $('#modal-toggle-flag-btn').data('order-id', orderId);

            $.ajax({
                url: kinboniAdminData.ajax_url,
                type: 'POST',
                data: {
                    action: 'kinboni_get_order_details',
                    nonce: kinboniAdminData.nonce,
                    order_id: orderId
                },
                success(res) {
                    if (res && res.success && res.data) {
                        const d = res.data;

                        $('#modal-order-title').text(`Order #${d.order_number}`);
                        $('#modal-order-status-badge').text(d.status_name).attr('class', `badge-status status-${d.status}`);
                        $('#modal-cust-name').text(d.customer_name);
                        $('#modal-cust-phone').text(d.customer_phone);
                        $('#modal-cust-email').text(d.customer_email || '—');
                        $('#modal-whatsapp-btn').attr('href', d.whatsapp_url);
                        $('#modal-delivery-addr').html(d.billing_address.replace(/\n/g, '<br>'));
                        $('#modal-shipping-method').text(d.shipping_method);

                        // Totals
                        $('#modal-subtotal').html(d.subtotal);
                        $('#modal-discount').html(d.discount_total);
                        $('#modal-shipping').html(d.shipping_total);
                        $('#modal-total').html(d.total);
                        $('#modal-pay-title').text(d.payment_title);

                        // Links
                        $('#modal-invoice-link').attr('href', d.print_invoice);
                        $('#modal-slip-link').attr('href', d.print_slip);

                        // Steadfast Courier Box
                        if (d.steadfast_tracking) {
                            $('#modal-steadfast-box').show();
                            $('#modal-steadfast-consignment').text(d.steadfast_consignment || '—');
                            $('#modal-steadfast-tracking').text(d.steadfast_tracking);
                            $('#btn-book-steadfast').prop('disabled', true).html('✓ Booked with Steadfast');
                        } else {
                            $('#modal-steadfast-box').hide();
                            $('#btn-book-steadfast').prop('disabled', false).html('<span class="dashicons dashicons-external"></span> Book Steadfast Courier');
                        }

                        // Suspicious warning
                        if (d.manual_flag || (d.repeat_orders && d.repeat_orders.length >= 2)) {
                            $('#modal-suspicious-alert').removeClass('hidden');
                            if (d.repeat_orders && d.repeat_orders.length >= 2) {
                                $('#modal-suspicious-heading').text(`⚠️ Phone Repeated (${d.repeat_orders.length + 1} Total Orders)`);
                                let repeatTags = '';
                                d.repeat_orders.forEach(ro => {
                                    repeatTags += `<span class="repeat-order-pill">#${ro.number} (${ro.status} - ${ro.total})</span>`;
                                });
                                $('#modal-repeat-orders-list').html(repeatTags);
                            } else {
                                $('#modal-suspicious-heading').text('🚩 Manually Flagged for Review');
                                $('#modal-repeat-orders-list').empty();
                            }
                        }

                        // Flag Button State
                        if (d.manual_flag) {
                            $('#modal-toggle-flag-btn').addClass('is-flagged').text('Remove Warning Flag');
                        } else {
                            $('#modal-toggle-flag-btn').removeClass('is-flagged').text('Flag as Suspicious');
                        }

                        // Line items
                        let itemsHtml = '';
                        if (d.items && d.items.length) {
                            d.items.forEach(it => {
                                itemsHtml += `
                                    <div class="modal-item-row">
                                        <img src="${it.image}" alt="${it.name}" class="modal-item-thumb" />
                                        <div class="modal-item-meta">
                                            <div class="modal-item-title">${it.name}</div>
                                            <div class="text-subtle text-xs">SKU: ${it.sku} &bull; Qty: <strong>${it.qty}</strong></div>
                                            ${it.meta ? `<div style="font-size:11px;color:#78716C;">${it.meta}</div>` : ''}
                                        </div>
                                        <div class="font-bold text-dark">${it.total}</div>
                                    </div>
                                `;
                            });
                        }
                        $('#modal-line-items').html(itemsHtml);

                        // Order Notes
                        let notesHtml = '';
                        if (d.notes && d.notes.length) {
                            d.notes.forEach(n => {
                                notesHtml += `
                                    <div class="note-bubble ${n.is_customer ? 'is-customer' : ''}">
                                        <div class="note-content">${n.content}</div>
                                        <div class="note-meta">${n.date} &bull; by ${n.author} ${n.is_customer ? '(Customer note)' : ''}</div>
                                    </div>
                                `;
                            });
                        } else {
                            notesHtml = '<p class="text-subtle text-xs">No notes recorded for this order yet.</p>';
                        }
                        $('#modal-notes-list').html(notesHtml);
                    }
                }
            });

            // Steadfast Courier One-Click Booking
            $('#btn-book-steadfast').on('click', function () {
                const orderId = self.state.activeOrderId;
                if (!orderId) return;

                if (!confirm(`Book Order #${orderId} with Steadfast Courier? A consignment will be created in your Steadfast account.`)) {
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Booking...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_book_steadfast_order',
                        nonce: kinboniAdminData.nonce,
                        order_id: orderId
                    },
                    success(res) {
                        if (res && res.success) {
                            alert(res.data.message || 'Booked successfully with Steadfast!');
                            $btn.prop('disabled', true).html('✓ Booked with Steadfast');
                            $('#modal-steadfast-box').show();
                            $('#modal-steadfast-consignment').text(res.data.consignment_id || '—');
                            $('#modal-steadfast-tracking').text(res.data.tracking_code || '—');
                        } else {
                            $btn.prop('disabled', false).html('<span class="dashicons dashicons-external"></span> Book Steadfast Courier');
                            alert(res.data ? res.data.message : 'Error booking with Steadfast.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-external"></span> Book Steadfast Courier');
                        alert('Server error communicating with Steadfast.');
                    }
                });
            });
        },

        // =====================================================================
        // 3. PRODUCTS & DIRECT PUBLISHING CONTROLLER
        // =====================================================================
        initProducts() {
            const self = this;
            let mediaFrame = null;

            // Check All Products
            $('#check-all-products').on('change', function () {
                $('.product-cb').prop('checked', $(this).prop('checked'));
            });

            // Bulk Action Dropdown change
            $('#bulk-product-action-select').on('change', function () {
                const val = $(this).val();
                if (val === 'price_adjust_pct' || val === 'stock_set') {
                    $('#bulk-product-val').show().focus();
                } else {
                    $('#bulk-product-val').hide();
                }
            });

            // Apply Bulk Product Action
            $('#btn-apply-bulk-product-action').on('click', function () {
                const action = $('#bulk-product-action-select').val();
                const bulkVal = $('#bulk-product-val').val();

                if (!action) {
                    alert('Please select a bulk operation.');
                    return;
                }

                const selectedIds = [];
                $('.product-cb:checked').each(function () {
                    selectedIds.push($(this).val());
                });

                if (!selectedIds.length) {
                    alert('Please select at least one creation with checkboxes.');
                    return;
                }

                if (!confirm(`Apply this operation to ${selectedIds.length} creations?`)) {
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).text('Applying...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_bulk_product_action',
                        nonce: kinboniAdminData.nonce,
                        product_ids: selectedIds,
                        bulk_action: action,
                        bulk_val: bulkVal
                    },
                    success(res) {
                        $btn.prop('disabled', false).text('Apply');
                        if (res && res.success) {
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error executing bulk action');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).text('Apply');
                        alert('Server error executing bulk action');
                    }
                });
            });

            // Inline Stock Quantity Edit
            $(document).on('change', '.input-inline-stock', function () {
                const $input = $(this);
                const productId = $input.data('product-id');
                const newQty = parseInt($input.val(), 10) || 0;

                $input.css('border-color', '#D4AF37');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_quick_update_stock',
                        nonce: kinboniAdminData.nonce,
                        product_id: productId,
                        stock_qty: newQty
                    },
                    success(res) {
                        if (res && res.success) {
                            $input.css('border-color', '#10B981');
                            setTimeout(() => $input.css('border-color', '#E8E6DF'), 1500);
                        } else {
                            alert(res.data ? res.data.message : 'Error saving stock');
                        }
                    }
                });
            });

            // Inline Price Edit
            $(document).on('change', '.input-inline-price', function () {
                const $input = $(this);
                const productId = $input.data('product-id');
                const newPrice = parseFloat($input.val()) || 0;

                $input.css('border-color', '#D4AF37');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_quick_update_price',
                        nonce: kinboniAdminData.nonce,
                        product_id: productId,
                        price: newPrice
                    },
                    success(res) {
                        if (res && res.success) {
                            $input.css('border-color', '#10B981');
                            setTimeout(() => $input.css('border-color', '#E8E6DF'), 1500);
                        } else {
                            alert(res.data ? res.data.message : 'Error saving price');
                        }
                    }
                });
            });

            // One-Click Duplicate Product
            $(document).on('click', '.btn-duplicate-product', function (e) {
                e.preventDefault();
                const $btn = $(this);
                const productId = $btn.data('product-id');

                if (!confirm('Duplicate this creation as a new Draft?')) return;

                $btn.find('.dashicons').addClass('spin');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_duplicate_product',
                        nonce: kinboniAdminData.nonce,
                        product_id: productId
                    },
                    success(res) {
                        $btn.find('.dashicons').removeClass('spin');
                        if (res && res.success) {
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error duplicating product');
                        }
                    },
                    error() {
                        $btn.find('.dashicons').removeClass('spin');
                        alert('Server error duplicating product');
                    }
                });
            });

            // Open "Upload New Product" Modal
            $('#btn-open-create-product').on('click', function () {
                $('#kinboni-product-form')[0].reset();
                $('#prod-form-id').val('0');
                $('#modal-product-title').text('Upload New Creation');
                $('#btn-save-product-submit').text('Save & Publish Creation');
                $('#prod-form-img-id').val('0');
                $('#prod-form-img-preview').attr('src', '');
                $('#btn-remove-prod-img').hide();
                $('.prod-cat-cb').prop('checked', false);
                $('#prod-form-type').val('simple');
                $('#prod-form-is-variable').val('0');
                $('#prod-form-variations-box').hide();
                $('#variations-rows-body').empty();
                $('#kinboni-product-modal').removeClass('hidden');
            });

            // Open "Edit Product" Modal
            $(document).on('click', '.btn-edit-product', function (e) {
                e.preventDefault();
                const productId = $(this).data('product-id');

                $('#modal-product-title').text(`Edit Creation #${productId}`);
                $('#btn-save-product-submit').text('Update Creation');
                $('#kinboni-product-modal').removeClass('hidden');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_get_product_data',
                        nonce: kinboniAdminData.nonce,
                        product_id: productId
                    },
                    success(res) {
                        if (res && res.success && res.data) {
                            const d = res.data;
                            $('#prod-form-id').val(d.id);
                            $('#prod-form-title').val(d.name);
                            $('#prod-form-sku').val(d.sku || '');
                            $('#prod-form-status').val(d.status || 'publish');
                            $('#prod-form-reg-price').val(d.regular_price || '');
                            $('#prod-form-sale-price').val(d.sale_price || '');
                            $('#prod-form-cost-price').val(d.cost_price || '');
                            $('#prod-form-sale-from').val(d.sale_date_from || '');
                            $('#prod-form-sale-to').val(d.sale_date_to || '');
                            $('#prod-form-stock-qty').val(d.stock_quantity !== null ? d.stock_quantity : '');
                            $('#prod-form-stock-st').val(d.stock_status || 'instock');
                            $('#prod-form-short-desc').val(d.short_description || '');
                            $('#prod-form-desc').val(d.description || '');

                            // Image
                            if (d.image_id > 0) {
                                $('#prod-form-img-id').val(d.image_id);
                                $('#prod-form-img-preview').attr('src', d.image_url);
                                $('#btn-remove-prod-img').show();
                            } else {
                                $('#prod-form-img-id').val('0');
                                $('#prod-form-img-preview').attr('src', '');
                                $('#btn-remove-prod-img').hide();
                            }

                            // Categories
                            $('.prod-cat-cb').prop('checked', false);
                            if (d.category_ids && d.category_ids.length) {
                                d.category_ids.forEach(cid => {
                                    $(`.prod-cat-cb[value="${cid}"]`).prop('checked', true);
                                });
                            }
                        }
                    }
                });
            });

            // Close Product Modal
            $('#btn-close-product-modal, #kinboni-product-modal .kinboni-modal-backdrop').on('click', function () {
                $('#kinboni-product-modal').addClass('hidden');
            });

            // Native WordPress Media Uploader for Featured Image
            $('#btn-select-prod-img').on('click', function (e) {
                e.preventDefault();

                if (mediaFrame) {
                    mediaFrame.open();
                    return;
                }

                if (typeof wp !== 'undefined' && wp.media) {
                    mediaFrame = wp.media({
                        title: 'Select Creation Artwork',
                        button: { text: 'Use this Image' },
                        multiple: false
                    });

                    mediaFrame.on('select', function () {
                        const attachment = mediaFrame.state().get('selection').first().toJSON();
                        $('#prod-form-img-id').val(attachment.id);
                        const url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                        $('#prod-form-img-preview').attr('src', url);
                        $('#btn-remove-prod-img').show();
                    });

                    mediaFrame.open();
                } else {
                    alert('WordPress Media Library is unavailable.');
                }
            });

            // Remove image
            $('#btn-remove-prod-img').on('click', function () {
                $('#prod-form-img-id').val('0');
                $('#prod-form-img-preview').attr('src', '');
                $(this).hide();
            });

            // Toggle Product Type (Simple vs Variable)
            $('#prod-form-type').on('change', function () {
                const isVar = $(this).val() === 'variable';
                $('#prod-form-is-variable').val(isVar ? '1' : '0');
                if (isVar) {
                    $('#prod-form-variations-box').slideDown();
                } else {
                    $('#prod-form-variations-box').slideUp();
                }
            });

            // Add Variation Row
            $('#btn-add-var-row').on('click', function (e) {
                e.preventDefault();
                const tag = $('#input-new-var-tag').val().trim();
                if (!tag) return;

                const basePrice = $('#prod-form-reg-price').val() || '';
                const baseStock = $('#prod-form-stock-qty').val() || '10';

                const rowHtml = `
                    <tr class="var-data-row">
                        <td><strong class="var-label">${tag}</strong></td>
                        <td><input type="number" step="0.01" class="form-control var-price" value="${basePrice}" style="padding:4px 8px; font-size:12px;" /></td>
                        <td><input type="number" class="form-control var-stock" value="${baseStock}" style="padding:4px 8px; font-size:12px; max-width:80px;" /></td>
                        <td><button type="button" class="btn-del-var-row" style="color:#DC2626; border:none; background:none; cursor:pointer; font-weight:bold;">✕</button></td>
                    </tr>
                `;
                $('#variations-rows-body').append(rowHtml);
                $('#input-new-var-tag').val('').focus();
            });

            // Delete Variation Row
            $(document).on('click', '.btn-del-var-row', function () {
                $(this).closest('tr').remove();
            });

            // Save Product Form Submission
            $('#kinboni-product-form').on('submit', function (e) {
                e.preventDefault();

                const formData = $(this).serializeArray();

                // If variable, collect variation matrix rows
                if ($('#prod-form-type').val() === 'variable') {
                    const varsData = [];
                    $('.var-data-row').each(function () {
                        const label = $(this).find('.var-label').text();
                        const price = $(this).find('.var-price').val();
                        const stock = $(this).find('.var-stock').val();
                        if (label) {
                            varsData.push({ label, price, stock });
                        }
                    });
                    formData.push({ name: 'is_variable', value: '1' });
                    formData.push({ name: 'variations_data', value: JSON.stringify(varsData) });
                }

                formData.push({ name: 'action', value: 'kinboni_save_product' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                const $btn = $('#btn-save-product-submit');
                $btn.prop('disabled', true).text('Saving Creation to Catalog...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).text('Save & Publish Creation');
                        if (res && res.success) {
                            alert(res.data.message);
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error saving creation.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).text('Save & Publish Creation');
                        alert('Server error saving creation.');
                    }
                });
            });

            // CSV Import Modal open / close
            $('#btn-open-import-modal').on('click', function () {
                $('#kinboni-import-modal').removeClass('hidden');
            });
            $('#btn-close-import-modal, #kinboni-import-modal .kinboni-modal-backdrop').on('click', function () {
                $('#kinboni-import-modal').addClass('hidden');
            });
        },

        // =====================================================================
        // 4. CUSTOMERS & PATRONS CONTROLLER
        // =====================================================================
        initCustomers() {
            const self = this;

            // In-line Customer Tag Change
            $(document).on('change', '.select-inline-customer-tag', function () {
                const $select = $(this);
                const custKey = $select.data('customer-key');
                const newTag  = $select.val();

                $select.prop('disabled', true);

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_update_customer_tag',
                        nonce: kinboniAdminData.nonce,
                        customer_key: custKey,
                        tag: newTag
                    },
                    success(res) {
                        $select.prop('disabled', false);
                        if (res && res.success) {
                            $select.css({ 'border-color': '#10B981', 'box-shadow': '0 0 0 1px #10B981' });
                            setTimeout(function () {
                                $select.css({ 'border-color': '', 'box-shadow': '' });
                            }, 1500);
                        } else {
                            alert(res.data ? res.data.message : 'Error updating tag');
                        }
                    },
                    error() {
                        $select.prop('disabled', false);
                        alert('Network connection error while saving patron tag.');
                    }
                });
            });

            // View Complete Customer Order History (Slide-Over)
            $(document).on('click', '.btn-view-customer-history', function (e) {
                e.preventDefault();
                const custKey = $(this).data('customer-key');
                if (!custKey) return;

                $('#modal-cust-name-title').text('Loading Patron Dossier...');
                $('#modal-dossier-orders-list').html('<div style="text-align:center;padding:24px;color:var(--kb-text-secondary);"><span class="dashicons dashicons-update spin"></span> Loading order records...</div>');
                $('#kinboni-customer-modal').removeClass('hidden');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_get_customer_history',
                        nonce: kinboniAdminData.nonce,
                        customer_key: custKey
                    },
                    success(res) {
                        if (res && res.success && res.data) {
                            const d = res.data;
                            $('#modal-cust-name-title').text(d.name || 'Connoisseur');
                            const tagClass = d.tag === 'VIP' ? 'status-completed' : (d.tag === 'Problematic' ? 'status-cancelled' : 'status-processing');
                            $('#modal-cust-tag-badge').text(d.tag || 'New').attr('class', 'badge-status ' + tagClass);

                            $('#modal-dossier-phone').text(d.phone || '—');
                            $('#modal-dossier-email').text(d.email || '—');

                            if (d.whatsapp_url && d.whatsapp_url !== '#') {
                                $('#modal-dossier-wa-btn').attr('href', d.whatsapp_url).show();
                            } else {
                                $('#modal-dossier-wa-btn').hide();
                            }

                            $('#modal-dossier-orders-count').text(d.orders_count);
                            $('#modal-dossier-total-spent').html(d.total_spent_formatted);
                            $('#modal-dossier-aov').html(d.aov_formatted);

                            let ordersHtml = '';
                            if (d.orders && d.orders.length) {
                                d.orders.forEach(function (ord) {
                                    ordersHtml += `
                                        <div class="modal-order-card" style="padding: 12px 14px; border: 1px solid var(--kb-border-light); border-radius: 6px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; background: #FAF9F6;">
                                            <div>
                                                <div class="font-bold text-dark">Order #${ord.order_number}</div>
                                                <div class="text-xs text-subtle" style="margin-top:2px;">${ord.date} &bull; ${ord.items_count} item(s)</div>
                                            </div>
                                            <div style="text-align: right;">
                                                <div class="font-bold text-dark" style="margin-bottom:2px;">${ord.total}</div>
                                                <span class="badge-status status-${ord.status}">${ord.status_name}</span>
                                            </div>
                                        </div>
                                    `;
                                });
                            } else {
                                ordersHtml = '<p class="text-subtle text-xs" style="text-align:center;padding:16px;">No orders found for this patron.</p>';
                            }
                            $('#modal-dossier-orders-list').html(ordersHtml);
                        } else {
                            alert(res.data ? res.data.message : 'Error fetching patron dossier.');
                        }
                    },
                    error() {
                        alert('Network connection error.');
                    }
                });
            });

            // Slide-Over Modal Close
            $('#btn-close-cust-modal, #kinboni-customer-modal .kinboni-modal-backdrop').on('click', function () {
                $('#kinboni-customer-modal').addClass('hidden');
            });
        },

        // =====================================================================
        // 5. MARKETING & GROWTH CONTROLLER
        // =====================================================================
        initMarketing() {
            const self = this;

            // --- SUBSCRIBER GROWTH CHART ---
            self.initSubscriberChart();

            // --- SUBSCRIBER CAMPAIGN MODAL ---
            $('#btn-open-campaign-modal').on('click', function () {
                $('#modal-marketing-campaign').removeClass('hidden');
            });
            $('#btn-close-campaign-modal, #modal-marketing-campaign .kinboni-modal-backdrop').on('click', function () {
                $('#modal-marketing-campaign').addClass('hidden');
            });

            // Dispatch Campaign
            $('#form-send-campaign').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btn-submit-campaign');
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Dispatching campaign...');

                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'kinboni_send_marketing_campaign' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Dispatch Broadcast Campaign');
                        if (res && res.success) {
                            alert(res.data.message || 'Campaign dispatched successfully!');
                            $('#modal-marketing-campaign').addClass('hidden');
                            $('#form-send-campaign')[0].reset();
                        } else {
                            alert(res.data ? res.data.message : 'Error dispatching campaign.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Dispatch Broadcast Campaign');
                        alert('Network communication error.');
                    }
                });
            });

            // --- ADD SUBSCRIBER MODAL ---
            $('#btn-open-add-sub-modal').on('click', function () {
                $('#modal-add-sub').removeClass('hidden');
            });
            $('#btn-close-add-sub, #modal-add-sub .kinboni-modal-backdrop').on('click', function () {
                $('#modal-add-sub').addClass('hidden');
            });

            // Submit Add Subscriber
            $('#form-add-subscriber').on('submit', function (e) {
                e.preventDefault();
                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).text('Saving...');

                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'kinboni_add_subscriber' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).text('Save Subscriber');
                        if (res && res.success) {
                            alert(res.data.message || 'Subscriber added!');
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error adding subscriber.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).text('Save Subscriber');
                        alert('Network communication error.');
                    }
                });
            });

            // Toggle Subscriber Status
            $(document).on('click', '.btn-toggle-sub', function () {
                const subId = $(this).data('sub-id');
                if (!subId) return;

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_toggle_subscriber_status',
                        nonce: kinboniAdminData.nonce,
                        subscriber_id: subId
                    },
                    success(res) {
                        if (res && res.success) {
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error updating status.');
                        }
                    }
                });
            });

            // Delete Subscriber
            $(document).on('click', '.btn-delete-sub', function () {
                if (!confirm('Are you sure you want to permanently delete this subscriber?')) return;
                const subId = $(this).data('sub-id');
                if (!subId) return;

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_delete_subscriber',
                        nonce: kinboniAdminData.nonce,
                        subscriber_id: subId
                    },
                    success(res) {
                        if (res && res.success) {
                            $('#sub-row-' + subId).fadeOut(300, function () { $(this).remove(); });
                        } else {
                            alert(res.data ? res.data.message : 'Error deleting subscriber.');
                        }
                    }
                });
            });

            // --- COUPON MODAL ---
            $('#btn-open-coupon-modal').on('click', function () {
                $('#modal-create-coupon').removeClass('hidden');
            });
            $('#btn-close-coupon-modal, #modal-create-coupon .kinboni-modal-backdrop').on('click', function () {
                $('#modal-create-coupon').addClass('hidden');
            });

            // Create Coupon Form Submit
            $('#form-create-coupon').on('submit', function (e) {
                e.preventDefault();
                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).text('Creating Coupon...');

                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'kinboni_create_coupon' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).text('Save & Activate Coupon');
                        if (res && res.success) {
                            alert(res.data.message || 'Coupon created successfully!');
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error creating coupon.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).text('Save & Activate Coupon');
                        alert('Network communication error.');
                    }
                });
            });

            // Delete Coupon
            $(document).on('click', '.btn-delete-coupon', function () {
                if (!confirm('Are you sure you want to delete this coupon?')) return;
                const cpnId = $(this).data('coupon-id');
                if (!cpnId) return;

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_delete_coupon',
                        nonce: kinboniAdminData.nonce,
                        coupon_id: cpnId
                    },
                    success(res) {
                        if (res && res.success) {
                            $('#coupon-row-' + cpnId).fadeOut(300, function () { $(this).remove(); });
                        } else {
                            alert(res.data ? res.data.message : 'Error deleting coupon.');
                        }
                    }
                });
            });

            // Copy Coupon Code
            $(document).on('click', '.btn-copy-code', function () {
                const code = $(this).data('code');
                if (!code) return;
                navigator.clipboard.writeText(code).then(function () {
                    alert('Coupon code "' + code + '" copied to clipboard!');
                });
            });

            // --- ABANDONED CARTS: SEND REMINDER EMAIL ---
            $(document).on('click', '.btn-send-cart-reminder', function () {
                const $btn = $(this);
                const cartId = $btn.data('cart-id');
                if (!cartId) return;

                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Sending...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_send_abandoned_reminder',
                        nonce: kinboniAdminData.nonce,
                        cart_id: cartId
                    },
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Remind');
                        if (res && res.success) {
                            alert(res.data.message || 'Reminder email dispatched!');
                            const $row = $('#cart-row-' + cartId);
                            $row.find('.badge-status').text('Sent ' + res.data.reminder_count + 'x').attr('class', 'badge-status status-completed');
                        } else {
                            alert(res.data ? res.data.message : 'Error sending reminder.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Remind');
                        alert('Network communication error.');
                    }
                });
            });

            // --- MARKETING SETTINGS FORM SUBMIT ---
            $('#form-marketing-settings').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btn-save-marketing-settings');
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Saving...');

                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'kinboni_save_marketing_settings' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Marketing Settings');
                        if (res && res.success) {
                            alert(res.data.message || 'Settings saved successfully!');
                        } else {
                            alert(res.data ? res.data.message : 'Error saving settings.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Marketing Settings');
                        alert('Network communication error.');
                    }
                });
            });

            // Media Uploader for Popup Banner Image
            $('#btn-upload-popup-img').on('click', function (e) {
                e.preventDefault();
                const mediaFrame = wp.media({
                    title: 'Select Promotional Banner Image',
                    button: { text: 'Use This Banner' },
                    multiple: false
                });

                mediaFrame.on('select', function () {
                    const attachment = mediaFrame.state().get('selection').first().toJSON();
                    $('#input-popup-image').val(attachment.url);
                });

                mediaFrame.open();
            });
        },

        initSubscriberChart() {
            const self = this;
            const canvas = document.getElementById('kinboniSubscribersChart');
            if (!canvas) return;

            self.subChartState = {
                canvas: canvas,
                ctx: canvas.getContext('2d'),
                chartData: window.kinboniSubscribersInit || { labels: [], counts: [] },
                activeRange: '30d'
            };

            self.resizeSubscriberCanvas();
            self.drawSubscriberChart();

            $(window).on('resize', function () {
                self.resizeSubscriberCanvas();
                self.drawSubscriberChart();
            });

            // Range button clicks
            $('#sub-chart-range-pills .btn-time-pill').on('click', function () {
                const range = $(this).data('range');
                if (!range || range === self.subChartState.activeRange) return;

                $('#sub-chart-range-pills .btn-time-pill').removeClass('is-active');
                $(this).addClass('is-active');
                self.subChartState.activeRange = range;

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_get_subscriber_growth_chart',
                        nonce: kinboniAdminData.nonce,
                        range: range
                    },
                    success(res) {
                        if (res && res.success && res.data) {
                            self.subChartState.chartData = res.data;
                            self.drawSubscriberChart();
                        }
                    }
                });
            });
        },

        resizeSubscriberCanvas() {
            if (!this.subChartState || !this.subChartState.canvas) return;
            const canvas = this.subChartState.canvas;
            const container = canvas.parentElement;
            const width = container.clientWidth || 800;
            const height = container.clientHeight || 240;
            const dpr = window.devicePixelRatio || 1;

            canvas.width = width * dpr;
            canvas.height = height * dpr;
            canvas.style.width = width + 'px';
            canvas.style.height = height + 'px';
            this.subChartState.ctx.scale(dpr, dpr);
        },

        drawSubscriberChart() {
            const state = this.subChartState;
            if (!state || !state.ctx || !state.chartData) return;

            const ctx = state.ctx;
            const container = state.canvas.parentElement;
            const width = container.clientWidth || 800;
            const height = container.clientHeight || 240;

            ctx.clearRect(0, 0, width, height);

            const labels = state.chartData.labels || [];
            const values = state.chartData.counts || [];

            if (!labels.length) {
                ctx.fillStyle = '#78716C';
                ctx.font = '13px sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('No subscriber acquisition data for this timeframe.', width / 2, height / 2);
                return;
            }

            const paddingLeft = 45;
            const paddingRight = 20;
            const paddingTop = 20;
            const paddingBottom = 30;

            const chartW = width - paddingLeft - paddingRight;
            const chartH = height - paddingTop - paddingBottom;

            let maxVal = Math.max(...values, 1);
            maxVal = Math.ceil(maxVal * 1.25);

            // Grid Lines & Labels
            const gridLines = 4;
            ctx.strokeStyle = '#E8E6DF';
            ctx.fillStyle = '#78716C';
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'right';
            ctx.lineWidth = 1;

            for (let i = 0; i <= gridLines; i++) {
                const y = paddingTop + (chartH / gridLines) * i;
                const val = Math.round(maxVal - (maxVal / gridLines) * i);
                ctx.beginPath();
                ctx.moveTo(paddingLeft, y);
                ctx.lineTo(width - paddingRight, y);
                ctx.stroke();

                ctx.fillText(val.toString(), paddingLeft - 8, y + 4);
            }

            const stepX = chartW / (values.length > 1 ? (values.length - 1) : 1);
            const points = [];

            for (let i = 0; i < values.length; i++) {
                const x = paddingLeft + i * stepX;
                const y = paddingTop + chartH - (values[i] / maxVal) * chartH;
                points.push({ x, y, val: values[i], label: labels[i] });
            }

            // Fill Area with Kinboni Gold gradient
            ctx.beginPath();
            ctx.moveTo(points[0].x, paddingTop + chartH);
            for (let i = 0; i < points.length; i++) {
                ctx.lineTo(points[i].x, points[i].y);
            }
            ctx.lineTo(points[points.length - 1].x, paddingTop + chartH);
            ctx.closePath();

            const gradient = ctx.createLinearGradient(0, paddingTop, 0, paddingTop + chartH);
            gradient.addColorStop(0, 'rgba(212, 175, 55, 0.28)');
            gradient.addColorStop(1, 'rgba(212, 175, 55, 0.01)');
            ctx.fillStyle = gradient;
            ctx.fill();

            // Stroke line
            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            for (let i = 1; i < points.length; i++) {
                ctx.lineTo(points[i].x, points[i].y);
            }
            ctx.strokeStyle = '#D4AF37';
            ctx.lineWidth = 2.5;
            ctx.stroke();

            // Draw Data Dots & Bottom Labels
            ctx.fillStyle = '#78716C';
            ctx.textAlign = 'center';
            const labelInterval = Math.ceil(labels.length / 8);

            for (let i = 0; i < points.length; i++) {
                const pt = points[i];

                if (i % labelInterval === 0 || i === points.length - 1) {
                    ctx.fillText(pt.label, pt.x, height - 8);
                }

                if (pt.val > 0) {
                    ctx.beginPath();
                    ctx.arc(pt.x, pt.y, 4, 0, Math.PI * 2);
                    ctx.fillStyle = '#121214';
                    ctx.fill();
                    ctx.lineWidth = 2;
                    ctx.strokeStyle = '#D4AF37';
                    ctx.stroke();
                }
            }
        },

        // =====================================================================
        // 6. ANALYTICS & TRAFFIC CONTROLLER
        // =====================================================================
        initAnalytics() {
            const self = this;
            self.analyticsState = {
                canvas: document.getElementById('kinboni-analytics-canvas'),
                ctx: null,
                activeView: 'visitors', // 'visitors' or 'pageviews'
                chartData: window.kinboniAnalyticsInit || null
            };

            if (self.analyticsState.canvas) {
                self.analyticsState.ctx = self.analyticsState.canvas.getContext('2d');
                self.resizeAnalyticsCanvas();
                self.drawAnalyticsChart();

                $(window).on('resize', function () {
                    self.resizeAnalyticsCanvas();
                    self.drawAnalyticsChart();
                });
            }

            // Chart Tab View Switcher (Unique Visitors vs Page Views)
            $(document).on('click', '#analytics-chart-tabs .chart-tab', function (e) {
                e.preventDefault();
                const view = $(this).data('view');
                if (view && view !== self.analyticsState.activeView) {
                    $('#analytics-chart-tabs .chart-tab').removeClass('is-active');
                    $(this).addClass('is-active');
                    self.analyticsState.activeView = view;
                    self.drawAnalyticsChart();
                }
            });

            // Date Range Switcher Pills via AJAX
            $(document).on('click', '#analytics-range-selector .kinboni-pill', function (e) {
                e.preventDefault();
                const range = $(this).data('range');
                if (!range) return;

                $('#analytics-range-selector .kinboni-pill').removeClass('is-active');
                $(this).addClass('is-active');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_get_analytics_data',
                        nonce: kinboniAdminData.nonce,
                        range: range
                    },
                    beforeSend() {
                        $('#kinboni-analytics-app').css('opacity', '0.7');
                    },
                    success(res) {
                        $('#kinboni-analytics-app').css('opacity', '1');
                        if (res && res.success && res.data) {
                            self.analyticsState.chartData = res.data.chart_data;
                            self.drawAnalyticsChart();
                            // Update metric counters
                            if (res.data.metrics) {
                                const m = res.data.metrics;
                                $('.kinboni-metric-card:eq(0) .metric-number').text(self.formatNumber(m.unique_visitors, 0));
                                $('.kinboni-metric-card:eq(1) .metric-number').text(self.formatNumber(m.page_views, 0));
                                $('.kinboni-metric-card:eq(2) .metric-number').text(self.formatNumber(m.orders_count, 0));
                                $('.kinboni-metric-card:eq(3) .metric-number').text(m.conversion_rate + '%');
                            }
                        }
                    },
                    error() {
                        $('#kinboni-analytics-app').css('opacity', '1');
                    }
                });
            });
        },

        resizeAnalyticsCanvas() {
            const canvas = this.analyticsState.canvas;
            if (!canvas) return;
            const container = canvas.parentElement;
            const width = container.clientWidth || 900;
            const height = container.clientHeight || 280;
            const dpr = window.devicePixelRatio || 1;

            canvas.width = width * dpr;
            canvas.height = height * dpr;
            canvas.style.width = width + 'px';
            canvas.style.height = height + 'px';
            this.analyticsState.ctx.scale(dpr, dpr);
        },

        drawAnalyticsChart() {
            const state = this.analyticsState;
            if (!state || !state.ctx || !state.chartData) return;

            const ctx = state.ctx;
            const container = state.canvas.parentElement;
            const width = container.clientWidth || 900;
            const height = container.clientHeight || 280;

            ctx.clearRect(0, 0, width, height);

            const isVisitors = state.activeView === 'visitors';
            const isConversions = state.activeView === 'conversions';
            const labels = state.chartData.labels || [];
            let values = [];
            if (isVisitors) {
                values = state.chartData.visitors || [];
            } else if (isConversions) {
                values = state.chartData.conversions || [];
            } else {
                values = state.chartData.pageviews || [];
            }

            if (!values.length || !labels.length) {
                ctx.fillStyle = '#78716C';
                ctx.font = '13px sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('No visitor interactions recorded for this date range.', width / 2, height / 2);
                return;
            }

            const paddingLeft = 50;
            const paddingRight = 20;
            const paddingTop = 25;
            const paddingBottom = 35;

            const chartW = width - paddingLeft - paddingRight;
            const chartH = height - paddingTop - paddingBottom;

            let maxVal = Math.max(...values, 1);
            if (isConversions) {
                maxVal = Math.max(10, Math.ceil(maxVal * 1.25));
            } else {
                maxVal = Math.ceil(maxVal * 1.2); // Headroom
            }

            // Draw Grid Lines & Labels
            const gridLines = 4;
            ctx.strokeStyle = '#E8E6DF';
            ctx.fillStyle = '#78716C';
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'right';
            ctx.lineWidth = 1;

            for (let i = 0; i <= gridLines; i++) {
                const y = paddingTop + (chartH / gridLines) * i;
                const val = Math.round(maxVal - (maxVal / gridLines) * i);
                ctx.beginPath();
                ctx.moveTo(paddingLeft, y);
                ctx.lineTo(width - paddingRight, y);
                ctx.stroke();

                const labelStr = isConversions ? (val + '%') : val.toLocaleString();
                ctx.fillText(labelStr, paddingLeft - 8, y + 4);
            }

            const stepX = chartW / (values.length > 1 ? (values.length - 1) : 1);
            const points = [];

            for (let i = 0; i < values.length; i++) {
                const x = paddingLeft + i * stepX;
                const y = paddingTop + chartH - (values[i] / maxVal) * chartH;
                points.push({ x, y, val: values[i], label: labels[i] });
            }

            // Draw Gradient Area
            const gradient = ctx.createLinearGradient(0, paddingTop, 0, height - paddingBottom);
            if (isVisitors) {
                gradient.addColorStop(0, 'rgba(212, 175, 55, 0.25)'); // Gold
                gradient.addColorStop(1, 'rgba(212, 175, 55, 0.01)');
            } else if (isConversions) {
                gradient.addColorStop(0, 'rgba(184, 150, 40, 0.28)'); // Bronze Gold
                gradient.addColorStop(1, 'rgba(184, 150, 40, 0.01)');
            } else {
                gradient.addColorStop(0, 'rgba(5, 150, 105, 0.25)'); // Emerald Green
                gradient.addColorStop(1, 'rgba(5, 150, 105, 0.01)');
            }

            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            for (let i = 0; i < points.length - 1; i++) {
                const xc = (points[i].x + points[i + 1].x) / 2;
                const yc = (points[i].y + points[i + 1].y) / 2;
                ctx.quadraticCurveTo(points[i].x, points[i].y, xc, yc);
            }
            const last = points[points.length - 1];
            ctx.lineTo(last.x, last.y);
            ctx.lineTo(last.x, height - paddingBottom);
            ctx.lineTo(points[0].x, height - paddingBottom);
            ctx.closePath();
            ctx.fillStyle = gradient;
            ctx.fill();

            // Draw Stroke Curve
            ctx.beginPath();
            ctx.strokeStyle = isVisitors ? '#D4AF37' : (isConversions ? '#B89628' : '#059669');
            ctx.lineWidth = 2.5;
            ctx.moveTo(points[0].x, points[0].y);
            for (let i = 0; i < points.length - 1; i++) {
                const xc = (points[i].x + points[i + 1].x) / 2;
                const yc = (points[i].y + points[i + 1].y) / 2;
                ctx.quadraticCurveTo(points[i].x, points[i].y, xc, yc);
            }
            ctx.lineTo(last.x, last.y);
            ctx.stroke();

            // Draw Points & X-Axis Labels
            ctx.fillStyle = '#78716C';
            ctx.textAlign = 'center';
            ctx.font = '10px sans-serif';

            const labelStride = Math.max(1, Math.ceil(points.length / 10));

            for (let i = 0; i < points.length; i++) {
                const pt = points[i];

                // Point dot
                ctx.beginPath();
                ctx.arc(pt.x, pt.y, 3, 0, Math.PI * 2);
                ctx.fillStyle = isVisitors ? '#D4AF37' : '#059669';
                ctx.fill();
                ctx.strokeStyle = '#FFFFFF';
                ctx.lineWidth = 1.5;
                ctx.stroke();

                // X label
                if (i % labelStride === 0 || i === points.length - 1) {
                    ctx.fillStyle = '#78716C';
                    ctx.fillText(pt.label, pt.x, height - paddingBottom + 16);
                }
            }
        },

        // =====================================================================
        // 7. DELIVERY & LOGISTICS CONTROLLER
        // =====================================================================
        initDelivery() {
            const self = this;

            // Submit Delivery Settings
            $('#form-delivery-settings').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btn-save-delivery');
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Saving...');

                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'kinboni_save_delivery_settings' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Delivery Configuration');
                        if (res && res.success) {
                            alert(res.data.message || 'Delivery rates saved successfully!');
                        } else {
                            alert(res.data ? res.data.message : 'Error saving delivery configuration.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Delivery Configuration');
                        alert('Network communication error.');
                    }
                });
            });

            // Dependent Dropdowns: Division Change
            $('#select-geo-division').on('change', function () {
                const division = $(this).val();
                const $distSelect = $('#select-geo-district');
                const $thanaSelect = $('#select-geo-thana');

                $distSelect.html('<option value="">Loading districts...</option>').prop('disabled', true);
                $thanaSelect.html('<option value="">Select District First</option>').prop('disabled', true);
                $('#geo-preview-box').hide();

                if (!division) {
                    $distSelect.html('<option value="">Select Division First</option>');
                    return;
                }

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_get_geo_districts',
                        nonce: kinboniAdminData.nonce,
                        division: division
                    },
                    success(res) {
                        if (res && res.success && res.data) {
                            let options = '<option value="">Select District...</option>';
                            res.data.forEach(function (d) {
                                options += `<option value="${d}">${d}</option>`;
                            });
                            $distSelect.html(options).prop('disabled', false);
                        }
                    }
                });
            });

            // Dependent Dropdowns: District Change
            $('#select-geo-district').on('change', function () {
                const district = $(this).val();
                const $thanaSelect = $('#select-geo-thana');

                $thanaSelect.html('<option value="">Loading thanas...</option>').prop('disabled', true);
                $('#geo-preview-box').hide();

                if (!district) {
                    $thanaSelect.html('<option value="">Select District First</option>');
                    return;
                }

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_get_geo_thanas',
                        nonce: kinboniAdminData.nonce,
                        district: district
                    },
                    success(res) {
                        if (res && res.success && res.data) {
                            let options = '<option value="">Select Thana / Upazila...</option>';
                            res.data.forEach(function (t) {
                                options += `<option value="${t}">${t}</option>`;
                            });
                            $thanaSelect.html(options).prop('disabled', false);
                        }
                    }
                });
            });

            // Dependent Dropdowns: Thana Change (Update Preview)
            $('#select-geo-thana').on('change', function () {
                const div = $('#select-geo-division').val();
                const dist = $('#select-geo-district').val();
                const thana = $(this).val();

                if (div && dist && thana) {
                    const formatted = `${thana}, ${dist}, ${div} Division, Bangladesh`;
                    $('#geo-preview-text').text(formatted);
                    $('#geo-preview-box').fadeIn(200);
                } else {
                    $('#geo-preview-box').hide();
                }
            });

            // Live Simulator / Rate Calculator
            $('#btn-calc-rate, #sim-subtotal, #sim-zone').on('change click', function () {
                const subtotal = $('#sim-subtotal').val();
                const zone = $('#sim-zone').val();

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_calc_delivery_rate',
                        nonce: kinboniAdminData.nonce,
                        subtotal: subtotal,
                        zone: zone
                    },
                    success(res) {
                        if (res && res.success && res.data) {
                            const d = res.data;
                            $('#sim-charge-val').html(d.charge_html).css('color', d.is_free ? '#059669' : '#121214');
                            $('#sim-total-val').text(d.total_html);
                            $('#sim-gap-text').text(d.threshold_gap_html);
                        }
                    }
                });
            });
        },

        // =====================================================================
        // 8. NOTIFICATIONS & ALERTS CONTROLLER
        // =====================================================================
        initNotifications() {
            const self = this;

            // Submit Notification Preferences
            $('#form-notification-settings').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btn-save-notifications');
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Saving...');

                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'kinboni_save_notification_settings' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Notification Preferences');
                        if (res && res.success) {
                            alert(res.data.message || 'Notification preferences saved!');
                        } else {
                            alert(res.data ? res.data.message : 'Error saving settings.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Notification Preferences');
                        alert('Network communication error.');
                    }
                });
            });

            // Test Audio Sound
            $('#btn-test-chime').on('click', function (e) {
                e.preventDefault();
                self.playOrderChime();
            });

            // Send Test Email
            $('#btn-test-email').on('click', function (e) {
                e.preventDefault();
                const $btn = $(this);
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Sending...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_send_test_notification_email',
                        nonce: kinboniAdminData.nonce
                    },
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Send Test Email');
                        if (res && res.success) {
                            alert(res.data.message || 'Test email dispatched!');
                        } else {
                            alert(res.data ? res.data.message : 'Error sending test email.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Send Test Email');
                        alert('Network communication error.');
                    }
                });
            });

            // Send Test SMS
            $('#btn-send-test-sms').on('click', function (e) {
                e.preventDefault();
                const phone = $('#test-sms-phone').val().trim();
                if (!phone) {
                    alert('Please enter a test mobile number (e.g. 01711000000).');
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Sending SMS...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_send_test_sms',
                        nonce: kinboniAdminData.nonce,
                        phone: phone
                    },
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-smartphone"></span> Send Test SMS');
                        if (res && res.success) {
                            alert(res.data.message || 'SMS dispatched successfully!');
                        } else {
                            alert(res.data ? res.data.message : 'Error sending SMS.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-smartphone"></span> Send Test SMS');
                        alert('Server network error sending SMS.');
                    }
                });
            });
        },

        // =====================================================================
        // 9. STOREFRONT CONTROLLER
        // =====================================================================
        initStorefront() {
            const self = this;

            // Save Storefront Settings
            $('#form-storefront-settings').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btn-save-storefront');
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Saving...');

                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'kinboni_save_storefront_settings' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Storefront Configuration');
                        if (res && res.success) {
                            alert(res.data.message || 'Storefront configurations saved!');
                        } else {
                            alert(res.data ? res.data.message : 'Error saving storefront settings.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Storefront Configuration');
                        alert('Network communication error saving storefront.');
                    }
                });
            });
        },

        // =====================================================================
        // 10. REPORTS & FINANCIAL P&L CONTROLLER
        // =====================================================================
        initReports() {
            const self = this;

            // Save Report Settings
            $('#form-report-settings').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btn-save-report-settings');
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Saving...');

                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'kinboni_save_report_settings' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Reporting Preferences');
                        if (res && res.success) {
                            alert(res.data.message || 'Report preferences saved!');
                        } else {
                            alert(res.data ? res.data.message : 'Error saving report settings.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save Reporting Preferences');
                        alert('Network communication error saving report settings.');
                    }
                });
            });

            // Instant Email Dispatch
            $('#btn-send-instant-report').on('click', function (e) {
                e.preventDefault();
                const $btn = $(this);
                const period = $btn.data('period') || 'weekly';

                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Sending...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_send_instant_report',
                        nonce: kinboniAdminData.nonce,
                        period: period
                    },
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Email Report');
                        if (res && res.success) {
                            alert(res.data.message || 'Executive report dispatched!');
                        } else {
                            alert(res.data ? res.data.message : 'Error sending report.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Email Report');
                        alert('Network communication error sending report.');
                    }
                });
            });
        },

        // =====================================================================
        // 11. SYSTEM & SECURITY CONTROLLER
        // =====================================================================
        initSystem() {
            const self = this;

            // Import Backup Form (FormData)
            $('#form-import-backup').on('submit', function (e) {
                e.preventDefault();
                const fileInput = document.getElementById('input-backup-file');
                if (!fileInput || !fileInput.files.length) {
                    alert('Please select a .json backup file to import.');
                    return;
                }

                if (!confirm('Restoring settings will overwrite existing KINBONI Pro configurations. Proceed?')) return;

                const $btn = $('#btn-submit-import-backup');
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Validating & Restoring...');

                const formData = new FormData(this);
                formData.append('action', 'kinboni_import_system_backup');
                formData.append('nonce', kinboniAdminData.nonce);

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> Validate & Restore Settings');
                        if (res && res.success) {
                            alert(res.data.message || 'Settings restored successfully!');
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error restoring backup.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> Validate & Restore Settings');
                        alert('Network communication error restoring backup.');
                    }
                });
            });

            // Factory Default Reset
            $('#btn-restore-defaults').on('click', function () {
                if (!confirm('DANGER: This will reset all KINBONI Pro configurations to factory defaults. Your WooCommerce orders and products will remain untouched. Continue?')) {
                    return;
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Resetting...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_restore_default_settings',
                        nonce: kinboniAdminData.nonce
                    },
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-image-rotate"></span> Reset to Factory Defaults');
                        if (res && res.success) {
                            alert(res.data.message || 'Factory default settings restored!');
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error restoring default settings.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-image-rotate"></span> Reset to Factory Defaults');
                        alert('Network communication error restoring default settings.');
                    }
                });
            });

            // Clear Activity Logs
            $('#btn-clear-activity-logs').on('click', function () {
                if (!confirm('Are you sure you want to permanently clear all activity logs?')) return;
                const $btn = $(this);
                $btn.prop('disabled', true);

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_clear_activity_logs',
                        nonce: kinboniAdminData.nonce
                    },
                    success(res) {
                        $btn.prop('disabled', false);
                        if (res && res.success) {
                            alert(res.data.message || 'Logs cleared!');
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error clearing logs.');
                        }
                    }
                });
            });

            // Save Staff Permissions
            $('#form-staff-permissions').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btn-save-staff-permissions');
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Saving...');

                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'kinboni_save_staff_permissions' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Update Staff Capabilities');
                        if (res && res.success) {
                            alert(res.data.message || 'Permissions updated!');
                        } else {
                            alert(res.data ? res.data.message : 'Error updating permissions.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Update Staff Capabilities');
                        alert('Network communication error saving permissions.');
                    }
                });
            });

            // Save System Settings
            $('#form-system-settings').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btn-save-system-settings');
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Saving...');

                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'kinboni_save_system_settings' });
                formData.push({ name: 'nonce', value: kinboniAdminData.nonce });

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: formData,
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save System Configuration');
                        if (res && res.success) {
                            alert(res.data ? res.data.message : 'Settings saved successfully!');
                        } else {
                            alert(res.data ? res.data.message : 'Error saving settings.');
                        }
                    },
                    error() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save System Configuration');
                        alert('Network communication error saving system settings.');
                    }
                });
            });

            // Optimize Database Tables
            $('#btn-optimize-db').on('click', function () {
                const $btn = $(this);
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Optimizing...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_optimize_database',
                        nonce: kinboniAdminData.nonce
                    },
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-database"></span> Optimize Tables Now');
                        if (res && res.success) {
                            alert(res.data.message || 'Database tables optimized!');
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error optimizing database.');
                        }
                    }
                });
            });

            // Clear Expired Transients
            $('#btn-clear-transients').on('click', function () {
                const $btn = $(this);
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Clearing...');

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_clear_transients',
                        nonce: kinboniAdminData.nonce
                    },
                    success(res) {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Clear Expired Transients');
                        if (res && res.success) {
                            alert(res.data.message || 'Transients cleared!');
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error clearing transients.');
                        }
                    }
                });
            });

            // Clear Error Log
            $('#btn-clear-error-logs').on('click', function () {
                if (!confirm('Are you sure you want to clear the debug error log?')) return;
                const $btn = $(this);
                $btn.prop('disabled', true);

                $.ajax({
                    url: kinboniAdminData.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'kinboni_clear_error_logs',
                        nonce: kinboniAdminData.nonce
                    },
                    success(res) {
                        $btn.prop('disabled', false);
                        if (res && res.success) {
                            alert(res.data.message || 'Error log cleared!');
                            window.location.reload();
                        } else {
                            alert(res.data ? res.data.message : 'Error clearing error log.');
                        }
                    }
                });
            });
        },

        // =====================================================================
        // 4. LIVE ORDER NOTIFICATION SOUND & BADGE POLLER
        // =====================================================================
        initLiveOrderPoller() {
            const self = this;

            // Check every 45 seconds for new orders (paused when tab is hidden)
            setInterval(function () {
                if (typeof document.hidden !== 'undefined' && document.hidden) {
                    return;
                }
                self.checkNewOrders();
            }, 45000);
        },

        checkNewOrders() {
            const self = this;
            $.ajax({
                url: kinboniAdminData.ajax_url,
                type: 'POST',
                data: {
                    action: 'kinboni_check_new_orders',
                    nonce: kinboniAdminData.nonce
                },
                success(res) {
                    if (res && res.success && res.data && res.data.has_new) {
                        self.playOrderChime();
                        if (res.data.pending_count !== undefined) {
                            $('#stat-pending').text(res.data.pending_count);
                        }
                    }
                }
            });
        },

        playOrderChime() {
            try {
                if (window.kinboniAdminData && kinboniAdminData.order_sound_enabled === false) {
                    return;
                }
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;

                if (!this.state.audioCtx) {
                    this.state.audioCtx = new AudioCtx();
                }

                const ctx = this.state.audioCtx;
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }

                // Two-tone luxury gold bell chime
                const now = ctx.currentTime;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                const tone = (window.kinboniAdminData && kinboniAdminData.sound_tone) || 'gold_bell';
                osc.type = (tone === 'digital_chime') ? 'triangle' : 'sine';
                const startFreq = (tone === 'cash_register') ? 1046.5 : ((tone === 'subtle_ping') ? 659.25 : 880);
                const endFreq   = (tone === 'cash_register') ? 1567.98 : ((tone === 'subtle_ping') ? 987.77 : 1320);
                osc.frequency.setValueAtTime(startFreq, now);
                osc.frequency.exponentialRampToValueAtTime(endFreq, now + 0.15);

                gain.gain.setValueAtTime(0.2, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.8);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.85);
            } catch (e) {
                // Audio autoplay policy
            }
        }
    };

    $(document).ready(function () {
        KinboniAdmin.init();
    });

})(jQuery);

