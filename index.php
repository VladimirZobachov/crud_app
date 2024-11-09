<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

// Load environment variables from .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

/**
 * Establish a PDO connection using environment variables.
 *
 * @return PDO
 */
function getConnection(): PDO {
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $db   = $_ENV['DB_DATABASE'];
    $user = $_ENV['DB_USERNAME'];
    $pass = $_ENV['DB_PASSWORD'];
    $charset = $_ENV['DB_CHARSET'];

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $user, $pass, $options);
}

/**
 * JSON response wrapper.
 *
 * @param bool $success
 * @param mixed $result
 * @param Response $response
 * @return Response
 */
function createJsonResponse(bool $success, $result, Response $response): Response {
    $response->getBody()->write(json_encode([
        "success" => $success,
        "result" => $result
    ]));
    return $response->withHeader('Content-Type', 'application/json');
}

// POST /create
$app->post('/create', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    $fullName = $data['full_name'] ?? null;
    $role = $data['role'] ?? null;
    $efficiency = $data['efficiency'] ?? null;

    if (!$fullName || !$role || $efficiency === null) {
        return createJsonResponse(false, ["error" => "Invalid input data"], $response)->withStatus(400);
    }

    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("INSERT INTO users (full_name, role, efficiency) VALUES (:full_name, :role, :efficiency)");
        $stmt->execute([
            'full_name' => $fullName,
            'role' => $role,
            'efficiency' => $efficiency
        ]);

        return createJsonResponse(true, ["id" => $pdo->lastInsertId()], $response)->withStatus(201);
    } catch (PDOException $e) {
        return createJsonResponse(false, ["error" => $e->getMessage()], $response)->withStatus(500);
    }
});

// GET /get or /get/{user_id}
$app->get('/get[/{user_id}]', function (Request $request, Response $response, array $args) {
    $userId = $args['user_id'] ?? null;
    $role = $request->getQueryParams()['role'] ?? null;

    try {
        $pdo = getConnection();
        $query = "SELECT id, full_name, role, efficiency FROM users";
        $params = [];

        if ($userId) {
            $query .= " WHERE id = :id";
            $params['id'] = $userId;
        } elseif ($role) {
            $query .= " WHERE role = :role";
            $params['role'] = $role;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        return empty($users)
            ? createJsonResponse(false, ["error" => "User not found"], $response)->withStatus(404)
            : createJsonResponse(true, ["users" => $users], $response);
    } catch (PDOException $e) {
        return createJsonResponse(false, ["error" => $e->getMessage()], $response)->withStatus(500);
    }
});

// PATCH /update/{user_id}
$app->patch('/update/{user_id}', function (Request $request, Response $response, array $args) {
    $userId = $args['user_id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $fields = [];
    $params = ['id' => $userId];

    if (isset($data['full_name'])) {
        $fields[] = "full_name = :full_name";
        $params['full_name'] = $data['full_name'];
    }
    if (isset($data['role'])) {
        $fields[] = "role = :role";
        $params['role'] = $data['role'];
    }
    if (isset($data['efficiency'])) {
        $fields[] = "efficiency = :efficiency";
        $params['efficiency'] = $data['efficiency'];
    }

    if (empty($fields)) {
        return createJsonResponse(false, ["error" => "No fields to update"], $response)->withStatus(400);
    }

    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id");
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            return createJsonResponse(false, ["error" => "User not found"], $response)->withStatus(404);
        }

        $stmt = $pdo->prepare("SELECT id, full_name, role, efficiency FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        return createJsonResponse(true, ["user" => $user], $response);
    } catch (PDOException $e) {
        return createJsonResponse(false, ["error" => $e->getMessage()], $response)->withStatus(500);
    }
});

// DELETE /delete or /delete/{user_id}
$app->delete('/delete[/{user_id}]', function (Request $request, Response $response, array $args) {
    $userId = $args['user_id'] ?? null;

    try {
        $pdo = getConnection();

        if ($userId) {
            $stmt = $pdo->prepare("SELECT id, full_name, role, efficiency FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();

            if (!$user) {
                return createJsonResponse(false, ["error" => "User not found"], $response)->withStatus(404);
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);

            return createJsonResponse(true, ["user" => $user], $response);
        } else {
            $pdo->exec("DELETE FROM users");
            return createJsonResponse(true, null, $response);
        }
    } catch (PDOException $e) {
        return createJsonResponse(false, ["error" => $e->getMessage()], $response)->withStatus(500);
    }
});

$app->run();

