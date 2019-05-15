<?php

namespace Nwogu\SearchMan\Helpers;

class Constants
{
    const HANDLER_EXCEPTION = "Handler is not supported. Should Implement PriorityHandlerInterface";

    const BREAKER_EXCEPTION = "Breaker must be instance of IndexBreaker Class";

    const CURLY_BRACKET_EXCEPTION = "Cannot currently index recursive curly bracket";
}