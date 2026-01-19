<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Redirect;

class AssetController extends Controller
{
    /**
     * Display assets list page (Livewire AssetsManager component handles everything)
     */
    public function index(): View
    {
        return view('pages.assets.index');
    }

    /**
     * Display asset summary page
     */
    public function summary(): View
    {
        return view('pages.assets.summary');
    }

    /**
     * Display asset detail page
     */
    public function show(Asset $asset): View
    {
        $asset->load(['category', 'location', 'supplier', 'transactions', 'loans', 'maintenances']);

        return view('pages.assets.show', compact('asset'));
    }
}
