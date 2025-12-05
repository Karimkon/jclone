<?php

namespace App\Services;

class ImportCalculator
{
    /**
     * Calculate ad valorem import costs
     */
    public function calculateAdValorem(
        float $itemCost,
        float $freight,
        float $insurance,
        float $dutyRate = 0.10,
        float $vatRate = 0.18,
        float $exciseRate = 0.00,
        float $withholdingTaxRate = 0.05,
        float $platformCommission = 0.05,
        float $importCommission = 0.03
    ): array {
        // Calculate CIF (Cost, Insurance, Freight)
        $cif = $itemCost + $freight + $insurance;
        
        // Calculate duties and taxes
        $duty = $cif * $dutyRate;
        $vatBase = $cif + $duty;
        $vat = $vatBase * $vatRate;
        $excise = $cif * $exciseRate;
        $withholdingTax = $cif * $withholdingTaxRate;
        
        $otherTaxes = $excise + $withholdingTax;
        $totalTax = $duty + $vat + $otherTaxes;
        
        // Calculate commissions
        $importCommissionAmount = $cif * $importCommission;
        $platformCommissionAmount = ($cif + $totalTax) * $platformCommission;
        
        // Calculate final cost
        $totalCost = $cif + $totalTax + $importCommissionAmount + $platformCommissionAmount;
        
        return [
            'item_cost' => $itemCost,
            'freight' => $freight,
            'insurance' => $insurance,
            'cif' => $cif,
            
            'duty_rate' => $dutyRate,
            'duty' => $duty,
            
            'vat_rate' => $vatRate,
            'vat' => $vat,
            
            'excise_rate' => $exciseRate,
            'excise' => $excise,
            
            'withholding_tax_rate' => $withholdingTaxRate,
            'withholding_tax' => $withholdingTax,
            
            'other_taxes' => $otherTaxes,
            'total_tax' => $totalTax,
            
            'import_commission_rate' => $importCommission,
            'import_commission' => $importCommissionAmount,
            
            'platform_commission_rate' => $platformCommission,
            'platform_commission' => $platformCommissionAmount,
            
            'total_cost' => $totalCost,
            
            'breakdown' => [
                'CIF' => $cif,
                'Taxes & Duties' => $totalTax,
                'Commissions' => $importCommissionAmount + $platformCommissionAmount,
                'Total Cost' => $totalCost,
            ]
        ];
    }

    /**
     * Calculate weight-based import costs
     */
    public function calculateWeightBased(
        float $ratePerKg,
        float $weightKg,
        float $itemCost,
        float $freight,
        float $insurance,
        float $vatRate = 0.18,
        float $exciseRate = 0.00,
        float $withholdingTaxRate = 0.05,
        float $platformCommission = 0.05,
        float $importCommission = 0.03
    ): array {
        // Calculate CIF
        $cif = $itemCost + $freight + $insurance;
        
        // Calculate weight-based charges
        $weightCharge = $weightKg * $ratePerKg;
        
        // Calculate taxes (based on CIF + weight charge)
        $taxBase = $cif + $weightCharge;
        $vat = $taxBase * $vatRate;
        $excise = $taxBase * $exciseRate;
        $withholdingTax = $taxBase * $withholdingTaxRate;
        
        $otherTaxes = $excise + $withholdingTax;
        $totalTax = $vat + $otherTaxes;
        
        // Calculate commissions
        $importCommissionAmount = $cif * $importCommission;
        $platformCommissionAmount = ($cif + $weightCharge + $totalTax) * $platformCommission;
        
        // Calculate final cost
        $totalCost = $cif + $weightCharge + $totalTax + $importCommissionAmount + $platformCommissionAmount;
        
        return [
            'item_cost' => $itemCost,
            'freight' => $freight,
            'insurance' => $insurance,
            'weight_kg' => $weightKg,
            'rate_per_kg' => $ratePerKg,
            'weight_charge' => $weightCharge,
            'cif' => $cif,
            
            'vat_rate' => $vatRate,
            'vat' => $vat,
            
            'excise_rate' => $exciseRate,
            'excise' => $excise,
            
            'withholding_tax_rate' => $withholdingTaxRate,
            'withholding_tax' => $withholdingTax,
            
            'other_taxes' => $otherTaxes,
            'total_tax' => $totalTax,
            
            'import_commission_rate' => $importCommission,
            'import_commission' => $importCommissionAmount,
            
            'platform_commission_rate' => $platformCommission,
            'platform_commission' => $platformCommissionAmount,
            
            'total_cost' => $totalCost,
            
            'breakdown' => [
                'CIF' => $cif,
                'Weight Charge' => $weightCharge,
                'Taxes' => $totalTax,
                'Commissions' => $importCommissionAmount + $platformCommissionAmount,
                'Total Cost' => $totalCost,
            ]
        ];
    }

    /**
     * Calculate recommended selling price
     */
    public function calculateSellingPrice(
        float $totalCost,
        float $marginPercentage = 30,
        float $shippingCost = 0,
        float $platformFeePercentage = 5
    ): array {
        $margin = $totalCost * ($marginPercentage / 100);
        $sellingPriceBeforeFee = $totalCost + $margin + $shippingCost;
        $platformFee = $sellingPriceBeforeFee * ($platformFeePercentage / 100);
        $finalSellingPrice = $sellingPriceBeforeFee + $platformFee;
        
        return [
            'cost_price' => $totalCost,
            'margin_percentage' => $marginPercentage,
            'margin_amount' => $margin,
            'shipping_cost' => $shippingCost,
            'platform_fee_percentage' => $platformFeePercentage,
            'platform_fee' => $platformFee,
            'selling_price' => $finalSellingPrice,
            'profit' => $margin - $platformFee,
        ];
    }

    /**
     * Calculate import timeline
     */
    public function calculateTimeline(
        string $originCountry = 'China',
        string $destinationCountry = 'Uganda'
    ): array {
        $timelines = [
            'China-Uganda' => [
                'processing' => 2,
                'shipping' => 10,
                'customs' => 3,
                'delivery' => 2,
            ],
            'USA-Uganda' => [
                'processing' => 3,
                'shipping' => 14,
                'customs' => 4,
                'delivery' => 2,
            ],
            'UK-Uganda' => [
                'processing' => 2,
                'shipping' => 12,
                'customs' => 3,
                'delivery' => 2,
            ],
            'default' => [
                'processing' => 3,
                'shipping' => 14,
                'customs' => 5,
                'delivery' => 3,
            ],
        ];
        
        $key = $originCountry . '-' . $destinationCountry;
        $timeline = $timelines[$key] ?? $timelines['default'];
        
        $totalDays = array_sum($timeline);
        $startDate = now();
        
        return [
            'origin' => $originCountry,
            'destination' => $destinationCountry,
            'timeline_days' => $timeline,
            'total_days' => $totalDays,
            'estimated_dates' => [
                'order_processed' => $startDate->addDays($timeline['processing']),
                'shipping_start' => $startDate,
                'arrival_at_port' => $startDate->addDays($timeline['shipping']),
                'customs_clearance' => $startDate->addDays($timeline['customs']),
                'delivery' => $startDate->addDays($timeline['delivery']),
                'estimated_delivery' => now()->addDays($totalDays),
            ]
        ];
    }

    /**
     * Get country-specific tariff rates
     */
    public function getTariffRates(string $country, string $productCategory): array
    {
        // Sample tariff rates database
        $tariffs = [
            'Uganda' => [
                'electronics' => ['duty' => 0.10, 'vat' => 0.18, 'excise' => 0.00],
                'clothing' => ['duty' => 0.25, 'vat' => 0.18, 'excise' => 0.00],
                'automotive' => ['duty' => 0.20, 'vat' => 0.18, 'excise' => 0.10],
                'cosmetics' => ['duty' => 0.10, 'vat' => 0.18, 'excise' => 0.20],
                'default' => ['duty' => 0.15, 'vat' => 0.18, 'excise' => 0.00],
            ],
            'Kenya' => [
                'electronics' => ['duty' => 0.25, 'vat' => 0.16, 'excise' => 0.00],
                'default' => ['duty' => 0.25, 'vat' => 0.16, 'excise' => 0.00],
            ],
            'Tanzania' => [
                'electronics' => ['duty' => 0.25, 'vat' => 0.18, 'excise' => 0.00],
                'default' => ['duty' => 0.25, 'vat' => 0.18, 'excise' => 0.00],
            ],
        ];
        
        $countryTariffs = $tariffs[$country] ?? $tariffs['Uganda'];
        $rates = $countryTariffs[$productCategory] ?? $countryTariffs['default'];
        
        return array_merge($rates, [
            'withholding_tax' => 0.05,
            'platform_commission' => 0.05,
            'import_commission' => 0.03,
        ]);
    }
}