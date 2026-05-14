<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('filament.owner.auth.login');
        }

        if ($user->role !== UserRole::Owner->value) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki akses ke halaman owner. Silakan login menggunakan akun owner.')
                ->danger()
                ->send();

            return redirect()->route('filament.owner.auth.login');
        }

        return $next($request);
    }
}
