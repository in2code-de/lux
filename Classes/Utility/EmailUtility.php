<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

class EmailUtility
{
    public static function extendEmailReceiverArray(array $emails, string $receiverName = 'receiver'): array
    {
        $extendedArray = [];
        foreach ($emails as $email) {
            $extendedArray[$email] = $receiverName;
        }
        return $extendedArray;
    }

    public static function getDomainFromEmail(string $email): string
    {
        return strtolower(substr(strrchr($email, '@') ?: '', 1));
    }
}
