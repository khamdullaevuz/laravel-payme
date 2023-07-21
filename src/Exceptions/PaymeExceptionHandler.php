<?php

namespace Khamdullaevuz\Payme\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Khamdullaevuz\Payme\Exceptions\PaymeException;
use Khamdullaevuz\Payme\Traits\JsonRPC;

class PaymeExceptionHandler extends ExceptionHandler
{
    use JsonRPC;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (PaymeException $e){
            return $this->error($e->error);
        });
    }
}
