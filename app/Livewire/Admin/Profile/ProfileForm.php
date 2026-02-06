<?php

namespace App\Livewire\Admin\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileForm extends Component
{
    use WithFileUploads;

    public $name;
    public $username;
    public $email;
    public $bio;
    public $avatar;
    public $password;
    public $password_confirmation;

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->bio = $user->bio;
    }

    public function updatePassword()
    {
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = auth()->user();
            $user->password = $this->password; // Will be hashed by cast or mutator if set, but standard is to hash manually or let Laravel handle it if cast is 'hashed'
            $user->password_changed_at = now();
            $user->save();

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

    public function updatedAvatar()
    {
        $this->validate([
            'avatar' => 'image|max:1024', // 1MB Max
        ]);

        try {
            $user = auth()->user();
            
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $path = $this->avatar->store('avatars', 'public');
            
            $user->avatar = $path;
            $user->save();

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

    public function updated($propertyName)
    {
        if ($propertyName === 'avatar') return;

        $this->validateOnly($propertyName, [
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore(auth()->id())],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore(auth()->id())],
            'bio' => 'nullable|string|max:500',
        ]);

        try {
            $user = auth()->user();
            $user->$propertyName = $this->$propertyName;
            $user->save();

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

    public function render()
    {
        return view('livewire.admin.profile.profile-form');
    }
}
