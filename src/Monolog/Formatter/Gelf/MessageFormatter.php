<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Monolog\Formatter\Gelf;

use Gelf\Message;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Serializes a log message to GELF with additional information about exception and request - useful for error logs etc.
 */
class MessageFormatter extends AbstractFormatter
{
    public function format(array $record): Message
    {
        $message = parent::format($record);

        if (!empty($record['request']['attributes'])) {
            foreach ($record['request']['attributes'] as $attribute => $value) {
                if (empty($value)) {
                    continue;
                }

                if (!is_scalar($value)) {
                    $this->enableDumpHandler();
                    $value = VarDumper::dump($value);
                    $this->disableDumpHandler();
                }

                // prefix all attributes because e.g. attribute with name 'id' is not allowed to send
                $message->setAdditional('attribute_' . $attribute, $value);
            }
        }

        $fullMessage = '';
        if (!empty($record['request']['query'])) {
            $fullMessage .= "\n------ GET ------\n"
                . var_export($record['request']['query'], true);
        }

        if (!empty($record['context']['exception'])) {
            /** @var \Exception $exception */
            $exception = $record['context']['exception'];

            $fullMessage .= "\n------ Exception ------\n";
            $fullMessage .= $exception->getMessage() . "\n";
            $fullMessage .= $exception->getTraceAsString();

            // Unset default data about exception
            $message->setAdditional($this->contextPrefix . 'exception', '');

            if ($exception->getPrevious()) {
                $fullMessage .= "\n\n------ Previous exception ------\n\n";
                $previousExpcetion = $exception->getPrevious();
                if ($previousExpcetion->getMessage()) {
                    $fullMessage .= $previousExpcetion->getMessage() . "\n";
                    $fullMessage .= $previousExpcetion->getTraceAsString();
                }
            }
        }

        if (!empty($fullMessage)) {
            $message->setFullMessage($fullMessage);
        }

        return $message;
    }

    /**
     * @see https://symfony.com/doc/current/components/var_dumper/advanced.html
     */
    private function enableDumpHandler(): void
    {
        VarDumper::setHandler(function ($variable) {
            $cloner = new VarCloner();
            $dumper = new CliDumper();

            $cloner->setMinDepth(3);
            $cloner->setMaxString(-1);

            return $dumper->dump($cloner->cloneVar($variable), true);
        });
    }

    private function disableDumpHandler(): void
    {
        VarDumper::setHandler();
    }
}
