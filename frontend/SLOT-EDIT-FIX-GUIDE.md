# SLOT EDIT/UPDATE FIX GUIDE

## Issue
Getting 422 error when creating/editing slots because:
1. Backend might still be expecting old datetime format
2. Migration to change slots table hasn't been run
3. Time format mismatch between frontend and backend

## Root Cause
The migration `m240816_000004_modify_slots_for_daily_recurring.php` needs to be applied to update the database schema from datetime to time format.

## Solution Steps

### Step 1: Deploy the Migration
Upload and run the migration on your server:

```bash
# Upload the migration file
scp migrations/m240816_000004_modify_slots_for_daily_recurring.php your-server:/path/to/api/migrations/

# SSH into server
ssh your-server

# Navigate to API directory
cd /path/to/your/api

# Run the migration
php yii migrate/up

# Confirm migration applied
php yii migrate/history
```

### Step 2: Update Backend Files (if not done already)
Upload the updated backend files:

```bash
# Upload updated models and controllers
scp models/Slot.php your-server:/path/to/api/models/
scp controllers/SlotController.php your-server:/path/to/api/controllers/
scp commands/ResultController.php your-server:/path/to/api/commands/
```

### Step 3: Restart Web Server
```bash
sudo systemctl restart apache2
# OR
sudo systemctl restart nginx && sudo systemctl restart php-fpm
```

### Step 4: Test the Fix
1. Try creating a new slot with time like "14:30"
2. Check browser console logs to see what data is being sent
3. Verify the slot appears with "Daily" and the correct time

## What the Migration Does

**Before Migration:**
- `scheduled_time` column: `DATETIME` (e.g., "2025-08-16 14:30:00")
- Slots need to be created for each date

**After Migration:**
- `scheduled_time` column: `TIME` (e.g., "14:30:00")  
- Slots run automatically every day at the specified time

## Frontend Changes Applied
✅ Form input changed from `datetime-local` to `time`
✅ Converts HH:MM to HH:MM:SS before sending to API
✅ Added debug logs to track what's being sent
✅ Fixed display to show "Daily" instead of trying to format time as date

## Debug Information
Check browser console for these logs:
- `Sending slot data:` - Shows what frontend sends
- `Slot result:` - Shows API response

If you see 422 errors, check:
1. Migration status: `php yii migrate/history`
2. Database schema: Check if `scheduled_time` is TIME or DATETIME
3. Backend logs for validation errors

## Manual Database Check
```sql
-- Check current table structure
DESCRIBE slots;

-- If scheduled_time is still DATETIME, run:
ALTER TABLE slots MODIFY scheduled_time TIME NOT NULL;
```

## Expected Behavior After Fix
- ✅ Creating slots with time works (e.g., "14:30")
- ✅ Editing existing slots works 
- ✅ Slots display as "Daily" with time
- ✅ No more 422 validation errors
- ✅ Auto-generation works with time-based matching
