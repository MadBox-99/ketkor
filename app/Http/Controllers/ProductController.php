<?php

namespace App\Http\Controllers;

use App\Models\AccessToken;
use App\Models\Log;
use App\Models\Partial;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;
use App\Models\Visible;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('product.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        $tools = Tool::orderBy('name')->get();

        return view('product.create', ['tools' => $tools, 'users' => $users]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'serial_number' => ['required', 'string', 'max:200', 'unique:products,serial_number,except,id'],
                'purchase_date' => ['sometimes', 'nullable'],
                'installation_date' => ['sometimes', 'nullable'],
                'tool_id' => ['required'],
                'owner_name' => ['required'],
            ]);
            $validator->validate();
            $product = Product::create(
                [
                    'serial_number' => $request->serial_number,
                    'user_id' => $request->user_id,
                    'installation_date' => $request->installation_date,
                    'warrantee_date' => $request->warrantee_date,
                    'purchase_date' => $request->purchase_date,
                    'owner_name' => $request->owner_name,
                    'city' => $request->city,
                    'street' => $request->street,
                    'zip' => $request->zip,
                    'tool_id' => $request->tool_id,
                ]
            );

            Partial::create([
                'name' => $request->owner_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'product_id' => $product->id,
            ]);

            DB::commit();

            return redirect()->route('products.index')->with('success', __('Product created successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return redirect()->back()->withInput()->with('error', $throwable->getMessage());
        }
    }

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
        Log::create([
            'user_id' => 1,
            'what' => 'product.edit page open/hover',
        ]);
        $product = Product::whereId($product->id)->with(['users'])->first();
        $userVisibility = Visible::where('user_id', $user->id)->where('product_id', $product->id)->where('isVisible', true)->first();
        $userVisibility = $userVisibility !== null && $userVisibility->isVisible;
        if ($user->getRoleNames()->first() == 'Admin' || $user->getRoleNames()->first() == 'Operator') {
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
            Log::create([
                'user_id' => 1,
                'what' => 'product.update successfully |'.json_encode($request->all()),
            ]);
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
            Log::create([
                'user_id' => 1,
                'what' => 'product update failed'.json_encode($request->all()).' | '.$throwable->getMessage(),
            ]);
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
            Log::create([
                'user_id' => 1,
                'what' => 'product.delete successfully | product id:'.$product->id,
            ]);
            DB::commit();

            return redirect()->route('products.index')->with('success', __('Product deleted successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();
            Log::create([
                'user_id' => 1,
                'what' => 'product.delete failed |',
            ]);

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
        Log::create([
            'user_id' => 1,
            'what' => 'product.edit page open/hover',
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

        Log::create([
            'user_id' => 1,
            'what' => 'product.remove from user success',
        ]);
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
