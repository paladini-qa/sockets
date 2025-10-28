<?php
class ChatClient {
    private $sendSocket;
    private $receiveSocket;
    private $serverAddress = '127.0.0.1';
    private $port = 8894;
    private $username;
    
    public function __construct($username) {
        $this->username = $username;
        
        // Socket para enviar
        $this->sendSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$this->sendSocket) {
            die("Erro ao criar socket de envio: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        socket_set_option($this->sendSocket, SOL_SOCKET, SO_BROADCAST, 1);
        
        // Socket para receber
        $this->receiveSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$this->receiveSocket) {
            die("Erro ao criar socket de recepção: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        socket_set_option($this->receiveSocket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        $localPort = $this->port + 1000 + rand(1, 1000); // Porta local aleatória
        
        // Vincular socket de recepção
        if (!socket_bind($this->receiveSocket, '0.0.0.0', $localPort)) {
            die("Erro ao vincular socket de recepção: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        // Enviar mensagem de JOIN
        $this->sendMessage('JOIN', $this->username);
    }
    
    public function sendMessage($type, $data = null) {
        $message = [
            'type' => $type,
            'data' => $data
        ];
        
        if ($type === 'MESSAGE') {
            $message['text'] = $data;
        } elseif ($type === 'JOIN') {
            $message['name'] = $data;
        }
        
        $json = json_encode($message) . "\n";
        socket_sendto($this->sendSocket, $json, strlen($json), 0, $this->serverAddress, $this->port);
    }
    
    public function receiveMessages() {
        $data = '';
        $from = '';
        $port = 0;
        
        socket_set_nonblock($this->receiveSocket);
        
        if (socket_recvfrom($this->receiveSocket, $data, 1024, 0, $from, $port) !== false) {
            $message = json_decode(trim($data), true);
            return $message;
        }
        
        return null;
    }
    
    public function disconnect() {
        $this->sendMessage('LEAVE');
        
        if ($this->sendSocket) {
            socket_close($this->sendSocket);
        }
        
        if ($this->receiveSocket) {
            socket_close($this->receiveSocket);
        }
    }
    
    public function interactive() {
        echo "\n=== Chat UDP ===\n";
        echo "Usuário: {$this->username}\n";
        echo "Digite 'exit' para sair\n\n";
        
        // Non-blocking mode
        socket_set_nonblock($this->receiveSocket);
        
        while (true) {
            // Verificar se há mensagens
            $message = $this->receiveMessages();
            if ($message) {
                $this->displayMessage($message);
            }
            
            // Verificar se há input do usuário
            $read = [STDIN];
            $write = [];
            $except = [];
            
            $changed = @stream_select($read, $write, $except, 0, 100000);
            
            if ($changed > 0 && in_array(STDIN, $read)) {
                $input = trim(fgets(STDIN));
                
                if ($input === 'exit' || $input === 'quit') {
                    break;
                }
                
                if (!empty($input)) {
                    $this->sendMessage('MESSAGE', $input);
                }
            }
        }
        
        $this->disconnect();
        echo "\nSaindo do chat...\n";
    }
    
    private function displayMessage($message) {
        switch ($message['type']) {
            case 'MESSAGE':
                $user = $message['user'] ?? 'Unknown';
                $text = $message['text'] ?? '';
                if ($user !== $this->username) {
                    echo "[{$user}]: {$text}\n";
                }
                break;
                
            case 'USER_JOINED':
                $user = $message['user'] ?? 'Unknown';
                if ($user !== $this->username) {
                    echo ">>> {$user} entrou no chat\n";
                }
                break;
                
            case 'USER_LEFT':
                $user = $message['user'] ?? 'Unknown';
                if ($user !== $this->username) {
                    echo ">>> {$user} saiu do chat\n";
                }
                break;
        }
    }
}

// Verificar argumento de usuário
$username = $argv[1] ?? 'User' . rand(1000, 9999);

$client = new ChatClient($username);
$client->interactive();