<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Helpers\CounterHelper;
use App\Helpers\PermissionHelper;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        $count = CounterHelper::incrementCounter('login');
        return Inertia::render('Admin/Auth/Login',[
            'count' => $count
        ]);
    }

    public function login(Request $request)
    {
        /*
        // Add your login logic here
        // Check if the user is an admin and redirect accordingly
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'isAdmin' => true])) {
            return redirect()->route('admin.dashboard'); // Redirect to the admin dashboard
        }
        if (PermissionHelper::userHasPermission('ver_dashboard')) {
            return redirect()->route('admin.dashboard');
        }
        */

        return redirect()->route('admin.login')->with('error', 'Invalid credentials.');
    }

    public function logout(Request $request)
    {        
        Auth::guard('web')->logout();
       
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        //return redirect('/');
        return redirect()->route('admin.login');
    }
}
