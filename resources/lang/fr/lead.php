<?php

return [
    'fields' => [
        'name'     => 'Nom complet',
        'phone'    => 'Téléphone',
        'whatsapp' => 'WhatsApp',
        'email'    => 'E-mail',
        'city'     => 'Ville',
        'message'  => 'Notes (optionnel)',
    ],
    'submit'  => 'Envoyer la demande',
    'modal' => [
        'about' => 'Concerne : :vehicle',
    ],
    'types' => [
        'inquiry'     => 'Demande d\'info',
        'quotation'   => 'Demande de devis',
        'reservation' => 'Réservation',
    ],
    'success' => [
        'title'    => 'Demande reçue',
        'subtitle' => 'Nous vous recontactons sous 24 heures.',
        'wa_cta'   => 'Continuer sur WhatsApp',
        'back'     => 'Retour aux véhicules',
    ],
    'errors' => [
        'rate_limited' => 'Vous avez envoyé plusieurs demandes. Merci de réessayer dans une heure.',
        'invalid'      => 'Veuillez vérifier les informations et réessayer.',
    ],
];
