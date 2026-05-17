<?php
class Response {
    public static function success(mixed $data = null, string $message = 'Berhasil', int $code = 200): void {
        http_response_code($code);
        echo json_encode(['success' => true,  'message' => $message, 'data' => $data]);
        exit();
    }

    public static function error(string $message = 'Terjadi kesalahan', int $code = 400): void {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message, 'data' => null]);
        exit();
    }

    public static function paginate(array $data, int $total, int $page, int $limit): void {
        http_response_code(200);
        echo json_encode([
            'success'    => true,
            'message'    => 'Berhasil',
            'data'       => $data,
            'pagination' => [
                'total'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => (int) ceil($total / $limit),
            ],
        ]);
        exit();
    }
}
