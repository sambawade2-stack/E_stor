<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('shop.admin_email');

        if (blank($email)) {
            $this->command?->warn('AdminUserSeeder ignoré : renseignez SHOP_ADMIN_EMAIL dans le .env.');

            return;
        }

        // Aucun mot de passe fourni : on en génère un aléatoire et on
        // l'affiche une seule fois. Un mot de passe par défaut en dur (ou
        // committé dans .env.example) donnerait un accès admin public à
        // toute installation déployée sans personnalisation.
        $password = config('shop.admin_default_password');
        $generated = blank($password);

        if ($generated) {
            $password = Str::password(16);
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrateur',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);

        if ($generated && $admin->wasRecentlyCreated) {
            $this->command?->newLine();
            $this->command?->warn('Compte admin créé — notez ce mot de passe, il ne sera plus affiché :');
            $this->command?->line("  {$email} / {$password}");
            $this->command?->newLine();
        }
    }
}
