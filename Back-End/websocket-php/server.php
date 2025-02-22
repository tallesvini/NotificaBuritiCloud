<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Nova conexão! ID: {$conn->resourceId}\n";
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Conexão fechada! ID: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $senderId = $from->resourceId; // ID único da conexão
        echo "Mensagem recebida de ID {$senderId}: {$msg}\n";

        // Decodificar a mensagem JSON recebida
        $data = json_decode($msg, true);

        // Verificar se a mensagem inclui um destinatário
        if (isset($data['to']) && isset($data['message'])) {
            $destinatarioId = $data['to']; // ID do destinatário
            $mensagem = $data['message'];
            $type = $data['type'];

            // Criar a resposta JSON
            $response = json_encode([
                'from' => $senderId, // ID de quem enviou
                'message' => $mensagem,
                'type' => $type,
            ]);

            // Buscar o cliente correto e enviar a mensagem
            foreach ($this->clients as $client) {
                if ($client->resourceId == $destinatarioId) {
                    $client->send($response);
                    echo "Mensagem enviada para ID {$destinatarioId}\n";
                    return;
                }
            }

            // Se o destinatário não for encontrado
            echo "Destinatário ID {$destinatarioId} não encontrado\n";
        } else {
            echo "Formato de mensagem inválido\n";
        }
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erro: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Usa a variável de ambiente PORT definida pelo Railway ou 8080 como padrão
$port = getenv('PORT') ?: 8080;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    (int) $port
);

echo "Servidor WebSocket rodando na porta {$port}\n";
$server->run();
