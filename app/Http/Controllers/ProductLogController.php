<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProductLogType;
use App\Models\Product;
use App\Models\ProductLog;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Factory|View
    {
        return view('productlog.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Factory|View
    {
        return view('productlog.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'what' => ['required'],
                'product_id' => ['required'],
            ]);
            $product = Product::query()->findOrFail($request->product_id);

            if ($request->what === ProductLogType::Maintenance) {
                $maintenanceCount = ProductLog::query()
                    ->where('product_id', $product->id)
                    ->where('what', ProductLogType::Maintenance)
                    ->count();

                if ($maintenanceCount >= 3) {
                    DB::rollback();

                    return back()->withInput()->with('error', __('Warrantee cannot be extended more than 3 times.'));
                }

                $warranteeDate = Date::parse($product->serializeDate($product->warrantee_date));
                $windowStart = $warranteeDate->copy()->subMonth();
                $windowEnd = $warranteeDate->copy()->addMonths(2);

                if (Date::now()->between($windowStart, $windowEnd)) {
                    ProductLog::query()->create([
                        'comment' => $request->comment,
                        'what' => $request->what,
                        'product_id' => $request->product_id,
                    ]);
                    $product->update(['warrantee_date' => $warranteeDate->copy()->addYear()]);

                    DB::commit();

                    return back()->withInput()->with('success', __('Product updated successfully.'));
                }

                DB::rollback();

                return back()->withInput()->with('error', __('Maintenance can only be recorded within one month before to two months after the warrantee date.'));
            }

            ProductLog::query()->create([
                'comment' => $request->comment,
                'what' => $request->what,
                'product_id' => $request->product_id,
            ]);

            DB::commit();

            return back()->withInput()->with('success', __('Product updated successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductLog $productLog): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductLog $productLog): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductLog $productLog): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductLog $productLog): void
    {
        //
    }
}
