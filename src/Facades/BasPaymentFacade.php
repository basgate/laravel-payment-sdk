<?php

namespace Bas\LaravelPayment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bas\LaravelPayment\Services\PaymentService
 */
class BasPaymentFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bas-payment';
    }
}
