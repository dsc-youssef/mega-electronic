<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\Models\User;
use App\Traits\Images\ImagesPaths;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;


class HandleInertiaRequests extends Middleware
{

  use ImagesPaths;

  /**
   * The root template that is loaded on the first page visit.
   *
   * @var string
   */
  protected $rootView = 'app';

  /**
   * Determine the current asset version.
   */
  public function version(Request $request): string|null
  {
    return parent::version($request);
  }


  /** Get user settings
   * @param Request $request
   * @return mixed
   */
  private function getUserSettings(Request $request): mixed
  {
    $settings = auth()->check() ? User::find($request->user()->id)->settings : [];
    return $settings;
  }

  /**
   * Define layout words.
   * @return array
   */
  private function layoutWords(): array
  {
    return [
      "auth" => array_merge(
        __("components/auth/navbar"),
      ),
      'dashboard' => array_unique(
        array_merge(
          __('components/dashboard/navbar'),
          __('components/dashboard/sidebar'),
          __('components/dashboard/cart'),
          __('components/dashboard/header'),
          __('components/dashboard/stateCard'),
        )
      )
    ];
  }

  /**
   * Define Session Data
   * @return mixed
   */
  private function sessionData()
  {
    return session()->has('request-data') ? session()->get('request-data') : null;
  }

  /**
   * Get notification from session data.
   * @return mixed
   */
  private function sessionNotification()
  {
    return session()->has('notification') ? session()->get('notification') : null;
  }

  /**
   * Define user auth.
   * @param Request $request
   * @return array
   */
  private function userAuth(Request $request): array
  {
    $userPermissions = $request->user ? $request->user()->permissions->pluck('') : null;
    return [
      'user' => $request->user(),
      'permissions' => $userPermissions
    ];
  }

  /**
   * Define paths.
   * @return array
   */
  private function paths(): array
  {
    return [
      'images_paths' => $this->getImagesPaths()
    ];
  }

  /**
   * Define the props that are shared by default.
   *
   * @return array<string, mixed>
   */
  public function share(Request $request): array
  {
    return [
      ...parent::share($request),

      'layoutsWords' => $this->layoutWords(),
      'auth' => $this->userAuth($request),
      'settings' => $this->getUserSettings($request),
      'request-data' => $this->sessionData(),
      'notification' => $this->sessionNotification(),
      'paths' => $this->paths()

      // 'ziggy' => fn() => [
      //   ...(new Ziggy)->toArray(),
      // ],
      // 'url' => $request->route()->uri(),
    ];
  }
}
