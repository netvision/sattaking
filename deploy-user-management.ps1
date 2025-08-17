# PowerShell script to deploy user management and set up admin user

Write-Host "Deploying user management system..." -ForegroundColor Green

# Replace these with your actual server details
$serverUser = "your-username"
$serverHost = "your-server-ip"
$serverPath = "/path/to/your/api"

# Upload the UserController
Write-Host "Uploading UserController..." -ForegroundColor Yellow
scp commands/UserController.php "${serverUser}@${serverHost}:${serverPath}/commands/"

Write-Host "Files uploaded successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Now SSH into your server and run:" -ForegroundColor Cyan
Write-Host "cd $serverPath" -ForegroundColor White
Write-Host "php yii user/create-admin" -ForegroundColor White
Write-Host ""
Write-Host "This will create the default admin user:" -ForegroundColor Yellow
Write-Host "Username: admin" -ForegroundColor White
Write-Host "Password: admin123" -ForegroundColor White
