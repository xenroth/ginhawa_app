# Ginhawa App

Ginhawa is a Laravel 12 community forum and global organization application by **Akhzaroth Khan (Richard C. Cupal, LPT)**. The supplied organization and forum templates remain in the project root as source references; their pitch-black, silver-metal, scanline, Courier Prime, Oswald, Font Awesome, terminal, filtering, modal, and Web Audio interaction language is integrated into the Blade experience.

Current release: `alpha-release-v.1.0.9` ([version file](VERSION)). See [CHANGELOG.md](CHANGELOG.md) for release history and deployment notes.

## First run

1. Install PHP 8.2+, Composer, and a database driver. The application uses Laravel 12.
2. Run `composer install`.
3. Copy `.env.example` to `.env` and set `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. SQLite works with `DB_DATABASE=database/database.sqlite`.
4. Run `php artisan key:generate`.
5. Start with `php artisan serve` and open `/`.

The web middleware checks for the `roles` table on the first request and automatically runs `migrate:fresh --seed`. This creates the RBAC roles and the initial administrator. Change the seeded password immediately: `admin@ginhawa.local` / `change-me-now`.

## Main routes

- `/` global organization page, four pillars, encrypted terminal
- `/community` member-only community hub with sector filtering, search, thread modal, replies, and transmission form
- `/register` pending registration workflow
- `/profile` member dashboard, citizen ID card, and verification submissions
- `/admin` moderator and administrator dashboard

## Community

- Live online-citizen presence and registered-citizens count, driven by real session and database queries.
- Clickable directives widget, per-sector hashtag filtering, and up/down reactions with a coming-soon hint for unsupported types.
- Council approval flow: sectors with `REQUIRES APPROVAL` enabled keep admin moderation; open sectors publish citizen transmissions instantly. Administrator and moderator posts are always approved.
- Mobile-safe scrolling in the transmission panel with an on-screen keyboard.

## Admin

- Dashboard with pagination and filtering across users, sectors, and directives.
- Per-sector `REQUIRES APPROVAL` toggles in the Sectors & Directives settings.
- CMS settings for SEO, site identity, logo, and favicon. Uploaded favicons and logos are served from the public disk through `public/storage`; the root `.htaccess` allows `/storage/*` requests so public branding assets resolve while `.env` and the raw application directories stay protected.

## RBAC

`citizen` is read-only, `member` can submit transmissions after approval, `moderator` can approve users/posts, and `administrator` has full control. The `RoleMiddleware` protects the admin endpoint and moderation actions.

## Implementation map

- `app/Models`: users, roles, sectors, directives, posts, and community interactions
- `app/Http/Controllers`: forum, auth, admin, and community-interaction actions
- `app/Http/Middleware`: first-load installation and role checks
- `database/migrations`: role/user, post, and citizen-number schema
- `database/seeders/DatabaseSeeder.php`: initial roles and administrator
- `resources/views`: global, forum, auth, admin, installer, and shared layout
