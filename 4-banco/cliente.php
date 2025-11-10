<?php
class BankClient {
    private $socket;
    private $host;
    private $port;
    
    public function __construct($host = 'localhost', $port = 8891) {
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
        
        echo "Conectado ao Servidor Bancário!\n\n";
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
        echo "Desconectado do servidor.\n";
    }
    
    public function interactive() {
        $this->showHelp();
        
        while (true) {
            echo "\n> ";
            $input = trim(fgets(STDIN));
            
            if ($input === 'exit' || $input === 'quit') {
                $this->sendCommand('EXIT');
                break;
            }
            
            if ($input === 'help') {
                $this->showHelp();
                continue;
            }
            
            if (empty($input)) {
                continue;
            }
            
            $this->sendCommand($input);
            $response = $this->receiveResponse();
            echo $response . "\n";
        }
        
        $this->disconnect();
    }
    
    private function showHelp() {
        echo "\n=== Cliente Bancário ===\n";
        echo "Comandos:\n";
        echo "  DEPOSIT <conta> <valor>   - Depositar valor na conta\n";
        echo "  WITHDRAW <conta> <valor>  - Sacar valor da conta\n";
        echo "  BALANCE <conta>           - Consultar saldo\n";
        echo "  CREATE <conta>            - Criar nova conta\n";
        echo "  help                      - Mostra esta ajuda\n";
        echo "  exit ou quit              - Sair\n";
        echo "\nExemplo de uso:\n";
        echo "  CREATE 0001\n";
        echo "  DEPOSIT 0001 500\n";
        echo "  WITHDRAW 0001 200\n";
        echo "  BALANCE 0001\n";
    }
}

$client = new BankClient();
$client->connect();
$client->interactive();