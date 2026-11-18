# MedAssist Dental

MedAssist Dental is a Laravel frontend for exploring dental specialties, finding dentists, and previewing appointment booking. It includes 38 desktop screens across the public site, account flow, patient portal, dentist workspace, and clinic administration. The interface supports English and Arabic, with a right-to-left layout in Arabic.

## Project references

- [Miro database schema](https://miro.com/app/board/uXjVEM0sPeY=/?share_link_id=20996196368)
- [Figma product design](https://www.figma.com/design/J5piMSsJScs8gRjD0dVl38/MedAssist-Dental-%E2%80%94-Complete-Figma-Product-Design?node-id=3-2768&t=KjRTMNjR2VPQEsis-1)
- [Trello project board invitation](https://trello.com/invite/b/6afd52643fff919485c17707/ATTIab64841c9d783c723a57e8753929029dDE3D6304/medassit)

## Run locally

The project uses Laravel 12, PHP 8.2 or newer, Composer, and SQLite. Enable the PHP `pdo_sqlite` and `mbstring` extensions. The Blade views load CSS and JavaScript directly from `public/`, so the preview does not require an npm build.

On a fresh copy, open PowerShell in the project folder and run:

```powershell
composer install
if (!(Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
if (!(Test-Path database/database.sqlite)) { New-Item -ItemType File database/database.sqlite | Out-Null }
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8018 --no-reload
```

For an already configured copy, run only the last command. Keep the server running while using the localhost links.

## Preview pages

| Area | Local link |
| --- | --- |
| Public homepage | [Home](http://127.0.0.1:8018/) |
| Dentist directory | [Find a dentist](http://127.0.0.1:8018/dentists) |
| Appointment flow | [Start booking](http://127.0.0.1:8018/patient/booking) |
| Account screens | [Sign-in preview](http://127.0.0.1:8018/login) |
| Patient portal | [Patient dashboard](http://127.0.0.1:8018/patient) |
| Dentist workspace | [Dentist dashboard](http://127.0.0.1:8018/dentist) |
| Clinic administration | [Admin dashboard](http://127.0.0.1:8018/admin) |
| Arabic interface | [Arabic homepage](http://127.0.0.1:8018/?lang=ar) · [Arabic admin](http://127.0.0.1:8018/admin?lang=ar) |

Use the **العربية** or **English** switch to change languages. The selection follows you across pages during the session.

## Project structure

| Path | Purpose |
| --- | --- |
| `routes/web.php` | Web routes for the frontend and original demo |
| `app/Http/Controllers/DesktopController.php` | Screen rendering, locale selection, and booking preview data |
| `config/desktop.php` | Desktop screen and route definitions |
| `config/desktop-content.php` | Sample data for the desktop screens |
| `resources/views/desktop/layouts/` | Shared Blade layouts extended by the screen view |
| `resources/views/desktop/partials/` | Shared navigation, role sidebars, language switch, and UI markup |
| `resources/views/desktop/screens/` | Screen templates |
| `public/css/desktop.css` | Frontend styles and right-to-left layout support |
| `lang/ar.json` | Arabic translations |

## Implementation status

The dentist directory and admin record filters work with sample data. The account screens, booking confirmation, and record editing are frontend previews. Authentication, real appointment reservations, and permanent record changes remain pending. The Miro board is the reference for the application schema. The local migrations currently initialize Laravel's starter tables.

Run the existing test suite with `php artisan test`. List available routes with `php artisan route:list --except-vendor`.
