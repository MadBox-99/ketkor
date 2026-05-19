<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Partial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PartialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): void
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:200'],
                'email' => ['nullable', 'email'],
                'phone' => ['nullable', 'string', 'max:50'],
                'product_id' => ['required', 'exists:products,id'],
            ]);
            Partial::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'product_id' => $validated['product_id'],
            ]);

            DB::commit();

            return to_route('products.edit', ['product' => $validated['product_id']])->with('success', __('Product updated successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Partial $productPartial): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partial $productPartial): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partial $productPartial): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partial $productPartial): void
    {
        //
    }
}
