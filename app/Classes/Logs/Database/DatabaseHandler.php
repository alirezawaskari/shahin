<?php
namespace App\Classes\Logs\Database;

use Monolog\Handler\AbstractProcessingHandler;
use App\Models\LogMessage;

class DatabaseHandler extends AbstractProcessingHandler {
    /**
    * @inheritDoc
    */
    protected function write( array $record ): void {
        LogMessage::create( [
            'level' => $record[ 'level' ],
            'level_name' => $record[ 'level_name' ],
            'message' => $record[ 'message' ],
            'logged_at' => $record[ 'datetime' ],
            'context' => json_encode($record[ 'context' ]),
            'extra' => json_encode($record[ 'extra' ]),
        ] );
    }
}
