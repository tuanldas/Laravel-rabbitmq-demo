<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasView = DB::select("SELECT table_name FROM information_schema.views WHERE table_name = 'first_checkin_last_checkout_view';");
        if (empty($hasView)) {
            DB::statement(
                'CREATE VIEW first_checkin_last_checkout_view AS
            select checkin.username,
       checkin.email,
       max(checkin.first_name) as first_name,
       max(checkin.last_name)  as last_name,
       min(checkin.create_at)  as checkin,
       max(c.create_at)        as checkout
from checkin
         left join public.checkout c on checkin.user_id = c.user_id and checkin.create_at::date = c.create_at::date
group by checkin.create_at::date,
         c.create_at::date,
         checkin.email,
         checkin.username;'
            );
        }
    }
};
