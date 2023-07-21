<?php

namespace Khamdullaevuz\Payme\Enums;

enum PaymeState: int
{
    case Pending = 1;
    case Done = 2;
    case Cancelled = -1;
    case Cancelled_After_Success = -2;
}