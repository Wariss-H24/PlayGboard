<?php

namespace App\Http\Controllers;

use App\Models\Traducteur;
use App\Http\Requests\StoreTraducteurRequest;
use App\Http\Requests\UpdateTraducteurRequest;
use Inertia\Inertia;

class TraducteurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Traducteur');
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
    public function store(StoreTraducteurRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Traducteur $traducteur)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Traducteur $traducteur)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTraducteurRequest $request, Traducteur $traducteur)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Traducteur $traducteur)
    {
        //
    }
}
