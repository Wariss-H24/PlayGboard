<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Requests\UpdateCompteRequest;
use Inertia\Inertia;

class CompteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Compte');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompteRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Compte $compte)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Compte $compte)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompteRequest $request, Compte $compte)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Compte $compte)
    {
        //
    }
}
