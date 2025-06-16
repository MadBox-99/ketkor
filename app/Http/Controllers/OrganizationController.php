<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use App\Models\Visible;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {

        return view('organization.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {

        return view('organization.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(
                [
                    'name' => ['required', 'string'],
                    'city' => ['string'],
                    'address' => ['string'],
                    'zip' => ['string'],
                ]
            );
            Organization::create(
                [
                    'name' => $request->name,
                    'address' => $request->address,
                    'tax_number' => $request->tax_number,
                    'zip' => $request->zip,
                ]
            );

            DB::commit();

            return redirect()->route('organizations.index')->with('success', __('Organization created successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return redirect()->back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization): void
    {
        //
    }

    public function productMove(Request $request)
    {
        DB::beginTransaction();
        try {
            // validation
            $request->validate(
                [
                    'selected_user_id' => ['required'],
                    'user_id' => ['required'],
                    'product_id' => ['required'],
                ]
            );
            $product = Product::with(['users'])->whereId($request->product_id)->first();
            $selectedUserId = $request->selected_user_id;
            $isAttached = $product->users->contains($selectedUserId);
            $product->users()->detach($request->user_id);
            $visible = Visible::whereUserId($request->user_id)->whereProductId($product->id)->first();
            if ($isAttached) {
                Visible::whereUserId($request->selected_user_id)->whereProductId($product->id)->first()->update(
                    [
                        'isVisible' => $visible->isVisible,
                    ]
                );
                $visible->delete();
            } else {
                $product->users()->attach($request->selected_user_id);
                $visible->update(
                    [
                        'user_id' => $request->selected_user_id,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('organizations.myorganization')->with('success', __('Product successfully moved.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return redirect()->back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        $organization = Organization::whereId($organization->id)->first();
        $organization_id = $organization->id;
        $products = Product::whereHas('users.organization', function ($query) use ($organization_id): void {
            $query->where('id', $organization_id);
        })->get();

        return view('organization.edit', ['organization' => $organization, 'products' => $products]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => ['required', 'string'],
            ]);
            $organization->update(
                [
                    'name' => $request->name,
                    'city' => $request->city,
                    'address' => $request->address,
                    'tax_number' => $request->tax_number,
                    'zip' => $request->zip,
                ]
            );
            DB::commit();

            return redirect()->route('organizations.index')->with('success', __('Organization updated successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return redirect()->back()->withInput()->with('error', $throwable->getMessage());
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

            return redirect()->route('organizations.index')->with('success', __('Organizations deleted successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return redirect()->route('organizations.index')->with('error', $throwable->getMessage());
        }
    }

    public function removeUserProduct(User $user, Organization $organization, Product $product)
    {

        $user->products()->detach($product->id);
        $user = Auth::user();
        $organization_id = $user->organization_id;
        $organization = Organization::with('users.products')->whereId($organization_id)->first();

        return view('organization.myorganization', ['organization' => $organization]);
    }

    public function myOrganization()
    {
        $user = Auth::user();
        $organization_id = $user->organization_id;
        $organization = Organization::with('users.products')->whereId($organization_id)->first();

        return view('organization.myorganization', ['organization' => $organization]);
    }

    public function myOrganizationUpdate(Request $request, Organization $organization)
    {
        DB::beginTransaction();
        try {
            $request->validate(
                [
                    'name' => ['required', 'string'],
                    'city' => ['string'],
                    'address' => ['string'],
                    'zip' => ['string'],
                    'tax_number' => ['required', 'max:24'],
                ]
            );
            $organization->update(
                [
                    'name' => $request->name,
                    'city' => $request->city,
                    'address' => $request->address,
                    'tax_number' => $request->tax_number,
                    'zip' => $request->zip,
                ]
            );
            DB::commit();

            return redirect()->route('organizations.myorganization')->with('success', __('Organization updated successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return redirect()->back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    public function removeUserFromOrganization(User $user)
    {
        DB::beginTransaction();
        try {
            if ($user->id != Auth::user()->id && ! $user->hasRole('Organizer')) {
                $user->products()->detach();
                $user->are_visible()->delete();
                $user->delete();
                DB::commit();

                return redirect()->route('organizations.myorganization')->with('success', __('Successfully removed the user from your organization.'));
            }

            return redirect()->route('organizations.myorganization')->with('error', __('The user could not be removed from the organisation. You cannot delete your account here.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return redirect()->route('organizations.myorganization')->with('error', __('The user could not be removed from the organisation. You cannot delete your account here.'.$throwable->getMessage()));
        }
    }
}
