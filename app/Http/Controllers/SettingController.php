<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoresettingRequest;
use App\Http\Requests\UpdatesettingRequest;
use App\Models\setting;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoresettingRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(setting $setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatesettingRequest $request, setting $setting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(setting $setting)
    {
        //
    }
}
