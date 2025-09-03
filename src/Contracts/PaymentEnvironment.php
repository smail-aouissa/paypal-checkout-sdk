<?php

namespace PayPal\Checkout\Contracts;

interface PaymentEnvironment
{
    public function baseUrl(): string;
    
    public function name(): string;
    
    public function getCredentials(): array;
}