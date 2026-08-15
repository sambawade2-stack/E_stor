<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Compte de l'équipe de la boutique — administration uniquement.
 *
 * Les clients n'ont pas de compte : ils commandent en renseignant leurs
 * coordonnées et suivent leur livraison depuis /suivi, avec le numéro de
 * commande et leur téléphone. Il n'existe donc pas d'inscription publique,
 * et les comptes se créent par le seeder ou en console.
 *
 * La vérification d'adresse a disparu avec l'inscription : elle servait à
 * prouver qu'un inscrit possédait bien l'adresse saisie, ce qui n'a plus
 * d'objet pour des comptes créés par la boutique elle-même.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}
