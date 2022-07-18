<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Injects application facility to the log record
 */
class FacilityProcessor implements ProcessorInterface
{
    /**
     * @param string $facility Application identification
     */
    public function __construct(private string $facility)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(channel: $this->facility);
    }
}
