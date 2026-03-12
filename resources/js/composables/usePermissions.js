import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Composable for checking user permissions
 */
export function usePermissions() {
    // Cache for user data to avoid repeated API calls
    let cachedUserData = null;
    let cacheTimestamp = null;
    const CACHE_DURATION = 60000; // 1 minute cache
    let isLoading = false;
    let loadPromise = null;

    // Get user data from session via API
    const getUserData = async () => {
        // Return cached data if still valid
        if (cachedUserData && cacheTimestamp && (Date.now() - cacheTimestamp) < CACHE_DURATION) {
            return cachedUserData;
        }

        // If already loading, return the existing promise
        if (isLoading && loadPromise) {
            return loadPromise;
        }

        isLoading = true;
        loadPromise = (async () => {
            try {
                const response = await axios.get('/api/auth/check');
                // #region agent log
                fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'usePermissions.js:getUserData',message:'API response received',data:{success:response.data.success,authenticated:response.data.authenticated,hasUser:!!response.data.user,userRole:response.data.user?.role,userPermissions:response.data.user?.permissions},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
                // #endregion
                if (response.data.success && response.data.authenticated && response.data.user) {
                    cachedUserData = response.data.user;
                    cacheTimestamp = Date.now();
                    // #region agent log
                    fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'usePermissions.js:getUserData',message:'User data cached',data:{role:cachedUserData.role,permissionsCount:cachedUserData.permissions?.length||0,permissions:cachedUserData.permissions},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
                    // #endregion
                    isLoading = false;
                    loadPromise = null;
                    return cachedUserData;
                }
            } catch (error) {
                console.error('Error getting user data from session:', error);
                // #region agent log
                fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'usePermissions.js:getUserData',message:'Error loading user data',data:{error:error.message},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
                // #endregion
            }
            
            cachedUserData = null;
            cacheTimestamp = null;
            isLoading = false;
            loadPromise = null;
            return null;
        })();

        return loadPromise;
    };

    // Synchronous version for backward compatibility (uses cache)
    const getUserDataSync = () => {
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'usePermissions.js:getUserDataSync',message:'Getting cached user data',data:{hasCache:!!cachedUserData,role:cachedUserData?.role,permissionsCount:cachedUserData?.permissions?.length||0},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
        // #endregion
        return cachedUserData;
    };

    // Get user permissions (async)
    const getUserPermissions = async () => {
        const user = await getUserData();
        if (!user) return [];
        
        // Super admin has all permissions
        if (user.role === 'super_admin') {
            return ['all'];
        }
        
        return user.permissions || [];
    };

    // Get user permissions (sync - uses cache)
    const getUserPermissionsSync = () => {
        const user = getUserDataSync();
        if (!user) return [];
        
        // Super admin has all permissions
        if (user.role === 'super_admin') {
            return ['all'];
        }
        
        return user.permissions || [];
    };

    // Check if user has a specific permission (async)
    const hasPermission = async (permission) => {
        const user = await getUserData();
        if (!user) return false;
        
        // Super admin has all permissions
        if (user.role === 'super_admin') {
            return true;
        }
        
        const permissions = user.permissions || [];
        return permissions.includes(permission) || permissions.includes('all');
    };

    // Check if user has a specific permission (sync - uses cache for immediate checks)
    const hasPermissionSync = (permission) => {
        const user = getUserDataSync();
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'usePermissions.js:hasPermissionSync',message:'Checking permission',data:{permission,hasUser:!!user,userRole:user?.role,userPermissions:user?.permissions,permissionsCount:user?.permissions?.length||0},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        if (!user) {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'usePermissions.js:hasPermissionSync',message:'No user data - returning false',data:{permission},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            return false;
        }
        
        // Super admin has all permissions
        if (user.role === 'super_admin') {
            // #region agent log
            fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'usePermissions.js:hasPermissionSync',message:'Super admin - returning true',data:{permission},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            return true;
        }
        
        const permissions = user.permissions || [];
        const hasPermission = permissions.includes(permission) || permissions.includes('all');
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'usePermissions.js:hasPermissionSync',message:'Permission check result',data:{permission,hasPermission,permissions},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        return hasPermission;
    };

    // Check if user has any of the given permissions
    const hasAnyPermission = async (permissionList) => {
        if (!Array.isArray(permissionList) || permissionList.length === 0) {
            return true; // No restrictions
        }
        
        const user = await getUserData();
        if (!user) return false;
        
        // Super admin has all permissions
        if (user.role === 'super_admin') {
            return true;
        }
        
        const permissions = user.permissions || [];
        return permissionList.some(perm => permissions.includes(perm)) || permissions.includes('all');
    };

    // Check if user has all of the given permissions
    const hasAllPermissions = async (permissionList) => {
        if (!Array.isArray(permissionList) || permissionList.length === 0) {
            return true; // No restrictions
        }
        
        const user = await getUserData();
        if (!user) return false;
        
        // Super admin has all permissions
        if (user.role === 'super_admin') {
            return true;
        }
        
        const permissions = user.permissions || [];
        return permissionList.every(perm => permissions.includes(perm)) || permissions.includes('all');
    };

    // Get current user role
    const getUserRole = async () => {
        const user = await getUserData();
        return user ? user.role : null;
    };

    // Check if user is super admin (computed - uses cache)
    const isSuperAdmin = computed(() => {
        const user = getUserDataSync();
        const result = user && user.role === 'super_admin';
        // #region agent log
        fetch('http://127.0.0.1:7245/ingest/9401a1a8-1208-480e-b431-a6361cb9534c',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'usePermissions.js:isSuperAdmin',message:'Checking super admin',data:{hasUser:!!user,userRole:user?.role,isSuperAdmin:result},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
        // #endregion
        return result;
    });

    return {
        getUserData,
        getUserDataSync,
        getUserPermissions,
        getUserPermissionsSync,
        hasPermission,
        hasPermissionSync,
        hasAnyPermission,
        hasAllPermissions,
        getUserRole,
        isSuperAdmin
    };
}
