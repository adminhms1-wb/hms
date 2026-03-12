<template>
    <div class="report-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Expense vs Income Report</h1>
                <p class="page-subtitle">Compare expenses against income</p>
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
            <button v-if="reportData" class="btn btn-secondary" @click="exportPDF">Print</button>
            <button v-if="reportData" class="btn btn-secondary" @click="exportExcel">Export Excel</button>
        </div>
        
        <div class="content-card" v-if="reportData" ref="reportContent">
            <div class="report-summary">
                <div class="summary-card revenue">
                    <h3>Total Income</h3>
                    <p class="amount">${{ parseFloat(reportData.total_income || 0).toFixed(2) }}</p>
                </div>
                <div class="summary-card expense">
                    <h3>Total Expenses</h3>
                    <p class="amount">${{ parseFloat(reportData.total_expenses || 0).toFixed(2) }}</p>
                </div>
                <div class="summary-card" :class="parseFloat(reportData.net_profit || 0) >= 0 ? 'profit' : 'loss'">
                    <h3>Net Profit/Loss</h3>
                    <p class="amount">${{ parseFloat(reportData.net_profit || 0).toFixed(2) }}</p>
                </div>
            </div>

            <div class="report-details">
                <h3>Daily Comparison</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Income</th>
                            <th>Expenses</th>
                            <th>Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="day in reportData.daily_comparison" :key="day.date">
                            <td>{{ day.date }}</td>
                            <td>${{ parseFloat(day.income || 0).toFixed(2) }}</td>
                            <td>${{ parseFloat(day.expenses || 0).toFixed(2) }}</td>
                            <td :class="parseFloat(day.net || 0) >= 0 ? 'positive' : 'negative'">
                                ${{ parseFloat(day.net || 0).toFixed(2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-else class="content-card">
            <div class="empty-state">
                <p>Select date range and click "Generate Report" to view expense vs income data</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAlert } from '@/composables/useAlert';
import { exportToPDF, exportToExcel } from '@/utils/exportUtils';

const { success: showSuccess, error: showError } = useAlert();

const dateFrom = ref('');
const dateTo = ref('');
const reportData = ref(null);
const loading = ref(false);
const reportContent = ref(null);

const generateReport = async () => {
    if (!dateFrom.value || !dateTo.value) {
        showError('Please select both from and to dates');
        return;
    }
    
    loading.value = true;
    try {
        const response = await window.axios.get('/api/reports/expense-vs-income', {
            params: { from: dateFrom.value, to: dateTo.value }
        });
        reportData.value = response.data.report || null;
        if (reportData.value) showSuccess('Report generated successfully');
    } catch (error) {
        showError('Failed to generate report');
        console.error('Error:', error);
    } finally {
        loading.value = false;
    }
};

const exportPDF = () => {
    if (reportContent.value) {
        exportToPDF(reportContent.value, `expense-vs-income-${dateFrom.value}-to-${dateTo.value}`);
    }
};

const exportExcel = () => {
    if (reportData.value && reportData.value.daily_comparison) {
        exportToExcel(reportData.value.daily_comparison, `expense-vs-income-${dateFrom.value}-to-${dateTo.value}`, [
            { key: 'date', label: 'Date' },
            { key: 'income', label: 'Income ($)' },
            { key: 'expenses', label: 'Expenses ($)' },
            { key: 'net', label: 'Net ($)' }
        ]);
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
.report-page { padding: 24px; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #666; margin: 0; }
.filters-section { display: flex; gap: 12px; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; }
.date-range { display: flex; flex-direction: column; gap: 4px; }
.date-range label { font-size: 14px; font-weight: 500; }
.date-input { padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
.btn-primary { background: #3498db; color: white; }
.btn-primary:hover:not(:disabled) { background: #2980b9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #95a5a6; color: white; }
.btn-secondary:hover { background: #7f8c8d; }
.content-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.report-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px; }
.summary-card { padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; }
.summary-card.revenue { background: #e3f2fd; }
.summary-card.expense { background: #ffebee; }
.summary-card.profit { background: #e8f5e9; }
.summary-card.loss { background: #ffebee; }
.summary-card h3 { font-size: 14px; color: #666; margin: 0 0 8px 0; }
.summary-card .amount { font-size: 24px; font-weight: 600; color: #2c3e50; margin: 0; }
.report-details { margin-top: 32px; }
.report-details h3 { font-size: 18px; margin-bottom: 16px; }
.report-table { width: 100%; border-collapse: collapse; }
.report-table th, .report-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
.report-table th { background: #f8f9fa; font-weight: 600; }
.report-table .positive { color: #27ae60; }
.report-table .negative { color: #e74c3c; }
.empty-state { text-align: center; padding: 40px; color: #999; }
</style>
