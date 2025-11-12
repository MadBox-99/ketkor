<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class EmployeeController extends Controller
{
    public function create(): View
    {
        return view('organization.createEmployee');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $user = User::query()->createOrFirst([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'organization_id' => $validated['organization_id'],
            ]);
            $user->assignRole('Servicer');

            DB::commit();

            return to_route('organizations.myorganization')->with(['success' => __('Successfully created an new employee.')]);
        } catch (Throwable $throwable) {
            DB::rollBack();

            return back()->withInput()->with('error', $throwable->getMessage());
        }
    }
}
