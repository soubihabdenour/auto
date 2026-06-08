<?php

declare(strict_types=1);

namespace App\Services\Estimate;

/**
 * Pure calculation. Inputs come from settings; vehicle price comes in per call.
 * All math in USD; convert to DZD at the end using the FX rate.
 */
final class ImportCostEstimator
{
    public function __construct(
        private float $shippingBaseUsd,
        private float $customsRate,
        private float $tvaRate,
        private float $serviceFeeFlatUsd,
        private float $serviceFeePercent,
        private float $fxUsdToDzd,
    ) {}

    /**
     * @return array{
     *   vehicle_usd: float,
     *   shipping_usd: float,
     *   customs_usd: float,
     *   service_fee_usd: float,
     *   total_usd: float,
     *   total_dzd: float,
     *   fx_rate: float
     * }
     */
    public function estimate(float $vehiclePriceUsd): array
    {
        $vehicle    = max(0.0, $vehiclePriceUsd);
        $shipping   = $this->shippingBaseUsd;
        $customs    = ($vehicle + $shipping) * $this->customsRate;
        $tva        = ($vehicle + $shipping + $customs) * $this->tvaRate;
        $customs   += $tva; // bundle TVA into the customs line for display simplicity
        $serviceFee = $this->serviceFeeFlatUsd + ($vehicle * $this->serviceFeePercent);
        $totalUsd   = $vehicle + $shipping + $customs + $serviceFee;
        $totalDzd   = $totalUsd * $this->fxUsdToDzd;

        return [
            'vehicle_usd'     => round($vehicle, 2),
            'shipping_usd'    => round($shipping, 2),
            'customs_usd'     => round($customs, 2),
            'service_fee_usd' => round($serviceFee, 2),
            'total_usd'       => round($totalUsd, 2),
            'total_dzd'       => round($totalDzd, 0),
            'fx_rate'         => $this->fxUsdToDzd,
        ];
    }
}
