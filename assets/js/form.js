/*!
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2015
 * @version 1.6.1
 *
 * Client validation extension for the yii2-builder extension
 * 
 * Author: Kartik Visweswaran
 * Copyright: 2014 - 2015, Kartik Visweswaran, Krajee.com
 * For more JQuery plugins visit http://plugins.krajee.com
 * For more Yii related demos visit http://demos.krajee.com
 */
(function ($) {
    "use strict";

    var KvFormBuilder = function (element, options) {
        var self = this;
        self.$element = $(element);
        self.options = options;
        self.init();
    };

    KvFormBuilder.prototype = {
        constructor: KvFormBuilder,
        init: function () {
            var self = this, $form = self.$element.closest('form');
            self.$target = self.$element.find('.kv-nested-attribute-block');
            $form.on('reset.yiiActiveForm', function () {
                setTimeout(function () {
                    self.$target.removeClass('has-success has-error');
                }, 100);
            });
            $form.on('afterValidateAttribute', function (event, attribute, messages) {
                self.validate(attribute, messages);
            });
        },
        validate: function (attribute, messages) {
            var self = this;
            if (self.$target.length === 0) {
                return;
            }
            self.$target.each(function () {
                var hasError = false, hasSuccess = false;
                var $el = $(this);
                $el.find('input').each(function () {
                    var id = $(this).attr('id');
                    if (id === attribute.id) {
                        if (messages.length > 0) {
                            hasError = true;
                            hasSuccess = false;
                        } else {
                            if (hasError === false && !attribute.cancelled && (attribute.status === 2 || attribute.status === 3)) {
                                hasSuccess = true;
                            }
                        }
                    }
                });
                if (hasError) {
                    $el.removeClass('has-success has-error').addClass('has-error');
                    return;
                }
                if (hasSuccess) {
                    $el.removeClass('has-success has-error').addClass('has-success');
                }
            });
        }
    };

    $.fn.kvFormBuilder = function (option) {
        var args = Array.apply(null, arguments);
        args.shift();
        return this.each(function () {
            var $this = $(this),
                data = $this.data('kvFormBuilder'),
                options = typeof option === 'object' && option;

            if (!data) {
                data = new KvFormBuilder(this, $.extend({}, options, $(this).data()));
                $this.data('kvFormBuilder', data);
            }

            if (typeof option === 'string') {
                data[option].apply(data, args);
            }
        });
    };

    $.fn.kvFormBuilder.Constructor = KvFormBuilder;

}(window.jQuery));