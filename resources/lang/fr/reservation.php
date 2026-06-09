<?php

return [
    'create' => [
        'title'              => 'Réserver ce véhicule',
        'subtitle'           => 'Bloquez :vehicle pendant :hours heures en soumettant ce formulaire et en envoyant l\'acompte de :amount via les instructions fournies.',
        'agree'              => 'Je comprends que l\'acompte doit être envoyé hors plateforme (virement / Wise / coordination WhatsApp) dans le délai indiqué.',
        'submit'             => 'Réserver avec un acompte de :amount',
        'summary_title'      => 'Résumé de la réservation',
        'instructions_title' => 'Instructions de paiement',
        'locked_title'       => 'Une réservation est déjà en cours pour ce véhicule.',
        'locked_body'        => 'Une seule réservation active à la fois. Si elle expire ou est annulée, le véhicule redeviendra disponible.',
        'view_existing'      => 'Voir la réservation en cours',
    ],
    'show' => [
        'eyebrow'             => 'Réservation',
        'title'               => 'Statut de la réservation',
        'instructions_title'  => 'Envoyez l\'acompte',
        'instructions_note'   => 'Indiquez la référence :reference sur le virement pour un rapprochement rapide.',
        'next_steps'          => 'Envoyez l\'acompte puis répondez à l\'email de confirmation (ou écrivez-nous sur WhatsApp) avec une preuve. Nous confirmerons depuis notre côté et bloquerons le véhicule pour vous.',
        'confirmed_msg'       => 'Votre acompte a été reçu. Le véhicule est bloqué pour vous — nous vous contacterons pour la suite.',
        'expired_msg'         => 'Cette réservation a expiré avant la réception de l\'acompte. Le véhicule est de nouveau disponible. Vous pouvez en démarrer une nouvelle.',
        'cancelled_msg'       => 'Cette réservation a été annulée.',
        'back_to_listings'    => 'Retour aux véhicules',
    ],
    'fields' => [
        'reference'    => 'Référence',
        'vehicle'      => 'Véhicule',
        'deposit'      => 'Acompte',
        'expiry'       => 'Délai',
        'expires_at'   => 'Expire le',
        'confirmed_at' => 'Confirmée le',
        'cancelled_at' => 'Annulée le',
        'status'       => 'Statut',
    ],
    'status' => [
        'pending_deposit' => 'Acompte en attente',
        'confirmed'       => 'Confirmée',
        'expired'         => 'Expirée',
        'cancelled'       => 'Annulée',
        'converted'       => 'Convertie en vente',
    ],
    'errors' => [
        'invalid'  => 'Impossible de créer la réservation. Vérifiez le formulaire et réessayez.',
    ],
];
