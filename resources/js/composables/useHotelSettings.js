import { ref, onMounted } from 'vue';
import axios from 'axios';
import { getCurrencyByCode, formatCurrency } from '../utils/currencies';
import { formatDateInTimezone } from '../utils/timezones';
import { setHotelCurrency as setGlobalCurrency, getCurrencySymbol as getGlobalCurrencySymbol } from '../utils/currencyFormatter';
import { convertCurrency } from '../utils/exchangeRates';

const hotelSettings = ref({
    currency: 'USD',
    baseCurrency: 'USD', // Base currency for storing prices
    timezone: 'UTC',
    currencySymbol: '$',
    currencyName: 'US Dollar'
});

let settingsLoaded = false;

export function useHotelSettings() {
    const loadSettings = async (forceReload = false) => {
        if (settingsLoaded && !forceReload) {
            // Return a resolved promise if already loaded
            return Promise.resolve();
        }
        
        try {
            const response = await axios.get('/api/hotel/settings');
            const data = response.data;
            
            const currencyCode = data.currency || 'USD';
            const currency = getCurrencyByCode(currencyCode);
            
            hotelSettings.value = {
                currency: currencyCode,
                baseCurrency: data.base_currency || 'USD', // Store base currency if available
                timezone: data.timezone || 'UTC',
                currencySymbol: currency.symbol,
                currencyName: currency.name
            };
            
            // Update global currency formatter
            setGlobalCurrency(currencyCode);
            
            settingsLoaded = true;
        } catch (error) {
            console.error('Error loading hotel settings:', error);
            // Set defaults on error
            hotelSettings.value = {
                currency: 'USD',
                baseCurrency: 'USD',
                timezone: 'UTC',
                currencySymbol: '$',
                currencyName: 'US Dollar'
            };
        }
    };

    /**
     * Format price with currency conversion
     * Converts from base currency (USD) to selected currency
     * 
     * @param {number} amount - Amount in base currency (USD)
     * @param {string} fromCurrency - Source currency (default: base currency/USD)
     * @returns {string} Formatted price with currency symbol
     */
    const formatPrice = (amount, fromCurrency = null) => {
        // Always assume prices in database are stored in USD (base currency)
        const baseCurrency = fromCurrency || 'USD';
        const targetCurrency = hotelSettings.value.currency || 'USD';
        
        // Ensure amount is a valid number
        const numAmount = parseFloat(amount) || 0;
        if (isNaN(numAmount) || numAmount <= 0) {
            return formatCurrency(0, targetCurrency);
        }
        
        // Convert amount if currencies are different
        let convertedAmount = numAmount;
        if (baseCurrency !== targetCurrency) {
            try {
                convertedAmount = convertCurrency(numAmount, baseCurrency, targetCurrency);
                // Round to 2 decimal places
                convertedAmount = Math.round(convertedAmount * 100) / 100;
            } catch (error) {
                console.error('Error converting currency:', error);
                convertedAmount = numAmount; // Fallback to original amount
            }
        }
        
        return formatCurrency(convertedAmount, targetCurrency);
    };

    /**
     * Convert price from base currency to current currency
     * 
     * @param {number} amount - Amount in base currency
     * @returns {number} Converted amount
     */
    const convertPrice = (amount) => {
        // Always assume prices in database are stored in USD (base currency)
        const baseCurrency = 'USD';
        const targetCurrency = hotelSettings.value.currency || 'USD';
        
        if (baseCurrency === targetCurrency || amount === 0) {
            return amount;
        }
        
        return convertCurrency(amount, baseCurrency, targetCurrency);
    };

    const getCurrencySymbol = () => {
        return hotelSettings.value.currencySymbol || '$';
    };

    const formatDate = (date) => {
        return formatDateInTimezone(date, hotelSettings.value.timezone);
    };

    const getCurrentDateTime = () => {
        return new Date().toLocaleString('en-US', {
            timeZone: hotelSettings.value.timezone
        });
    };

    // Load settings on first use
    if (!settingsLoaded) {
        loadSettings();
    }

    return {
        hotelSettings,
        loadSettings,
        formatPrice,
        convertPrice,
        getCurrencySymbol,
        formatDate,
        getCurrentDateTime
    };
}
