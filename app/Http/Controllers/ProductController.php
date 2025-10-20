<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AccessToken;
use App\Models\Partial;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;
use App\Models\Visible;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Product $product): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $user = Auth::user();
        dump($product);
        $product = Product::find($product->id)->first();
        $userVisibility = Visible::where('user_id', Auth::user()->id)
            ->where('product_id', $product->id)
            ->where('isVisible', true)
            ->first();
        $userVisibility = $userVisibility !== null && $userVisibility->isVisible;
        if ($user->hasAnyRole([UserRole::Admin, UserRole::Operator])) {
            $userVisibility = true;
        }

        $partials = Partial::where('product_id', $product->id)->latest()->limit(6)->get();
        $users = User::orderBy('name')->get();
        $tools = Tool::orderBy('name')->get();

        return view('product.edit', ['users' => $users, 'tools' => $tools, 'product' => $product, 'partials' => $partials, 'userVisibility' => $userVisibility]);
    }

    public function partialUpdate(Request $request, Product $product) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $request->validate([
                'tool_id' => ['required'],
                'user_ids' => ['required'],
            ]);
            // Validate Request
            $product = Product::whereId($product->id)->first();
            $product->update([
                'serial_number' => $request->serial_number,
                'installation_date' => $request->installation_date,
                'warrantee_date' => $request->warrantee_date,
                'purchase_date' => $request->purchase_date,
                'owner_name' => $request->owner_name,
                'city' => $request->city,
                'street' => $request->street,
                'zip' => $request->zip,
                'tool_id' => $request->tool_id,
            ]);
            $product->users()->sync($request->user_ids);

            DB::commit();
            $users = User::orderBy('name')->get();
            $tools = Tool::orderBy('name')->get();
            $partials = Partial::where('product_id', $product->id)->latest()->limit(6)->get();
            $userVisibility = Visible::whereRelation('product', 'user_id', $user->id)->whereRelation('product', 'product_id', $product->id)->whereRelation('product', 'isVisible', true)->first();
            $userVisibility = $userVisibility !== null && $userVisibility->isVisible;
            $success = __('Products updated successfully.');

            return redirect()->route('products.edit', ['product' => $product])->with(['success' => $success, 'users' => $users, 'tools' => $tools, 'product' => $product, 'partials' => $partials, 'userVisibility' => $userVisibility]);
        } catch (Throwable $throwable) {
            DB::rollback();

            $userVisibility = Visible::whereRelation('product', 'user_id', $user->id)->whereRelation('product', 'product_id', $product->id)->whereRelation('product', 'isVisible', true)->first();
            $userVisibility = $userVisibility !== null && $userVisibility->isVisible;
            $partials = Partial::where('product_id', $product->id)->latest()->limit(6)->get();
            $users = User::orderBy('name')->get();
            $tools = Tool::orderBy('name')->get();
            $error = $throwable->getMessage();

            return redirect()->route('products.edit', ['product' => $product])->with(['error' => $error, 'users' => $users, 'tools' => $tools, 'product' => $product, 'partials' => $partials, 'userVisibility' => $userVisibility]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $product->delete();

            DB::commit();

            return redirect()->route('products.index')->with('success', __('Product deleted successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return redirect()->route('products.index')->with('error', $throwable->getMessage());
        }
    }

    public function add(Product $product)
    {
        $userId = Auth::user()->id;
        $userVisibility = Visible::firstOrCreate([
            'product_id' => $product->id,
            'user_id' => $userId,
        ]);

        $user = Auth::user();
        $user->products()->attach($product->id);
        $product = Product::whereId($product->id)->with(['users'])->first();
        $userVisibility = Visible::whereRelation('product', 'user_id', $user->id)->whereRelation('product', 'product_id', $product->id)->whereRelation('product', 'isVisible', true)->first();
        $userVisibility = $userVisibility !== null && $userVisibility->isVisible;

        $partials = Partial::where('product_id', $product->id)->latest()->limit(6)->get();
        $users = User::orderBy('name')->get();
        $tools = Tool::orderBy('name')->get();

        return redirect()->route('products.edit', ['product' => $product])->with(['users' => $users, 'tools' => $tools, 'product' => $product, 'partials' => $partials, 'userVisibility' => $userVisibility]);
    }

    public function remove(Product $product)
    {
        $userId = Auth::user()->id;
        $userVisibility = Visible::where('product_id', $product->id)->where('user_id', $userId)->first();
        $accessToken = AccessToken::where('product_id', $product->id)->where('user_id', $userId)->first();
        if (! is_null($accessToken)) {
            $userVisibility->delete();
        }

        if (! is_null($accessToken)) {
            $accessToken->delete();
        }

        $user = User::find($userId);
        $user->products()->detach($product->id);

        return redirect()->route('products.myproducts')->with('success', __('Succesfuly removed the product from your account.'));
    }

    public function search()
    {
        return view('product.search');
    }

    public function myproducts(): View
    {
        return view('product.myproduct');
    }
}
