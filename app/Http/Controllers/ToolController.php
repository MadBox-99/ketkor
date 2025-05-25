<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ToolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('tool.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
                'name' => ['required', 'string'],
                'category' => ['string'],
                'tag' => ['string'],
                'factory_name' => ['string'],
            ]);
            Tool::create(
                [
                    'name' => $request->name,
                    'category' => $request->category,
                    'tag' => $request->tag,
                    'factory_name' => $request->factory_name,
                ]
            );
            DB::commit();

            return redirect()->route('tools.index')->with('success', __('Tool created successfully.'));
        } catch (Throwable $throwable) {

            DB::rollback();

            return redirect()->back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tool $tool): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tool $tool)
    {
        return view('tool.edit', ['tool' => $tool]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tool $tool)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => ['required', 'string'],
                'category' => ['string'],
                'factory_name' => ['string'],
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
        } catch (Throwable $throwable) {
            DB::rollback();

            return redirect()->back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tool $tool): void
    {
        //
    }
}
