<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    /**
     * Root admin URL — redirect to dashboard when authenticated, otherwise to login.
     */
    public function index()
    {
        if (auth()->check()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('login');
    }
}
