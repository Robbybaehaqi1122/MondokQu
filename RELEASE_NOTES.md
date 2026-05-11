# Release Notes

## 2026-05-11

- Added Wali Santri account assignment directly from Santri create/edit forms, including list/detail visibility and tenant-safe validation.
- Added Wali Santri portal MVP with guardian-to-santri links, parent dashboard summaries, active invoices, and recent payment history.
- Added tenant impersonation for Superadmin support troubleshooting, including an active impersonation banner and audit trail.
- Added tenant-scoped audit log filters and summary cards for Admin Pondok while keeping log deletion limited to Superadmin.
- Added operational dashboard reporting widgets for monthly revenue, outstanding invoices, overdue invoices, santri status, and top overdue bills.
- Hardened santri payment create/update/delete flows with transaction-level invoice locking to reduce race condition risk.
- Added CSV export for santri data, payment reports, and filtered invoice lists.
- Added short-lived admin dashboard statistics cache with configurable TTL.
