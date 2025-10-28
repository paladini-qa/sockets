<?php
class IntegerSequenceClient {
    private $socket;
    private $host;
    private $port;
    
    public function __construct($host = 'localhost', $port = 8889) {
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
        
        echo "Conectado ao servidor de Sequência de Inteiros!\n\n";
    }
    
    public function sendSequenceAndOperation($sequence, $operation) {
        echo "Enviando sequência: " . implode(', ', $sequence) . "\n";
        echo "Operação: {$operation}\n\n";
        
        // Enviar números
        foreach ($sequence as $num) {
            socket_write($this->socket, $num . "\n");
        }
        
        // Enviar operação
        socket_write($this->socket, $operation . "\n");
        
        // Fechar socket para escrita (EOF)
        socket_shutdown($this->socket, 1);
        
        // Receber resultado
        $result = socket_read($this->socket, 1024);
        echo "Resultado: " . trim($result) . "\n";
    }
    
    public function disconnect() {
        socket_close($this->socket);
        echo "\nDesconectado do servidor.\n";
    }
    
    public function interactive() {
        $this->showHelp();
        
        while (true) {
            echo "\nDigite os números separados por espaço (ex: 5 10 15 20):\n> ";
            $input = trim(fgets(STDIN));
            
            if ($input === 'exit' || $input === 'quit') {
                break;
            }
            
            if ($input === 'help') {
                $this->showHelp();
                continue;
            }
            
            $numbers = array_filter(explode(' ', $input), function($n) {
                return is_numeric($n);
            });
            
            if (empty($numbers)) {
                echo "Digite números válidos!\n";
                continue;
            }
            
            echo "\nEscolha a operação:\n";
            echo "  1 - SUM (soma)\n";
            echo "  2 - AVG (média)\n";
            echo "  3 - MAX (máximo)\n";
            echo "  4 - MIN (mínimo)\n";
            echo "  5 - MULT (multiplicação)\n";
            echo "> ";
            
            $choice = trim(fgets(STDIN));
            $operations = ['1' => 'SUM', '2' => 'AVG', '3' => 'MAX', '4' => 'MIN', '5' => 'MULT'];
            
            if (!isset($operations[$choice])) {
                echo "Operação inválida!\n";
                continue;
            }
            
            $this->connect();
            $this->sendSequenceAndOperation($numbers, $operations[$choice]);
            $this->disconnect();
        }
    }
    
    private function showHelp() {
        echo "\n=== Cliente de Sequência de Inteiros ===\n";
        echo "Envie uma sequência de números e uma operação.\n";
        echo "Comandos:\n";
        echo "  help      - Mostra esta ajuda\n";
        echo "  exit/quit - Sair\n";
    }
}

// Executar cliente
$client = new IntegerSequenceClient();
$client->interactive();