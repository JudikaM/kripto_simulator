<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimulatorController extends Controller
{
    /**
     * Render the unified Kripto Simulator UI.
     */
    public function index(): \Illuminate\View\View
    {
        return view('simulator.index');
    }
}
