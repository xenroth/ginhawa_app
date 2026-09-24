# Changelog

All notable Ginhawa changes are documented here. Versions follow the Git tag or the value stored in `VERSION`.

## [alpha-release-v.1.0.0] - 2026-09-24

### Added

- Laravel 12 application baseline and shared-hosting deployment support.
- Member-only `/community` endpoint replacing the public `/forum` endpoint.
- Safe post visibility queries, dynamic sectors, replies, connections, and direct messages.
- Transactional registration with unique `GHW-XXXX-XXXX-YYYY` citizen numbers.
- Optional XRP donation address and destination memo configuration.
- Member profiles with privacy settings, avatars, cover photos, and public-disk storage.
- Verification document submission and administrator/moderator review queue.
- Benefactor role and digital citizen-card access.
- Dynamic roles, permissions, sectors, core directives, SEO settings, favicon, logo, and site identity controls.
- GitHub release updater with protected `.env`, `vendor`, `storage`, and cache paths.

### Fixed

- Corrected the approved-post community loading path and null-safe tag handling.
- Added required Laravel runtime directory placeholders and shared-hosting Composer guidance.

### Deployment notes

- Run `php artisan storage:link` for uploaded profile, verification, and branding files.
- Do not run `migrate:fresh` against an existing production database.

## [alpha-release-v.1.0.1] - 2026-09-24

### Added

- Member profile editing, avatar and cover uploads, privacy controls, and verification submissions.
- Admin verification review queue, role creation, sector management, directive management, and CMS settings.
- Dynamic SEO metadata, favicon/logo uploads, XRP configuration, comments, connections, and messages.
- Benefactor role migration and managed community defaults for existing installations.

### Fixed

- Removed the public `/forum` endpoint in favor of the authenticated `/community` endpoint.
- Made post tag decoding and visibility queries safe for approved content.

### Deployment notes

- Run `php artisan migrate --force` to apply the profile, CMS, verification, and community migrations.
- Run `php artisan storage:link` before using profile, verification, favicon, or logo uploads.
- Run `php artisan optimize:clear` after deployment.

## [alpha-release-v.1.0.2] - 2026-09-24

### Added

- Restored the homepage organization experience with hero metrics, four pillars, identity generator, member access, and encrypted terminal sections.
- Added an embedded login form directly to the homepage.
- Added authenticated citizen-card preview binding and PNG export using the registered user's citizen number.

### Changed

- The homepage is publicly viewable for orientation, while `/community`, profiles, identity downloads, and member tools remain authenticated.
- Guest citizen-card previews are visibly locked until registration and login.

## [alpha-release-v.1.0.3] - 2026-09-24

### Changed

- Replaced the legacy admin dashboard with a cyberpunk command-center interface.
- Added dedicated navigation for CMS settings and verification review.
- Improved citizen registry controls, post moderation actions, release checking, metrics, and destructive-action confirmation.

## [alpha-release-v.1.0.4] - 2026-09-24

### Changed

- Hide the homepage member-access section after authentication; signed-in members see the community entry instead.
- Replace hardcoded identity values with admin-managed defaults and per-member jurisdiction/designation assignments.
- Replace visible BAKUNAWA terminology with GINHAWA branding across the application interface.
- Make the shared header website name and tagline editable from `/admin/settings`.

### Database

- Added migration `2026_09_24_000006_add_identity_assignment_fields` for member identity assignments.

## [alpha-release-v.1.0.5] - 2026-09-24

### Added

- Added a clickable authenticated username control in the shared header.
- Added account actions for Profile & Verification, Community, Admin Command, and Logout.

### Profile management

- Confirmed the existing `/profile` dashboard supports name, phone, social handle, privacy, avatar, cover photo, and verification document management.

## [alpha-release-v.1.0.6] - 2026-09-24

### Fixed

- Removed the duplicate `/community` route alias named `forum`.
- Ensured Laravel registers the `GET /community` endpoint under the `community` route name used by the shared layout.
- Prevented the `Route [community] not defined` production 500 error after route-cache clearing.

### Deployment

- After updating, run `php artisan optimize:clear` and verify with `php artisan route:list --path=community`.

## [alpha-release-v.1.0.7] - 2026-09-24

### Fixed

- Added non-destructive content schema repair for missing post moderation columns and managed sectors/directives.
- Wrapped community loading, transmission creation/edit/delete, moderation, and replies with logging and user-safe failure messages.
- Added an orphan-safe post author relationship so old records cannot crash the community/admin feed.
- Restricted verification uploads to JPG, JPEG, and PNG files.
- Replaced broken public verification URLs with authenticated document downloads from private storage.

### Admin improvements

- Added typed member search for identity assignment with a maximum of 10 results.
- Kept managed sectors connected to community validation and added clearer synchronization feedback.

### Deployment

- Run `php artisan migrate --force`, `php artisan storage:link` is no longer required for verification documents, and run `php artisan optimize:clear` after updating.

## [alpha-release-v.1.0.8] - 2026-09-24

### Profile

- Added reliable authenticated avatar and cover-photo delivery that works without a public `storage` symlink.
- Added a complete profile citizen-ID generator and PNG export using the member's assigned title, roles, jurisdiction, and citizen number.
- Added visible title/designation and role details to the profile dashboard.
- Added password change with current-password verification and confirmed new password fields.

### Database

- Added a migration to backfill citizen numbers for existing accounts.

### Deployment

- Run `php artisan migrate --force` and `php artisan optimize:clear` after updating.

## Unreleased

Future changes should be added here before the next release tag.