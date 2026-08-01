<?php

return [

    /*
    | Règles les plus courantes traduites en français.
    | Toute règle absente retombe automatiquement sur l'anglais
    | (fallback_locale) sans erreur.
    */

    'accepted' => 'Le champ :attribute doit être accepté.',
    'after_or_equal' => 'Le champ :attribute doit être une date postérieure ou égale à :date.',
    'alpha_num' => 'Le champ :attribute ne peut contenir que des lettres et des chiffres.',
    'array' => 'Le champ :attribute doit être un tableau.',
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => 'Le champ :attribute n\'est pas une date valide.',
    'email' => 'Le champ :attribute doit être une adresse email valide.',
    'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
    'image' => 'Le champ :attribute doit être une image.',
    'in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'lowercase' => 'Le champ :attribute doit être en minuscules.',
    'lt' => [
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
    ],
    'max' => [
        'numeric' => 'Le champ :attribute ne doit pas dépasser :max.',
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
        'file' => 'Le fichier :attribute ne doit pas dépasser :max kilo-octets.',
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
    ],
    'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'min' => [
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'not_in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'regex' => 'Le format du champ :attribute est invalide.',
    'required' => 'Le champ :attribute est obligatoire.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'unique' => 'Cette valeur de :attribute est déjà utilisée.',
    'url' => 'Le champ :attribute doit être une URL valide.',

    'attributes' => [
        'name' => 'nom',
        'email' => 'adresse email',
        'password' => 'mot de passe',
        'current_password' => 'mot de passe actuel',
        'phone' => 'téléphone',
        'code' => 'code promo',
        'quantity' => 'quantité',
    ],

];
