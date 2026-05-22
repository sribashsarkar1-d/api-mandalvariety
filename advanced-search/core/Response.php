<?php
namespace Core;

class Response {
    public static function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function error($message, $status = 400, $did_you_mean = null, $data = []) {
        $response = [
            'success' => false,
            'message' => $message,
            'query' => Request::getQuery('q'),
            'normalized_query' => strtolower(trim(Request::getQuery('q') ?? '')),
            'did_you_mean' => $did_you_mean,
            'data' => $data
        ];
        self::json($response, $status);
    }
}
