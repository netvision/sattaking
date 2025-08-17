# Satta King API Documentation

## Base URL
```
https://api.sattaking.app
```

## Authentication
Admin endpoints require Bearer token authentication. Include the token in the Authorization header:
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

## API Endpoints

### Public Endpoints (No Authentication Required)

#### Platform Info
- `GET /info` - Get platform information
- `GET /info/status` - Get system status

#### Results
- `GET /results` - Get today's results
- `GET /results/today` - Get today's results (same as above)
- `GET /results/archive?date=YYYY-MM-DD` - Get archived results for specific date
- `GET /results/latest?limit=10` - Get latest results (max 50)

#### Slots
- `GET /slots` - Get all slots (with optional filters)
- `GET /slots/today` - Get today's slots
- `GET /slots?date=YYYY-MM-DD` - Get slots for specific date
- `GET /slots?active=1` - Get only active slots

### Admin Endpoints (Authentication Required)

#### Authentication
- `POST /auth/login` - Admin login
  ```json
  {
    "username": "admin",
    "password": "admin123"
  }
  ```
- `POST /auth/logout` - Admin logout
- `GET /auth/me` - Get current user info

#### Slot Management
- `POST /slots` - Create new slot
  ```json
  {
    "title": "Morning King",
    "scheduled_time": "2025-08-16 10:00:00",
    "is_auto": 1,
    "description": "Morning lottery"
  }
  ```
- `PUT /slots/{id}` - Update slot
- `DELETE /slots/{id}` - Delete slot

#### Result Management
- `POST /results` - Set result for slot
  ```json
  {
    "slot_id": 1,
    "result": 45,
    "declared_at": "2025-08-16 10:00:00"
  }
  ```
- `PUT /results/{id}` - Update result
- `POST /results/{id}/lock` - Lock result (make immutable)

## Response Format

All API responses follow this format:

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

## Console Commands

Run these commands on the server for maintenance:

### Auto-generate Results
```bash
# Run every minute via cron
php yii result/auto-generate
```

### Lock Expired Results
```bash
php yii result/lock-expired
```

### Generate Test Data
```bash
php yii result/generate-test
```

### View Statistics
```bash
php yii result/stats
```

## Cron Job Setup

Add this to your server's crontab to auto-generate results:

```bash
# Auto-generate results every minute
* * * * * cd /path/to/your/yii2/project && php yii result/auto-generate

# Lock expired results every 5 minutes
*/5 * * * * cd /path/to/your/yii2/project && php yii result/lock-expired
```

## CORS Configuration

The API is configured to allow CORS requests. In production, update the CORS origins in each controller to restrict access to your frontend domain only.

## Sample Frontend Integration

### JavaScript/Vue3 Example

```javascript
// API client setup
const API_BASE = 'https://api.sattaking.app';
let authToken = localStorage.getItem('auth_token');

// Axios configuration
const api = axios.create({
  baseURL: API_BASE,
  headers: {
    'Content-Type': 'application/json',
    ...(authToken && { 'Authorization': `Bearer ${authToken}` })
  }
});

// Get today's results
const getTodayResults = async () => {
  const response = await api.get('/results/today');
  return response.data;
};

// Admin login
const adminLogin = async (username, password) => {
  const response = await api.post('/auth/login', { username, password });
  if (response.data.success) {
    authToken = response.data.data.access_token;
    localStorage.setItem('auth_token', authToken);
    api.defaults.headers['Authorization'] = `Bearer ${authToken}`;
  }
  return response.data;
};

// Set result (admin)
const setResult = async (slotId, result) => {
  const response = await api.post('/results', {
    slot_id: slotId,
    result: result
  });
  return response.data;
};
```
