<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf;

use Gelf\Message;
use Monolog\Formatter\GelfMessageFormatter;

/**
 * Serializes a log message to GELF format and adds some additional information
 */
class AbstractFormatter extends GelfMessageFormatter
{
    public function format(array $record): Message
    {
        $message = parent::format($record);

        if (($user = $record['context']['user'] ?? null) && is_object($user)) {
            if (method_exists($user, 'getUsername')) {
                $email = $user->getUsername();
            } elseif (method_exists($user, 'getUserIdentifier')) {
                $email = $user->getUserIdentifier();
            } else {
                $email = null;
            }

            if ($email !== null) {
                $message->setAdditional('email', $email);
            }
        }

        return $message;
    }
}
