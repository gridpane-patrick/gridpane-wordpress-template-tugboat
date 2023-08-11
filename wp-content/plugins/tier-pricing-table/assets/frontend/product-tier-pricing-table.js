jQuery(document).ready(function ($) {
    $.widget.bridge('uiTooltip', $.ui.tooltip);

    var TiredPriceTable = function () {

        this.settings = tieredPricingTable.settings;
        this.currencyOptions = tieredPricingTable.currency_options;
        this.productType = tieredPricingTable.product_type;
        this.tieredPriceTableSelector = '[data-price-rules-table]';

        this.init = function () {

            this.$productQuantityField = $('form.cart').find('[name=quantity]');

            if (this.settings !== undefined) {

                if (this.productType === 'variable' || this.productType === 'variable-subscription') {
                    $(".single_variation_wrap").on("show_variation", this.loadVariationTable.bind(this));

                    $(document).on('reset_data', function () {
                        $('[data-variation-price-rules-table]').html('');
                    });
                }

                if (this.settings.display_type === 'tooltip') {
                    this.initTooltip();
                }

                if (this.settings.clickable_table_rows === 'yes') {
                    $(document).on('click', this.tieredPriceTableSelector + ' tr', this.setQuantityByClick.bind(this));
                }

                this.$productQuantityField.on('change input', this.setPriceByQuantity.bind(this));
                this.$productQuantityField.trigger('change');
            }
        };

        this.setQuantityByClick = function (e) {
            var row = $(e.target).closest('tr');
            if (row) {
                var qty = parseInt(row.data('price-rules-amount'));

                if (qty > 0) {
                    this.$productQuantityField.val(qty);
                }
            }

            this.$productQuantityField.trigger('change');
        };

        this.initTooltip = function () {
            var self = this;

            if (this.settings.tooltip_border === 'yes') {
                $(self.tieredPriceTableSelector).css('border', '2px solid ' + this.settings.selected_quantity_color);
            }

            $(document).uiTooltip({
                items: '.price-table-tooltip-icon',
                tooltipClass: "price-table-tooltip",
                content: function () {
                    return $(self.tieredPriceTableSelector);
                },
                hide: {
                    effect: "fade",
                },
                position: {
                    my: "center bottom-40",
                    at: "center bottom",
                    using: function (position) {
                        $(this).css(position);
                    }
                },
                close: function (e, tooltip) {
                    tooltip.tooltip.innerHTML = '';
                }
            });
        };

        this.setPriceByQuantity = function () {
            $('.price-rule-active').removeClass('price-rule-active');

            if ($(this.tieredPriceTableSelector).length > 0) {

                var priceRules = JSON.parse($(this.tieredPriceTableSelector).attr('data-price-rules'));

                var quantity = parseInt(this.$productQuantityField.val());
                var _keys = [];

                for (var k in priceRules) {
                    if (priceRules.hasOwnProperty(k)) {
                        _keys.push(parseInt(k));
                    }
                }

                _keys = _keys.sort(function (a, b) {
                    return a > b
                }).reverse();

                for (var i = 0; i < _keys.length; i++) {
                    var amount = parseInt(_keys[i]);
                    var foundPrice = false;
                    var priceHtml;
                    var price;

                    if (quantity >= amount) {
                        price = parseFloat($('[data-price-rules-amount="' + amount + '"]').data('price-rules-price'));
                        priceHtml = $('[data-price-rules-amount=' + amount + ']').find('[data-price-rules-formated-price]').html();

                        this.changePriceHtml(priceHtml);

                        foundPrice = true;

                        $(document).trigger('tiered_price_update', {price, quantity, __instance: this});

                        break;
                    }
                }

                amount = foundPrice ? amount : this.getTableMinimum();

                var currentPrice = $('[data-price-rules-amount="' + amount + '"]').data('price-rules-price');

                if (this.settings.show_total_price === 'yes') {

                    var formatedPrice = this.formatPrice(quantity * currentPrice);

                    this.changePriceHtml(formatedPrice, true);
                }


                if (!foundPrice) {

                    if (this.settings.show_total_price !== 'yes') {
                        this.changePriceHtml(this.getDefaultPriceHtml());
                    }

                    $('[data-price-rules-amount=' + this.getTableMinimum() + ']').addClass('price-rule-active');

                    price = parseFloat($('[data-price-rules-amount="' + this.getTableMinimum() + '"]').data('price-rules-price'));

                    $(document).trigger('tiered_price_update', {price, quantity, __instance: this});

                    return;

                }

                $('[data-price-rules-amount="' + amount + '"]').addClass('price-rule-active');

            }
        };

        this.formatPrice = function (price, includeSuffix = true) {
            var price = this.formatNumber(price, this.currencyOptions.decimals, this.currencyOptions.decimal_separator, this.currencyOptions.thousand_separator);
            var currency = '<span class="woocommerce-Price-currencySymbol">' + this.currencyOptions.currency_symbol + '</span>';
            var priceSuffixPart = includeSuffix ? ' %3$s ' : '';

            var template = '<span class="woocommerce-Price-amount amount">' + this.currencyOptions.price_format + priceSuffixPart + '</span>';

            return $('<textarea />').html(template.replace('%2$s', price).replace('%1$s', currency).replace('%3$s', this.getPriceSuffix())).text();
        };

        this.getPriceSuffix = function () {
            // Allow external plugins modifying suffix
            if (typeof tieredPriceTableGetProductPriceSuffix !== "undefined") {
                return tieredPriceTableGetProductPriceSuffix();
            }

            return this.currencyOptions.price_suffix;
        }

        this.formatNumber = function (number, decimals, dec_point, thousands_sep) {

            var i, j, kw, kd, km;

            if (isNaN(decimals = Math.abs(decimals))) {
                decimals = this.currencyOptions.decimals;
            }
            if (dec_point == undefined) {
                dec_point = this.currencyOptions.decimal_separator;
            }
            if (thousands_sep == undefined) {
                thousands_sep = this.currencyOptions.thousand_separator;
            }

            i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

            if ((j = i.length) > 3) {
                j = j % 3;
            } else {
                j = 0;
            }

            km = (j ? i.substr(0, j) + thousands_sep : "");
            kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);

            kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");
            return km + kw + kd;
        };

        this.getDefaultPriceHtml = function () {
            return $('[data-price-rules-amount=' + this.getTableMinimum() + ']').find('[data-price-rules-formated-price]').html();
        };

        this.changePriceHtml = function (priceHtml, wipeDiscount) {

            wipeDiscount = wipeDiscount === undefined ? false : wipeDiscount;

            var priceContainer = $('form.cart').closest('.summary').find('[data-tiered-price-wrapper]');

            if (priceContainer.length < 1 && typeof tieredPriceTableGetProductPriceContainer != "undefined") {
                priceContainer = tieredPriceTableGetProductPriceContainer();
            }

            if (wipeDiscount) {
                priceContainer.html(priceHtml);
            }

            if (priceContainer.children('ins').length > 0) {
                priceContainer.find('ins').html(priceHtml);
            } else {
                priceContainer.find('span:first').html(priceHtml);
            }
        };

        this.loadVariationTable = function (event, variation) {

            $.post(document.location.origin + document.location.pathname + '?wc-ajax=get_price_table', {
                variation_id: variation['variation_id'],
                nonce: tieredPricingTable.load_table_nonce
            }, (function (response) {
                $('.price-rules-table').remove();
                $('[data-variation-price-rules-table]').html(response);

                if (!response) {
                    this.changePriceHtml(this.formatPrice(variation.display_price), true);
                    this.$productQuantityField.val(variation.min_qty);
                } else {
                    this.$productQuantityField.trigger('change');
                }

                if (this.settings.display_type === 'tooltip' && this.settings.tooltip_border === 'yes') {
                    $(this.tieredPriceTableSelector).css('border', '2px solid ' + this.settings.selected_quantity_color);
                }

            }).bind(this));
        };

        this.getTableMinimum = function () {
            var min = $(this.tieredPriceTableSelector).data('minimum');

            min = min ? parseInt(min) : 1;

            return min;
        };

        this.getProductName = function () {
            return $(this.tieredPriceTableSelector).data('product-name');
        }
    };

    document.tieredPriceTable = new TiredPriceTable();

    document.tieredPriceTable.init();
});

/**
 * SUMMARY TABLE
 */
(function ($) {

    $(document).on('tiered_price_update', function (event, data) {
        $('[data-tier-pricing-table-summary]').removeClass('tier-pricing-summary-table--hidden');

        $('[data-tier-pricing-table-summary-product-qty]').text(data.__instance.formatNumber(data.quantity, 0));
        $('[data-tier-pricing-table-summary-product-price]').html(data.__instance.formatPrice(data.price, false));
        $('[data-tier-pricing-table-summary-total]').html(data.__instance.formatPrice(data.price * data.quantity, false));
        $('[data-tier-pricing-table-summary-product-name]').html(data.__instance.getProductName());
    });

    $(document).on('reset_data', function () {
        $('[data-tier-pricing-table-summary]').addClass('tier-pricing-summary-table--hidden');
    });

    $(document).on('found_variation', function () {
        $('[data-tier-pricing-table-summary]').addClass('tier-pricing-summary-table--hidden');
    });

})(jQuery);

/**
 * MIN QUANTITIES
 */

(function ($) {

    $(document).on('found_variation', function (event, variation) {
        if (typeof variation.qty_value !== "undefined" && variation.qty_value > 1) {
            $('form.cart').find('[name=quantity]').val(variation.qty_value)
        }
    });

})(jQuery);
