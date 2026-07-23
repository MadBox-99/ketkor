<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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
    public function store(Request $request): RedirectResponse
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
                ],
            );
            $organization = Organization::query()->create([
                'name' => $request->name,
                'city' => $request->city,
                'address' => $request->address,
                'tax_number' => $request->tax_number,
                'zip' => $request->zip,
            ]);

            /** @var User $user */
            $user = Auth::user();
            $user->organization_id = $organization->id;
            $user->save();

            DB::commit();

            return to_route('organizations.myorganization')->with('success', __('Organization created successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization): void
    {
        //
    }

    public function productMove(Request $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $request->validate(
                [
                    'selected_user_id' => ['required', 'exists:users,id'],
                    'user_id' => ['required', 'exists:users,id'],
                    'product_id' => ['required', 'exists:products,id'],
                ],
            );
            $product = Product::query()->findOrFail($request->product_id);
            $product->users()->detach($request->user_id);
            $product->users()->attach($request->selected_user_id);

            DB::commit();

            return to_route('organizations.myorganization')->with('success', __('Product successfully moved.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization): Factory|View
    {
        return view('organization.edit', ['organization' => $organization]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization): RedirectResponse
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
                ],
            );
            DB::commit();

            return to_route('organizations.index')->with('success', __('Organization updated successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $organization->delete();
            DB::commit();

            return to_route('organizations.index')->with('success', __('Organizations deleted successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return to_route('organizations.index')->with('error', $throwable->getMessage());
        }
    }

    public function removeUserProduct(User $user, Organization $organization, Product $product): Factory|View|RedirectResponse
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if ($user->organization_id !== $authUser->organization_id) {
            return to_route('organizations.myorganization')->with('error', __('You are not allowed to modify this user.'));
        }

        $user->products()->detach($product->id);

        return $this->renderMyOrganization($authUser->organization_id);
    }

    public function myOrganization(): Factory|View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return $this->renderMyOrganization($user->organization_id);
    }

    private function renderMyOrganization(?int $organizationId): Factory|View|RedirectResponse
    {
        $organization = Organization::with('users.products')->whereId($organizationId)->first();

        if (! $organization instanceof Organization) {
            return to_route('organizations.create')->with('error', __('You do not have an organization yet. Please create one.'));
        }

        return view('organization.myorganization', ['organization' => $organization]);
    }

    public function myOrganizationUpdate(Request $request, Organization $organization): RedirectResponse
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
                ],
            );
            $organization->update(
                [
                    'name' => $request->name,
                    'city' => $request->city,
                    'address' => $request->address,
                    'tax_number' => $request->tax_number,
                    'zip' => $request->zip,
                ],
            );
            DB::commit();

            return to_route('organizations.myorganization')->with('success', __('Organization updated successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    public function removeUserFromOrganization(User $user): RedirectResponse
    {
        DB::beginTransaction();
        try {
            /** @var User $authUser */
            $authUser = Auth::user();

            if ($user->organization_id !== $authUser->organization_id) {
                return to_route('organizations.myorganization')->with('error', __('The user could not be removed from the organisation. You cannot delete your account here.'));
            }

            if ($user->id !== $authUser->id && ! $user->hasRole('Organizer')) {
                $user->products()->detach();
                $user->delete();
                DB::commit();

                return to_route('organizations.myorganization')->with('success', __('Successfully removed the user from your organization.'));
            }

            return to_route('organizations.myorganization')->with('error', __('The user could not be removed from the organisation. You cannot delete your account here.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return to_route('organizations.myorganization')->with('error', __('The user could not be removed from the organisation. You cannot delete your account here.') . ' ' . $throwable->getMessage());
        }
    }
}
