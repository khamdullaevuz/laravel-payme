<?php

namespace Khamdullaevuz\Payme;


use Khamdullaevuz\Payme\Exceptions\PaymeException;
use Khamdullaevuz\Payme\Handlers\PaymeRequestHandler;
use Khamdullaevuz\Payme\Services\PaymeService;
use Illuminate\Http\Request;

class Payme
{
    /**
     * @throws PaymeException
     */
    public function handle(Request $request)
    {
        $handler = new PaymeRequestHandler($request);
        return (new PaymeService($handler->params))->{$handler->method}();
    }
}