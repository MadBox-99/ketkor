<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProductLogType;
use App\Models\Partial;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
    public function store(Request $request)
    {
        // $user = auth()->user();
        User::query()->find(1);
        DB::beginTransaction();
        try {
            $request->validate([
                'what' => ['required'],
                'product_id' => ['required'],
            ]);
            $product = Product::whereId($request->product_id)->first();
            if ($request->what == ProductLogType::Maintenance) {
                // get last maintenance created_at
                $lastProductLog = $product->whereRelation('product_logs', 'product_id', $product->id)->whereRelation('product_logs', 'what', ProductLogType::Maintenance)->latest('created_at')->first();
                $maintenanceCount = $product->whereRelation('product_logs', 'product_id', $product->id)->whereRelation('product_logs', 'what', ProductLogType::Maintenance)->count();
                // if last maintenance created_at isn't null
                if ($maintenanceCount == 0) {
                    $product_warrantee_date = $product->serializeDate($product->warrantee_date);
                    $product_warrantee_date = Date::parse($product_warrantee_date);
                    $elevenMonthsLater = $product_warrantee_date->copy()->addMonths(11);
                    $thirteenMonthsLater = $product_warrantee_date->copy()->addMonths(13);
                    if (Date::now() >= $product_warrantee_date->subMonth() && Date::now() <= $product_warrantee_date->addMonths(2)) {
                        ProductLog::query()->create([
                            'comment' => $request->comment,
                            'what' => $request->what,
                            'product_id' => $request->product_id,
                        ]);
                        $product->update(['warrantee_date' => $product_warrantee_date->addYear()]);

                        DB::commit();

                        return back()->withInput()->with('success', __('Products updated successfully.'));
                    }
                }

                if ($maintenanceCount < 3 && $maintenanceCount > 0) {
                    $product_warrantee_date = $product->warrantee_date;
                    $product_warrantee_date = Date::parse($product_warrantee_date);
                    $creationDate = Date::parse($lastProductLog->created_at);
                    if (Date::now() >= $product_warrantee_date->subMonth() && Date::now() <= $product_warrantee_date->addMonths(2)) {
                        ProductLog::query()->create([
                            'comment' => $request->comment,
                            'what' => $request->what,
                            'product_id' => $request->product_id,
                        ]);
                        $product->update(['warrantee_date' => $product_warrantee_date->addYear()]);

                        DB::commit();

                        return back()->withInput()->with('success', __('Product updated successfully.'));
                    }
                }

                DB::rollback();

                return back()->withInput()->with(['error' => __('Can\'t create maintenance in 11 month from last maintenance or after 13 month or cant extend warrantee more than 3 year')]);
            }

            if ($request->what != ProductLogType::Maintenance) {
                ProductLog::query()->create([
                    'comment' => $request->comment,
                    'what' => $request->what,
                    'product_id' => $request->product_id,
                ]);

                DB::commit();

                return back()->withInput()->with('success', __('Product updated successfully.'));
            }
        } catch (Throwable $throwable) {
            DB::rollback();

            $product = Product::whereId($request->product_id)->first();

            // return user and visibility data also

            $partials = Partial::query()->where('product_id', $product->id)->latest()->limit(6)->get();
            $users = User::query()->get();
            $tools = Tool::query()->get();
            $error = $throwable->getMessage();

            return to_route('products.edit', ['product' => $product])->with(['error' => $error, 'users' => $users, 'tools' => $tools, 'product' => $product, 'partials' => $partials]);
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
