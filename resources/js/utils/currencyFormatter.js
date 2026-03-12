/**
 * Global Currency Formatter Utility
 * 
 * This utility provides functions to format currency values based on hotel configuration.
 * Use this for all currency formatting across the application.
 * 
 * Example usage:
 * import { formatCurrency, getCurrencySymbol } from '@/utils/currencyFormatter';
 * 
 * const price = formatCurrency(100.50); // Uses hotel currency
 * const symbol = getCurrencySymbol(); // Returns current currency symbol
 */

import { getCurrencyByCode } from './currencies';

// Default currency (fallback)
const DEFAULT_CURRENCY = 'USD';

// Cache for hotel currency settings
let hotelCurrency = {
    code: DEFAULT_CURRENCY,
    symbol: '$',
    name: 'US Dollar'
};

/**
 * Initialize currency settings from hotel configuration
 * This should be called when hotel settings are loaded
 * 
 * @param {string} currencyCode - Currency code (e.g., 'USD', 'EUR', 'PKR')
 */
export function setHotelCurrency(currencyCode) {
    const currency = getCurrencyByCode(currencyCode || DEFAULT_CURRENCY);
    hotelCurrency = {
        code: currency.code,
        symbol: currency.symbol,
        name: currency.name
    };
}

/**
 * Get the current hotel currency symbol
 * 
 * @returns {string} Currency symbol (e.g., '$', '€', 'Rs')
 */
export function getCurrencySymbol() {
    return hotelCurrency.symbol;
}

/**
 * Get the current hotel currency code
 * 
 * @returns {string} Currency code (e.g., 'USD', 'EUR', 'PKR')
 */
export function getCurrencyCode() {
    return hotelCurrency.code;
}

/**
 * Get the current hotel currency name
 * 
 * @returns {string} Currency name (e.g., 'US Dollar', 'Euro', 'Pakistani Rupee')
 */
export function getCurrencyName() {
    return hotelCurrency.name;
}

/**
 * Format a number as currency using hotel currency settings
 * 
 * @param {number} amount - The amount to format
 * @param {Object} options - Formatting options
 * @param {number} options.decimals - Number of decimal places (default: 2)
 * @param {boolean} options.showSymbol - Whether to show currency symbol (default: true)
 * @param {string} options.symbolPosition - 'before' or 'after' (default: 'before')
 * @returns {string} Formatted currency string
 */
export function formatCurrency(amount, options = {}) {
    const {
        decimals = 2,
        showSymbol = true,
        symbolPosition = 'before'
    } = options;

    const formattedAmount = parseFloat(amount || 0).toFixed(decimals);
    
    if (!showSymbol) {
        return formattedAmount;
    }

    if (symbolPosition === 'after') {
        return `${formattedAmount} ${hotelCurrency.symbol}`;
    }

    return `${hotelCurrency.symbol}${formattedAmount}`;
}

/**
 * Format a number with thousand separators
 * 
 * @param {number} amount - The amount to format
 * @param {number} decimals - Number of decimal places (default: 2)
 * @returns {string} Formatted number string
 */
export function formatNumber(amount, decimals = 2) {
    return parseFloat(amount || 0).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

/**
 * Format currency with thousand separators
 * 
 * @param {number} amount - The amount to format
 * @param {Object} options - Formatting options
 * @returns {string} Formatted currency string with thousand separators
 */
export function formatCurrencyWithSeparators(amount, options = {}) {
    const {
        decimals = 2,
        showSymbol = true,
        symbolPosition = 'before'
    } = options;

    const formattedAmount = formatNumber(amount, decimals);
    
    if (!showSymbol) {
        return formattedAmount;
    }

    if (symbolPosition === 'after') {
        return `${formattedAmount} ${hotelCurrency.symbol}`;
    }

    return `${hotelCurrency.symbol}${formattedAmount}`;
}
