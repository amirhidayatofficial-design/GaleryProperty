<?php

namespace App\Http\Controllers;

use App\Models\PropertyProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Katalog publik: hanya listing yang sudah diverifikasi admin.
 * Kontak agency (WhatsApp/email) sengaja tidak ditampilkan; pembeli menghubungi agency lewat chat.
 */
class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = PropertyProject::published()->with(['agencyPartner', 'primaryPhoto']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('province', 'like', "%{$search}%");
            });
        }

        foreach (['property_type', 'syariah_scheme'] as $filter) {
            if ($value = $request->input($filter)) {
                $query->where($filter, $value);
            }
        }

        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', $maxPrice);
        }

        $propertyProjects = $query->latest()->paginate(9)->withQueryString();

        return view('catalog.index', compact('propertyProjects'));
    }

    public function show(PropertyProject $propertyProject): View
    {
        abort_unless($propertyProject->is_verified, 404);

        $propertyProject->load(['agencyPartner', 'photos']);

        return view('catalog.show', compact('propertyProject'));
    }
}
