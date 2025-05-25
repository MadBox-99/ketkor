<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

class ProfileController extends Controller
{
    public function index(): View
    {
        $users = User::with(['roles', 'organization'])->paginate(20);

        return view('user.index', ['users' => $users]);
    }

    public function create(Request $request): View
    {
        $organizations = Organization::orderBy('name')->get();

        return view('user.create', ['organizations' => $organizations]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            User::createOrFirst(
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'organization_id' => $validated['organization_id'],
                ]
            );
            $success = __('Successfully created the user.');
            DB::commit();

            return redirect()->route('users.index')->with(['success' => $success]);
        } catch (Throwable $throwable) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    public function show(User $user): View
    {
        $organizations = Organization::get();
        $user = User::whereId($user->id)->with('organization')->first();
        $roles = Role::all();

        return view('user.edit', ['organizations' => $organizations, 'user' => $user, 'roles' => $roles]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function userUpdate(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'organization_id' => $validated['organization'],
        ]);
        $user->syncRoles($validated['role']);

        return redirect()->back()->with('status', __('User updated!'))->withInput();
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $request->user()->fill($request->validated());

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            $request->user()->save();
            DB::commit();

            return redirect()->back()->with('status', 'Profile updated successfully.');
        } catch (Throwable $throwable) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion',
            [
                'password' => ['required', 'current_password'],
            ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
