<template>
    <div class="profit-loss-report-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Profit & Loss Report</h1>
                <p class="page-subtitle">View profit and loss reports</p>
            </div>
        </div>

        <div class="filters-section">
            <div class="date-range">
                <label>From Date</label>
                <input type="date" v-model="dateFrom" class="date-input" />
            </div>
            <div class="date-range">
                <label>To Date</label>
                <input type="date" v-model="dateTo" class="date-input" />
            </div>
            <button class="btn btn-primary" @click="generateReport" :disabled="loading">
                {{ loading ? 'Generating...' : 'Generate Report' }}
            </button>
        </div>
        
        <div class="content-card" v-if="reportData">
            <div class="report-summary">
                <div class="summary-card revenue">
                    <h3>Total Revenue</h3>
                    <p class="amount">${{ parseFloat(reportData.total_revenue || 0).toFixed(2) }}</p>
                </div>
                <div class="summary-card expenses">
                    <h3>Total Expenses</h3>
                    <p class="amount">${{ parseFloat(reportData.total_expenses || 0).toFixed(2) }}</p>
                </div>
                <div class="summary-card profit" :class="{ loss: parseFloat(reportData.net_profit || 0) < 0 }">
                    <h3>Net Profit/Loss</h3>
                    <p class="amount">${{ parseFloat(reportData.net_profit || 0).toFixed(2) }}</p>
                </div>
            </div>

            <div class="report-details">
                <h3>Revenue Breakdown</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(amount, source) in reportData.revenue_breakdown" :key="source">
                            <td>{{ source }}</td>
                            <td>${{ parseFloat(amount || 0).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Expense Breakdown</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(amount, category) in reportData.expense_breakdown" :key="category">
                            <td>{{ category || 'Uncategorized' }}</td>
                            <td>${{ parseFloat(amount || 0).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-else-if="!loading" class="content-card">
            <div class="empty-state">
                <p>Select date range and click "Generate Report" to view profit and loss data</p>
            </div>
        </div>

        <div v-if="loading" class="content-card">
            <div class="loading-state">
                <div class="loading-spinner"></div>
                <p>Generating report...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';

const { success: showSuccess, error: showError } = useAlert();

const dateFrom = ref('');
const dateTo = ref('');
const reportData = ref(null);
const loading = ref(false);

const generateReport = async () => {
    if (!dateFrom.value || !dateTo.value) {
        showError('Please select both from and to dates');
        return;
    }
    
    loading.value = true;
    try {
        const response = await window.axios.get('/api/profit-loss-report', {
            params: {
                from: dateFrom.value,
                to: dateTo.value,
            }
        });
        reportData.value = response.data.report || null;
        if (reportData.value) {
            showSuccess('Report generated successfully');
        }
    } catch (error) {
        if (error.response && error.response.status === 403) {
            showError('You do not have permission to view this report');
        } else if (error.response && error.response.data && error.response.data.message) {
            showError(error.response.data.message);
        } else {
            showError('Failed to generate report. Please try again.');
        }
        console.error('Error generating report:', error);
        reportData.value = null;
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    dateTo.value = today.toISOString().split('T')[0];
    dateFrom.value = firstDay.toISOString().split('T')[0];
});
</script>

<style scoped>
.profit-loss-report-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.filters-section { display: flex; gap: 12px; align-items: flex-end; margin-bottom: 24px; }
.date-range { display: flex; flex-direction: column; gap: 4px; }
.date-range label { font-size: 14px; font-weight: 500; }
.date-input { padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.report-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px; }
.summary-card { padding: 20px; border-radius: 8px; }
.summary-card.revenue { background: #e3f2fd; }
.summary-card.expenses { background: #ffebee; }
.summary-card.profit { background: #e8f5e9; }
.summary-card.profit.loss { background: #ffebee; }
.summary-card h3 { margin: 0 0 12px 0; font-size: 14px; color: #666; }
.summary-card .amount { margin: 0; font-size: 24px; font-weight: 600; }
.report-details { margin-top: 32px; }
.report-details h3 { margin: 0 0 16px 0; font-size: 18px; }
.report-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
.report-table th { text-align: left; padding: 12px; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
.report-table td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
.empty-state { text-align: center; padding: 40px; color: #999; }
.loading-state { text-align: center; padding: 40px; }
.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #1976d2;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-primary { background: #1976d2; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #1565c0; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
