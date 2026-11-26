# Mobile API

These endpoints use the platform's existing users, profiles and posts. The JSON matches the Flutter app's current API client.

## Setup

```powershell
composer install
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8000
```

Sign in using an existing platform account's **email and password**. Its email must already be verified. The demo alias `admin` and demo password are not added to the platform.

For a USB-connected Android phone, replace DEVICE_ID with the ID from `adb devices`:

```powershell
adb -s DEVICE_ID reverse tcp:8000 tcp:8000
```

From the Flutter app folder:

```powershell
flutter run -d DEVICE_ID --dart-define=ALUMNI_USE_API=true --dart-define=ALUMNI_API_BASE_URL=http://127.0.0.1:8000/api/mobile --dart-define=ALUMNI_ALLOW_LOCAL_HTTP=true
```

The same forwarding works with an emulator. Local HTTP requires a debug build; use HTTPS for a deployed server. No Flutter screens need changing. Native Android requests do not require browser CORS settings.

## Routes

| Method | Path | Result |
| --- | --- | --- |
| POST | /api/mobile/login | Send `login` (email) and `password`; returns `token` and profile under `data`. Limited to five requests per minute per IP. |
| POST | /api/mobile/logout | Revokes the current token; returns 204. |
| GET | /api/mobile/me | Profile under `data`: id, name, email, bio, picture_url. |
| PATCH | /api/mobile/me | Update name and/or bio; returns the saved profile under `data`. |
| GET | /api/mobile/feed?page=1 | Up to 20 public posts under `data`, newest first, with integer `next_page` or null. |
| GET | /api/mobile/feed/{id} | One public post under `data`; private/missing posts return 404. |

Authenticated requests use `Authorization: Bearer TOKEN` and `Accept: application/json`. Tokens expire after 30 minutes. Profiles and feed require verified email, as on the website. Logout remains available for an authenticated account even if verification has been removed.

Names must be nonblank and at most 255 characters; biographies may be empty and are limited to 1000 characters. Only name and bio may be submitted to the profile endpoint. Account IDs, email, roles and other fields are rejected with 422. The signed-in user determines which record is changed.

Post fields are id, title, body, author_name and created_at (UTC ISO date). The public feed excludes all private posts, including the user's own. Invalid/missing tokens return 401; unverified accounts return 403; invalid input returns 422. No password or full user record is exposed.

Run the API tests with `php artisan test --compact tests/Feature/MobileApiTest.php`. They use an isolated in-memory database.
