<?php

namespace App\Logging;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Monolog\Processor\UidProcessor;

class CustomizeFormatter
{
    public function __invoke($logger)
    {
        $coloredLineFormatter = new ColoredLineFormatter();
        $uidProcessor         = new UidProcessor(10); // 请求唯一id
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($coloredLineFormatter);
            $handler->pushProcessor($uidProcessor);
        }
    }
}
