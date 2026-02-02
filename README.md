# Event Registration Module for Drupal 10

A custom Drupal 10 module enabling event management with user registration, email notifications, AJAX-powered forms, and administrative tools.

**Developer:** Nyasa Singh  
**Purpose:** FOSSEE Web Development Screening Task  
**Drupal Version:** 10.x

---

## Features

✅ Admin event configuration with registration windows  
✅ Dynamic user registration form with AJAX cascading dropdowns  
✅ Dual email notifications (user + admin)  
✅ Comprehensive validation (Email + Event ID duplicate check, format validation, special characters)  
✅ Admin dashboard with filtering and CSV export  
✅ Custom permissions for access control  
✅ Built entirely with Drupal core APIs (no contrib modules)

---

## Requirements

- Drupal 10.x
- PHP 8.1+
- MySQL 5.7+ / PostgreSQL 10+ / SQLite 3
- No contributed modules required

---

## Installation

### 1. Copy Module Files
```bash
cp -r event_registration /path/to/drupal/web/modules/custom/
```

### 2. Import Database Schema
```bash
# MySQL/MariaDB
mysql -u username -p database_name < database/event_registration.sql

# SQLite
sqlite3 web/sites/default/files/.ht.sqlite < database/event_registration.sql
```

### 3. Enable Module
```bash
drush en event_registration -y
drush cr
```

Or via UI: Navigate to `/admin/modules`, check **Custom Event Registration**, click **Install**.

---

## Configuration

### Initial Setup

1. **Configure Email Settings**  
   URL: `/admin/config/event-registration/settings`
   - Set admin notification email address
   - Enable/disable admin notifications
   - Settings stored via Config API (no hardcoded values)

2. **Set Permissions**  
   URL: `/admin/people/permissions`
   
   | Permission | Description | Recommended Roles |
   |------------|-------------|-------------------|
   | Administer Event Registrations | Create events, manage settings | Administrator |
   | View Event Registrations | View and export registration data | Administrator, Content Editor |

---

## URLs Reference

| Feature | URL | Permission Required |
|---------|-----|---------------------|
| Event Configuration | `/admin/config/event-registration` | Administer Event Registrations |
| View All Events | `/admin/event-registration/view-events` | Administer Event Registrations |
| Admin Settings | `/admin/config/event-registration/settings` | Administer Event Registrations |
| User Registration Form | `/event-registration` | Public Access |
| View Registrations | `/admin/event-registration/registrations` | View Event Registrations |

---

## Usage Guide

### For Administrators

#### Creating Events
1. Navigate to `/admin/config/event-registration`
2. Fill in event details:
   - **Event Name:** Descriptive name
   - **Category:** Online Workshop, Hackathon, Conference, One-day Workshop
   - **Event Date:** When event occurs
   - **Registration Start/End Dates:** Registration window
3. Click **Save Event**

**Validation:** Registration end date must be after start date; event date must be after registration end date.

#### Managing Registrations
Navigate to `/admin/event-registration/registrations`

**Features:**
- **Dynamic Filters:** Event Date and Event Name dropdowns (AJAX-powered)
- **Participant Count:** Real-time total above table
- **CSV Export:** Download filtered data

**Table Columns:**
- Name, Email, Event Date, College Name, Department, Submission Date

#### Exporting Data
1. Apply filters (optional)
2. Click **Export to CSV**
3. File downloads as `event_registrations_YYYY-MM-DD_HHMMSS.csv`

---

### For End Users

#### Registering for Events
Navigate to `/event-registration`

**Process:**
1. Enter personal details (name, email, college, department)
2. Select event category
3. Select event date (AJAX-populated based on category)
4. Select event name (AJAX-populated based on category + date)
5. Submit form

**Upon Success:**
- Confirmation message displayed
- Confirmation email sent to user
- Admin notification sent (if enabled)

---

## Database Schema

### Table: `event_config`
Stores event configuration.

| Column | Type | Description |
|--------|------|-------------|
| id | SERIAL (PK) | Unique identifier |
| event_name | VARCHAR(255) | Event name |
| event_category | VARCHAR(100) | Category (online_workshop, hackathon, conference, oneday_workshop) |
| event_date | DATE | Event date |
| registration_start_date | DATE | Registration opening date |
| registration_end_date | DATE | Registration closing date |
| created | INT | Unix timestamp |

> Date fields use Drupal Schema API `date` type and are stored in YYYY-MM-DD format.

**Indexes:** Primary key on `id`

---

### Table: `event_registration`
Stores user registrations.

| Column | Type | Description |
|--------|------|-------------|
| `id` | SERIAL (PK) | Unique identifier |
| `full_name` | VARCHAR(255) | Registrant's name |
| `email` | VARCHAR(255) | Registrant's email |
| `college_name` | VARCHAR(255) | College/university |
| `department` | VARCHAR(255) | Academic department |
| `event_id` | INT (FK) | References `event_config.id` |
| `created` | INT | Unix timestamp |

**Foreign Key:** `event_registration.event_id` → `event_config.id`

---

## Validation Logic

### Event Configuration Form
**File:** `src/Form/EventConfigForm.php`

- **Date Sequence:** Registration Start < Registration End < Event Date
- **Required Fields:** All fields enforced via Drupal Form API

### Event Registration Form
**File:** `src/Form/EventRegistrationForm.php`

1. **Email Format:** Validates using `filter_var()` with `FILTER_VALIDATE_EMAIL`

2. **Special Character Restrictions:**
   - **Full Name:** Letters and spaces only (`/^[a-zA-Z\s]+$/`)
   - **College Name:** Letters, numbers, spaces (`/^[a-zA-Z0-9\s]+$/`)
   - **Department:** Letters and spaces only (`/^[a-zA-Z\s]+$/`)

3. **Duplicate Prevention:** Checks `email` + `event_id` combination
   - Error: "You have already registered for this event."

4. **Required Fields:** All fields marked required; AJAX dropdowns enforce selection

---

## Email Notifications

### Architecture
**Service:** `src/Service/EmailService.php`  
Uses Dependency Injection with:
- `MailManagerInterface` (Drupal mail system)
- `ConfigFactoryInterface` (module configuration)
- `Connection` (database access)

### User Confirmation Email
**Trigger:** Successful registration  
**Recipient:** User's email  
**Content:** Name, Event Name, Category, Event Date

### Admin Notification Email
**Trigger:** Successful registration  
**Recipient:** Admin email from settings  
**Condition:** Only if "Enable Admin Notifications" is checked  
**Content:** Full registration details + timestamp

**Note:** Email requires configured mail server. In local development without SMTP, emails won't send but registration completes successfully.

---

## AJAX Functionality

### Registration Form
**File:** `src/Form/EventRegistrationForm.php`

1. **Category → Event Date**
   - Callback: `updateDatesCallback()`
   - Queries distinct event dates for selected category
   - Clears Event Name dropdown

2. **Event Date → Event Name**
   - Callback: `updateEventNamesCallback()`
   - Queries events matching category AND date
   - Populates Event Name dropdown with event IDs as values

### Admin Listing Page
**File:** `src/Form/RegistrationFilterForm.php`

1. **Event Date → Event Name Filter**
   - Callback: `updateEventNamesCallback()`
   - Filters available event names

2. **Filter → Table Refresh**
   - Callback: `updateTableCallback()`
   - Updates participant count
   - Refreshes registration table with filters applied
   - Filters also apply to CSV export

---

## Technical Implementation

### PSR-4 Autoloading
**Namespace:** `Drupal\event_registration`

```
event_registration/
├── src/
│   ├── Controller/
│   │   └── ViewEventsController.php
│   ├── Form/
│   │   ├── EventConfigForm.php
│   │   ├── EventRegistrationForm.php
│   │   ├── RegistrationFilterForm.php
│   │   └── SettingsForm.php
│   └── Service/
│       └── EmailService.php
```

### Dependency Injection
**Service Definition:** `event_registration.services.yml`

```yaml
services:
  event_registration.email_service:
    class: Drupal\event_registration\Service\EmailService
    arguments: ['@plugin.manager.mail', '@config.factory', '@database']
```

**Benefits:**
- Testability
- No static service calls in business logic
- Follows Drupal best practices

### Config API Usage
**Configuration:** `config/install/event_registration.settings.yml`

```yaml
admin_email: 'admin@example.com'
enable_admin_notifications: true
```

**Access:**
```php
$config = $this->configFactory->get('event_registration.settings');
$admin_email = $config->get('admin_email');
```

### Database API
All queries use Drupal's Database API with parameterized queries for security, preventing SQL injection while maintaining database abstraction across MySQL, PostgreSQL, and SQLite.

---

## Troubleshooting

- **Emails not sending:** Local environments may not have SMTP configured. Registration will still succeed. Check logs at `/admin/reports/dblog`.
- **AJAX not updating:** Clear cache (`drush cr`) and ensure events exist at `/admin/event-registration/view-events`.
- **Permission denied:** Verify permissions at `/admin/people/permissions`.

---



---

## Development Standards

- PSR-4 autoloading structure
- Dependency injection for services
- Drupal coding standards compliance
- Comprehensive inline documentation
- Security best practices (parameterized queries, input validation)
- No hardcoded configuration values

---



---

## License

GPL-2.0-or-later

---

## Support

For technical questions, refer to:
- Drupal API Documentation: https://api.drupal.org
- Drupal Form API: https://www.drupal.org/docs/drupal-apis/form-api
- Drupal Database API: https://www.drupal.org/docs/drupal-apis/database-api

---

**End of Documentation**
