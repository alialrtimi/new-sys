<?php

namespace App\Http\Middleware;

use App\Models\Department;
use App\Models\internalState;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    // public function share(Request $request): array
    // {
    //     // dd();
    //     if (isset(auth('sanctum')->user()->email)) {
    //         $internal_states = internalState::get(['name', 'id']);

    //         return array_merge(parent::share($request), []);
    //     } else
    //         return array_merge(parent::share($request), []);
    // }
    public function share(Request $request): array
    {
        // dd(Department::get(['id', 'name'])->sortByDesc('id'));
        return array_merge(parent::share($request), [
            // 'internal_states' => auth('sanctum')->check()
            //     ? fn() => internalState::get(['id', 'name'])
            //     : null,

            //

            'Departments' => auth('sanctum')->check()
                ? fn() => Department::get(['id', 'name'])->sortBy('id')->values()
                : null,
        ]);
    }
}
