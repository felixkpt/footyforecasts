<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AdminController extends Controller
{
    function index()
    {
        return Inertia::render('Dashboard/ECommerce');
    }
}
