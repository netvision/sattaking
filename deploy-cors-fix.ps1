# PowerShell deployment script for CORS fixes
# Run this from your local machine

Write-Host "Uploading CORS configuration fixes..." -ForegroundColor Green

# Replace these with your actual server details
$serverUser = "your-username"
$serverHost = "your-server-ip"
$serverPath = "/path/to/your/api"

# Upload the updated web.php config (removed duplicate CORS)
Write-Host "Uploading web.php (removed duplicate CORS)..." -ForegroundColor Yellow
scp config/web.php "${serverUser}@${serverHost}:${serverPath}/config/"

# Upload updated controllers
Write-Host "Uploading controllers..." -ForegroundColor Yellow
scp controllers/AuthController.php "${serverUser}@${serverHost}:${serverPath}/controllers/"
scp controllers/SlotController.php "${serverUser}@${serverHost}:${serverPath}/controllers/"
scp controllers/ResultController.php "${serverUser}@${serverHost}:${serverPath}/controllers/"
scp controllers/InfoController.php "${serverUser}@${serverHost}:${serverPath}/controllers/"

Write-Host "Files uploaded successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. SSH into your server"
Write-Host "2. Restart your web server:"
Write-Host "   sudo systemctl restart apache2"
Write-Host "   # OR"
Write-Host "   sudo systemctl restart nginx && sudo systemctl restart php-fpm"
Write-Host ""
Write-Host "3. Test the CORS fix by trying to login from your frontend"
