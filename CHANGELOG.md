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

## Unreleased

Future changes should be added here before the next release tag.