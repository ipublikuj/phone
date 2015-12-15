/**
 * ipub.phone.js
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Phone!
 * @subpackage     java-script
 * @since          1.0.1
 *
 * @date           14.12.15
 */

/**
 * Client-side script for iPublikuj:Phone!
 *
 * @author        Adam Kadlec (http://www.ipublikuj.eu)
 * @package       iPublikuj:Phone!
 * @version       1.0
 *
 * @param {jQuery} $ (version > 1.7)
 * @param {Window} window
 * @param {Document} document
 * @param {Location} location
 * @param {Navigator} navigator
 */
;(function ($, window, document, location, navigator) {
    /* jshint laxbreak: true, expr: true */
    "use strict";

    var IPub = window.IPub || {};

    IPub.Phone = IPub.Phone || {};

    IPub.Phone.Utils = {};

    /**
     * Phone extension utils definition
     */
    IPub.Phone.Utils = function ()
    {

    };

    IPub.Phone.Utils.prototype =
    {
        /**
         * Tests whether a phone number matches a valid pattern. Note this doesn't
         * verify the number is actually in use, which is impossible to tell by just
         * looking at a number itself
         *
         * @param {String} phone
         * @param {String} country
         *
         * @returns {bool}
         */
        isValidNumber: function (phone, country)
        {
            try {
                phone = helpers.cleanPhone(phone);
                country = country.toUpperCase();

                var phoneUtil = i18n.phonenumbers.PhoneNumberUtil.getInstance();
                var number = phoneUtil.parseAndKeepRawInput(phone, country);

                return phoneUtil.isValidNumber(number);

            } catch (e) {
                return false;
            }
        },

        /**
         * Return the phone number in international format
         *
         * @param {String} phone 2 digit country code
         * @param {String} country number to format
         *
         * @returns {String}
         */
        formatInternational: function (phone, country)
        {
            try {
                phone = helpers.cleanPhone(phone);
                country = country.toUpperCase();

                var formatter = new i18n.phonenumbers.AsYouTypeFormatter(country);
                var output = new goog.string.StringBuffer();

                for (var i = 0; i < phone.length; ++i) {
                    var inputChar = phone.charAt(i);
                    output = (formatter.inputDigit(inputChar));
                }

                return output.toString();

            } catch (e) {
                return phone;
            }
        },

        /**
         * Return the phone number in the format local to the user
         *
         * @param {String} phone 2 digit country code
         * @param {String} country number to format
         *
         * @returns {String}
         */
        formatLocal: function (phone, country)
        {
            try {
                phone = helpers.cleanPhone(phone);
                country = country.toUpperCase();

                var phoneUtil = i18n.phonenumbers.PhoneNumberUtil.getInstance();
                var number = phoneUtil.parseAndKeepRawInput(phone, country);

                if (phoneUtil.isValidNumberForRegion(number, country)) {
                    var PNF = i18n.phonenumbers.PhoneNumberFormat;
                    var output = phoneUtil.format(number, PNF.NATIONAL);

                    return output.toString();

                } else {
                    return this.formatInternational(phone, country);
                }

            } catch (e) {
                return this.formatInternational(phone, country);
            }
        },

        /**
         * Return the phone number in e164 format
         *
         * @param {String} phone 2 digit country code
         * @param {String} country number to format
         *
         * @returns {String}
         */
        formatE164: function (phone, country)
        {
            try {
                phone = helpers.cleanPhone(phone);
                country = country.toUpperCase();

                var phoneUtil = i18n.phonenumbers.PhoneNumberUtil.getInstance();
                var number = phoneUtil.parseAndKeepRawInput(phone, country);
                var PNF = i18n.phonenumbers.PhoneNumberFormat;
                var output = phoneUtil.format(number, PNF.E164);

                return output.toString();

            } catch (e) {
                return phone
            }
        }
    };

    IPub.Phone.Validator = {};

    /**
     * Phone extension validator definition
     */
    IPub.Phone.Validator = function ($element, options)
    {
        this.utils = new IPub.Phone.Utils;

        this.$element = $element;

        this.options = options;
    };

    IPub.Phone.Validator.prototype =
    {
        validate: function ()
        {
            // Get field value
            var value = this.$element.value;
            // Get list of allowed countries from params
            var allowedCountries = this.determineCountries(this.options);

            var result = false;

            for (var i in allowedCountries) {
                if (this.utils.isValidNumber(value, allowedCountries[i])) {
                    result = true;
                    break;
                }
            }

            return result;
        },

        /**
         * Determine countries from params
         *
         * @param {Array} params
         *
         * @returns {Array}
         */
        determineCountries: function(params)
        {
            var countries = [];

            // Check if we need to parse for automatic detection
            if (helpers.inArray('AUTO', params)) {
                countries.push('ZZ');

            // Else use the supplied parameters
            } else {
                for (var i in params) {
                    var countryCode = params[i];

                    if (typeof countryCode == 'string') {
                        countryCode = countryCode.toUpperCase()
                    }

                    if (countryCode.length === 2 && typeof countryCode === 'string' && countryCode != 'ZZ') {
                        countries.push(countryCode);
                    }
                }
            }

            return countries;
        }
    };

    /**
     * IPub Phone helpers
     */

    var helpers =
    {
        /**
         * @param {String} needle
         * @param {Array} haystack
         *
         * @returns {boolean}
         */
        inArray: function (needle, haystack) {
            var result = false;

            for (var i in haystack) {
                if (haystack[i] === needle) {
                    result = true;
                    break;
                }
            }

            return result;
        },

        /**
         * Remove any non numeric characters from the phone number but leave any plus sign at the beginning
         *
         * @param {String} phone
         *
         * @returns {String}
         */
        cleanPhone: function (phone) {
            phone = phone.replace(/[^\d\+]/g,'');

            if (phone.substr(0, 1) == "+") {
                phone = "+" + phone.replace(/[^\d]/g,'');

            } else {
                phone = phone.replace(/[^\d]/g,'');
            }

            return phone;
        }
    };

    // Assign plugin data to DOM
    window.IPub = IPub;

    return IPub;

})(jQuery, window, document, location, navigator);

/**
 * Nette.forms custom validator
 *
 * @param {obj} elem
 * @param {Array} arg
 * @param {string} value
 */
Nette.validators.IPubPhoneFormsPhoneValidator_validatePhone = function (elem, arg, value) {
    return new IPub.Phone.Validator(elem, arg).validate();
};
