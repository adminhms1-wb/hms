<template>
    <div class="front-desk-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Front Desk Operations</h1>
                <p class="page-subtitle">
                    Manage quick check-ins, express check-outs, deposits, extra charges, and guest bills in one place.
                </p>
            </div>
        </div>

        <div class="fd-grid">
            <!-- Quick Check-in -->
            <section class="fd-card">
                <div class="fd-card-header">
                    <h2>Quick Check-in</h2>
                    <span class="fd-tag">Arrivals Today</span>
                </div>
                <div class="fd-card-body">
                    <div class="fd-filter-row">
                        <div class="search-box">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.5" />
                                <path d="M11 11L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                            <input
                                type="text"
                                v-model="quickCheckinSearch"
                                placeholder="Search by guest or room..."
                                @input="filterQuickCheckins"
                            />
                        </div>
                    </div>

                    <div v-if="loadingQuick" class="loading-state">
                        <p>Loading arrivals...</p>
                    </div>
                    <div v-else-if="filteredQuickCheckins.length === 0" class="empty-state">
                        <p>No arrivals for today</p>
                    </div>
                    <div v-else class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="reservation in filteredQuickCheckins" :key="reservation.id">
                                    <td>#{{ reservation.id }}</td>
                                    <td>
                                        <div class="guest-info">
                                            <strong>{{ reservation.guest_name }}</strong>
                                            <small v-if="reservation.guest_email">{{ reservation.guest_email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ reservation.room?.room_number }}</strong>
                                        <small>{{ reservation.room?.room_type?.name }}</small>
                                    </td>
                                    <td>{{ formatDate(reservation.check_in_date) }}</td>
                                    <td>{{ formatDate(reservation.check_out_date) }}</td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-success"
                                            @click="quickCheckIn(reservation)"
                                            :disabled="actingOnId === reservation.id"
                                        >
                                            {{ actingOnId === reservation.id ? 'Checking in...' : 'Quick Check-in' }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Express Check-out -->
            <section class="fd-card">
                <div class="fd-card-header">
                    <h2>Express Check-out</h2>
                    <span class="fd-tag fd-tag-warning">Departures Today</span>
                </div>
                <div class="fd-card-body">
                    <div class="fd-filter-row">
                        <div class="search-box">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.5" />
                                <path d="M11 11L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                            <input
                                type="text"
                                v-model="expressCheckoutSearch"
                                placeholder="Search by ID, guest name, email, or room number..."
                                @input="filterExpressCheckouts"
                            />
                        </div>
                    </div>

                    <div v-if="loadingExpress" class="loading-state">
                        <p>Loading departures...</p>
                    </div>
                    <div v-else-if="filteredExpressCheckouts.length === 0" class="empty-state">
                        <p>No departures for today</p>
                    </div>
                    <div v-else class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="reservation in filteredExpressCheckouts" :key="reservation.id">
                                    <td>#{{ reservation.id }}</td>
                                    <td>
                                        <div class="guest-info">
                                            <strong>{{ reservation.guest_name }}</strong>
                                            <small v-if="reservation.guest_email">{{ reservation.guest_email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ reservation.room?.room_number }}</strong>
                                        <small>{{ reservation.room?.room_type?.name }}</small>
                                    </td>
                                    <td>{{ formatDate(reservation.check_in_date) }}</td>
                                    <td>{{ formatDate(reservation.check_out_date) }}</td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-primary"
                                            @click="expressCheckOut(reservation)"
                                            :disabled="actingOnId === reservation.id"
                                        >
                                            {{ actingOnId === reservation.id ? 'Checking out...' : 'Express Check-out' }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Key & Deposit Handling -->
            <section class="fd-card fd-card-wide">
                <div class="fd-card-header">
                    <h2>Key Issuance & Security Deposit</h2>
                    <span class="fd-tag fd-tag-info">In-house Guests</span>
                </div>
                <div class="fd-card-body">
                    <div v-if="loadingInHouse" class="loading-state">
                        <p>Loading in-house guests...</p>
                    </div>
                    <div v-else-if="inHouseReservations.length === 0" class="empty-state">
                        <p>No in-house guests at the moment</p>
                    </div>
                    <div v-else class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Deposit</th>
                                    <th>Key Type</th>
                                    <th>Key Issued</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="reservation in inHouseReservations" :key="reservation.id">
                                    <td>
                                        <div class="guest-info">
                                            <strong>{{ reservation.guest_name }}</strong>
                                            <small v-if="reservation.guest_email">{{ reservation.guest_email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ reservation.room?.room_number }}</strong>
                                        <small>{{ reservation.room?.room_type?.name }}</small>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            v-model.number="frontDeskState[reservation.id].deposit"
                                            class="fd-input"
                                        />
                                    </td>
                                    <td>
                                        <select
                                            v-model="frontDeskState[reservation.id].keyType"
                                            class="fd-select"
                                        >
                                            <option value="">Select</option>
                                            <option value="manual">Manual</option>
                                            <option value="digital">Digital</option>
                                        </select>
                                    </td>
                                    <td>
                                        <span
                                            v-if="frontDeskState[reservation.id].keyIssued"
                                            class="badge badge-success"
                                        >
                                            Issued
                                        </span>
                                        <span
                                            v-else
                                            class="badge badge-secondary"
                                        >
                                            Pending
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button
                                                class="btn btn-sm btn-outline"
                                                @click="markKeyIssued(reservation)"
                                            >
                                                {{ frontDeskState[reservation.id].keyIssued ? 'Mark Pending' : 'Mark Issued' }}
                                            </button>
                                            <button
                                                class="btn btn-sm btn-primary"
                                                @click="saveDeposit(reservation)"
                                                :disabled="savingDepositId === reservation.id"
                                            >
                                                {{ savingDepositId === reservation.id ? 'Saving...' : 'Save Deposit' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Charges & Bill Preview -->
            <section class="fd-card fd-card-wide">
                <div class="fd-card-header">
                    <h2>Charges & Guest Bill Preview</h2>
                    <span class="fd-tag fd-tag-dark">Any Reservation</span>
                </div>
                <div class="fd-card-body fd-bill-layout">
                    <div class="fd-bill-left">
                        <div class="form-group">
                            <label>Select Reservation</label>
                            <select
                                v-model="selectedBillReservationId"
                                class="fd-select"
                                @change="loadBillPreview"
                            >
                                <option value="">Select reservation...</option>
                                <option
                                    v-for="reservation in recentReservations"
                                    :key="reservation.id"
                                    :value="reservation.id"
                                >
                                    #{{ reservation.id }} - {{ reservation.guest_name }} ({{ reservation.room?.room_number }})
                                </option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Late Checkout Charges</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    v-model.number="lateCheckoutCharge"
                                    class="fd-input"
                                />
                            </div>
                            <div class="form-group">
                                <label>Extra Bed Charges</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    v-model.number="extraBedCharge"
                                    class="fd-input"
                                />
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Other Charges / Adjustments</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                v-model.number="otherCharges"
                                class="fd-input"
                            />
                        </div>

                        <div class="form-group">
                            <label>Notes (will appear on bill preview)</label>
                            <textarea
                                v-model="billNotes"
                                rows="3"
                                class="fd-textarea"
                                placeholder="E.g., Late checkout approved till 2 PM, extra bed for child..."
                            ></textarea>
                        </div>

                        <div class="fd-actions">
                            <button
                                class="btn btn-primary"
                                @click="loadBillPreview"
                                :disabled="!selectedBillReservationId || loadingBill"
                            >
                                {{ loadingBill ? 'Loading Bill...' : 'Preview Guest Bill' }}
                            </button>
                        </div>
                    </div>

                    <div class="fd-bill-right" v-if="billPreview">
                        <div class="bill-card">
                            <div class="bill-header">
                                <div>
                                    <h3>Guest Bill Preview</h3>
                                    <p>#{{ billPreview.reservation.id }} • {{ billPreview.reservation.guest_name }}</p>
                                </div>
                                <div class="bill-status">
                                    <span class="badge" :class="getStatusClass(billPreview.reservation.status)">
                                        {{ billPreview.reservation.status }}
                                    </span>
                                </div>
                            </div>

                            <div class="bill-body">
                                <div class="bill-row">
                                    <span>Room Charges</span>
                                    <span>${{ billPreview.room_amount.toFixed(2) }}</span>
                                </div>
                                <div class="bill-row" v-if="billPreview.extra_bed_fee > 0">
                                    <span>Extra Bed Charges</span>
                                    <span>${{ billPreview.extra_bed_fee.toFixed(2) }}</span>
                                </div>
                                <div class="bill-row" v-if="billPreview.late_checkout_fee > 0">
                                    <span>Late Checkout Charges</span>
                                    <span>${{ billPreview.late_checkout_fee.toFixed(2) }}</span>
                                </div>
                                <div class="bill-row" v-if="billPreview.other_charges > 0">
                                    <span>Other Charges</span>
                                    <span>${{ billPreview.other_charges.toFixed(2) }}</span>
                                </div>
                                <div class="bill-row bill-row-muted" v-if="billPreview.deposit > 0">
                                    <span>Security Deposit (Paid)</span>
                                    <span>- ${{ billPreview.deposit.toFixed(2) }}</span>
                                </div>
                                <div class="bill-divider"></div>
                                <div class="bill-row bill-total">
                                    <span>Total Payable</span>
                                    <span>${{ billPreview.total_payable.toFixed(2) }}</span>
                                </div>
                            </div>

                            <div v-if="billPreview.notes" class="bill-notes">
                                <h4>Notes</h4>
                                <p>{{ billPreview.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="fd-bill-right empty" v-else>
                        <p>Select a reservation and click "Preview Guest Bill" to see a detailed breakdown.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

export default {
    name: 'FrontDeskOperations',
    setup() {
        const today = new Date().toISOString().split('T')[0];

        const quickCheckins = ref([]);
        const expressCheckouts = ref([]);
        const inHouseReservations = ref([]);
        const recentReservations = ref([]);

        const loadingQuick = ref(false);
        const loadingExpress = ref(false);
        const loadingInHouse = ref(false);
        const loadingBill = ref(false);

        const quickCheckinSearch = ref('');
        const expressCheckoutSearch = ref('');

        const filteredQuickCheckins = ref([]);
        const filteredExpressCheckouts = ref([]);

        const actingOnId = ref(null);
        const savingDepositId = ref(null);

        const frontDeskState = ref({});

        const selectedBillReservationId = ref('');
        const lateCheckoutCharge = ref(0);
        const extraBedCharge = ref(0);
        const otherCharges = ref(0);
        const billNotes = ref('');
        const billPreview = ref(null);

        const initializeFrontDeskState = (reservations) => {
            reservations.forEach(res => {
                if (!frontDeskState.value[res.id]) {
                    frontDeskState.value[res.id] = {
                        deposit: res.checkin?.deposit || 0,
                        keyType: '',
                        keyIssued: false,
                    };
                }
            });
        };

        const fetchQuickCheckins = async () => {
            loadingQuick.value = true;
            try {
                const response = await axios.get('/api/reservations', {
                    params: {
                        status: 'confirmed',
                        check_in_date: today,
                    }
                });
                if (response.data.success) {
                    const data = response.data.data.data || response.data.data || [];
                    quickCheckins.value = data;
                    filteredQuickCheckins.value = data;
                }
            } catch (error) {
                console.error('Error loading arrivals:', error);
            } finally {
                loadingQuick.value = false;
            }
        };

        const fetchExpressCheckouts = async () => {
            loadingExpress.value = true;
            try {
                const response = await axios.get('/api/reservations', {
                    params: {
                        status: 'checked_in',
                    }
                });
                if (response.data.success) {
                    const data = response.data.data.data || response.data.data || [];
                    // Filter by check-out date today or earlier
                    const todayDate = new Date(today);
                    expressCheckouts.value = data.filter(res => {
                        if (!res.check_out_date) return false;
                        const d = new Date(res.check_out_date);
                        return d <= todayDate;
                    });
                    filteredExpressCheckouts.value = expressCheckouts.value;
                }
            } catch (error) {
                console.error('Error loading departures:', error);
            } finally {
                loadingExpress.value = false;
            }
        };

        const fetchInHouse = async () => {
            loadingInHouse.value = true;
            try {
                const response = await axios.get('/api/reservations', {
                    params: {
                        status: 'checked_in',
                    }
                });
                if (response.data.success) {
                    const data = response.data.data.data || response.data.data || [];
                    inHouseReservations.value = data;
                    initializeFrontDeskState(data);
                }
            } catch (error) {
                console.error('Error loading in-house reservations:', error);
            } finally {
                loadingInHouse.value = false;
            }
        };

        const fetchRecentReservations = async () => {
            try {
                const response = await axios.get('/api/reservations', {
                    params: {
                        // No filters, just first page ordered by date
                    }
                });
                if (response.data.success) {
                    const data = response.data.data.data || response.data.data || [];
                    recentReservations.value = data;
                }
            } catch (error) {
                console.error('Error loading reservations for bill preview:', error);
            }
        };

        const filterList = (list, search) => {
            if (!search) return list;
            const term = search.toLowerCase().trim();
            return list.filter(res => {
                const id = String(res.id || '').toLowerCase();
                const guest = (res.guest_name || '').toLowerCase();
                const email = (res.guest_email || '').toLowerCase();
                const room = (res.room?.room_number || '').toLowerCase();
                const roomType = (res.room?.room_type?.name || '').toLowerCase();
                return id.includes(term) || 
                       guest.includes(term) || 
                       email.includes(term) || 
                       room.includes(term) ||
                       roomType.includes(term);
            });
        };

        const filterQuickCheckins = () => {
            filteredQuickCheckins.value = filterList(quickCheckins.value, quickCheckinSearch.value);
        };

        const filterExpressCheckouts = () => {
            filteredExpressCheckouts.value = filterList(expressCheckouts.value, expressCheckoutSearch.value);
        };

        const quickCheckIn = async (reservation) => {
            if (!confirm(`Quick check-in guest ${reservation.guest_name}?`)) return;
            actingOnId.value = reservation.id;
            try {
                await axios.post(`/api/reservations/${reservation.id}/check-in`);
                await Promise.all([
                    fetchQuickCheckins(),
                    fetchInHouse(),
                    fetchExpressCheckouts(),
                ]);
            } catch (error) {
                console.error('Error during quick check-in:', error);
                alert(error.response?.data?.message || 'Failed to check in guest');
            } finally {
                actingOnId.value = null;
            }
        };

        const expressCheckOut = async (reservation) => {
            if (!confirm(`Express check-out guest ${reservation.guest_name}?`)) return;
            actingOnId.value = reservation.id;
            try {
                await axios.post(`/api/reservations/${reservation.id}/check-out`);
                await Promise.all([
                    fetchExpressCheckouts(),
                    fetchInHouse(),
                    fetchRecentReservations(),
                ]);
            } catch (error) {
                console.error('Error during express check-out:', error);
                alert(error.response?.data?.message || 'Failed to check out guest');
            } finally {
                actingOnId.value = null;
            }
        };

        const markKeyIssued = (reservation) => {
            const state = frontDeskState.value[reservation.id];
            if (!state) return;
            state.keyIssued = !state.keyIssued;
        };

        const saveDeposit = async (reservation) => {
            const state = frontDeskState.value[reservation.id];
            if (!state) return;
            savingDepositId.value = reservation.id;
            try {
                await axios.post(`/api/reservations/${reservation.id}/deposit`, {
                    deposit: state.deposit || 0,
                });
                alert('Security deposit updated successfully');
                await Promise.all([
                    fetchInHouse(),
                    loadBillPreview(),
                ]);
            } catch (error) {
                console.error('Error saving deposit:', error);
                alert(error.response?.data?.message || 'Failed to save deposit');
            } finally {
                savingDepositId.value = null;
            }
        };

        const loadBillPreview = async () => {
            if (!selectedBillReservationId.value) {
                billPreview.value = null;
                return;
            }
            loadingBill.value = true;
            try {
                const response = await axios.get(`/api/reservations/${selectedBillReservationId.value}/bill-preview`, {
                    params: {
                        late_checkout_fee: lateCheckoutCharge.value || 0,
                        extra_bed_fee: extraBedCharge.value || 0,
                        other_charges: otherCharges.value || 0,
                        notes: billNotes.value || '',
                    }
                });
                if (response.data.success) {
                    billPreview.value = response.data.data;
                }
            } catch (error) {
                console.error('Error loading bill preview:', error);
                alert(error.response?.data?.message || 'Failed to load bill preview');
            } finally {
                loadingBill.value = false;
            }
        };

        const formatDate = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        };

        const getStatusClass = (status) => {
            const classes = {
                pending: 'badge-warning',
                confirmed: 'badge-info',
                checked_in: 'badge-success',
                checked_out: 'badge-secondary',
                cancelled: 'badge-danger',
            };
            return classes[status] || 'badge-secondary';
        };

        onMounted(async () => {
            await Promise.all([
                fetchQuickCheckins(),
                fetchExpressCheckouts(),
                fetchInHouse(),
                fetchRecentReservations(),
            ]);
        });

        return {
            quickCheckins,
            expressCheckouts,
            inHouseReservations,
            recentReservations,
            loadingQuick,
            loadingExpress,
            loadingInHouse,
            loadingBill,
            quickCheckinSearch,
            expressCheckoutSearch,
            filteredQuickCheckins,
            filteredExpressCheckouts,
            actingOnId,
            savingDepositId,
            frontDeskState,
            selectedBillReservationId,
            lateCheckoutCharge,
            extraBedCharge,
            otherCharges,
            billNotes,
            billPreview,
            filterQuickCheckins,
            filterExpressCheckouts,
            quickCheckIn,
            expressCheckOut,
            markKeyIssued,
            saveDeposit,
            loadBillPreview,
            formatDate,
            getStatusClass,
        };
    },
};
</script>

<style scoped>
.front-desk-page {
    max-width: 1600px;
    margin: 0 auto;
    padding: 0 20px 40px;
}

.page-header {
    margin-bottom: 24px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 4px;
}

.page-subtitle {
    font-size: 14px;
    color: #718096;
}

.fd-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.fd-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    padding: 18px 20px 20px;
    border: 1px solid #e2e8f0;
}

.fd-card-wide {
    grid-column: span 2;
}

.fd-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.fd-card-header h2 {
    font-size: 18px;
    font-weight: 600;
    color: #1a202c;
}

.fd-tag {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 4px 10px;
    border-radius: 999px;
    background: #edf2ff;
    color: #4c6fff;
    font-weight: 600;
}

.fd-tag-warning {
    background: #fffaf0;
    color: #d97706;
}

.fd-tag-info {
    background: #e0f2fe;
    color: #0369a1;
}

.fd-tag-dark {
    background: #e5e7eb;
    color: #111827;
}

.fd-card-body {
    margin-top: 8px;
}

.fd-filter-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    gap: 12px;
}

.search-box {
    position: relative;
    flex: 1;
}

.search-box svg {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
}

.search-box input {
    width: 100%;
    padding: 8px 10px 8px 30px;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    font-size: 13px;
    outline: none;
    transition: all 0.15s ease;
}

.search-box input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 1px rgba(102, 126, 234, 0.3);
}

.table-container {
    width: 100%;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.data-table th,
.data-table td {
    padding: 8px 10px;
    text-align: left;
    border-bottom: 1px solid #edf2f7;
}

.data-table th {
    font-weight: 600;
    color: #4a5568;
    background: #f7fafc;
}

.guest-info strong {
    display: block;
    color: #1a202c;
}

.guest-info small {
    display: block;
    color: #718096;
}

.loading-state,
.empty-state {
    padding: 12px;
    text-align: center;
    color: #718096;
    font-size: 13px;
}

.fd-input,
.fd-select,
.fd-textarea {
    width: 100%;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 8px 10px;
    font-size: 13px;
    outline: none;
    transition: all 0.15s ease;
}

.fd-input:focus,
.fd-select:focus,
.fd-textarea:focus {
    border-color: #4c6fff;
    box-shadow: 0 0 0 1px rgba(76, 111, 255, 0.2);
}

.fd-textarea {
    resize: vertical;
}

.form-group {
    margin-bottom: 12px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #4a5568;
    margin-bottom: 4px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.action-buttons {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    border: none;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 11px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #ffffff;
}

.btn-primary:hover {
    opacity: 0.95;
}

.btn-success {
    background: #16a34a;
    color: #ffffff;
}

.btn-success:hover {
    background: #15803d;
}

.btn-outline {
    background: transparent;
    color: #4a5568;
    border: 1px solid #cbd5e0;
}

.btn-outline:hover {
    background: #f7fafc;
}

.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

.badge-secondary {
    background: #e5e7eb;
    color: #374151;
}

.badge-warning {
    background: #fef9c3;
    color: #854d0e;
}

.badge-info {
    background: #e0f2fe;
    color: #0369a1;
}

.badge-danger {
    background: #fee2e2;
    color: #b91c1c;
}

.fd-bill-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
    gap: 16px;
    align-items: flex-start;
}

.fd-bill-right.empty {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #718096;
    font-size: 13px;
    padding: 16px;
    border-radius: 10px;
    background: #f7fafc;
}

.bill-card {
    border-radius: 12px;
    background: radial-gradient(circle at top left, #fef3c7 0, #fff 45%);
    border: 1px solid #e5e7eb;
    padding: 14px 16px 16px;
}

.bill-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.bill-header h3 {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 2px;
}

.bill-header p {
    margin: 0;
    font-size: 12px;
    color: #4b5563;
}

.bill-body {
    font-size: 13px;
}

.bill-row {
    display: flex;
    justify-content: space-between;
    padding: 3px 0;
}

.bill-row-muted {
    color: #6b7280;
}

.bill-divider {
    height: 1px;
    background: linear-gradient(to right, transparent, #d1d5db, transparent);
    margin: 8px 0;
}

.bill-total span:last-child {
    font-weight: 700;
    font-size: 15px;
}

.bill-notes {
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px dashed #d1d5db;
    font-size: 12px;
    color: #4b5563;
}

.bill-notes h4 {
    margin: 0 0 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.fd-actions {
    margin-top: 8px;
}

@media (max-width: 1024px) {
    .fd-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    .fd-card-wide {
        grid-column: span 1;
    }

    .fd-bill-layout {
        grid-template-columns: minmax(0, 1fr);
    }
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: minmax(0, 1fr);
    }
}
</style>

