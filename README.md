# Satta King Lottery Portal

A complete lottery management system with Yii2 backend API and Vue3 frontend.

## Project Overview

This application provides a lottery platform with two main sections:
- **Public Section**: Display current results, platform information, and archived results with calendar navigation
- **Admin Section**: Password-protected area for managing slots, setting results, and platform administration

## Features

- **Slot Management**: Admin can define lottery slots with titles and scheduled announcement times
- **Result Management**: Results are numbers between 0-99, can be set manually by admin or auto-generated
- **Auto-Generation**: Configurable slots automatically generate random results at scheduled times if not manually set
- **Result Immutability**: Once a result is declared and time has elapsed, it cannot be changed
- **Archive System**: Historical results accessible through calendar interface
- **Database Storage**: All data stored in MySQL database

## Technology Stack

### Backend (API)
- **Framework**: Yii2 Basic Template
- **Database**: MySQL
- **Server**: Apache2/PHP on Ubuntu
- **Architecture**: RESTful API

### Frontend
- **Framework**: Vue 3
- **Deployment**: Static files deployed on Netlify

## Database Schema

### Tables
1. **users** - Admin authentication
2. **slots** - Lottery slot definitions
3. **results** - Result records with auto-generation flags

```sql
-- Admin users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    auth_key VARCHAR(32),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Slots table
CREATE TABLE slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    scheduled_time DATETIME NOT NULL,
    is_auto TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Results table
CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_id INT NOT NULL,
    result INT NOT NULL,
    declared_at DATETIME NOT NULL,
    is_auto TINYINT(1) DEFAULT 0,
    locked TINYINT(1) DEFAULT 0,
    FOREIGN KEY (slot_id) REFERENCES slots(id)
);
```

## API Endpoints

### Public Endpoints
- `GET /api/results` - Get current results
- `GET /api/results?date=YYYY-MM-DD` - Get archived results for specific date
- `GET /api/info` - Get platform information

### Admin Endpoints (Authentication Required)
- `POST /api/auth/login` - Admin authentication
- `GET /api/admin/slots` - List all slots
- `POST /api/admin/slots` - Create new slot
- `PUT /api/admin/slots/{id}` - Update slot
- `DELETE /api/admin/slots/{id}` - Delete slot
- `POST /api/admin/results` - Set result for a slot
- `GET /api/admin/results` - View all results with logs

## Installation & Setup

### Prerequisites
- Ubuntu server with Apache2 and PHP
- MySQL database
- Composer (for Yii2)
- Node.js and npm (for Vue3 frontend)

### Backend Setup (Yii2)

1. **Install Yii2 Basic Template**
   ```bash
   composer create-project --prefer-dist yiisoft/yii2-app-basic backend
   cd backend
   ```

2. **Configure Database**
   - Edit `config/db.php` with your MySQL credentials
   - Import the database schema (see schema above)

3. **Create Models and Controllers**
   - Copy the provided model files to `models/` directory
   - Copy controller files to `controllers/` directory
   - Copy console command to `commands/` directory

4. **Configure URL Rules** (in `config/web.php`)
   ```php
   'urlManager' => [
       'enablePrettyUrl' => true,
       'showScriptName' => false,
       'rules' => [
           ['class' => 'yii\rest\UrlRule', 'controller' => 'result'],
           ['class' => 'yii\rest\UrlRule', 'controller' => 'slot'],
           // Add more API rules
       ],
   ],
   ```

5. **Set Up Auto-Generation Cron Job**
   ```bash
   # Add to crontab (runs every minute)
   * * * * * /path/to/yii result/auto-generate
   ```

### Frontend Setup (Vue3)

1. **Create Vue3 Project**
   ```bash
   npm create vue@latest frontend
   cd frontend
   npm install
   ```

2. **Install Additional Packages**
   ```bash
   npm install axios vue-router@4 @vueuse/core
   ```

3. **Configure API Base URL**
   - Set up environment variables for API endpoints
   - Configure Axios for API communication

4. **Build for Production**
   ```bash
   npm run build
   ```


### Deployment

1. **Backend Deployment**
   - Copy Yii2 project to `/var/www/html/api/`
   - Configure Apache virtual host for API
   - Set appropriate permissions

2. **Frontend Deployment (Netlify)**
   - Push your Vue3 project to a Git repository (GitHub, GitLab, etc.)
   - Sign in to [Netlify](https://www.netlify.com/) and connect your repository
   - Set the build command to `npm run build` and the publish directory to `dist`
   - Configure environment variables for the API base URL (pointing to your server's API endpoint)
   - Deploy the site

3. **API CORS Configuration**
   - Ensure your Yii2 API allows CORS requests from your Netlify domain. You can use the `yii\filters\Cors` filter in your API controllers.

**Example CORS filter in Yii2 controller:**
```php
public function behaviors()
{
   $behaviors = parent::behaviors();
   $behaviors['corsFilter'] = [
      'class' => \yii\filters\Cors::class,
      'cors' => [
         'Origin' => ['https://your-netlify-site.netlify.app'],
         'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
         'Access-Control-Allow-Credentials' => true,
      ],
   ];
   return $behaviors;
}
```

## Project Structure

```
sattaking/
├── README.md
├── projectplan.txt
├── backend/                 # Yii2 API
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── ResultController.php
│   │   └── SlotController.php
│   ├── models/
│   │   ├── User.php
│   │   ├── Slot.php
│   │   └── Result.php
│   ├── commands/
│   │   └── ResultController.php
│   └── config/
│       ├── web.php
│       └── db.php
└── frontend/                # Vue3 App
    ├── src/
    │   ├── components/
    │   ├── views/
    │   │   ├── PublicView.vue
    │   │   └── AdminView.vue
    │   ├── router/
    │   └── services/
   └── public/
```

## Development Workflow

1. **Start with Database**: Create tables and sample data
2. **Backend Development**: Implement models, controllers, and API endpoints
3. **Frontend Development**: Create Vue components and integrate with API
4. **Testing**: Test both public and admin functionality
5. **Deployment**: Deploy backend to Ubuntu server and frontend to Netlify

## Security Considerations

- Use proper authentication for admin endpoints
- Validate and sanitize all input data
- Implement rate limiting for API endpoints
- Use HTTPS in production
- Secure database credentials
- Implement proper CORS policies

## Maintenance

- **Cron Job**: Ensure auto-generation cron job is running
- **Database Backups**: Regular MySQL backups
- **Log Monitoring**: Monitor Apache and application logs
- **Result Verification**: Regular checks on auto-generated results

## Support

For issues or questions regarding this project, refer to:
- Yii2 Documentation: https://www.yiiframework.com/doc/guide/2.0/en
- Vue3 Documentation: https://vuejs.org/guide/
- Project plan file: `projectplan.txt`
