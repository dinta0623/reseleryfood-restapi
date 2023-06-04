<?php

namespace Dinta\Framework\Http;

class Response
{
    public function __construct(
        private ?string $content = '',
        private ?int $status = 200,
        private ?array $headers = [],
    ) {
    }

    public function send(): void
    {
        if (isset($this->headers)) {
            foreach ($this->headers as $header) {
                header($header);
            }
        }
        http_response_code($this->status);
        echo $this->content;
    }
}
