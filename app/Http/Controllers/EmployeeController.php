<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'organization_id' => Auth::user()->organization_id,
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
