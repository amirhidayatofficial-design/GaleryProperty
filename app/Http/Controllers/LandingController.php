<?php

namespace App\Http\Controllers;

use App\Models\AgencyPartner;
use App\Models\PropertyProject;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        $featuredProjects = PropertyProject::published()
            ->with('primaryPhoto')
            ->latest()
            ->take(6)
            ->get();

        $stats = [
            'projects' => PropertyProject::published()->count(),
            'agencies' => AgencyPartner::where('verification_status', 'verified')->count(),
            'cities' => PropertyProject::published()->distinct()->count('city'),
        ];

        return view('welcome', compact('featuredProjects', 'stats'));
    }
}
