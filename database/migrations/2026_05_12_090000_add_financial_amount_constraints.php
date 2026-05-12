<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        match (DB::getDriverName()) {
            'sqlite' => $this->addSqliteConstraints(),
            'mysql' => $this->addMysqlConstraints(),
            'mariadb' => $this->addMariaDbConstraints(),
            'pgsql' => $this->addPostgresConstraints(),
            'sqlsrv' => $this->addSqlServerConstraints(),
            default => null,
        };
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        match (DB::getDriverName()) {
            'sqlite' => $this->dropSqliteConstraints(),
            'mysql' => $this->dropMysqlConstraints(),
            'mariadb' => $this->dropMariaDbConstraints(),
            'pgsql' => $this->dropPostgresConstraints(),
            'sqlsrv' => $this->dropSqlServerConstraints(),
            default => null,
        };
    }

    protected function addSqliteConstraints(): void
    {
        DB::statement(<<<'SQL'
            CREATE TRIGGER santri_invoices_amount_insert_check
            BEFORE INSERT ON santri_invoices
            FOR EACH ROW
            WHEN NEW.amount <= 0 OR NEW.paid_amount < 0 OR NEW.paid_amount > NEW.amount
            BEGIN
                SELECT RAISE(ABORT, 'santri_invoices amount constraints violated');
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER santri_invoices_amount_update_check
            BEFORE UPDATE OF amount, paid_amount ON santri_invoices
            FOR EACH ROW
            WHEN NEW.amount <= 0 OR NEW.paid_amount < 0 OR NEW.paid_amount > NEW.amount
            BEGIN
                SELECT RAISE(ABORT, 'santri_invoices amount constraints violated');
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER santri_payments_amount_insert_check
            BEFORE INSERT ON santri_payments
            FOR EACH ROW
            WHEN NEW.amount <= 0
            BEGIN
                SELECT RAISE(ABORT, 'santri_payments amount constraints violated');
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER santri_payments_amount_update_check
            BEFORE UPDATE OF amount ON santri_payments
            FOR EACH ROW
            WHEN NEW.amount <= 0
            BEGIN
                SELECT RAISE(ABORT, 'santri_payments amount constraints violated');
            END
        SQL);
    }

    protected function addMysqlConstraints(): void
    {
        DB::statement('ALTER TABLE santri_invoices ADD CONSTRAINT santri_invoices_amount_positive_chk CHECK (amount > 0)');
        DB::statement('ALTER TABLE santri_invoices ADD CONSTRAINT santri_invoices_paid_nonnegative_chk CHECK (paid_amount >= 0)');
        DB::statement('ALTER TABLE santri_invoices ADD CONSTRAINT santri_invoices_paid_lte_amount_chk CHECK (paid_amount <= amount)');
        DB::statement('ALTER TABLE santri_payments ADD CONSTRAINT santri_payments_amount_positive_chk CHECK (amount > 0)');
    }

    protected function addMariaDbConstraints(): void
    {
        $this->addMysqlConstraints();
    }

    protected function addPostgresConstraints(): void
    {
        DB::statement('ALTER TABLE santri_invoices ADD CONSTRAINT santri_invoices_amount_positive_chk CHECK (amount > 0)');
        DB::statement('ALTER TABLE santri_invoices ADD CONSTRAINT santri_invoices_paid_nonnegative_chk CHECK (paid_amount >= 0)');
        DB::statement('ALTER TABLE santri_invoices ADD CONSTRAINT santri_invoices_paid_lte_amount_chk CHECK (paid_amount <= amount)');
        DB::statement('ALTER TABLE santri_payments ADD CONSTRAINT santri_payments_amount_positive_chk CHECK (amount > 0)');
    }

    protected function addSqlServerConstraints(): void
    {
        DB::statement('ALTER TABLE santri_invoices ADD CONSTRAINT santri_invoices_amount_positive_chk CHECK (amount > 0)');
        DB::statement('ALTER TABLE santri_invoices ADD CONSTRAINT santri_invoices_paid_nonnegative_chk CHECK (paid_amount >= 0)');
        DB::statement('ALTER TABLE santri_invoices ADD CONSTRAINT santri_invoices_paid_lte_amount_chk CHECK (paid_amount <= amount)');
        DB::statement('ALTER TABLE santri_payments ADD CONSTRAINT santri_payments_amount_positive_chk CHECK (amount > 0)');
    }

    protected function dropSqliteConstraints(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS santri_invoices_amount_insert_check');
        DB::statement('DROP TRIGGER IF EXISTS santri_invoices_amount_update_check');
        DB::statement('DROP TRIGGER IF EXISTS santri_payments_amount_insert_check');
        DB::statement('DROP TRIGGER IF EXISTS santri_payments_amount_update_check');
    }

    protected function dropMysqlConstraints(): void
    {
        DB::statement('ALTER TABLE santri_invoices DROP CHECK santri_invoices_amount_positive_chk');
        DB::statement('ALTER TABLE santri_invoices DROP CHECK santri_invoices_paid_nonnegative_chk');
        DB::statement('ALTER TABLE santri_invoices DROP CHECK santri_invoices_paid_lte_amount_chk');
        DB::statement('ALTER TABLE santri_payments DROP CHECK santri_payments_amount_positive_chk');
    }

    protected function dropMariaDbConstraints(): void
    {
        DB::statement('ALTER TABLE santri_invoices DROP CONSTRAINT santri_invoices_amount_positive_chk');
        DB::statement('ALTER TABLE santri_invoices DROP CONSTRAINT santri_invoices_paid_nonnegative_chk');
        DB::statement('ALTER TABLE santri_invoices DROP CONSTRAINT santri_invoices_paid_lte_amount_chk');
        DB::statement('ALTER TABLE santri_payments DROP CONSTRAINT santri_payments_amount_positive_chk');
    }

    protected function dropPostgresConstraints(): void
    {
        DB::statement('ALTER TABLE santri_invoices DROP CONSTRAINT IF EXISTS santri_invoices_amount_positive_chk');
        DB::statement('ALTER TABLE santri_invoices DROP CONSTRAINT IF EXISTS santri_invoices_paid_nonnegative_chk');
        DB::statement('ALTER TABLE santri_invoices DROP CONSTRAINT IF EXISTS santri_invoices_paid_lte_amount_chk');
        DB::statement('ALTER TABLE santri_payments DROP CONSTRAINT IF EXISTS santri_payments_amount_positive_chk');
    }

    protected function dropSqlServerConstraints(): void
    {
        DB::statement('ALTER TABLE santri_invoices DROP CONSTRAINT santri_invoices_amount_positive_chk');
        DB::statement('ALTER TABLE santri_invoices DROP CONSTRAINT santri_invoices_paid_nonnegative_chk');
        DB::statement('ALTER TABLE santri_invoices DROP CONSTRAINT santri_invoices_paid_lte_amount_chk');
        DB::statement('ALTER TABLE santri_payments DROP CONSTRAINT santri_payments_amount_positive_chk');
    }
};
