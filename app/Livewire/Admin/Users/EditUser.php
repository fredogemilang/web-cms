<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class EditUser extends Component
{
    use WithFileUploads;

    public User $user;
    
    public $name;
    public $username;
    public $email;
    public $bio;
    public $avatar;
    public $password;
    public $password_confirmation;
    public $selectedRole;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->bio = $user->bio;
        // Take the first role id as the selected one for radio button
        $this->selectedRole = $user->roles->first()?->id;
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'avatar') return;
        if ($propertyName === 'selectedRole') {
            // Sync as array even though it's a single value
            $this->user->roles()->sync([$this->selectedRole]);
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Role updated successfully'
            ]);
            return;
        }

        $this->validateOnly($propertyName, [
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'bio' => 'nullable|string|max:500',
        ]);

        try {
            $this->user->$propertyName = $this->$propertyName;
            $this->user->save();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => ucfirst($propertyName) . ' updated successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update ' . $propertyName
            ]);
        }
    }

    public function updatedAvatar()
    {
        $this->validate([
            'avatar' => 'image|max:1024', // 1MB Max
        ]);

        try {
            // Delete old avatar if exists
            if ($this->user->avatar) {
                Storage::disk('public')->delete($this->user->avatar);
            }

            // Store new avatar
            $path = $this->avatar->store('avatars', 'public');
            
            $this->user->avatar = $path;
            $this->user->save();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Profile picture updated successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update profile picture'
            ]);
        }
    }

    public function updatePassword()
    {
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->user->password = $this->password;
            $this->user->password_changed_at = now();
            $this->user->save();

            $this->password = '';
            $this->password_confirmation = '';

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Password updated successfully'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update password'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.users.edit-user', [
            'roles' => Role::all()
        ]);
    }
}
