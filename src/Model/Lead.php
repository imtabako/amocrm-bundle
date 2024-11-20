<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Model;

class Lead
{
    public function __construct(
        ?Contact $contact = null,
        ?string $email = null,
        ?int $price = null,
        ?string $leadName = null,
        array $tags = [],
    ) {}
}
