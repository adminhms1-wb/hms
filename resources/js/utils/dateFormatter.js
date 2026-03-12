/**
 * Global date formatting utility
 * Handles various date formats including ISO strings, date objects, and string dates
 */

/**
 * Format date to YYYY-MM-DD format (default display format)
 * @param {string|Date|null|undefined} date - Date to format
 * @returns {string} Formatted date in YYYY-MM-DD format or '—' if invalid
 */
export function formatDate(date) {
    if (!date) return '—';
    
    // Handle ISO format strings like "2026-01-16T00:00:00.000000Z"
    if (typeof date === 'string') {
        // Check for ISO format (contains 'T' and optionally 'Z')
        if (date.includes('T')) {
            const datePart = date.split('T')[0];
            if (datePart.match(/^\d{4}-\d{2}-\d{2}$/)) {
                return datePart;
            }
        }
        // Handle space-separated datetime (YYYY-MM-DD HH:mm:ss)
        if (date.includes(' ')) {
            const datePart = date.split(' ')[0];
            if (datePart.match(/^\d{4}-\d{2}-\d{2}$/)) {
                return datePart;
            }
        }
        // Handle plain date string (YYYY-MM-DD)
        if (date.match(/^\d{4}-\d{2}-\d{2}$/)) {
            return date;
        }
    }
    
    // Handle Date objects
    try {
        const d = new Date(date);
        if (!isNaN(d.getTime())) {
            return d.toISOString().split('T')[0];
        }
    } catch (e) {
        // Ignore errors
    }
    
    return '—';
}

/**
 * Format date for HTML date input (YYYY-MM-DD)
 * @param {string|Date|null|undefined} date - Date to format
 * @returns {string} Formatted date in YYYY-MM-DD format or empty string if invalid
 */
export function formatDateForInput(date) {
    if (!date) return '';
    
    // Handle ISO format strings like "2026-01-16T00:00:00.000000Z"
    if (typeof date === 'string') {
        // Check for ISO format (contains 'T' and optionally 'Z')
        if (date.includes('T')) {
            const datePart = date.split('T')[0];
            if (datePart.match(/^\d{4}-\d{2}-\d{2}$/)) {
                return datePart;
            }
        }
        // Handle space-separated datetime (YYYY-MM-DD HH:mm:ss)
        if (date.includes(' ')) {
            const datePart = date.split(' ')[0];
            if (datePart.match(/^\d{4}-\d{2}-\d{2}$/)) {
                return datePart;
            }
        }
        // Handle plain date string (YYYY-MM-DD)
        if (date.match(/^\d{4}-\d{2}-\d{2}$/)) {
            return date;
        }
    }
    
    // Handle Date objects
    try {
        const d = new Date(date);
        if (!isNaN(d.getTime())) {
            return d.toISOString().split('T')[0];
        }
    } catch (e) {
        // Ignore errors
    }
    
    return '';
}

/**
 * Format time to HH:mm format
 * @param {string|null|undefined} time - Time to format
 * @returns {string|null} Formatted time in HH:mm format or null if invalid
 */
export function formatTime(time) {
    if (!time) return null;
    
    // Handle different time formats
    if (typeof time === 'string') {
        // If it's already in HH:mm format, return it
        if (time.match(/^\d{2}:\d{2}$/)) {
            return time;
        }
        // If it's in HH:mm:ss format, extract HH:mm
        if (time.match(/^\d{2}:\d{2}:\d{2}/)) {
            return time.substring(0, 5);
        }
        // If it contains a space, extract the time part
        if (time.includes(' ')) {
            const timePart = time.split(' ').pop();
            if (timePart.match(/^\d{2}:\d{2}/)) {
                return timePart.substring(0, 5);
            }
        }
        // Handle ISO format time (HH:mm:ss.ssssssZ)
        if (time.includes('T')) {
            const timePart = time.split('T')[1];
            if (timePart) {
                const timeOnly = timePart.split('.')[0].split('Z')[0];
                if (timeOnly.match(/^\d{2}:\d{2}:\d{2}/)) {
                    return timeOnly.substring(0, 5);
                }
            }
        }
    }
    
    return time;
}

/**
 * Format date to readable format (e.g., "Jan 16, 2026")
 * @param {string|Date|null|undefined} date - Date to format
 * @returns {string} Formatted date or '—' if invalid
 */
export function formatDateReadable(date) {
    if (!date) return '—';
    
    try {
        const d = new Date(date);
        if (!isNaN(d.getTime())) {
            return d.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }
    } catch (e) {
        // Ignore errors
    }
    
    return '—';
}

/**
 * Format datetime to readable format (e.g., "Jan 16, 2026, 10:30 AM")
 * @param {string|Date|null|undefined} date - Date to format
 * @returns {string} Formatted datetime or '—' if invalid
 */
export function formatDateTimeReadable(date) {
    if (!date) return '—';
    
    try {
        const d = new Date(date);
        if (!isNaN(d.getTime())) {
            return d.toLocaleString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }
    } catch (e) {
        // Ignore errors
    }
    
    return '—';
}
