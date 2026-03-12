<template>
    <div class="staff-profiles-page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Staff Profiles</h1>
                <p class="page-subtitle">Manage staff member profiles and information</p>
            </div>
            <button class="btn btn-primary" @click="openCreateModal">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Add Staff Profile
            </button>
        </div>

        <div class="filters-section">
            <div class="search-box">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2"/>
                    <path d="M13 13L16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input type="text" v-model="searchQuery" placeholder="Search by name, employee ID..." @input="filterProfiles" />
            </div>
        </div>
        
        <div class="content-card">
            <div v-if="loading" class="loading-state">Loading profiles...</div>
            <div v-else-if="filteredProfiles.length === 0" class="empty-state">No profiles found</div>
            <div v-else>
                <div class="table-header">
                    <div class="record-count">
                        <span class="count-label">Showing:</span>
                        <span class="count-value">{{ startIndex }}-{{ endIndex }} of {{ filteredProfiles.length }}</span>
                    </div>
                </div>
                <div class="inventory-table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Hire Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="profile in paginatedProfiles" :key="profile.id">
                                <td>{{ profile.employee_id || '—' }}</td>
                                <td>{{ profile.user?.name || '—' }}</td>
                                <td>{{ profile.role?.name || '—' }}</td>
                                <td>{{ profile.phone || '—' }}</td>
                                <td>{{ formatDate(profile.hire_date) }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-btn" @click="openEditModal(profile)" title="Edit">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M11.3333 2.00001C11.5084 1.82489 11.7163 1.68603 11.9447 1.59128C12.1731 1.49654 12.4173 1.44775 12.6667 1.44775C12.916 1.44775 13.1602 1.49654 13.3886 1.59128C13.617 1.68603 13.8249 1.82489 14 2.00001C14.1751 2.17513 14.314 2.38305 14.4087 2.61143C14.5035 2.83981 14.5523 3.08405 14.5523 3.33334C14.5523 3.58263 14.5035 3.82687 14.4087 4.05525C14.314 4.28363 14.1751 4.49155 14 4.66667L5 13.6667L1.33333 14.6667L2.33333 11L11.3333 2.00001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button class="icon-btn icon-btn-danger" @click="confirmDelete(profile)" title="Delete">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M2 4H14M12.6667 4V13.3333C12.6667 13.687 12.5262 14.0263 12.2761 14.2764C12.026 14.5265 11.6867 14.6667 11.3333 14.6667H4.66667C4.31305 14.6667 3.97391 14.5265 3.72381 14.2764C3.47371 14.0263 3.33333 13.687 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2.31305 5.47371 1.97391 5.72381 1.72381C5.97391 1.47371 6.31305 1.33333 6.66667 1.33333H9.33333C9.68696 1.33333 10.0261 1.47371 10.2762 1.72381C10.5263 1.97391 10.6667 2.31305 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div v-if="totalPages > 1" class="pagination-wrapper">
                    <div class="pagination-info"><span>Page {{ currentPage }} of {{ totalPages }}</span></div>
                    <div class="pagination-controls">
                        <button class="pagination-btn" @click="prevPage" :disabled="currentPage === 1">Previous</button>
                        <div class="pagination-numbers">
                            <button v-for="page in totalPages" :key="page" class="pagination-number" :class="{ active: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
                        </div>
                        <button class="pagination-btn" @click="nextPage" :disabled="currentPage === totalPages">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingProfile ? 'Edit Profile' : 'Add Profile' }}</h2>
                    <button class="modal-close" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="saveProfile" class="modal-body">
                    <div class="form-group">
                        <label>User *</label>
                        <select v-model="form.user_id" class="form-control" required>
                            <option value="">Select User</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select v-model="form.role_id" class="form-control" required>
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Employee ID</label>
                        <input type="text" v-model="form.employee_id" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" v-model="form.phone" class="form-control" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-content modal-sm" @click.stop>
                <div class="modal-header">
                    <h2>Delete Profile</h2>
                    <button class="modal-close" @click="closeDeleteModal">×</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this profile?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeDeleteModal">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="deleteProfile" :disabled="deleting">{{ deleting ? 'Deleting...' : 'Delete' }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAlert } from '../../composables/useAlert';
import { formatDate } from '../../utils/dateFormatter';

export default {
    name: 'StaffProfiles',
    setup() {
        const { success: showSuccess, error: showError } = useAlert();
        const loading = ref(false);
        const saving = ref(false);
        const deleting = ref(false);
        const profiles = ref([]);
        const users = ref([]);
        const roles = ref([]);
        const searchQuery = ref('');
        const showModal = ref(false);
        const showDeleteModal = ref(false);
        const editingProfile = ref(null);
        const profileToDelete = ref(null);
        const currentPage = ref(1);
        const itemsPerPage = ref(10);

        const form = ref({
            user_id: '',
            role_id: '',
            employee_id: '',
            phone: '',
            address: '',
            hire_date: '',
            birth_date: '',
            gender: '',
            emergency_contact_name: '',
            emergency_contact_phone: '',
            notes: ''
        });

        const filteredProfiles = computed(() => {
            if (!searchQuery.value) return profiles.value;
            const query = searchQuery.value.toLowerCase();
            return profiles.value.filter(p => 
                (p.user?.name || '').toLowerCase().includes(query) ||
                (p.employee_id || '').toLowerCase().includes(query)
            );
        });

        const totalPages = computed(() => Math.ceil(filteredProfiles.value.length / itemsPerPage.value));
        const paginatedProfiles = computed(() => {
            const start = (currentPage.value - 1) * itemsPerPage.value;
            return filteredProfiles.value.slice(start, start + itemsPerPage.value);
        });
        const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value + 1);
        const endIndex = computed(() => Math.min(currentPage.value * itemsPerPage.value, filteredProfiles.value.length));

        const goToPage = (page) => { if (page >= 1 && page <= totalPages.value) currentPage.value = page; };
        const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
        const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
        const filterProfiles = () => { currentPage.value = 1; };

        const fetchData = async () => {
            loading.value = true;
            try {
                const [profilesRes, usersRes, rolesRes] = await Promise.all([
                    window.axios.get('/api/staff-profiles'),
                    window.axios.get('/api/users'),
                    window.axios.get('/api/roles')
                ]);
                profiles.value = profilesRes.data || [];
                users.value = usersRes.data?.data || usersRes.data || [];
                roles.value = rolesRes.data?.data || rolesRes.data || [];
            } catch (error) {
                console.error('Error fetching data:', error);
                showError('Failed to load data');
            } finally {
                loading.value = false;
            }
        };

        const openCreateModal = () => {
            editingProfile.value = null;
            form.value = { user_id: '', role_id: '', employee_id: '', phone: '', address: '', hire_date: '', birth_date: '', gender: '', emergency_contact_name: '', emergency_contact_phone: '', notes: '' };
            showModal.value = true;
        };

        const openEditModal = (profile) => {
            editingProfile.value = profile;
            form.value = { ...profile };
            showModal.value = true;
        };

        const closeModal = () => {
            showModal.value = false;
            editingProfile.value = null;
        };

        const saveProfile = async () => {
            saving.value = true;
            try {
                if (editingProfile.value) {
                    await window.axios.post(`/api/staff-profiles/${editingProfile.value.id}`, form.value);
                    showSuccess('Profile updated successfully!');
                } else {
                    await window.axios.post('/api/staff-profiles', form.value);
                    showSuccess('Profile created successfully!');
                }
                window.location.reload();
            } catch (error) {
                showError(error.response?.data?.message || 'Failed to save profile');
                saving.value = false;
            }
        };

        const confirmDelete = (profile) => {
            profileToDelete.value = profile;
            showDeleteModal.value = true;
        };

        const closeDeleteModal = () => {
            showDeleteModal.value = false;
            profileToDelete.value = null;
        };

        const deleteProfile = async () => {
            deleting.value = true;
            try {
                await window.axios.post(`/api/staff-profiles/${profileToDelete.value.id}/delete`);
                showSuccess('Profile deleted successfully!');
                window.location.reload();
            } catch (error) {
                showError('Failed to delete profile');
                deleting.value = false;
            }
        };

        onMounted(() => fetchData());

        return {
            loading, saving, deleting, profiles, users, roles, searchQuery, showModal, showDeleteModal,
            editingProfile, profileToDelete, form, filteredProfiles, paginatedProfiles, totalPages,
            currentPage, startIndex, endIndex, formatDate, goToPage, nextPage, prevPage, filterProfiles,
            openCreateModal, openEditModal, closeModal, saveProfile, confirmDelete, closeDeleteModal, deleteProfile
        };
    }
}
</script>

<style scoped>
.staff-profiles-page { padding: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-title { font-size: 24px; font-weight: 600; color: #2c3e50; margin: 0 0 4px 0; }
.page-subtitle { font-size: 14px; color: #718096; margin: 0; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
.btn-primary { background: #667eea; color: white; }
.btn-secondary { background: #e2e8f0; color: #4a5568; }
.btn-danger { background: #e53e3e; color: white; }
.filters-section { display: flex; gap: 16px; margin-bottom: 24px; }
.search-box { flex: 1; display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; }
.search-box input { flex: 1; border: none; outline: none; font-size: 14px; }
.content-card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px; }
.loading-state, .empty-state { text-align: center; padding: 48px; color: #718096; }
.table-header { display: flex; justify-content: space-between; margin-bottom: 16px; }
.inventory-table-wrapper { overflow-x: auto; }
.inventory-table { width: 100%; border-collapse: collapse; }
.inventory-table th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #4a5568; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
.inventory-table td { padding: 16px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
.action-buttons { display: flex; gap: 8px; }
.icon-btn { background: none; border: none; cursor: pointer; padding: 6px; color: #718096; border-radius: 4px; }
.icon-btn:hover { background: #edf2f7; }
.icon-btn-danger:hover { background: #fed7d7; color: #c53030; }
.pagination-wrapper { display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
.pagination-controls { display: flex; align-items: center; gap: 8px; }
.pagination-btn { padding: 8px 16px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; }
.pagination-number { min-width: 36px; height: 36px; padding: 0 8px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; }
.pagination-number.active { background: #667eea; color: white; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: white; border-radius: 8px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
.modal-sm { max-width: 400px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #e2e8f0; }
.modal-header h2 { font-size: 20px; font-weight: 600; margin: 0; }
.modal-close { background: none; border: none; cursor: pointer; font-size: 24px; color: #718096; }
.modal-body { padding: 24px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; }
.form-control { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #e2e8f0; margin-top: 24px; }
</style>
