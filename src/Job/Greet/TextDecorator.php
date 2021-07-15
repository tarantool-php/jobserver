<?php

declare(strict_types=1);

namespace App\Job\Greet;

final class TextDecorator
{
    public function decorate(string $text) : string
    {
        return "**$text**";
    }
}
