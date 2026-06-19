# MondokQu Project Summary

## Goal
Build KitabQu module for book memorization tracking (kitab data, setoran, progress, rapor) as a separate module following existing module patterns.

## Constraints & Preferences
- Must match PerpustakaanQu module structure (Controllers, Models, Requests, Routes, views under `resources/views/modules/kitab-qu/`).
- Permission: `manage kitab` (register in PermissionSeeder, sidebar gate, route middleware).
- Module registration: add to `TenantManagementController` modules list and `defaultActiveModules` config, plus validation `in:` rules.
- Use `BelongsToTenant` trait, `withoutTenantScope()` for cross-tenant queries.
- Cannot change logic/database of existing modules.

## Progress
### Done
- Issue #175: Simplify room index – removed Aktif/Nonaktif cards, kept only Total Kamar & Total Kapasitas; renamed "Santri Aktif" column header to "Santri"; removed unused queries from controller.
- Issue #176: Fix assignable santris logic – filter `whereNull('room_id')`; replaced multiselect with checkbox list in modal; empty state when no assignable santris.
- Issue #177: Fix santri_id validation for superadmin – used `isSuperAdmin()` to skip tenant filter in `Rule::exists` for `StoreLeaveRequestRequest` and `UpdateLeaveRequestRequest`.
- Issue #178: Fix return type mismatch + remove CSV – added `BinaryFileResponse` to `FormatDispatcher` download methods; deleted 3 CSV export classes; removed all CSV branches from controllers/views/enum; default format changed to XLSX.
- Issue #179: Simplify technical terms in Auth > Role & Permission – changed "Manajemen Role" → "Atur Hak Akses", "Permission Management" → "Atur Izin Akun", "Atur Permission" → "Atur Izin Akun", updated all flash messages and descriptive labels.
- Issue #180: Move Branding Pondok to standalone menu – removed from Autentikasi dropdown; added "Profile Pondok" as standalone `sidebar-link` between Autentikasi and SaaS sections.
- Issue #181: Created KitabQu module migrations, models, requests, controllers, and views (data kitab + setoran hafalan + rapor). Registered routes, permission, sidebar navigation, and tenant module configuration.

### In Progress
- (none)

### Blocked
- (none)

## Key Decisions
- KitabQu as separate module (not sub-menu of PerpustakaanQu) because TahfidzQu is the intended pattern and both are distinct features.
- Hafalan tracking simplified to: `kitab_setorans` table with santri_id, kitab_id, tanggal_setoran, materi, status (pending/disetujui/ditolak), catatan, approved_by.
- Rapor page uses grouped query (`GROUP BY santri_id, kitab_id`) for rekap with progress bar.

## Relevant Files
- `app/Modules/KitabQu/`: module directory (Controllers, Models, Requests, Routes)
- `resources/views/modules/kitab-qu/`: dashboard, kategori, kitab, setoran views
- `database/migrations/2026_06_19_00000{1..3}_create_kitab_*.php`: 3 migrations
- `database/seeders/PermissionSeeder.php`: added `manage kitab`
- `routes/web.php`: added `require base_path('app/Modules/KitabQu/Routes/web.php');`
- `resources/views/layouts/navigation.blade.php`: added KitabQu dropdown
- `app/Modules/Saas/Controllers/TenantManagementController.php`: added kitab module entry, defaultActiveModules, and validation rule
