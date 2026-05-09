<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateCashier
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('filament.cashier.auth.login');
        }

        if ($user->role !== UserRole::Cashier->value) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki akses ke halaman kasir. Silakan login menggunakan akun cashier.')
                ->danger()
                ->send();

            return redirect()->route('filament.cashier.auth.login');
        }

        return $next($request);
    }
}
