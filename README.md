# Symfony Firmware Manager

This project is a Symfony-based admin dashboard and firmware lookup tool. It provides:

- A public landing page at `/` for frontend visitors
- A separate admin login at `/login`
- An admin dashboard at `/admin/dashboard`
- A firmware download screen for matching the correct package
- Admin pages to manage firmware entries
- Admin pages to manage users
- A profile page where the signed-in user can change their password

## Requirements

- PHP 8.4 or newer
- Composer
- Symfony CLI
- A local database configured through `.env`

## How to Run the App

1. Copy or clone the project into your local web workspace, for example `localhost` or any local development folder.
2. Open a terminal in the project root:

```bash
cd /path/to/symfony-admin-dashboard
```

3. Install PHP dependencies if needed:

```bash
composer install
```

4. Run database migrations:

```bash
php bin/console doctrine:migrations:migrate
```

5. Start the Symfony development server:

```bash
symfony server:start
```

6. Open the local URL shown in the terminal. The default app front page opens the public landing page at `/`.

## Main User Flow

### 1. Public Landing Page

- Open `/`
- The landing page introduces the app and its main features
- End users are not required to sign in to start using the frontend-facing flow
- The menu exposes links to the firmware lookup screen and the admin login page

### 2. Login

- Go to `/login`
- Enter your email and password
- After a successful login, the app redirects to the admin dashboard at `/admin/dashboard`

### 3. Dashboard

- The dashboard is the main signed-in landing page
- The page layout is split into:
  - Header
  - Left sidebar
  - Content area
  - Footer
- The user menu in the header contains:
  - Profile
  - Logout

### 4. Profile

- Click the user placeholder in the top-right header
- Click `Profile`
- You will be redirected to `/profile`
- On this page, the current user can:
  - View their email
  - Change their password by entering the current password and a new password

### 5. Download Firmwares

- Open `/download-firmware`
- Enter the correct:
  - Software Version
  - HW Version
- Submit the form
- If a matching firmware exists, the app shows the available download links
- If the firmware parts are incorrect or no match is found, the app shows an error result
- This page is public and does not require login

Legacy firmware page routes also exist for compatibility:

- `/carplay/software-download`
- `/api2/carplay/software/version`

### 6. Manage Firmware Records

- Signed-in admins can open `/admin/firmware`
- This section lets the admin:
  - View firmware versions
  - Add a firmware version
  - Edit a firmware version
  - Delete a firmware version
- Firmware data is also exported to the JSON store used by the firmware lookup flow

### 7. Manage Users

- Signed-in admins can open `/admin/users`
- This section lets the admin:
  - View users
  - Add a new user
  - Edit a user
  - Delete a user
- The current signed-in user cannot delete their own account from the user list

## Important Routes

- `/` -> public landing page
- `/login` -> admin login page
- `/logout` -> logout
- `/profile` -> current user profile and password change
- `/admin/dashboard` -> admin dashboard
- `/admin/users` -> user management
- `/admin/firmware` -> firmware management
- `/download-firmware` -> firmware lookup page
- `/api/firmware/software/version` -> firmware lookup API

## Notes for End Users

- You can use the frontend pages without logging in
- Admin login is available separately from the site navigation
- Use exact firmware version values when downloading firmware
- Do not install a firmware package unless the software version and hardware version match correctly
- If the app shows no matching package, do not guess; verify the version details first
