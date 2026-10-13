<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

// Middleware para aceitar JSON no corpo da requisição
$app->addBodyParsingMiddleware();

// Middleware de tratamento de erros
$app->addErrorMiddleware(true, true, true);

// ==========================================
// DADOS INICIAIS (Array com 5 missões)
// ==========================================
$missoes = [
    ["id" => 1, "nome" => "Apollo 11", "ano" => 1969, "agencia" => "NASA", "status" => "Concluída"],
    ["id" => 2, "nome" => "Voyager 1", "ano" => 1977, "agencia" => "NASA", "status" => "Em operação"],
    ["id" => 3, "nome" => "Artemis II", "ano" => 2026, "agencia" => "NASA", "status" => "Planejada"],
    ["id" => 4, "nome" => "Sputnik 1", "ano" => 1957, "agencia" => "URSS", "status" => "Concluída"],
    ["id" => 5, "nome" => "Mars Pathfinder", "ano" => 1997, "agencia" => "NASA", "status" => "Concluída"]
];

// ==========================================
// ROTA 1: GET /status (Verificar funcionamento)
// ==========================================
$app->get('/status', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(["status" => "ok"]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// ==========================================
// ROTA 2: GET /missoes (Listar todas)
// ==========================================
$app->get('/missoes', function (Request $request, Response $response) use ($missoes) {
    $response->getBody()->write(json_encode($missoes));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// ==========================================
// ROTA 3: GET /missoes/{id} (Buscar por ID)
// ==========================================
$app->get('/missoes/{id}', function (Request $request, Response $response, array $args) use ($missoes) {
    $id = (int)$args['id'];
    foreach ($missoes as $missao) {
        if ($missao['id'] === $id) {
            $response->getBody()->write(json_encode($missao));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    }
    $response->getBody()->write(json_encode(["erro" => "Missão não encontrada"]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

// ==========================================
// ROTA 4: POST /missoes (Cadastrar nova)
// ==========================================
$app->post('/missoes', function (Request $request, Response $response) use (&$missoes) {
    $dados = $request->getParsedBody();

    // Gera um novo ID
    $novoId = count($missoes) > 0 ? max(array_column($missoes, 'id')) + 1 : 1;

    $novaMissao = [
        "id" => $novoId,
        "nome" => $dados['nome'] ?? '',
        "ano" => $dados['ano'] ?? '',
        "agencia" => $dados['agencia'] ?? '',
        "status" => $dados['status'] ?? ''
    ];

    $missoes[] = $novaMissao;

    $response->getBody()->write(json_encode($novaMissao));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

// ==========================================
// ROTA 5: PUT /missoes/{id} (Atualizar)
// ==========================================
$app->put('/missoes/{id}', function (Request $request, Response $response, array $args) use (&$missoes) {
    $id = (int)$args['id'];
    $dados = $request->getParsedBody();

    foreach ($missoes as $index => $missao) {
        if ($missao['id'] === $id) {
            $missoes[$index] = [
                "id" => $id,
                "nome" => $dados['nome'] ?? $missao['nome'],
                "ano" => $dados['ano'] ?? $missao['ano'],
                "agencia" => $dados['agencia'] ?? $missao['agencia'],
                "status" => $dados['status'] ?? $missao['status']
            ];
            $response->getBody()->write(json_encode($missoes[$index]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    }
    $response->getBody()->write(json_encode(["erro" => "Missão não encontrada"]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

// ==========================================
// ROTA 6: DELETE /missoes/{id} (Remover)
// ==========================================
$app->delete('/missoes/{id}', function (Request $request, Response $response, array $args) use (&$missoes) {
    $id = (int)$args['id'];

    foreach ($missoes as $index => $missao) {
        if ($missao['id'] === $id) {
            unset($missoes[$index]);
            $missoes = array_values($missoes); // Reorganiza os índices
            return $response->withStatus(204);
        }
    }
    $response->getBody()->write(json_encode(["erro" => "Missão não encontrada"]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

$app->run();