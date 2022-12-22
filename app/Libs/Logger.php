<?php

namespace App\Libs;

use Exception;

class Logger
{
    const ACTION_CREATE = 'create';

    const ACTION_SEND_BROADCAST_MESSAGE = 'send_broadcast_message';

    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    const TABLE_BROADCAST_MESSAGE = 'broadcast_messages';
    const TABLE_BROADCAST_MESSAGE_RECIPIENT = 'broadcast_message_recipients';

    public static function log(
        ?Exception $e,
        ?string $level,
        ?string $action,
        ?string $table,
        ?string $resourceId,
        ?string $companyId,
        ?string $branchId,
        ?string $userId,
        ?string $data,
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
        $log->message = $e?->getMessage();
        $log->exception = !empty($e) ? get_class($e) : null;
        $log->file = $e?->getFile();
        $log->line = $e?->getLine();
        $log->trace = $e?->getTraceAsString();
        $log->save();
    }
}
