<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class PageController extends Controller
{
    public function welcome()
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }



    public function Chat()
    {
        return Inertia::render('Chat');
    }

    public function dashboard()
    {
        return Inertia::render('Dashboard');
    }
}
