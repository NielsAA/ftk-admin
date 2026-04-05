<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Contracts\View\View;

class HomePageController extends Controller
{
    public function __invoke(): View
    {
        $teams = Team::query()
            ->with(['trainingSessions:id,name'])
            ->orderBy('price')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'photo_path', 'price', 'price_type']);

        return view('home', [
            'teams' => $teams,
        ]);
    }
}
