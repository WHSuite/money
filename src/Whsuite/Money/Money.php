<?php
namespace Whsuite\Money;
/**
 * Money Utility
 *
 * The Money Utility provides an easy way to format currency based on its
 * currency code information stored within WHSuite. So for example if you give
 * the currency formatter the value '15.9500' and want it to format into GBP, you'd
 * get '£15.95' back as the formatted value.
 */
class Money
{
    public $currencies = array();
    public $default_currency = 'USD';

    /**
     * Init Currency Utility
     * @param  object $currencies       Object of the currencies from the database
     * @param  string $default_currency An optional default currency code
     * @return null
     */
    public function init($currencies, $default_currency = null)
    {
        // the $currencies value provided here will be direct from the db, so lets
        // format it nicely

        foreach($currencies as $currency)
        {
            $this->currencies[$currency->code] = $currency;
        }

        // If another default currency has been provided, override the one we've
        // set as default
        if($default_currency)
        {
            $this->default_currency = $default_currency;
        }
    }

    /**
     * Format Currency
     * @param  decimal $value         The currency value to be formatted
     * @param  string $currency_code Optionally the currency code to format with
     * @param  boolean $hide_symbol Optionally hide the currency symbols
     * @param  boolean $show_code Optionally show the currency code at the end
     * @return string                The formatted currency string
     */
    public function format($value, $currency_code = null, $hide_symbols = false, $show_code = false)
    {
        // First things first, remove any formatting that may have already been applied to the value.
        $value = floatval(preg_replace('/[^\d.]/', '', $value));
        
        if (is_numeric($currency_code)) {
            // We've got the currency id instead of code.
            $currency = \Currency::find($currency_code);

            // If the currency can't be loaded, just return the value as-is as it likely means
            // that the currency code was forcefully deleted.
            if (empty($currency)) {
                return $value;
            }

            $currency_code = $currency->code;
        } else {

            if ($currency_code && $this->currencies[$currency_code]) {
                $currency = $this->currencies[$currency_code];
            } elseif (isset($this->currencies[$this->default_currency])) {
                $currency = $this->currencies[$this->default_currency];
            } else {
                return $value; // No currency codes found so just return the value as-is
            }
        }

        if ($hide_symbols) {
            $return =  number_format($value, $currency->decimals, $currency->decimal_point, $currency->thousand_separator);
        } else {
            // The output format will be {prefix}{value}{suffix} with the value having
            // been formatted by the number of decimals, decimal point character and
            // thousand separator character
            $return = $currency->prefix.number_format($value, $currency->decimals, $currency->decimal_point, $currency->thousand_separator).$currency->suffix;
        }

        if ($show_code) {
            $return .= ' '.$currency->code;
        }

        return $return;
    }
}
