<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class userController extends Controller
{

    public function index()
    {
        if (Auth::user()->type != 'super_admin')
            abort(404);
        $users = User::withTrashed()->get();
        // dd($users);
        // Pass the data to the Inertia view
        return Inertia::render('Users', [
            'users' => $users,
        ]);
    }

    public function change_user_password(Request $request)
    {
        if (Auth::user()->type != 'super_admin')
            abort(404);
        $request->validate([

            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // dd($request->id);

        $user = User::where('id', '=', $request->id)->get()[0];
        // dd(1);
        // Update the user type
        $user->password =  $request->password;
        $user->save();


        return redirect()->back()->with('success', 'تم التغيير');
    }
    public function change_user_type(Request $request)
    {
        if (Auth::user()->type != 'super_admin')
            abort(404);
        // dd($request->id);

        $user = User::where('id', '=', $request->id)->get()[0];
        // dd(1);
        // Update the user type
        $user->type = $request->type;
        $user->save();


        return redirect()->back()->with('success', 'تم التغيير');
    }


    public function delete_user($id)
    {
        if (Auth::user()->type != 'super_admin')
            abort(404);
        $user = DB::table('users')->where('id', $id)->first();

        if ($user) {
            DB::table('users')->where('id', $id)->update([
                'email_verified_at' => null,
            ]);

            return redirect()->back()->with('success', 'تم إلغاء التفعيل');
        }

        return redirect()->back()->with('error', 'المستخدم غير موجود');
    }

    public function active_user($id)
    {
        if (Auth::user()->type != 'super_admin')
            abort(404);

        $user = DB::table('users')->where('id', $id)->first();

        if ($user) {
            DB::table('users')->where('id', $id)->update([
                'email_verified_at' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'تم التفعيل');
        }

        return redirect()->back()->with('error', 'المستخدم غير موجود');
    }



    public function store(Request $request)
    {
        if (Auth::user()->type != 'super_admin')
            abort(404);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'type' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => $request['password'],
        ]);

        return redirect()->back()->with('success', 'تم انشاء حساب جديد');
    }
}
