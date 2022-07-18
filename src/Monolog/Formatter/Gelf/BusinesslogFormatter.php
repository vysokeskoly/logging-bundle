<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf;

use Gelf\Message;
use Monolog\LogRecord;

class BusinesslogFormatter extends AbstractFormatter
{
    /** Message which replaces original message of all records */
    protected string $message = 'Business log';

    public function format(LogRecord $record): Message
    {
        $gelfMessage = parent::format($record);

        // Move metric name from message to additional and full_message
        $gelfMessage->setAdditional('metric', $record->message);
        $gelfMessage->setFullMessage($record->message);
        // Set same message for all business log events to make them easily searchable
        $gelfMessage->setShortMessage($this->message);

        return $gelfMessage;
    }
}
