<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /** Create the table / column. */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Secret token the customer uses on /track (format XXXX-XXXX-XXXX).
            $table->string('tracking_token', 20)->nullable()->unique()->after('order_number');
        });

        // Give every order that already exists a token.
        $used = [];
        foreach (DB::table('orders')->whereNull('tracking_token')->pluck('id') as $id) {
            do {
                $token = $this->token();
            } while (isset($used[$token]));
            $used[$token] = true;

            DB::table('orders')->where('id', $id)->update(['tracking_token' => $token]);
        }
    }

    /** Reverse the migration. */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['tracking_token']);
            $table->dropColumn('tracking_token');
        });
    }

    /** Random 12 character token (same format as Order::generateToken). */
    private function token(): string
    {
        $chars = '';
        for ($i = 0; $i < 12; $i++) {
            $chars .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return implode('-', str_split($chars, 4));
    }
};
