<?php

namespace App\Filament\Pages\Owner;

use App\Enums\UserRole;
use App\Models\Subscription;
use App\Models\Toko;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OwnerRegistration extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPhoneFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function getPhoneFormComponent(): TextInput
    {
        return TextInput::make('phone')
            ->label('Nomor Telepon')
            ->tel()
            ->required()
            ->maxLength(20);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['role'] = UserRole::Owner->value;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Create user
            $user = $this->getUserModel()::create($data);

            // 2. Find free subscription
            $freeSubscription = Subscription::whereName('Free')
                ->orWhereName('Free Plan')
                ->orWhere('price', 0)
                ->first();

            // 3. Create toko
            $toko = Toko::create([
                'name' => $user->name.' Toko',
                'address' => '-',
                'phone' => $user->phone ?? '-',
                'email' => $user->email,
                'city' => '-',
                'province' => '-',
                'description' => 'Toko milik '.$user->name,
                'owner_name' => $user->name,
                'logo_url' => null,
                'is_active' => true,
                'created_by' => $user->id,
                'subscription_id' => $freeSubscription?->id,
                'tax_percentage' => 10,
                'service_charge_percentage' => 5,
            ]);

            // 4. Update user with toko_id
            $user->update(['toko_id' => $toko->id]);

            return $user;
        });
    }
}
