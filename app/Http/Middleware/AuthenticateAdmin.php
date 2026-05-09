<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('filament.admin.auth.login');
        }

        if ($user->role !== UserRole::SuperAdmin->value) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki akses ke halaman admin. Silakan login menggunakan akun super admin.')
                ->danger()
                ->send();

            return redirect()->route('filament.admin.auth.login');
        }

        return $next($request);
    }
}
