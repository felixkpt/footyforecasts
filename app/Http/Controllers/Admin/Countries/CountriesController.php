<?php

namespace App\Http\Controllers\Admin\Countries;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Inertia\Inertia;

class CountriesController extends Controller
{

    function index()
    {
        $countries = Country::with(['competitions']);
        return response('Countries/Index', compact('countries'));
    }
}
