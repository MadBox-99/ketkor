<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class MyOrganization extends Component
{
    public Organization $organization;

    #[Validate('required|string')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $city = '';

    #[Validate('nullable|string')]
    public string $address = '';

    #[Validate('nullable|string')]
    public string $zip = '';

    #[Validate('required|max:24')]
    public string $tax_number = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $organization = Organization::query()
            ->whereKey($user->organization_id)
            ->first();

        if (! $organization instanceof Organization) {
            $this->redirectRoute('organizations.create', navigate: true);

            return;
        }

        $this->organization = $organization;
        $this->name = $organization->name;
        $this->city = (string) $organization->city;
        $this->address = (string) $organization->address;
        $this->zip = (string) $organization->zip;
        $this->tax_number = (string) $organization->tax_number;
    }

    public function updateOrganization(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {
            $this->organization->update($validated);
            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollback();

            session()->flash('error', $throwable->getMessage());

            return;
        }

        session()->flash('success', __('Organization updated successfully.'));
    }

    public function moveProduct(int $productId, int $fromUserId, int $toUserId): void
    {
        $validator = Validator::make(
            [
                'product_id' => $productId,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
            ],
            [
                'product_id' => ['required', 'exists:products,id'],
                'from_user_id' => ['required', 'exists:users,id'],
                'to_user_id' => ['required', 'exists:users,id'],
            ],
        );

        if ($validator->fails()) {
            session()->flash('error', $validator->errors()->first());

            return;
        }

        /** @var User $authUser */
        $authUser = Auth::user();

        $fromUser = User::query()->find($fromUserId);
        $toUser = User::query()->find($toUserId);

        if (
            $authUser->organization_id === null
            || ! $fromUser instanceof User
            || ! $toUser instanceof User
            || $fromUser->organization_id !== $authUser->organization_id
            || $toUser->organization_id !== $authUser->organization_id
        ) {
            session()->flash('error', __('You are not allowed to modify this user.'));

            return;
        }

        DB::beginTransaction();

        try {
            $product = Product::query()->findOrFail($productId);
            $product->users()->detach($fromUserId);
            $product->users()->attach($toUserId);

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollback();

            session()->flash('error', $throwable->getMessage());

            return;
        }

        session()->flash('success', __('Product successfully moved.'));

        $this->refreshOrganization();
    }

    public function detachProduct(int $userId, int $productId): void
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        $user = User::query()->findOrFail($userId);

        if ($authUser->organization_id === null || $user->organization_id !== $authUser->organization_id) {
            session()->flash('error', __('You are not allowed to modify this user.'));

            return;
        }

        $user->products()->detach($productId);

        $this->refreshOrganization();
    }

    public function removeMember(int $userId): void
    {
        DB::beginTransaction();

        try {
            /** @var User $authUser */
            $authUser = Auth::user();

            $user = User::query()->findOrFail($userId);

            if ($authUser->organization_id === null || $user->organization_id !== $authUser->organization_id) {
                DB::rollback();

                session()->flash('error', __('The user could not be removed from the organisation. You cannot delete your account here.'));

                return;
            }

            if ($user->id !== $authUser->id && ! $user->hasRole('Organizer')) {
                $user->products()->detach();
                $user->delete();
                DB::commit();

                session()->flash('success', __('Successfully removed the user from your organization.'));

                $this->refreshOrganization();

                return;
            }

            DB::rollback();

            session()->flash('error', __('The user could not be removed from the organisation. You cannot delete your account here.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            session()->flash('error', __('The user could not be removed from the organisation. You cannot delete your account here.') . ' ' . $throwable->getMessage());
        }
    }

    private function refreshOrganization(): void
    {
        $this->organization->load('users.products');
    }

    public function render(): Factory|View
    {
        return view('livewire.organizations.my-organization');
    }
}
