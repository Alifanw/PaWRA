<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns if they don't exist
        if (!Schema::hasColumn('ticket_sale_items', 'unit_price') || !Schema::hasColumn('ticket_sale_items', 'subtotal')) {
            Schema::table('ticket_sale_items', function (Blueprint $table) {
                if (!Schema::hasColumn('ticket_sale_items', 'unit_price')) {
                    $table->decimal('unit_price', 10, 2)->default(0)->after('ticket_type_id');
                }
                if (!Schema::hasColumn('ticket_sale_items', 'subtotal')) {
                    $table->decimal('subtotal', 12, 2)->default(0)->after('unit_price');
                }
            });
        }

        // Add indexes only if the referenced columns exist
        if (Schema::hasColumn('ticket_sale_items', 'ticket_sale_id')) {
            // add index only if it does not already exist
            $driver = \Illuminate\Support\Facades\Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                $exists = \Illuminate\Support\Facades\DB::select(
                    "SHOW INDEX FROM `ticket_sale_items` WHERE Key_name = ?",
                    ['ticket_sale_items_ticket_sale_id_index']
                );
                $shouldCreate = empty($exists);
            } elseif ($driver === 'sqlite') {
                $indexes = \Illuminate\Support\Facades\DB::select("PRAGMA index_list('ticket_sale_items')");
                $found = false;
                foreach ($indexes as $idx) {
                    if (isset($idx->name) && $idx->name === 'ticket_sale_items_ticket_sale_id_index') {
                        $found = true;
                        break;
                    }
                    // Some sqlite builds return arrays instead of objects
                    if (is_array((array) $idx) && in_array('ticket_sale_items_ticket_sale_id_index', (array) $idx)) {
                        $found = true;
                        break;
                    }
                }
                $shouldCreate = !$found;
            } else {
                // Fallback: attempt to create the index if it doesn't exist according to Schema
                $shouldCreate = true;
            }

            if ($shouldCreate) {
                Schema::table('ticket_sale_items', function (Blueprint $table) {
                    $table->index('ticket_sale_id');
                });
            }
        }

        if (Schema::hasColumn('ticket_sale_items', 'ticket_type_id')) {
            $driver = \Illuminate\Support\Facades\Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                $exists = \Illuminate\Support\Facades\DB::select(
                    "SHOW INDEX FROM `ticket_sale_items` WHERE Key_name = ?",
                    ['ticket_sale_items_ticket_type_id_index']
                );
                $shouldCreate = empty($exists);
            } elseif ($driver === 'sqlite') {
                $indexes = \Illuminate\Support\Facades\DB::select("PRAGMA index_list('ticket_sale_items')");
                $found = false;
                foreach ($indexes as $idx) {
                    if (isset($idx->name) && $idx->name === 'ticket_sale_items_ticket_type_id_index') {
                        $found = true;
                        break;
                    }
                    if (is_array((array) $idx) && in_array('ticket_sale_items_ticket_type_id_index', (array) $idx)) {
                        $found = true;
                        break;
                    }
                }
                $shouldCreate = !$found;
            } else {
                $shouldCreate = true;
            }

            if ($shouldCreate) {
                Schema::table('ticket_sale_items', function (Blueprint $table) {
                    $table->index('ticket_type_id');
                });
            }
        }
    }

    public function down(): void
    {
        // Drop indexes and columns if they exist
        if (Schema::hasColumn('ticket_sale_items', 'ticket_sale_id')) {
            Schema::table('ticket_sale_items', function (Blueprint $table) {
                $table->dropIndex(['ticket_sale_id']);
            });
        }

        if (Schema::hasColumn('ticket_sale_items', 'ticket_type_id')) {
            Schema::table('ticket_sale_items', function (Blueprint $table) {
                $table->dropIndex(['ticket_type_id']);
            });
        }

        Schema::table('ticket_sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_sale_items', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
            if (Schema::hasColumn('ticket_sale_items', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
        });
    }
};
