<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboard)
    {
        return Inertia::render('Dashboard/Index', $dashboard->data());
    }
}