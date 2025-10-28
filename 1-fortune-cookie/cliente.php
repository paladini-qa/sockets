<?php
class FortuneClient {
    private $socket;
    private $host;
    private $port;
    
    public function __construct($host = 'localhost', $port = 8888) {
        $this->host = $host;
        $this->port = $port;
    }
    
    public function connect() {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->socket) {
            die("Erro ao criar socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        if (!socket_connect($this->socket, $this->host, $this->port)) {
            die("Erro ao conectar: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        echo "Conectado ao servidor de Fortunes!\n\n";
    }
    
    public function sendCommand($command) {
        socket_write($this->socket, $command . "\n");
    }
    
    public function receiveResponse() {
        $response = socket_read($this->socket, 2048);
        return trim($response);
    }
    
    public function disconnect() {
        socket_close($this->socket);
        echo "\nDesconectado do servidor.\n";
    }
    
    public function interactive() {
        $this->showHelp();
        
        while (true) {
            echo "\n> ";
            $command = trim(fgets(STDIN));
            
            if ($command === 'exit' || $command === 'quit') {
                break;
            }
            
            if ($command === 'help') {
                $this->showHelp();
                continue;
            }
            
            if (empty($command)) {
                continue;
            }
            
            $this->sendCommand($command);
            $response = $this->receiveResponse();
            echo $response . "\n";
        }
        
        $this->disconnect();
    }
    
    private function showHelp() {
        echo "\n=== Cliente de Fortunes ===\n";
        echo "Comandos:\n";
        echo "  GET-FORTUNE                    - Obtém uma fortune aleatória\n";
        echo "  ADD-FORTUNE <frase>            - Adiciona uma nova fortune\n";
        echo "  UPD-FORTUNE <pos> <frase>      - Atualiza uma fortune\n";
        echo "  LST-FORTUNE                    - Lista todas as fortunes\n";
        echo "  help                           - Mostra esta ajuda\n";
        echo "  exit ou quit                   - Sair\n";
    }
}

// Executar cliente
$client = new FortuneClient();
$client->connect();
$client->interactive();