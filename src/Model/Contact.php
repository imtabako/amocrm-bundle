<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Model;

use Ectool\AmoCrmBundle\Enum\PhoneValueEnum;

class Contact
{
    public function __construct(
        public string $phoneNumber,
        public PhoneValueEnum $phoneType = PhoneValueEnum::WORK,
        public ?string $name = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
    ) {}
}
