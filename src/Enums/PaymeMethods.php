<?php

namespace Khamdullaevuz\Payme\Enums;

enum PaymeMethods: string
{
    case CheckPerformTransaction = 'CheckPerformTransaction';
    case CreateTransaction = 'CreateTransaction';
    case PerformTransaction = 'PerformTransaction';
    case CancelTransaction = 'CancelTransaction';
    case CheckTransaction = 'CheckTransaction';
    case GetStatement = 'GetStatement';
    case SetFiscalData = 'SetFiscalData';
}