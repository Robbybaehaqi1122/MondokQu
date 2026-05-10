# Release Notes

## Unreleased

- Added tenant impersonation for Superadmin support troubleshooting, including an active impersonation banner and audit trail.
- Added tenant-scoped audit log filters and summary cards for Admin Pondok while keeping log deletion limited to Superadmin.
- Added operational dashboard reporting widgets for monthly revenue, outstanding invoices, overdue invoices, santri status, and top overdue bills.
- Hardened santri payment create/update/delete flows with transaction-level invoice locking to reduce race condition risk.
