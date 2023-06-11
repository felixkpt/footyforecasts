<?php

namespace App\Http\Controllers\Client\Posts;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class PostsController extends Controller
{
    function index()
    {
        return response(['data' => []]);
    }

    function show()
    {
        // "mastering-the-game-how-to-predict-football-matches-and-maximize-betting-profits" // routes/web.php:19
        return Inertia::render('Client/Posts/Show');
    }
}
