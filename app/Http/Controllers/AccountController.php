<?php

namespace App\Http\Controllers;

use App\Helpers\RouteHelper;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Pages\Models\Page;

class AccountController extends Controller
{
    /**
     * Display the user's profile page
     * 
     * @param Page $Page
     * @return View|RedirectResponse
     * @throws ValidationException
     */
    public function profile(Page $Page): View|RedirectResponse
    {
        if (!$this->getCurrentUser()) {
            return response()->redirectTo(route('theme'));
        }

        if (request()->isMethod('post')) {
            if (request()->input('delete_account')) {
                return $this->deleteAccount();
            }
            
            if (request()->input('pw_form')) {
                return $this->updatePassword($Page);
            }

            return $this->editProfile($Page);
        }

        return view(
            'account.profile',
            [
                'Page'          => $Page,
                'User'          => $this->getCurrentUser(),
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Page->url)
            ]
        );
    }

    /**
     * Edit the user's profile
     * 
     * @param Page $Page
     * @return RedirectResponse
     * @throws ValidationException
     */
    protected function editProfile(Page $Page): RedirectResponse
    {
        if (!$this->getCurrentUser()) {
            return response()->redirectTo(route('theme'));
        }

        $this->validate(
            request(),
            [
                'name'      => 'required'
            ],
            [
                'name.required' => 'Please provide a name'
            ]
        );

        $User = $this->getCurrentUser();
        $User->name = request()->input('name');
        $User->update();

        return response()->redirectTo($Page->url)
            ->with('status', 'Your details have been updated.');
    }

    /**
     * Update the user's password
     * 
     * @param Page $Page
     * @return RedirectResponse
     * @throws ValidationException
     */
    protected function updatePassword(Page $Page): RedirectResponse
    {
        if (!$this->getCurrentUser()) {
            return response()->redirectTo(route('theme'));
        }

        $this->validate(
            request(),
            [
                'current_password'  => ['required', 'current_password'],
                'password'          => ['required', 'confirmed', Rules\Password::defaults()]
            ],
            [
                'current_password.required'         => 'Please provide your current password',
                'current_password.current_password' => 'Sorry, that password is incorrect',
                'password.required'                 => 'Please provide a new password',
                'password.confirmed'                => 'Please confirm your new password'
            ]
        );

        $User = $this->getCurrentUser();
        $User->password = Hash::make(request()->input('password'));
        $User->save();

        return response()->redirectTo($Page->url)
            ->with('status', 'Your password has been updated.');
    }

    /**
     * Delete the user's account
     * 
     * @return RedirectResponse
     */
    protected function deleteAccount(): RedirectResponse
    {
        if (!$this->getCurrentUser()) {
            return response()->redirectTo(route('theme'));
        }
        
        $User = $this->getCurrentUser();
        Auth::logout();
        $User->delete();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->redirectTo('/');
    }

    /**
     * Get the currently logged in user, or null if not 
     * logged in
     * 
     * @return ?User
     */
    protected function getCurrentUser(): ?User
    {
        $User = Auth::user();

        if (!$User instanceof User) { return null; }
        return $User;
    }
}