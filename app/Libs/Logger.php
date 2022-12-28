<?php

namespace App\Libs;

use Exception;
use Illuminate\Support\Facades\Log;

class Logger
{
    const ACTION_SEND_BROADCAST_MESSAGE = 'send_broadcast_message';

    const LEVEL_EMERGENCY = 'emergency';
    const LEVEL_ALERT = 'alert';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_ERROR = 'error';
    const LEVEL_WARNING = 'warning';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_INFO = 'info';
    const LEVEL_DEBUG = 'debug';

    const TABLE_BROADCAST_MESSAGE = 'broadcast_messages';
    const TABLE_BROADCAST_MESSAGE_RECIPIENT = 'broadcast_message_recipients';
    const TABLE_COMPANY = 'companies';

    public static function log(
        ?Exception $e,
        string $level = self::LEVEL_ERROR,
        ?string $action = null,
        ?string $table = null,
        ?string $resourceId = null,
        ?string $companyId = null,
        ?string $branchId = null,
        ?string $userId = null,
        ?string $data = null,
        ?string $response = null
    ): void {
        $log = new \App\Models\Log();
        $log->company_id = $companyId;
        $log->branch_id = $branchId;
        $log->user_id = $userId;
        $log->resource_id = $resourceId;
        $log->level = $level;
        $log->table = $table;
        $log->action = $action;
        $log->data = $data;
        $log->response = $response;
        $log->message = $e?->getMessage();
        $log->exception = !empty($e) ? get_class($e) : null;
        $log->file = $e?->getFile();
        $log->line = $e?->getLine();
        $log->trace = $e?->getTraceAsString();
        $log->save();

        if (!empty($e) && in_array($level, [self::LEVEL_EMERGENCY, self::LEVEL_ALERT, self::LEVEL_CRITICAL, self::LEVEL_ERROR])) {
            Log::$level($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
