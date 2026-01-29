# Event Registration Module (Drupal 10)

A custom Drupal 10 module for managing events and user registrations.

## Overview

The **Event Registration** module allows administrators to create and manage events and provides a public registration form for users. This module demonstrates core Drupal concepts:

- Form API
- Database API
- Config API
- Custom routing and controllers
- Module-based database schema using `.install` files

## Module Location

```
web/modules/custom/event_registration
```

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Nyasa11/Drupal-Event-Registration.git
   ```

2. **Enable the module:**
   - Visit: `/admin/modules`
   - Enable **Event Registration**
   - Click **Install**

3. **Clear cache:**
   ```bash
   drush cr
   ```

> **Note:** SQLite is used for development. Database files and generated assets are excluded using `.gitignore`.

## Available URLs

### Admin Pages
- `/admin/config/event-registration` - Create and configure events
- `/admin/event-registration/view-events` - View all created events
- `/admin/config/event-registration/settings` - Configure admin email and notification settings

### Public Page
- `/event-registration` - Public event registration form

## Database Tables

### event_config
Stores events created by administrators.

| Field | Description |
|-------|-------------|
| id | Primary key |
| event_name | Name of the event |
| event_category | Category of the event |
| event_date | Event date |
| registration_start_date | Registration start date |
| registration_end_date | Registration end date |
| created | Unix timestamp |

### event_registration
Stores user registrations for events.

| Field | Description |
|-------|-------------|
| id | Primary key |
| full_name | User's full name |
| email | User email |
| college | College name |
| department | Department |
| event_category | Selected event category |
| event_date | Selected event date |
| event_name | Selected event name |
| created | Unix timestamp |

## Forms and Logic

### Event Configuration Form (Admin)
- **File:** `src/Form/EventConfigForm.php`
- Allows admin to create events
- Saves event details to `event_config` table
- Validates date consistency before saving

### Event Registration Form (User)
- **File:** `src/Form/EventRegistrationForm.php`
- Public-facing registration form
- Collects user and event information
- Saves registration data to `event_registration` table

### Settings Form (Admin)
- **File:** `src/Form/SettingsForm.php`
- Configure notification email address
- Enable/disable notifications
- Uses Drupal Config API

## Validation Logic

### Admin Event Validation
- Registration end date must be after registration start date
- Event date must not be before the registration period
- Implemented in `validateForm()`

### User Registration Validation
- Required field validation
- Email format validation
- Additional AJAX-based logic planned for future phases

## Current Status

- ✅ Event creation
- ✅ Event listing (admin)
- ✅ User registration form
- ✅ Database schema
- ✅ Validation logic
- ✅ Config API usage

## Future Enhancements

- AJAX-based dynamic dropdowns
- Email notifications
- Admin registration listing with filters
- CSV export functionality

## Author

**Nyasa11**
- GitHub: [@Nyasa11](https://github.com/Nyasa11)
- Repository: [Drupal-Event-Registration](https://github.com/Nyasa11/Drupal-Event-Registration)
