<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'user.roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
            'user.permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name') : [],
            'og' => [
                'title' => 'UdiaNIX - Minecraft Server',
                'type' => 'website',
                'url' => $request->url(),
                'image' => asset('storage/background.png'),
                'image:url' => asset('storage/background.png'),
                'image:secure_url' => secure_asset('storage/background.png'),
                'image:type' => 'image/png',
                'image:width' => 1200,
                'image:height' => 630,
                'description' => 'Aventure-se no melhor servidor medieval sediado em Uberlândia-MG. Construa, batalhe e desvende segredos em UdiaNIX.',
                'fb:app_id' => env('FACEBOOK_CLIENT_ID', '568238442004396'),
                'twitter:card' => 'summary_large_image',
                'twitter:site' => '@udia_nix',
                'twitter:title' => 'UdiaNIX - Minecraft Server',
                'twitter:description' => 'Aventure-se no melhor servidor medieval sediado em Uberlândia-MG. Construa, batalhe e desvende segredos em UdiaNIX.',
                'twitter:image' => asset('storage/background.png'),
                'twitter:image:alt' => 'Banner do servidor UdiaNIX',
            ],
        ]);
    }
}