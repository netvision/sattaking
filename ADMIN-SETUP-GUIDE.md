# ADMIN USER SETUP GUIDE

## Problem
You can't login to the admin panel with admin/admin123 because the default admin user doesn't exist in the database yet.

## Solution
You need to create the default admin user in your database.

## Step 1: Upload the User Management Controller

Upload the `commands/UserController.php` file to your server:

### Option A: Using PowerShell (Windows)
```powershell
# Edit deploy-user-management.ps1 with your server details first
.\deploy-user-management.ps1
```

### Option B: Manual Upload
Upload `commands/UserController.php` to your server at:
`/path/to/your/api/commands/UserController.php`

## Step 2: Create the Admin User

SSH into your server and run:

```bash
# Navigate to your API directory
cd /path/to/your/api

# Create the default admin user
php yii user/create-admin

# This will create:
# Username: admin
# Password: admin123
```

## Step 3: Verify the User was Created

```bash
# List all users to confirm
php yii user/list
```

You should see the admin user in the list.

## Step 4: Test Login

Now try logging into your admin panel again with:
- Username: `admin`
- Password: `admin123`

## Additional Commands

```bash
# Create admin with custom credentials
php yii user/create-admin myusername mypassword

# Reset password for existing user
php yii user/reset-password admin newpassword123

# Generate new access token
php yii user/generate-token admin

# List all users
php yii user/list
```

## Troubleshooting

If you're still having issues:

1. **Check if user exists:**
   ```bash
   php yii user/list
   ```

2. **Check database connection:**
   ```bash
   # Test a simple query
   php yii migrate/history
   ```

3. **Check API endpoint:**
   ```bash
   # Test login endpoint directly
   curl -X POST https://api.sattaking.app/auth/login \
        -H "Content-Type: application/json" \
        -d '{"username":"admin","password":"admin123"}'
   ```

4. **Check logs:**
   ```bash
   # Check PHP/Apache error logs
   tail -f /var/log/apache2/error.log
   # OR
   tail -f /var/log/nginx/error.log
   ```

## Security Note

After setting up, consider changing the default password:
```bash
php yii user/reset-password admin your-secure-password
```
