<?php

namespace App\Libs;

use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Log;

class Response
{
    private array $headers = [];

    public function json(
        array|object|null $data = null,
        array|string|object $message,
        int $status = HttpResponse::HTTP_OK,
        $excep = null,
        $file = null,
        $line = null,
        $trace = null
    ): \Illuminate\Http\JsonResponse {
        $statusText = HttpResponse::$statusTexts[$status] ?? 'Unknown';

        $data = (array) $data;
        $pagination = $data['pagination'] ?? null;
        if ($pagination) {
            unset($data['pagination']);
        }

        $response = [
            'code' => $status,
            'status' => $statusText,
            'is_error' => $status >= HttpResponse::HTTP_BAD_REQUEST,
            'message' => $message,
            'data' => $data,
        ];

        if ($pagination) {
            $response['pagination'] = $pagination;
        }


        $exception = $this->exception($status, $excep, $file, $line, $trace);

        if (config('app.debug')) {
            $response['debug'] = $exception;
        }

        return response()->json($response, $status, $this->getHeaders());
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    private function getHeaders()
    {
        return $this->headers;
    }

    private function exception(int $status, $excep, $file, $line, $trace): array {
        $debug = [];
        $debug['user_id'] = auth()->user()->id ?? null;
        $debug['exception'] = $excep;
        $debug['file'] = $file;
        $debug['line'] = $line;
        $debug['trace'] = $trace;

        $statusText = HttpResponse::$statusTexts[$status] ?? 'Unknown';

        $clientError = $status >= HttpResponse::HTTP_BAD_REQUEST && $status < HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
        $serverError = $status >= HttpResponse::HTTP_INTERNAL_SERVER_ERROR;

        if ($clientError && $status !== 404)
            Log::warning($statusText, $debug);
        elseif ($serverError)
            Log::error($statusText, $debug);

        return $debug;
    }
}
