<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use app\models\User;

/**
 * User management commands
 */
class UserController extends Controller
{
    /**
     * Create a new admin user
     */
    public function actionCreateAdmin($username = 'rjangid', $password = 'admin123')
    {
        $this->stdout("Creating admin user...\n", Console::FG_GREEN);
        
        // Check if user already exists
        $existingUser = User::findByUsername($username);
        if ($existingUser) {
            $this->stdout("User '$username' already exists!\n", Console::FG_YELLOW);
            $this->stdout("Do you want to update the password? (y/n): ", Console::FG_YELLOW);
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            fclose($handle);
            
            if (trim($line) !== 'y') {
                $this->stdout("Operation cancelled.\n", Console::FG_RED);
                return;
            }
            
            // Update existing user password
            $existingUser->setPassword($password);
            $existingUser->generateAccessToken();
            $existingUser->status = User::STATUS_ACTIVE;
            
            if ($existingUser->save()) {
                $this->stdout("Admin user '$username' password updated successfully!\n", Console::FG_GREEN);
                $this->stdout("Username: $username\n", Console::FG_CYAN);
                $this->stdout("Password: $password\n", Console::FG_CYAN);
                $this->stdout("Access Token: {$existingUser->access_token}\n", Console::FG_CYAN);
            } else {
                $this->stdout("Failed to update user!\n", Console::FG_RED);
                foreach ($existingUser->errors as $field => $errors) {
                    foreach ($errors as $error) {
                        $this->stdout("$field: $error\n", Console::FG_RED);
                    }
                }
            }
            return;
        }
        
        // Create new user
        $user = new User();
        $user->username = $username;
        $user->setPassword($password);
        $user->generateAccessToken();
        $user->status = User::STATUS_ACTIVE;
        
        if ($user->save()) {
            $this->stdout("Admin user created successfully!\n", Console::FG_GREEN);
            $this->stdout("Username: $username\n", Console::FG_CYAN);
            $this->stdout("Password: $password\n", Console::FG_CYAN);
            $this->stdout("Access Token: {$user->access_token}\n", Console::FG_CYAN);
        } else {
            $this->stdout("Failed to create user!\n", Console::FG_RED);
            foreach ($user->errors as $field => $errors) {
                foreach ($errors as $error) {
                    $this->stdout("$field: $error\n", Console::FG_RED);
                }
            }
        }
    }
    
    /**
     * List all users
     */
    public function actionList()
    {
        $this->stdout("All users:\n", Console::FG_GREEN);
        $this->stdout(str_repeat('-', 80) . "\n", Console::FG_CYAN);
        $this->stdout(sprintf("%-5s %-20s %-10s %-20s\n", 'ID', 'Username', 'Status', 'Created'), Console::FG_CYAN);
        $this->stdout(str_repeat('-', 80) . "\n", Console::FG_CYAN);
        
        $users = User::find()->all();
        foreach ($users as $user) {
            $status = $user->status == User::STATUS_ACTIVE ? 'Active' : 
                     ($user->status == User::STATUS_INACTIVE ? 'Inactive' : 'Deleted');
            $this->stdout(sprintf("%-5d %-20s %-10s %-20s\n", 
                $user->id, 
                $user->username, 
                $status, 
                $user->created_at
            ));
        }
        
        if (empty($users)) {
            $this->stdout("No users found.\n", Console::FG_YELLOW);
            $this->stdout("Run: php yii user/create-admin to create the default admin user.\n", Console::FG_YELLOW);
        }
    }
    
    /**
     * Reset user password
     */
    public function actionResetPassword($username, $newPassword)
    {
        $user = User::findByUsername($username);
        if (!$user) {
            $this->stdout("User '$username' not found!\n", Console::FG_RED);
            return;
        }
        
        $user->setPassword($newPassword);
        $user->generateAccessToken();
        
        if ($user->save()) {
            $this->stdout("Password reset successfully for user '$username'!\n", Console::FG_GREEN);
            $this->stdout("New password: $newPassword\n", Console::FG_CYAN);
            $this->stdout("New access token: {$user->access_token}\n", Console::FG_CYAN);
        } else {
            $this->stdout("Failed to reset password!\n", Console::FG_RED);
            foreach ($user->errors as $field => $errors) {
                foreach ($errors as $error) {
                    $this->stdout("$field: $error\n", Console::FG_RED);
                }
            }
        }
    }
    
    /**
     * Generate new access token for user
     */
    public function actionGenerateToken($username)
    {
        $user = User::findByUsername($username);
        if (!$user) {
            $this->stdout("User '$username' not found!\n", Console::FG_RED);
            return;
        }
        
        $user->generateAccessToken();
        
        if ($user->save()) {
            $this->stdout("New access token generated for user '$username'!\n", Console::FG_GREEN);
            $this->stdout("Access token: {$user->access_token}\n", Console::FG_CYAN);
        } else {
            $this->stdout("Failed to generate token!\n", Console::FG_RED);
        }
    }
}
