<?php

return [
    'fields' => [
        'name'     => 'Full name',
        'phone'    => 'Phone',
        'whatsapp' => 'WhatsApp',
        'email'    => 'Email',
        'city'     => 'City',
        'message'  => 'Notes (optional)',
    ],
    'submit'  => 'Send request',
    'modal' => [
        'about' => 'About: :vehicle',
    ],
    'types' => [
        'inquiry'     => 'Inquiry',
        'quotation'   => 'Quote request',
        'reservation' => 'Reservation',
    ],
    'success' => [
        'title'    => 'Request received',
        'subtitle' => 'We\'ll get back to you within 24 hours.',
        'wa_cta'   => 'Continue on WhatsApp',
        'back'     => 'Back to vehicles',
    ],
    'errors' => [
        'rate_limited' => 'You\'ve sent several requests. Please try again in an hour.',
        'invalid'      => 'Please check the information and try again.',
    ],
];
