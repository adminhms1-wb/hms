/**
 * Exchange Rates Utility
 * 
 * Provides currency conversion functionality.
 * Exchange rates are relative to USD (base currency).
 * 
 * Note: These are approximate rates. For production, consider using a live API.
 */

// Exchange rates relative to USD (1 USD = X of other currency)
export const exchangeRates = {
    'USD': 1.0,      // Base currency
    'EUR': 0.92,
    'GBP': 0.79,
    'JPY': 149.50,
    'AUD': 1.52,
    'CAD': 1.35,
    'CHF': 0.88,
    'CNY': 7.24,
    'INR': 83.12,
    'PKR': 278.50,
    'BRL': 4.95,
    'ZAR': 18.75,
    'MXN': 17.05,
    'SGD': 1.34,
    'HKD': 7.82,
    'NOK': 10.65,
    'SEK': 10.85,
    'DKK': 6.87,
    'PLN': 4.02,
    'RUB': 91.50,
    'TRY': 32.05,
    'THB': 35.85,
    'IDR': 15750.00,
    'MYR': 4.68,
    'PHP': 55.85,
    'VND': 24350.00,
    'KRW': 1320.00,
    'TWD': 31.45,
    'SAR': 3.75,
    'AED': 3.67,
    'ILS': 3.65,
    'EGP': 30.90,
    'NGN': 780.00,
    'KES': 130.50,
    'ARS': 350.00,
    'CLP': 920.00,
    'COP': 3900.00,
    'PEN': 3.70,
    'NZD': 1.68,
    'HUF': 360.00,
    'CZK': 22.85,
    'RON': 4.58,
    'BGN': 1.80,
    'HRK': 7.00,
    'ISK': 137.50,
    'UAH': 36.50,
    'BHD': 0.377,
    'QAR': 3.64,
    'KWD': 0.307,
    'OMR': 0.385,
    'JOD': 0.709,
    'LBP': 15000.00,
    'BND': 1.34,
    'MMK': 2100.00,
    'KHR': 4100.00,
    'LAK': 20800.00,
    'BDT': 110.00,
    'LKR': 325.00,
    'NPR': 133.00,
    'AFN': 70.00,
    'IRR': 42000.00,
    'IQD': 1310.00,
    'YER': 250.00,
    'SYP': 13000.00,
    'JMD': 155.00,
    'TTD': 6.78,
    'BBD': 2.00,
    'BZD': 2.00,
    'XCD': 2.70,
    'GYD': 209.00,
    'SRD': 38.00,
    'FJD': 2.25,
    'PGK': 3.75,
    'SBD': 8.40,
    'VUV': 119.00,
    'WST': 2.70,
    'TOP': 2.35,
    'XPF': 110.00,
    'MOP': 8.05,
    'BWP': 13.65,
    'ZMW': 24.00,
    'MWK': 1680.00,
    'UGX': 3700.00,
    'TZS': 2300.00,
    'ETB': 55.00,
    'MAD': 10.05,
    'TND': 3.10,
    'DZD': 134.50,
    'LYD': 4.85,
    'SDG': 600.00,
    'SSP': 1300.00,
    'RWF': 1200.00,
    'BIF': 2850.00,
    'DJF': 178.00,
    'SOS': 570.00,
    'ERN': 15.00,
    'MZN': 63.85,
    'AOA': 830.00,
    'XAF': 600.00,
    'XOF': 600.00,
    'GHS': 12.00,
    'GMD': 67.00,
    'GNF': 8600.00,
    'SLE': 22000.00,
    'LRD': 190.00,
    'CVE': 101.00,
    'STN': 22.65,
    'MGA': 4500.00,
    'MUR': 45.00,
    'SCR': 13.50,
    'KMF': 450.00,
    'ALL': 95.00,
    'MKD': 56.50,
    'RSD': 108.00,
    'BAM': 1.80,
    'MDL': 18.00,
    'GEL': 2.65,
    'AMD': 405.00,
    'AZN': 1.70,
    'KZT': 450.00,
    'KGS': 89.50,
    'TJS': 10.95,
    'TMT': 3.50,
    'UZS': 12200.00,
    'MNT': 3450.00,
    'KPW': 900.00,
    'BTN': 83.12,
    'MVR': 15.40
};

/**
 * Get exchange rate for a currency (relative to USD)
 * 
 * @param {string} currencyCode - Currency code (e.g., 'EUR', 'PKR')
 * @returns {number} Exchange rate (1 USD = X of currency)
 */
export function getExchangeRate(currencyCode) {
    return exchangeRates[currencyCode] || 1.0;
}

/**
 * Convert amount from one currency to another
 * 
 * @param {number} amount - Amount to convert
 * @param {string} fromCurrency - Source currency code (default: 'USD')
 * @param {string} toCurrency - Target currency code
 * @returns {number} Converted amount
 */
export function convertCurrency(amount, fromCurrency = 'USD', toCurrency = 'USD') {
    if (fromCurrency === toCurrency) {
        return amount;
    }

    // Convert to USD first (base currency)
    const fromRate = getExchangeRate(fromCurrency);
    const amountInUSD = amount / fromRate;

    // Convert from USD to target currency
    const toRate = getExchangeRate(toCurrency);
    const convertedAmount = amountInUSD * toRate;

    return convertedAmount;
}

/**
 * Convert amount from base currency (USD) to target currency
 * 
 * @param {number} amount - Amount in USD
 * @param {string} toCurrency - Target currency code
 * @returns {number} Converted amount
 */
export function convertFromUSD(amount, toCurrency) {
    return convertCurrency(amount, 'USD', toCurrency);
}

/**
 * Convert amount to base currency (USD) from source currency
 * 
 * @param {number} amount - Amount in source currency
 * @param {string} fromCurrency - Source currency code
 * @returns {number} Amount in USD
 */
export function convertToUSD(amount, fromCurrency) {
    return convertCurrency(amount, fromCurrency, 'USD');
}
