<template>
    <div class="dashboard-page">
        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="white"/>
                        <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ totalUsers }}</div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-change positive">+12.5%</div>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="white"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ formatPrice(revenueAmount) }}</div>
                    <div class="stat-label">Revenue</div>
                    <div class="stat-change positive">+8.2%</div>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ activeUsers }}</div>
                    <div class="stat-label">Active Users</div>
                    <div class="stat-change positive">+5.3%</div>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 6V12L16 14" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">89%</div>
                    <div class="stat-label">Conversion Rate</div>
                    <div class="stat-change negative">-2.1%</div>
                </div>
            </div>
        </div>
        
        <!-- All Users Working Chart -->
        <div class="content-card users-chart-card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title">All Users Working</h3>
                <div class="chart-legend">
                    <span class="legend-item">
                        <span class="legend-color" style="background: #667eea;"></span>
                        Active Users
                    </span>
                    <span class="legend-item">
                        <span class="legend-color" style="background: #95a5a6;"></span>
                        Inactive Users
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div v-if="loadingUsers" class="chart-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading user data...</p>
                </div>
                <div v-else-if="userError" class="chart-error">
                    <p>{{ userError }}</p>
                </div>
                <div v-else class="users-chart-container">
                    <div class="users-chart">
                        <div class="chart-bars">
                            <div 
                                v-for="(roleData, index) in usersByRole" 
                                :key="index"
                                class="chart-bar-item"
                            >
                                <div class="bar-label">{{ roleData.role || 'No Role' }}</div>
                                <div class="bar-container">
                                    <div 
                                        class="bar-fill active-bar" 
                                        :style="{ width: roleData.activePercentage + '%', height: '24px' }"
                                        :title="`${roleData.active} active`"
                                    ></div>
                                    <div 
                                        class="bar-fill inactive-bar" 
                                        :style="{ width: roleData.inactivePercentage + '%', height: '24px' }"
                                        :title="`${roleData.inactive} inactive`"
                                    ></div>
                                </div>
                                <div class="bar-value">{{ roleData.total }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-if="usersByRole.length === 0" class="no-data-message">
                        No user data available
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Chart - Daily, Monthly, Yearly -->
        <div class="content-card revenue-chart-card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title">Revenue Analysis</h3>
                <div class="chart-tabs">
                    <button 
                        class="tab-button" 
                        :class="{ active: revenueView === 'daily' }"
                        @click="revenueView = 'daily'"
                    >
                        Daily
                    </button>
                    <button 
                        class="tab-button" 
                        :class="{ active: revenueView === 'monthly' }"
                        @click="revenueView = 'monthly'"
                    >
                        Monthly
                    </button>
                    <button 
                        class="tab-button" 
                        :class="{ active: revenueView === 'yearly' }"
                        @click="revenueView = 'yearly'"
                    >
                        Yearly
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div v-if="loadingRevenue" class="chart-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading revenue data...</p>
                </div>
                <div v-else-if="revenueError" class="chart-error">
                    <p>{{ revenueError }}</p>
                </div>
                <div v-else class="revenue-chart-container">
                    <!-- Daily Revenue Chart -->
                    <div v-if="revenueView === 'daily'" class="revenue-chart">
                        <div class="chart-summary">
                            <div class="summary-item">
                                <span class="summary-label">Total (Last 30 Days)</span>
                                <span class="summary-value">{{ formatPrice(dailyRevenueTotal) }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Average Daily</span>
                                <span class="summary-value">{{ formatPrice(dailyRevenueAverage) }}</span>
                            </div>
                        </div>
                        <div class="chart-bars-horizontal">
                            <div 
                                v-for="(day, index) in displayDailyRevenue" 
                                :key="index"
                                class="chart-bar-row"
                            >
                                <div class="bar-date">{{ formatDate(day.date) }}</div>
                                <div class="bar-wrapper">
                                    <div 
                                        class="bar-fill revenue-bar" 
                                        :style="{ width: getBarWidth(day.revenue, dailyMaxRevenue) + '%' }"
                                        :title="formatPrice(day.revenue)"
                                    >
                                        <span class="bar-label">{{ formatPrice(day.revenue) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="displayDailyRevenue.length === 0" class="no-data-message">
                            No daily revenue data available
                        </div>
                    </div>

                    <!-- Monthly Revenue Chart -->
                    <div v-if="revenueView === 'monthly'" class="revenue-chart">
                        <div class="chart-summary">
                            <div class="summary-item">
                                <span class="summary-label">Total (Last 12 Months)</span>
                                <span class="summary-value">{{ formatPrice(monthlyRevenueTotal) }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Average Monthly</span>
                                <span class="summary-value">{{ formatPrice(monthlyRevenueAverage) }}</span>
                            </div>
                        </div>
                        <div class="chart-bars-horizontal">
                            <div 
                                v-for="(month, index) in displayMonthlyRevenue" 
                                :key="index"
                                class="chart-bar-row"
                            >
                                <div class="bar-date">{{ formatMonth(month.month) }}</div>
                                <div class="bar-wrapper">
                                    <div 
                                        class="bar-fill revenue-bar" 
                                        :style="{ width: getBarWidth(month.revenue, monthlyMaxRevenue) + '%' }"
                                        :title="formatPrice(month.revenue)"
                                    >
                                        <span class="bar-label">{{ formatPrice(month.revenue) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="displayMonthlyRevenue.length === 0" class="no-data-message">
                            No monthly revenue data available
                        </div>
                    </div>

                    <!-- Yearly Revenue Chart -->
                    <div v-if="revenueView === 'yearly'" class="revenue-chart">
                        <div class="chart-summary">
                            <div class="summary-item">
                                <span class="summary-label">Total (All Years)</span>
                                <span class="summary-value">{{ formatPrice(yearlyRevenueTotal) }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Average Yearly</span>
                                <span class="summary-value">{{ formatPrice(yearlyRevenueAverage) }}</span>
                            </div>
                        </div>
                        <div class="chart-bars-horizontal">
                            <div 
                                v-for="(year, index) in displayYearlyRevenue" 
                                :key="index"
                                class="chart-bar-row"
                            >
                                <div class="bar-date">{{ year.year }}</div>
                                <div class="bar-wrapper">
                                    <div 
                                        class="bar-fill revenue-bar" 
                                        :style="{ width: getBarWidth(year.revenue, yearlyMaxRevenue) + '%' }"
                                        :title="formatPrice(year.revenue)"
                                    >
                                        <span class="bar-label">{{ formatPrice(year.revenue) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="displayYearlyRevenue.length === 0" class="no-data-message">
                            No yearly revenue data available
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profit & Loss Analysis -->
        <div class="content-card profit-loss-chart-card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title">Profit & Loss Analysis</h3>
                <div class="chart-legend">
                    <span class="legend-item">
                        <span class="legend-color" style="background: #27ae60;"></span>
                        Income
                    </span>
                    <span class="legend-item">
                        <span class="legend-color" style="background: #e74c3c;"></span>
                        Expenses
                    </span>
                    <span class="legend-item">
                        <span class="legend-color" style="background: #3498db;"></span>
                        Net Profit/Loss
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div v-if="loadingProfitLoss" class="chart-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading profit & loss data...</p>
                </div>
                <div v-else-if="profitLossError" class="chart-error">
                    <p>{{ profitLossError }}</p>
                </div>
                <div v-else class="profit-loss-chart-container">
                    <div class="profit-loss-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total Income</span>
                            <span class="summary-value income-value">{{ formatPrice(totalIncome) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Total Expenses</span>
                            <span class="summary-value expense-value">{{ formatPrice(totalExpenses) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Net Profit/Loss</span>
                            <span class="summary-value" :class="netProfit >= 0 ? 'profit-value' : 'loss-value'">
                                {{ formatPrice(netProfit) }}
                            </span>
                        </div>
                    </div>
                    <div class="profit-loss-chart">
                        <div class="chart-bars-horizontal">
                            <div 
                                v-for="(day, index) in displayProfitLossData" 
                                :key="index"
                                class="chart-bar-row profit-loss-row"
                            >
                                <div class="bar-date">{{ formatDate(day.date) }}</div>
                                <div class="profit-loss-bars-container">
                                    <div class="bar-group">
                                        <div class="bar-label-small">Income</div>
                                        <div class="bar-wrapper income-wrapper">
                                            <div 
                                                class="bar-fill income-bar" 
                                                :style="{ width: getBarWidth(day.income, profitLossMaxValue) + '%' }"
                                                :title="`Income: ${formatPrice(day.income)}`"
                                            >
                                                <span class="bar-label" v-if="day.income > 0">{{ formatPrice(day.income) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bar-group">
                                        <div class="bar-label-small">Expenses</div>
                                        <div class="bar-wrapper expense-wrapper">
                                            <div 
                                                class="bar-fill expense-bar" 
                                                :style="{ width: getBarWidth(day.expenses, profitLossMaxValue) + '%' }"
                                                :title="`Expenses: ${formatPrice(day.expenses)}`"
                                            >
                                                <span class="bar-label" v-if="day.expenses > 0">{{ formatPrice(day.expenses) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bar-group">
                                        <div class="bar-label-small">Net</div>
                                        <div class="bar-wrapper net-wrapper">
                                            <div 
                                                class="bar-fill net-bar" 
                                                :class="day.net >= 0 ? 'profit-bar' : 'loss-bar'"
                                                :style="{ width: Math.abs(getBarWidth(day.net, profitLossMaxValue)) + '%' }"
                                                :title="`Net: ${formatPrice(day.net)}`"
                                            >
                                                <span class="bar-label" v-if="Math.abs(day.net) > 0">{{ formatPrice(day.net) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="displayProfitLossData.length === 0" class="no-data-message">
                        No profit & loss data available
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                    <button class="card-action">View All</button>
                </div>
                <div class="card-body">
                    <div class="activity-list">
                        <div class="activity-item" v-for="i in 6" :key="i">
                            <div class="activity-avatar">
                                <div class="avatar-circle">{{ String.fromCharCode(65 + i) }}</div>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Activity Item #{{ i }}</div>
                                <div class="activity-meta">2 hours ago</div>
                            </div>
                            <div class="activity-badge">New</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="actions-grid">
                        <button class="action-button">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Add User
                        </button>
                        <button class="action-button">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4 4H16V16H4V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 8H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Generate Report
                        </button>
                        <button class="action-button">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2L2 7L10 12L18 7L10 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 17L10 22L18 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 12L10 17L18 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            View Analytics
                        </button>
                        <button class="action-button">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 12C11.1046 12 12 11.1046 12 10C12 8.89543 11.1046 8 10 8C8.89543 8 8 8.89543 8 10C8 11.1046 8.89543 12 10 12Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 2V4M10 16V18M18 10H16M4 10H2M15.6569 4.34315L14.2426 5.75736M5.75736 14.2426L4.34315 15.6569M15.6569 15.6569L14.2426 14.2426M5.75736 5.75736L4.34315 4.34315" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted, watch, ref, computed } from 'vue';
import { useHotelSettings } from '../../composables/useHotelSettings';
import axios from 'axios';

export default {
    name: 'DashboardHome',
    setup() {
        const { formatPrice, loadSettings, hotelSettings } = useHotelSettings();
        const revenueAmount = ref(45678); // Store revenue amount (default, overridden by live data)
        
        // User data for chart
        const users = ref([]);
        const loadingUsers = ref(false);
        const userError = ref(null);
        
        // Revenue chart data
        const revenueView = ref('daily'); // 'daily', 'monthly', 'yearly'
        const dailyRevenueData = ref([]);
        const monthlyRevenueData = ref([]);
        const yearlyRevenueData = ref([]);
        const loadingRevenue = ref(false);
        const revenueError = ref(null);
        
        // Profit & Loss chart data
        const profitLossData = ref([]);
        const loadingProfitLoss = ref(false);
        const profitLossError = ref(null);
        
        // Computed properties for user statistics
        const totalUsers = computed(() => users.value.length);
        const activeUsers = computed(() => users.value.filter(u => u.status === 'active').length);
        const inactiveUsers = computed(() => users.value.filter(u => u.status === 'inactive').length);
        
        // Computed properties for revenue charts
        const displayDailyRevenue = computed(() => {
            // Show last 30 days
            return dailyRevenueData.value.slice(-30);
        });
        
        const displayMonthlyRevenue = computed(() => {
            // Show last 12 months
            return monthlyRevenueData.value.slice(-12);
        });
        
        const displayYearlyRevenue = computed(() => {
            return yearlyRevenueData.value;
        });
        
        const dailyMaxRevenue = computed(() => {
            if (displayDailyRevenue.value.length === 0) return 1;
            return Math.max(...displayDailyRevenue.value.map(d => d.revenue || 0), 1);
        });
        
        const monthlyMaxRevenue = computed(() => {
            if (displayMonthlyRevenue.value.length === 0) return 1;
            return Math.max(...displayMonthlyRevenue.value.map(m => m.revenue || 0), 1);
        });
        
        const yearlyMaxRevenue = computed(() => {
            if (displayYearlyRevenue.value.length === 0) return 1;
            return Math.max(...displayYearlyRevenue.value.map(y => y.revenue || 0), 1);
        });
        
        const dailyRevenueTotal = computed(() => {
            return displayDailyRevenue.value.reduce((sum, d) => sum + (d.revenue || 0), 0);
        });
        
        const dailyRevenueAverage = computed(() => {
            const count = displayDailyRevenue.value.length;
            return count > 0 ? dailyRevenueTotal.value / count : 0;
        });
        
        const monthlyRevenueTotal = computed(() => {
            return displayMonthlyRevenue.value.reduce((sum, m) => sum + (m.revenue || 0), 0);
        });
        
        const monthlyRevenueAverage = computed(() => {
            const count = displayMonthlyRevenue.value.length;
            return count > 0 ? monthlyRevenueTotal.value / count : 0;
        });
        
        const yearlyRevenueTotal = computed(() => {
            return displayYearlyRevenue.value.reduce((sum, y) => sum + (y.revenue || 0), 0);
        });
        
        const yearlyRevenueAverage = computed(() => {
            const count = displayYearlyRevenue.value.length;
            return count > 0 ? yearlyRevenueTotal.value / count : 0;
        });
        
        // Computed properties for profit & loss
        const displayProfitLossData = computed(() => {
            // Show last 30 days
            return profitLossData.value.slice(-30);
        });
        
        const totalIncome = computed(() => {
            return displayProfitLossData.value.reduce((sum, day) => sum + (day.income || 0), 0);
        });
        
        const totalExpenses = computed(() => {
            return displayProfitLossData.value.reduce((sum, day) => sum + (day.expenses || 0), 0);
        });
        
        const netProfit = computed(() => {
            return totalIncome.value - totalExpenses.value;
        });
        
        const profitLossMaxValue = computed(() => {
            if (displayProfitLossData.value.length === 0) return 1;
            const maxIncome = Math.max(...displayProfitLossData.value.map(d => d.income || 0), 0);
            const maxExpenses = Math.max(...displayProfitLossData.value.map(d => d.expenses || 0), 0);
            return Math.max(maxIncome, maxExpenses, 1);
        });
        
        // Group users by role for chart
        const usersByRole = computed(() => {
            const roleMap = {};
            
            users.value.forEach(user => {
                const roleName = user.role?.name || 'No Role';
                if (!roleMap[roleName]) {
                    roleMap[roleName] = {
                        role: roleName,
                        active: 0,
                        inactive: 0,
                        total: 0
                    };
                }
                roleMap[roleName].total++;
                if (user.status === 'active') {
                    roleMap[roleName].active++;
                } else {
                    roleMap[roleName].inactive++;
                }
            });
            
            // Convert to array and calculate percentages
            return Object.values(roleMap).map(roleData => {
                const maxTotal = Math.max(...Object.values(roleMap).map(r => r.total), 1);
                roleData.activePercentage = maxTotal > 0 ? (roleData.active / maxTotal) * 100 : 0;
                roleData.inactivePercentage = maxTotal > 0 ? (roleData.inactive / maxTotal) * 100 : 0;
                return roleData;
            }).sort((a, b) => b.total - a.total);
        });
        
        // Helper functions for revenue chart
        const getBarWidth = (value, maxValue) => {
            if (!maxValue || maxValue === 0) return 0;
            return Math.min((value / maxValue) * 100, 100);
        };
        
        const formatDate = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        };
        
        const formatMonth = (monthString) => {
            if (!monthString) return '';
            const [year, month] = monthString.split('-');
            const date = new Date(year, parseInt(month) - 1);
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        };
        
        // Fetch profit & loss data
        const loadProfitLossData = async () => {
            loadingProfitLoss.value = true;
            profitLossError.value = null;
            
            try {
                const today = new Date();
                const from = new Date(today);
                from.setDate(from.getDate() - 30);
                
                const response = await axios.get('/api/reports/expense-vs-income', {
                    params: {
                        from: from.toISOString().split('T')[0],
                        to: today.toISOString().split('T')[0]
                    }
                });
                
                if (response.data && response.data.report) {
                    profitLossData.value = response.data.report.daily_comparison || [];
                }
            } catch (error) {
                console.error('Error loading profit & loss data:', error);
                profitLossError.value = 'Failed to load profit & loss data. Please try again later.';
                profitLossData.value = [];
            } finally {
                loadingProfitLoss.value = false;
            }
        };
        
        // Fetch revenue data
        const loadRevenueData = async () => {
            loadingRevenue.value = true;
            revenueError.value = null;
            
            try {
                const today = new Date();
                
                // Daily revenue - last 30 days
                const dailyFrom = new Date(today);
                dailyFrom.setDate(dailyFrom.getDate() - 30);
                const dailyResponse = await axios.get('/api/reports/daily-monthly-revenue', {
                    params: {
                        from: dailyFrom.toISOString().split('T')[0],
                        to: today.toISOString().split('T')[0]
                    }
                });
                
                if (dailyResponse.data && dailyResponse.data.report) {
                    dailyRevenueData.value = dailyResponse.data.report.daily_revenue || [];
                }
                
                // Monthly revenue - last 12 months
                const monthlyFrom = new Date(today);
                monthlyFrom.setMonth(monthlyFrom.getMonth() - 12);
                const monthlyResponse = await axios.get('/api/reports/daily-monthly-revenue', {
                    params: {
                        from: monthlyFrom.toISOString().split('T')[0],
                        to: today.toISOString().split('T')[0]
                    }
                });
                
                if (monthlyResponse.data && monthlyResponse.data.report) {
                    monthlyRevenueData.value = monthlyResponse.data.report.monthly_revenue || [];
                }
                
                // Yearly revenue - calculate from all monthly data
                const yearlyMap = {};
                monthlyRevenueData.value.forEach(month => {
                    const year = month.month.split('-')[0];
                    if (!yearlyMap[year]) {
                        yearlyMap[year] = { year: year, revenue: 0 };
                    }
                    yearlyMap[year].revenue += month.revenue || 0;
                });
                yearlyRevenueData.value = Object.values(yearlyMap).sort((a, b) => a.year.localeCompare(b.year));
                
            } catch (error) {
                console.error('Error loading revenue data:', error);
                revenueError.value = 'Failed to load revenue data. Please try again later.';
                dailyRevenueData.value = [];
                monthlyRevenueData.value = [];
                yearlyRevenueData.value = [];
            } finally {
                loadingRevenue.value = false;
            }
        };
        
        // Fetch all users data
        const loadUsers = async () => {
            loadingUsers.value = true;
            userError.value = null;
            
            try {
                // Fetch all users (paginate through all pages)
                let allUsers = [];
                let currentPage = 1;
                let lastPage = 1;
                
                // First request to get pagination info
                const firstResponse = await axios.get('/api/users', {
                    params: { page: 1 }
                });
                
                if (firstResponse.data && firstResponse.data.data) {
                    allUsers = allUsers.concat(firstResponse.data.data);
                    lastPage = firstResponse.data.last_page || 1;
                    
                    // Fetch remaining pages if any
                    for (let page = 2; page <= lastPage; page++) {
                        const response = await axios.get('/api/users', {
                            params: { page: page }
                        });
                        
                        if (response.data && response.data.data) {
                            allUsers = allUsers.concat(response.data.data);
                        }
                    }
                }
                
                users.value = allUsers;
            } catch (error) {
                console.error('Error loading users:', error);
                userError.value = 'Failed to load user data. Please try again later.';
                users.value = [];
            } finally {
                loadingUsers.value = false;
            }
        };

        // Fetch revenue data for current month using existing reports API
        const loadRevenue = async () => {
            try {
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                const to = today.toISOString().split('T')[0];
                const from = firstDay.toISOString().split('T')[0];

                const response = await axios.get('/api/reports/daily-monthly-revenue', {
                    params: { from, to }
                });

                if (response.data && response.data.report && typeof response.data.report.total_revenue !== 'undefined') {
                    // Use backend-calculated total_revenue to reflect accurate revenue
                    revenueAmount.value = response.data.report.total_revenue;
                }
            } catch (error) {
                console.error('Error loading revenue data:', error);
                // Keep existing default revenueAmount on error
            }
        };
        
        // Watch for currency changes to trigger re-render
        watch(() => hotelSettings.value.currency, () => {
            // Force re-render by updating a reactive value
            revenueAmount.value = revenueAmount.value;
        });
        
        onMounted(async () => {
            await loadSettings();
            await loadUsers();
            await loadRevenue();
            await loadRevenueData();
            await loadProfitLossData();
        });
        
        return {
            formatPrice,
            revenueAmount,
            users,
            loadingUsers,
            userError,
            totalUsers,
            activeUsers,
            inactiveUsers,
            usersByRole,
            revenueView,
            loadingRevenue,
            revenueError,
            displayDailyRevenue,
            displayMonthlyRevenue,
            displayYearlyRevenue,
            dailyMaxRevenue,
            monthlyMaxRevenue,
            yearlyMaxRevenue,
            dailyRevenueTotal,
            dailyRevenueAverage,
            monthlyRevenueTotal,
            monthlyRevenueAverage,
            yearlyRevenueTotal,
            yearlyRevenueAverage,
            getBarWidth,
            formatDate,
            formatMonth,
            profitLossData,
            loadingProfitLoss,
            profitLossError,
            displayProfitLossData,
            totalIncome,
            totalExpenses,
            netProfit,
            profitLossMaxValue
        };
    }
}
</script>

<style scoped>
.dashboard-page {
    max-width: 1400px;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-primary .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-success .stat-icon {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-info .stat-icon {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.stat-warning .stat-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 13px;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.stat-change {
    font-size: 12px;
    font-weight: 600;
}

.stat-change.positive {
    color: #27ae60;
}

.stat-change.negative {
    color: #e74c3c;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.content-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #ecf0f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.card-action {
    background: none;
    border: none;
    color: #667eea;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    padding: 0;
}

.card-action:hover {
    text-decoration: underline;
}

.card-body {
    padding: 24px;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px;
    border-radius: 6px;
    transition: background 0.2s;
}

.activity-item:hover {
    background: #f7f8fc;
}

.activity-avatar {
    flex-shrink: 0;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 14px;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-size: 14px;
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 4px;
}

.activity-meta {
    font-size: 12px;
    color: #95a5a6;
}

.activity-badge {
    background: #667eea;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 12px;
    text-transform: uppercase;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.action-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 16px;
    background: #f7f8fc;
    border: 1px solid #ecf0f1;
    border-radius: 6px;
    color: #2c3e50;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.action-button:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
    transform: translateY(-1px);
}

/* Users Chart Styles */
.users-chart-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.chart-legend {
    display: flex;
    gap: 16px;
    align-items: center;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #7f8c8d;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

.chart-loading,
.chart-error {
    text-align: center;
    padding: 40px 20px;
    color: #7f8c8d;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #ecf0f1;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.users-chart-container {
    padding: 0;
}

.users-chart {
    margin-top: 24px;
}

.chart-bars {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.chart-bar-item {
    display: grid;
    grid-template-columns: 120px 1fr 60px;
    gap: 12px;
    align-items: center;
}

.bar-label {
    font-size: 13px;
    font-weight: 500;
    color: #2c3e50;
    text-align: right;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.bar-container {
    position: relative;
    display: flex;
    height: 24px;
    background: #ecf0f1;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fill {
    transition: width 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: white;
    font-weight: 600;
}

.active-bar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.inactive-bar {
    background: #95a5a6;
}

.bar-value {
    font-size: 13px;
    font-weight: 600;
    color: #2c3e50;
    text-align: left;
}

.no-data-message {
    text-align: center;
    padding: 40px 20px;
    color: #95a5a6;
    font-size: 14px;
}

/* Revenue Chart Styles */
.revenue-chart-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.chart-tabs {
    display: flex;
    gap: 8px;
}

.tab-button {
    padding: 8px 16px;
    border: 1px solid #ecf0f1;
    background: white;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #7f8c8d;
    cursor: pointer;
    transition: all 0.2s;
}

.tab-button:hover {
    background: #f7f8fc;
    border-color: #667eea;
    color: #667eea;
}

.tab-button.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.revenue-chart-container {
    padding: 0;
}

.revenue-chart {
    padding: 0;
}

.chart-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #ecf0f1;
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.summary-label {
    font-size: 12px;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-value {
    font-size: 24px;
    font-weight: 700;
    color: #27ae60;
}

.chart-bars-horizontal {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.chart-bar-row {
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 12px;
    align-items: center;
}

.bar-date {
    font-size: 12px;
    font-weight: 500;
    color: #2c3e50;
    text-align: right;
}

.bar-wrapper {
    position: relative;
    height: 32px;
    background: #ecf0f1;
    border-radius: 4px;
    overflow: hidden;
}

.revenue-bar {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 8px;
    transition: width 0.3s ease;
    min-width: 60px;
}

.bar-label {
    font-size: 11px;
    font-weight: 600;
    color: white;
    white-space: nowrap;
}

/* Profit & Loss Chart Styles */
.profit-loss-chart-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.profit-loss-chart-container {
    padding: 0;
}

.profit-loss-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #ecf0f1;
}

.income-value {
    color: #27ae60;
}

.expense-value {
    color: #e74c3c;
}

.profit-value {
    color: #27ae60;
}

.loss-value {
    color: #e74c3c;
}

.profit-loss-row {
    grid-template-columns: 120px 1fr;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ecf0f1;
}

.profit-loss-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.profit-loss-bars-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    flex: 1;
}

.bar-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.bar-label-small {
    font-size: 11px;
    font-weight: 600;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.income-wrapper,
.expense-wrapper,
.net-wrapper {
    position: relative;
    background: #ecf0f1;
    height: 40px;
    border-radius: 4px;
    overflow: hidden;
}

.income-bar {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 8px;
    transition: width 0.3s ease;
    min-width: 60px;
}

.expense-bar {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 8px;
    transition: width 0.3s ease;
    min-width: 60px;
}

.net-bar {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 8px;
    transition: width 0.3s ease;
    min-width: 60px;
}

.profit-bar {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.loss-bar {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
}

.profit-text {
    color: #27ae60;
    font-weight: 600;
}

.loss-text {
    color: #e74c3c;
    font-weight: 600;
}

@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-bar-item {
        grid-template-columns: 100px 1fr 50px;
        gap: 8px;
    }
    
    .bar-label {
        font-size: 12px;
    }
    
    .chart-legend {
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
    }
}
</style>
