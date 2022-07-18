<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Processor;

/**
 * Injects application facility to the log record
 */
class FacilityProcessor
{
    private string $facility;

    /**
     * @param string $facility Application identification
     */
    public function __construct(string $facility)
    {
        $this->facility = $facility;
    }

    /**
     * @param array $record Current log record
     * @return array Log record with additional data
     */
    public function __invoke(array $record): array
    {
        $record['channel'] = $this->facility;

        return $record;
    }
}
