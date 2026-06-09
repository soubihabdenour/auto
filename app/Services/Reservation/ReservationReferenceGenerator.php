<?php

declare(strict_types=1);

namespace App\Services\Reservation;

use App\Repositories\ReservationRepository;

/**
 * Generates short, customer-facing reservation references like "RES-A3F9K2".
 *
 * Format: "RES-" + 6 chars from an unambiguous alphabet (no 0/O/1/I/L).
 * Collisions are vanishingly rare (~31^6 ≈ 887M) but we still check the DB
 * and retry; cap retries to avoid unbounded loops if the alphabet shrinks.
 */
final class ReservationReferenceGenerator
{
    private const PREFIX     = 'RES-';
    private const ALPHABET   = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // 31 unambiguous chars
    private const LENGTH     = 6;
    private const MAX_TRIES  = 8;

    public function __construct(private ReservationRepository $reservations) {}

    public function next(): string
    {
        $alphaLen = strlen(self::ALPHABET);
        for ($i = 0; $i < self::MAX_TRIES; $i++) {
            $candidate = self::PREFIX;
            for ($c = 0; $c < self::LENGTH; $c++) {
                $candidate .= self::ALPHABET[random_int(0, $alphaLen - 1)];
            }
            if (! $this->reservations->referenceExists($candidate)) {
                return $candidate;
            }
        }
        // Astronomically unlikely; fall back to a timestamp tail so we never spin forever.
        return self::PREFIX . strtoupper(substr(dechex((int) microtime(true) * 1000), -self::LENGTH));
    }
}
