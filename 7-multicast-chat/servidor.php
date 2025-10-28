<?php
class ChatServer {
    private $socket;
    private $port = 8894;
    private $clients = [];
    
    public function __construct() {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$this->socket) {
            die("Erro ao criar socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, 1);
        
        if (!socket_bind($this->socket, '0.0.0.0', $this->port)) {
            die("Erro ao vincular socket: " . socket_strerror(socket_last_error()) . "\n");
        }
    }
    
    public function start() {
        echo "Servidor de Chat UDP iniciado na porta {$this->port}\n";
        echo "Aguardando mensagens...\n\n";
        
        while (true) {
            $data = '';
            $from = '';
            $port = 0;
            
            if (socket_recvfrom($this->socket, $data, 1024, 0, $from, $port) !== false) {
                $clientKey = "{$from}:{$port}";
                
                if (!isset($this->clients[$clientKey])) {
                    $this->clients[$clientKey] = ['name' => 'User' . count($this->clients) + 1];
                    echo "Novo cliente conectado: {$clientKey} ({$this->clients[$clientKey]['name']})\n";
                }
                
                $message = json_decode(trim($data), true);
                
                if ($message && isset($message['type'])) {
                    $this->handleMessage($clientKey, $message, $from, $port);
                }
            }
        }
    }
    
    private function handleMessage($clientKey, $message, $from, $port) {
        switch ($message['type']) {
            case 'JOIN':
                $this->clients[$clientKey]['name'] = $message['name'] ?? $this->clients[$clientKey]['name'];
                echo "{$this->clients[$clientKey]['name']} entrou no chat\n";
                $this->broadcast(['type' => 'USER_JOINED', 'user' => $this->clients[$clientKey]['name']]);
                break;
                
            case 'MESSAGE':
                $username = $this->clients[$clientKey]['name'];
                echo "[{$username}]: {$message['text']}\n";
                $this->broadcast(['type' => 'MESSAGE', 'user' => $username, 'text' => $message['text']]);
                break;
                
            case 'LEAVE':
                $username = $this->clients[$clientKey]['name'];
                echo "{$username} saiu do chat\n";
                unset($this->clients[$clientKey]);
                $this->broadcast(['type' => 'USER_LEFT', 'user' => $username]);
                break;
        }
    }
    
    private function broadcast($message) {
        $json = json_encode($message) . "\n";
        
        foreach ($this->clients as $clientKey => $client) {
            list($ip, $port) = explode(':', $clientKey);
            socket_sendto($this->socket, $json, strlen($json), 0, $ip, $port);
        }
    }
    
    public function __destruct() {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
}

$server = new ChatServer();
$server->start();