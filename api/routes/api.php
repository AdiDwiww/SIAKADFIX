<?php
// ─────────────────────────────────────────────
//  API Router
// ─────────────────────────────────────────────
require_once __DIR__ . '/../helpers/Response.php';

$method  = $_SERVER['REQUEST_METHOD'];
$baseDir = dirname($_SERVER['SCRIPT_NAME']);          // e.g. /uas/api
$uri     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path    = trim(substr($uri, strlen($baseDir)), '/');
$seg     = $path !== '' ? explode('/', $path) : [];

$resource = $seg[0] ?? '';
$param1   = $seg[1] ?? null;   // id  OR  'bulk-delete'
$param2   = $seg[2] ?? null;   // (unused currently)

switch ($resource) {

    /* ── AUTH ─────────────────────────────── */
    case 'auth':
        require_once dirname(__DIR__) . '/controllers/AuthController.php';
        $c = new AuthController();
        match(true) {
            $method === 'POST' && $param1 === 'login'           => $c->login(),
            $method === 'POST' && $param1 === 'register'        => $c->register(),
            $method === 'POST' && $param1 === 'change-password' => $c->changePassword(),
            $method === 'GET'  && $param1 === 'me'              => $c->me(),
            default => Response::error('Endpoint tidak ditemukan.', 404),
        };
        break;

    /* ── DASHBOARD ────────────────────────── */
    case 'dashboard':
        require_once dirname(__DIR__) . '/controllers/DashboardController.php';
        $c = new DashboardController();
        if ($method === 'GET') $c->index();
        else Response::error('Method tidak diizinkan.', 405);
        break;

    /* ── MAHASISWA ────────────────────────── */
    case 'mahasiswa':
        require_once dirname(__DIR__) . '/controllers/MahasiswaController.php';
        $c = new MahasiswaController();
        match(true) {
            $method === 'GET'    && !$param1                       => $c->index(),
            $method === 'POST'   && !$param1                       => $c->store(),
            $method === 'GET'    && is_numeric($param1)            => $c->show($param1),
            $method === 'POST'   && is_numeric($param1)            => $c->update($param1),
            $method === 'DELETE' && $param1 === 'bulk-delete'      => $c->bulkDestroy(),
            $method === 'DELETE' && is_numeric($param1)            => $c->destroy($param1),
            default => Response::error('Endpoint tidak ditemukan.', 404),
        };
        break;

    /* ── DOSEN ────────────────────────────── */
    case 'dosen':
        require_once dirname(__DIR__) . '/controllers/DosenController.php';
        $c = new DosenController();
        match(true) {
            $method === 'GET'    && !$param1                 => $c->index(),
            $method === 'POST'   && !$param1                 => $c->store(),
            $method === 'GET'    && is_numeric($param1)      => $c->show($param1),
            $method === 'POST'   && is_numeric($param1)      => $c->update($param1),
            $method === 'DELETE' && $param1 === 'bulk-delete'=> $c->bulkDestroy(),
            $method === 'DELETE' && is_numeric($param1)      => $c->destroy($param1),
            default => Response::error('Endpoint tidak ditemukan.', 404),
        };
        break;

    /* ── KULIAH ───────────────────────────── */
    case 'kuliah':
        require_once dirname(__DIR__) . '/controllers/KuliahController.php';
        $c = new KuliahController();
        match(true) {
            $method === 'GET'    && !$param1            => $c->index(),
            $method === 'POST'   && !$param1            => $c->store(),
            $method === 'GET'    && $param1 === 'dosen' => $c->getDosenList(),
            $method === 'GET'    && is_numeric($param1) => $c->show($param1),
            $method === 'PUT'    && is_numeric($param1) => $c->update($param1),
            $method === 'DELETE' && is_numeric($param1) => $c->destroy($param1),
            default => Response::error('Endpoint tidak ditemukan.', 404),
        };
        break;

    default:
        Response::error('Resource tidak ditemukan.', 404);
}
