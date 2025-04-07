<?php
// app/Services/QuoteCalculator.php

namespace App\Services;

use App\Models\Service;

class QuoteCalculator
{
    public function calculateQuote($serviceId, $quantity)
    {
        $service = Service::findOrFail($serviceId);
        $basePrice = $service->price * $quantity;
        
        // Appliquer des réductions éventuelles
        $discount = $this->calculateDiscount($quantity);
        
        return [
            'base_price' => $basePrice,
            'discount' => $discount,
            'total' => $basePrice - $discount,
        ];
    }
    
    private function calculateDiscount($quantity)
    {
        // Logique de remise par volume
        if ($quantity > 10) {
            return $quantity * 5; // 5€ de remise par unité au-delà de 10
        }
        
        return 0;
    }
}