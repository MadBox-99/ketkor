<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Tool;
use App\Models\User;
use App\Models\Partial;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartialController extends Controller
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
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $product = Product::whereId($request->product_id)->with(['users'])->first();
            $request->validate([
                'name' => 'required|string|max:200',
            ]);
            Partial::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'product_id' => $request->product_id
            ]);
            Log::create([
                'user_id' => 1,
                'what' => 'Partial.create Partial save successfully |' . json_encode($request->all())
            ]);

            DB::commit();
            $users = User::get();
            $tools = Tool::get();
            $success = __('Product updated successfully.');
            return redirect()->route('products.edit', ['product' => $product])->with(compact('tools', 'users', 'success'));
        } catch (\Throwable $th) {
            DB::rollback();
            Log::create([
                'user_id' => 1,
                'what' => 'Partial create failed' . json_encode($request->all()) . " | " . $th->getMessage()
            ]);
            $users = User::get();
            $tools = Tool::get();
            return redirect()->route('products.edit', ['product' => $product])->with(['error' => $th->getMessage(), 'users' => $users, 'tools' => $tools]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Partial $productPartial)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partial $productPartial)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partial $productPartial)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partial $productPartial)
    {
        //
    }
}