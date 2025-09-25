<?php

namespace App\Http\Controllers;

use App\Models\Postman;
use App\Models\Location;
use Illuminate\Http\Request;

class PostmanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $postmen = Postman::with('location')->paginate(15);
        return view('admin.postmen.index', compact('postmen'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locations = Location::all();
        return view('admin.postmen.create', compact('locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:postmen,mobile',
            'location_id' => 'required|exists:locations,id',
            'status' => 'required|in:active,inactive',
        ]);

        Postman::create($request->all());

        return redirect()->route('postmen.index')
                        ->with('success', 'Postman created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Postman $postman)
    {
        return view('admin.postmen.show', compact('postman'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Postman $postman)
    {
        $locations = Location::all();
        return view('admin.postmen.edit', compact('postman', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Postman $postman)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:postmen,mobile,' . $postman->id,
            'location_id' => 'required|exists:locations,id',
            'status' => 'required|in:active,inactive',
        ]);

        $postman->update($request->all());

        return redirect()->route('postmen.index')
                        ->with('success', 'Postman updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Postman $postman)
    {
        $postman->delete();

        return redirect()->route('postmen.index')
                        ->with('success', 'Postman deleted successfully.');
    }
}
