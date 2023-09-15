<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ToolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::create([
            'user_id' => 1,
            'what' => 'tool.index page open/hover'
        ]);
        return view('tool.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Log::create([
            'user_id' => 1,
            'what' => 'tool.create page open/hover'
        ]);
        return view('tool.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|string',
                'category' => 'string',
                'tag' => 'string',
                'factory_name' => 'string',
            ]);
            Tool::create(
                [
                    'name' => $request->name,
                    'category' => $request->category,
                    'tag' => $request->tag,
                    'factory_name' => $request->factory_name,
                ]
            );
            Log::create([
                'user_id' => 1,
                'what' => 'tool.create Tool created successfully |' . json_encode($request->all())
            ]);
            DB::commit();
            return redirect()->route('tools.index')->with('success', __('Tool created successfully.'));
        } catch (\Throwable $th) {

            DB::rollback();
            Log::create([
                'user_id' => 1,
                'what' => 'tool store failed' . json_encode($request->all()) . " | " . $th->getMessage()
            ]);
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tool $tool)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tool $tool)
    {
        Log::create([
            'user_id' => 1,
            'what' => 'tool.edit page open/hover | id:' . $tool->id
        ]);
        return view('tool.edit', compact('tool'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tool $tool)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|string',
                'category' => 'string',
                'tag' => 'string',
                'factory_name' => 'string',
            ]);
            $tool->update(
                [
                    'name' => $request->name,
                    'category' => $request->category,
                    'tag' => $request->tag,
                    'factory_name' => $request->factory_name,
                ]
            );

            DB::commit();
            return redirect()->route('tools.index')->with('success', __('Tool updated successfully.'));
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tool $tool)
    {
        //
    }
}