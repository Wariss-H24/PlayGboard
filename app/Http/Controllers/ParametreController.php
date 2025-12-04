<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use App\Http\Requests\StoreParametreRequest;
use App\Http\Requests\UpdateParametreRequest;
use Inertia\Inertia;
class ParametreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Parametres');
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
    public function store(StoreParametreRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Parametre $parametre)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Parametre $parametre)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateParametreRequest $request, Parametre $parametre)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Parametre $parametre)
    {
        //
    }
}
