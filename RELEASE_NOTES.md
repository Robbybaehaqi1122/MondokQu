# Release Notes

## 2026-05-11

- Added database-level financial amount constraints for santri invoices and payments to reject negative, zero, and overpaid invoice balances.
- Added print-friendly Wali Santri payment receipts for linked invoice details.
- Added Wali Santri invoice detail pages with linked-santri access checks and payment history.
- Simplified mobile screens by hiding optional explanatory copy while preserving data, validation errors, and action labels.
- Tightened global mobile UI density with smaller typography, compact Tabler cards, forms, buttons, tables, modals, and navigation spacing.
- Improved the Wali Santri portal finance sections with mobile card layouts for active invoices and recent payments.
- Optimized Admin dashboard cache-miss aggregates by grouping user, santri, and finance counters into fewer queries.
- Cleared stale dashboard route references after the Santri create flow moved to the modal-based `santri.index` page.
- Added Wali Santri account assignment directly from Santri create/edit forms, including list/detail visibility and tenant-safe validation.
- Added Wali Santri portal MVP with guardian-to-santri links, parent dashboard summaries, active invoices, and recent payment history.
- Added tenant impersonation for Superadmin support troubleshooting, including an active impersonation banner and audit trail.
- Added tenant-scoped audit log filters and summary cards for Admin Pondok while keeping log deletion limited to Superadmin.
- Added operational dashboard reporting widgets for monthly revenue, outstanding invoices, overdue invoices, santri status, and top overdue bills.
- Hardened santri payment create/update/delete flows with transaction-level invoice locking to reduce race condition risk.
- Added CSV export for santri data, payment reports, and filtered invoice lists.
- Added short-lived admin dashboard statistics cache with configurable TTL.
