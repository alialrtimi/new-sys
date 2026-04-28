<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $users = DB::select("SELECT phone_number,user_code,password_state,full_name,type,add_date,uid,f_id,phone_number,AES_DECRYPT(UNHEX(passwords),'5AX_RYO@IDFG##H876b7&') as p2,id,user_name  FROM users2");



        foreach ($users as $user) {

            User::create([
                'id' => $user->id,
                'name' => $user->user_name,
                'email' => $user->user_name,
                'password' => $user->p2,

                'type' => 'super_admin',

                'email_verified_at' => now(),
                ////
                'phone_number' => $user->phone_number,
                'full_name' => $user->full_name,
                'code' => $user->user_code,
                'password_state' => $user->password_state,
                'adding_date' => $user->add_date,
                'department_id' => $user->f_id,
                'inserted_user_id' => $user->uid,
                'user_type_id' => $user->type,

            ]);
        }
    }
}
