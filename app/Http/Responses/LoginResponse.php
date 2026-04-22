<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse|Response
    {
        $home = $request->user()->role === 'admin'
            ? '/admin/dashboard'
            : '/tickets';

        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : redirect()->intended($home);
    }
}
