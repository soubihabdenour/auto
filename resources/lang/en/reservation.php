<?php

return [
    'create' => [
        'title'              => 'Reserve this vehicle',
        'subtitle'           => 'Lock :vehicle for you for :hours hours by submitting this form and sending the :amount deposit using the instructions provided.',
        'agree'              => 'I understand the deposit must be sent off-platform (bank transfer / Wise / WhatsApp coordination) within the window shown.',
        'submit'             => 'Reserve for :amount deposit',
        'summary_title'      => 'Reservation summary',
        'instructions_title' => 'Payment instructions',
        'locked_title'       => 'A reservation is already in progress for this vehicle.',
        'locked_body'        => 'Only one active reservation can exist at a time. If it expires or is cancelled, the vehicle will become available again.',
        'view_existing'      => 'View existing reservation',
    ],
    'show' => [
        'eyebrow'             => 'Reservation',
        'title'               => 'Reservation status',
        'instructions_title'  => 'Send the deposit',
        'instructions_note'   => 'Use reference :reference on the wire so we can match it quickly.',
        'next_steps'          => 'Send the deposit and reply to your confirmation email (or message us on WhatsApp) with proof. We will confirm it from our side and lock the vehicle for you.',
        'confirmed_msg'       => 'Your deposit has been received. The vehicle is locked for you — we will contact you for next steps.',
        'expired_msg'         => 'This reservation expired before the deposit was received. The vehicle is available again. You can start a new reservation.',
        'cancelled_msg'       => 'This reservation has been cancelled.',
        'back_to_listings'    => 'Back to vehicles',
    ],
    'fields' => [
        'reference'    => 'Reference',
        'vehicle'      => 'Vehicle',
        'deposit'      => 'Deposit',
        'expiry'       => 'Window',
        'expires_at'   => 'Expires at',
        'confirmed_at' => 'Confirmed at',
        'cancelled_at' => 'Cancelled at',
        'status'       => 'Status',
    ],
    'status' => [
        'pending_deposit' => 'Pending deposit',
        'confirmed'       => 'Confirmed',
        'expired'         => 'Expired',
        'cancelled'       => 'Cancelled',
        'converted'       => 'Converted to sale',
    ],
    'errors' => [
        'invalid'  => 'Could not create the reservation. Please check the form and try again.',
    ],
];
