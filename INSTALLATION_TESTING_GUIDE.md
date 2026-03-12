# Installation System Testing Guide

## Pre-Testing Checklist

### 1. Verify Old Code Integrity
- ✅ All existing routes remain unchanged
- ✅ No modifications to existing controllers
- ✅ No changes to existing Vue components
- ✅ Database migrations remain intact

### 2. Installation Routes Verification
- ✅ `/install` - Installation page
- ✅ `/api/install/check` - Check installation status
- ✅ `/api/install/check-database` - Test database connection
- ✅ `/api/install/run-migrations` - Run database migrations
- ✅ `/api/install/create-super-admin` - Create super admin
- ✅ `/api/install/complete` - Complete installation

## Testing Steps

### Test 1: Verify Installation Page Access
**Steps:**
1. Navigate to `http://your-domain/install` in browser
2. Should see installation wizard interface
3. Should show "Step 1 of 4" with progress bar at 25%

**Expected Result:**
- Installation page loads without errors
- Progress bar shows 25% (1/4 steps)
- Database configuration form is visible

---

### Test 2: Database Connection Test
**Steps:**
1. Fill in database credentials:
   - Host: `127.0.0.1` (or your DB host)
   - Port: `3306` (or your DB port)
   - Database: `your_database_name`
   - Username: `your_db_username`
   - Password: `your_db_password`
2. Click "Test Connection"

**Expected Result:**
- If credentials are correct: Move to Step 2
- If credentials are wrong: Show error message
- Progress bar should update to 50% when moving to Step 2

---

### Test 3: Database Migration Test
**Steps:**
1. After successful database connection, you should be on Step 2
2. Click "Create Database Tables"
3. Wait for migration to complete

**Expected Result:**
- Loading spinner appears
- Migration runs successfully
- Success message displayed
- Automatically moves to Step 3 after 1.5 seconds
- Progress bar shows 75% (3/4 steps)
- All database tables are created

**Verify Database:**
- Check your database - all tables should be created
- Verify `users`, `roles`, `permissions` tables exist
- Verify all other migration tables exist

---

### Test 4: Super Admin Creation Test
**Steps:**
1. Fill in super admin details:
   - Name: `Admin User`
   - Email: `admin@example.com`
   - Password: `password123` (min 8 characters)
   - Confirm Password: `password123`
2. Click "Create Super Admin"

**Expected Result:**
- Super admin user created successfully
- Success message displayed
- Automatically moves to Step 4
- Progress bar shows 100% (4/4 steps)

**Verify Database:**
- Check `users` table - should have 1 user
- Check `roles` table - should have `super_admin` role
- Verify user has `role_id` pointing to super_admin role

---

### Test 5: Installation Completion Test
**Steps:**
1. Should see "Installation Complete" screen
2. Click "Go to Login Page"

**Expected Result:**
- Redirects to `/login` page
- Installation lock file created at `storage/app/installed.lock`
- Summary shows all completed steps

---

### Test 6: Re-Installation Prevention Test
**Steps:**
1. After installation, try accessing `/install` again
2. Should automatically redirect to login

**Expected Result:**
- Cannot access installation page after completion
- Redirects to login page
- API endpoint `/api/install/check` returns `installed: true`

---

### Test 7: Existing Functionality Test
**Critical: Verify these still work after installation**

#### 7.1 Authentication
- ✅ Login page loads: `/login`
- ✅ Super admin login works: `/super-admin/login`
- ✅ Can login with created super admin credentials
- ✅ Session persists after login

#### 7.2 Dashboard
- ✅ Dashboard loads after login
- ✅ All dashboard sections display correctly
- ✅ Navigation menu works

#### 7.3 API Endpoints
Test a few key endpoints:
- ✅ `GET /api/auth/check` - Returns authenticated user
- ✅ `GET /api/roles` - Returns roles list
- ✅ `GET /api/users` - Returns users list
- ✅ `GET /api/permissions` - Returns permissions list

#### 7.4 Role-Based Permissions
- ✅ Role-Based Permissions page loads
- ✅ Can view roles and permissions
- ✅ No 403 errors

---

### Test 8: Error Handling Test

#### 8.1 Invalid Database Credentials
**Steps:**
1. Enter wrong database credentials
2. Click "Test Connection"

**Expected Result:**
- Error message displayed
- Does not proceed to next step

#### 8.2 Migration Failure
**Steps:**
1. Use database without proper permissions
2. Try to run migrations

**Expected Result:**
- Error message displayed
- Clear error description shown

#### 8.3 Invalid Super Admin Data
**Steps:**
1. Try to create super admin with:
   - Empty fields
   - Password less than 8 characters
   - Mismatched passwords
   - Existing email

**Expected Result:**
- Validation errors displayed
- Cannot proceed until valid data entered

---

## Post-Installation Verification

### Database Verification Checklist
- [ ] `users` table exists with super admin user
- [ ] `roles` table exists with `super_admin` role
- [ ] `permissions` table exists
- [ ] All other migration tables created
- [ ] Foreign key relationships intact

### File System Verification
- [ ] `.env` file updated with database credentials
- [ ] `storage/app/installed.lock` file exists
- [ ] No error logs in `storage/logs/laravel.log`

### Application Verification
- [ ] Can login with super admin credentials
- [ ] All existing features work normally
- [ ] No console errors in browser
- [ ] No PHP errors in logs

---

## Rollback Procedure (If Needed)

If installation fails or you need to reset:

1. **Delete installation lock:**
   ```bash
   rm storage/app/installed.lock
   ```

2. **Drop database tables:**
   ```sql
   DROP DATABASE your_database_name;
   CREATE DATABASE your_database_name;
   ```

3. **Clear Laravel cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Start installation again:**
   - Navigate to `/install`
   - Follow installation steps

---

## Common Issues & Solutions

### Issue: "Database connection failed"
**Solution:** 
- Verify database credentials
- Ensure MySQL/MariaDB is running
- Check database user has proper permissions

### Issue: "Migration failed"
**Solution:**
- Check database user has CREATE, ALTER, DROP permissions
- Verify database is empty or can be overwritten
- Check Laravel logs: `storage/logs/laravel.log`

### Issue: "Super admin creation failed"
**Solution:**
- Ensure email is unique
- Password must be at least 8 characters
- Check database connection is still active

### Issue: "Cannot access installation page"
**Solution:**
- Clear browser cache
- Check route is registered: `php artisan route:list --path=install`
- Verify `resources/js/app.js` has install route

---

## Success Criteria

✅ Installation completes without errors
✅ All database tables created
✅ Super admin user created and can login
✅ All existing functionality works normally
✅ No old code modified or broken
✅ Installation cannot be run twice

---

## Notes

- Installation routes are placed BEFORE the catch-all route to ensure they work
- Installation page bypasses authentication checks
- Old code remains completely unchanged
- All new code is isolated in new files
