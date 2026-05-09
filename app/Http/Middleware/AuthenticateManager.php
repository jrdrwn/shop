<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('filament.manager.auth.login');
        }

        if ($user->role !== UserRole::Manager->value) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki akses ke halaman manajer. Silakan login menggunakan akun manager.')
                ->danger()
                ->send();

            return redirect()->route('filament.manager.auth.login');
        }

        return $next($request);
    }
}
