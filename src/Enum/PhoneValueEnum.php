<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Enum;

enum PhoneValueEnum: string
{
    /** Рабочий */
    case WORK = 'WORK';

    /** Рабочий прямой */
    case WORKDD = 'WORKDD';

    /** Мобильный */
    case MOB = 'MOB';

    /** Факс */
    case FAX = 'FAX';

    /** Домашний */
    case HOME = 'HOME';

    /** Другой */
    case OTHER = 'OTHER';
}
