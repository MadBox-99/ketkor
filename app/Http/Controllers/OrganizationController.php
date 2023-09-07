<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use App\Models\Product;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::create([
            'user_id' => 1,
            'what' => 'organization.index page open/hover'
        ]);
        $organizations = Organization::get();
        return view('organization.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Log::create([
            'user_id' => 1,
            'what' => 'organization.create page open/hover'
        ]);
        return view('organization.create');
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
                'city' => 'string',
                'address' => 'string',
                'zip' => 'string',
            ]);
            Organization::create(
                [
                    'name' => $request->name,
                    'address' => $request->address,
                    'tax_number' => $request->tax_number,
                    'zip' => $request->zip,
                ]
            );
            Log::create([
                'user_id' => 1,
                'what' => 'organization.create Organization created successfully |' . json_encode($request->all())
            ]);
            DB::commit();
            return redirect()->route('organizations.index')->with('success', 'Organization created successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            Log::create([
                'user_id' => 1,
                'what' => 'organization store failed' . json_encode($request->all()) . " | " . $th->getMessage()
            ]);
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        $organization = Organization::whereId($organization->id)->first();
        $organization_id = $organization->id;
        $products = Product::whereHas('users.organization', function ($query) use ($organization_id) {
            $query->where('id', $organization_id);
        })->get();
        return view('organization.edit', compact('organization', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|string',
                'city' => 'string',
                'address' => 'string',
                'zip' => 'string',
                'tax_number' => 'required|max:24',
            ]);
            $organization->update(
                [
                    'name' => $request->name,
                    'address' => $request->address,
                    'tax_number' => $request->tax_number,
                    'zip' => $request->zip,
                ]
            );

            DB::commit();
            return redirect()->route('organizations.index')->with('success', 'Organization updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        DB::beginTransaction();
        try {

            $organization->delete();

            DB::commit();
            return redirect()->route('organizations.index')->with('success', 'Organizations deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('organizations.index')->with('error', $th->getMessage());
        }
    }
    public function removeUserProduct(User $user, Organization $organization, Product $product)
    {

        $user->products()->detach($product->id);
        //dd($organization);
        $organization = Organization::whereId($organization->id)->first();
        $organization_id = $organization->id;
        $products = Product::whereHas('users.organization', function ($query) use ($organization_id) {
            $query->where('id', $organization_id);
        })->get();
        return view('organization.edit', compact('organization', 'products'));
    }

    public function myOrganization()
    {
        $user = Auth::user();
        $organization_id = $user->organization_id;
        $organization = Organization::with('users.products')->whereId($organization_id)->first();
        return view('organization.myorganization', compact('organization'));

    }
    public function myOrganizationUpdate(Request $request, Organization $organization)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|string',
                'city' => 'string',
                'address' => 'string',
                'zip' => 'string',
                'tax_number' => 'required|max:24',
            ]);
            $organization->update(
                [
                    'name' => $request->name,
                    'address' => $request->address,
                    'tax_number' => $request->tax_number,
                    'zip' => $request->zip,
                ]
            );

            DB::commit();
            return redirect()->route('organizations.myorganization')->with('success', 'Organization updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }
    public function removeUserFromOrganization(User $user)
    {
        DB::beginTransaction();
        try {

            $organization = Auth::user()->organization();
            $organization->users()->detach($user);
            if ($user->id != Auth::user()->id)
                $user->delete();
            DB::commit();
            return redirect()->route('organizations.myorganization')->with('success', __('Successfully removed the user from your organization.'));
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('organizations.myorganization')->with('error', __("The user could not be removed from the organisation. You cannot delete your account here."));
        }


    }
}