<?php
declare(strict_types = 1);
namespace In2code\Lux\Utility;

/**
 * Class EmailUtility
 */
class EmailUtility
{

    /**
     * @param array $emails
     * @param string $receiverName
     * @return array
     */
    public static function extendEmailReceiverArray(array $emails, string $receiverName = 'receiver'): array
    {
        $extendedArray = [];
        foreach ($emails as $email) {
            $extendedArray[$email] = $receiverName;
        }
        return $extendedArray;
    }
}
