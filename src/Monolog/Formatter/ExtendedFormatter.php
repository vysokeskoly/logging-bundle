<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter;

use Monolog\Formatter\NormalizerFormatter;

/**
 * Formats incoming records into full report with stack trace and previous exception
 */
class ExtendedFormatter extends NormalizerFormatter
{
    public function format(array $record): string
    {
        $output = sprintf(
            '%s (%s) ',
            $record['datetime']->format('Y-m-d H:i:s'),
            $record['message']
        ) . "\n";

        if (isset($record['extra']['url'])) {
            $output .= sprintf('URI: %s', $record['extra']['url']) . "\n";
        }

        if (isset($record['extra']['referrer'])) {
            $output .= sprintf('Referer: %s', $record['extra']['referrer']) . "\n";
        }

        if (($user = $record['context']['user'] ?? null) && is_object($user)) {
            if (method_exists($user, 'getUsername')) {
                $email = $user->getUsername();
            } elseif (method_exists($user, 'getUserIdentifier')) {
                $email = $user->getUserIdentifier();
            } else {
                $email = null;
            }

            if ($email !== null) {
                $output .= sprintf("Email: %s\n", $email);
            }
        }

        $output .= sprintf(
            'IP: %s, UA: %s',
            $record['extra']['ip'] ?? '?',
            $record['extra']['ua'] ?? '?'
        ) . "\n\n";

        if (!empty($record['request']['query'])) {
            $output .= 'GET: ';
            $output .= $this->varToString($record['request']['query']) . "\n";
        }

        if (!empty($record['context']['exception'])) {
            $output .= sprintf(
                'Exception: %s (%s) ',
                $record['context']['exception']->getMessage(),
                $record['context']['exception']->getCode()
            ) . "\n";
            $output .= $record['context']['exception']->getTraceAsString() . "\n\n";
        }

        if (!empty($record['context']['exception']) && $record['context']['exception']->getPrevious()) {
            $previousException = $record['context']['exception']->getPrevious();
            if ($previousException->getMessage()) {
                $output .= 'Previous Exception' . "\n";
                $output .= $previousException->getMessage() . "\n";
                $output .= $previousException->getTraceAsString() . "\n";
            }
        }

        return $output . "\n\n";
    }

    /** @param mixed $var */
    protected function varToString($var): string
    {
        // the output buffering prevents "nesting to deep" error if var_export being used
        ob_start();
        var_dump($var);
        $dump = (string) ob_get_clean();

        return (string) preg_replace('/\s+/', ' ', $dump);
    }
}
