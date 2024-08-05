# Accredity Backend Assessment

## Overview

This assessment involves creating an API with Laravel to upload and verify a given file following the employer's requirements. The project includes API endpoints for file verification and utilizes Laravel features for authentication and file handling.

## Getting Started

Follow these steps to set up the project locally:

### 1. Clone the Repository

```bash
git clone https://github.com/vincent-artisan-7/accredify-be.git
cd accredify-be
```

### 2. PHP Artisan
Run the following commands to set up the database and seed initial data:

```bash
php artisan migrate
php artisan db:seed
```

### 3. Login
Use the following credentials to log in:

Email: test@example.com
Password: 123456

Navigate to the login page of the application to enter these credentials and access the application.

## Routes Configuration
The application's routing is configured as follows:

- Welcome Page: Displays a welcome message and links to login and register pages.
    - URL: /
- Dashboard Page: Accessible only to authenticated and verified users. Displays the dashboard.
    - URL: /dashboard
- JSON Upload Page: Allows authenticated users to upload JSON files.
    - URL: /json-upload

## ERD diagram
![accredify_be_erd (1)](https://github.com/user-attachments/assets/28109281-ff91-48a4-bedb-e0ce84bf982d)

## Upload Status
- Verified
    ![image](https://github.com/user-attachments/assets/f449439e-0d90-4b42-962e-19d227ebac1f)
- Error types
    ![image](https://github.com/user-attachments/assets/261a9b42-ff8d-431b-93c4-53070c4f8700)


### Frontend
The project frontend is running React Typescript and InertiaJS rendering.

Please run `npm` command to navigate main page.
```bash
npm run dev
```
