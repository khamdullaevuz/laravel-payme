<?php

namespace Khamdullaevuz\Payme\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Http\Request;

/**
 * @method static \Khamdullaevuz\Payme\Payme handle(Request $request)
 * @see \Khamdullaevuz\Payme\Payme
 */
class Payme extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'payme';
    }
}