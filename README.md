# Shoryan — Backend API

The backend/API for **Shoryan**, a blood donation matching platform. It is a Laravel 13 (PHP 8.3+) REST API that powers the Shoryan mobile client.

> The API is served under the prefix **`/my-api`**, not `/api` (set in `bootstrap/app.php`).

---

## Libraries Used

- **PHP ^8.3** — language runtime
- **laravel/framework ^13.8** — core framework (routing, Eloquent, validation, cache)
- **laravel/sanctum ^4.0** — bearer token auth for the mobile client
- **kreait/firebase-php** — Firebase Admin SDK; used by `FirebaseService` to send FCM push notifications
- **propaganistas/laravel-phone** — Egyptian mobile number validation (`phone:EG,mobile`)
- **resend/resend-laravel** — mail transport used for sending OTP emails (`MAIL_MAILER=resend`, via the Resend API)

**Database:** MySQL, hosted on **Aiven** (managed cloud MySQL), connected over TLS using the bundled `database/certs/ca.pem` CA certificate (referenced via `MYSQL_ATTR_SSL_CA`).

**Cache:** Laravel's cache system (database driver by default) — used for OTP storage

---

## Project File Structure

```text
app/
├── Enums/             → BloodRequestStatus, BloodRequestUrgency,
│                         NotificationType (DONATION_REMINDER, DONATION_MATCHED,
│                         REQUEST_ACCEPTED, REQUEST_FULFILLED), ResponseStatus
├── Console/Commands/   → SendDonationReminders (scheduled, daily 09:00)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/       → Registration, login, logout, OTP verify/resend,
│   │   │                 password reset, FcmTokenController
│   │   ├── BloodRequestController.php
│   │   ├── ChatbotController.php
│   │   ├── DonationController.php
│   │   ├── NotificationController.php
│   │   └── ResponseController.php
│   ├── Middleware/     → EnsureEmailIsVerified
│   ├── Requests/       → Auth/, StoreBloodRequestRequest, StoreDonationRequest,
│   │                     StoreMessageRequest, IndexBloodRequestRequest,
│   │                     StoreResponseRequest (unused)
│   └── Resources/      → BloodRequestResource, DonationResource
├── Mail/               → SendRegistrationOtpMail, SendPasswordResetOtpMail
├── Models/             → User (now incl. fcm_token, eligibility_notified_at),
│                         BloodRequest, Hospital, Response, Donation,
│                         Notification, Message
├── Providers/          → AppServiceProvider
└── Services/           → FirebaseService (sends FCM message),
                          NotificationService (creates DB notification + triggers push)

config/                 → app, auth, cache, cors, database, mail, sanctum,
                          services (incl. firebase.credentials), etc.

database/
├── certs/ca.pem        → TLS CA cert for the Aiven-hosted MySQL connection
├── factories/, migrations/ (incl. add fcm_token + eligibility_notified_at to users)
└── seeders/

routes/
├── api.php             → authenticated (Sanctum) endpoints
├── auth.php            → register/login/logout/OTP/password-reset/fcm-token
├── console.php         → schedules SendDonationReminders
└── web.php

resources/views/emails/ → OTP email templates
api/index.php, vercel.json → Vercel deployment entry point/config
tests/                  → default scaffolding only, no real coverage yet
```

---

## App Flow

- **Register** — `POST /my-api/auth/register` → validates input, hashes password, generates a 4-digit OTP, caches it (3 min TTL) alongside the pending user data, sends the OTP via email, and returns a `verification_id` (UUID) in the response — **no user is created yet at this point**.
  ```json
  {
      "message": "OTP sent to your email. Please verify to complete registration.",
      "verification_id": "9c3b6c2a-2f2f-4e3e-9a2f-7a2e2b2f6d9a"
  }
  ```
  The client must hold onto this `verification_id` and submit it together with the OTP to complete registration.
- **Verify Email** — `POST /my-api/auth/register/verify-email` → takes `verification_id` + `otp`; on success, creates the `User`, marks the email as verified, and issues a Sanctum `token`. On failure (expired/mismatched OTP), returns `400`.
- **Resend OTP** — `POST /my-api/auth/resend-registration-otp` → takes the same `verification_id`, re-generates and re-sends a new OTP for it if the original expired or wasn't received (rate-limited to 1 request/minute).
- **Login** → validate credentials → rate-limited → Sanctum token issued
- **Logout** → revokes only the current token
- **Blood Requests** → create (hospital + request in one transaction) → list nearby/compatible/critical (10km radius) → no update/cancel/show endpoints exist
- **Responses** → donor accepts/rejects a request (compatibility check on accept) → on accept, notifies the requester (`REQUEST_ACCEPTED`: DB row + FCM push if they have a token)
- **Donations** → donor must have accepted + be eligible (3-month rule) → records donation → auto-marks request `FULFILLED` when units met → notifies requester (`REQUEST_FULFILLED`) → resets donor's `eligibility_notified_at`
- **Notifications** → every notification is a DB row (`NotificationService`); if the user has an `fcm_token`, a push is also sent via `FirebaseService` → list / mark-read / mark-all-read
- **FCM Token** → client registers device token (`POST /my-api/auth/fcm-token`) / clears it (`DELETE`)
- **Donation Reminders** → daily scheduled command finds donors eligible again (3 months since last donation, not yet notified) → notifies them (`DONATION_REMINDER`) → marks them available again
- **Chatbot** → proxies message to an external chatbot HTTP service → stores Q&A pair → returns answer

---

## Installation

1. **Clone the repo**
   ```bash
   git clone https://github.com/Shoryan-org/Backend.git
   cd Backend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Set up environment**
   ```bash
   cp .env.example .env
   ```
   Fill in:
   - **Database (Aiven MySQL):** `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` from your Aiven service, plus `MYSQL_ATTR_SSL_CA` pointing to the bundled `database/certs/ca.pem` (Aiven requires TLS).
   - **Mail (Resend):** set `MAIL_MAILER=resend` and add `RESEND_API_KEY` (not present in `.env.example` — add it manually) with your Resend API key. This is what actually delivers the registration/password-reset OTP emails.

   Also manually add these (not present in `.env.example`):
   - `Chatbot_Url` — the external chatbot service URL
   - `FIREBASE_CREDENTIALS` — path to (or contents of) your Firebase service-account JSON — **never commit this file**

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Run the server**
   ```bash
   php artisan serve
   ```
   API available at `http://127.0.0.1:8000/my-api/...`

---

## How to Use (Steps)

1. Register (`POST /my-api/auth/register`) → get `verification_id`
2. Verify email OTP (`POST /my-api/auth/register/verify-email`) → get bearer `token`
3. Attach `Authorization: Bearer <token>` to all further requests
4. Register your device's FCM token (`POST /my-api/auth/fcm-token`) if you want push notifications
5. Create a blood request, or browse nearby ones (`GET /my-api/blood-requests?show=compatible|critical`)
6. Accept/reject a request as a donor — the requester gets notified (DB + push)
7. Donate against an accepted request (`POST /my-api/blood-requests/{id}/donate`) — requester gets notified again if fulfilled
8. Check notifications and donation history
9. Chat with the assistant (`POST /my-api/chatbot`)
10. Clear your FCM token on logout (`DELETE /my-api/auth/fcm-token`)
