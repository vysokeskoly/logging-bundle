Logging Bundle
==============

Changelog
---------
See CHANGELOG.md

Installation
-----------------------------

### Step 1

Install using *composer*

    "require": {
        "vysokeskoly/logging-bundle" : "^7.0"
    },

### Step 2

Add VysokeSkolyLoggingBundle to AppKernel to list of loaded bundles. Configure required parameters for bundle.

**config.yml**

    # bundle configuration
    vysoke_skoly_logging:
        app_id: appcz #should not contain dot (.)
        graylog:
            hostname: log01
            facility: app.cz
        
        doctrine_execute_time_threshold: 0 # optional, in ms, null will disable this feature

    # add channel monolog.logger.perflog and optionally also businesslog channel
    monolog:
         channels: ["perflog", "businesslog"]

### Step 3

Configure monolog logging options.

**config_prod.yml**

    monolog:
        handlers:
            main:
                type: stream
                path: "%kernel.logs_dir%/%kernel.environment%.log"
                level: notice
                formatter: vysokeskoly.monolog.formatter.extended
                channels: ["!perflog", "!businesslog"]
            console:
                type: console
            gelf:
                type: gelf
                level: notice
                publisher: vysokeskoly.monolog.handler.gelf
                formatter: vysokeskoly.monolog.formatter.gelf.message
                channels: ["!businesslog"]

Performance logging of commands
-------------------------------

Performance logging of HTTP Requests is enabled by default, but you can also enable performance logging of your CLI commands.

You just need to make the Command you want to be performance-logged to implement `PerfloggableCommandInterface` like this:

    class MyGreatCommand extends Command implements PerfloggableCommandInterface
    {
    ...
    }

The execution time of this command will be then measured and sent to the `perflog` channel with metric named
according to the name of the command.
