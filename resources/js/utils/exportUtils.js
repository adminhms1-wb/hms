/**
 * Export utilities for PDF and Excel
 */

/**
 * Export data to Excel (CSV format)
 * @param {Array} data - Array of objects to export
 * @param {string} filename - Filename without extension
 * @param {Array} headers - Array of header objects {key: 'column_key', label: 'Column Label'}
 */
export function exportToExcel(data, filename = 'export', headers = null) {
    if (!data || data.length === 0) {
        alert('No data to export');
        return;
    }

    // If headers not provided, use keys from first object
    if (!headers) {
        const firstRow = data[0];
        headers = Object.keys(firstRow).map(key => ({
            key: key,
            label: key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ')
        }));
    }

    // Create CSV content
    let csvContent = '';
    
    // Add headers
    csvContent += headers.map(h => `"${h.label}"`).join(',') + '\n';
    
    // Add data rows
    data.forEach(row => {
        const values = headers.map(h => {
            const value = row[h.key];
            // Handle null/undefined
            if (value === null || value === undefined) return '';
            // Escape quotes and wrap in quotes
            return `"${String(value).replace(/"/g, '""')}"`;
        });
        csvContent += values.join(',') + '\n';
    });

    // Create blob and download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `${filename}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Export data to PDF (using window.print for simple PDF generation)
 * @param {HTMLElement} element - HTML element to export
 * @param {string} filename - Filename without extension
 */
export function exportToPDF(element, filename = 'export') {
    if (!element) {
        alert('No content to export');
        return;
    }

    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>${filename}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        color: #333;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                        font-weight: bold;
                    }
                    .summary-card {
                        margin: 10px 0;
                        padding: 10px;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                    }
                    h1, h2, h3 {
                        color: #2c3e50;
                    }
                    @media print {
                        @page {
                            margin: 1cm;
                        }
                    }
                </style>
            </head>
            <body>
                ${element.innerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    
    // Wait for content to load, then print
    setTimeout(() => {
        printWindow.print();
    }, 250);
}

/**
 * Export table data to Excel with custom formatting
 * @param {Array} data - Array of objects
 * @param {string} filename - Filename
 * @param {Object} options - Options for export
 */
export function exportTableToExcel(data, filename, options = {}) {
    const {
        headers = null,
        sheetName = 'Sheet1',
        title = null
    } = options;

    exportToExcel(data, filename, headers);
}
