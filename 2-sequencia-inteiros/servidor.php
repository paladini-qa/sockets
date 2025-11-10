<?php
class IntegerSequenceServer {
    private $port;
    private $socket;
    
    public function __construct($port = 8889) {
        $this->port = $port;
    }
    
    public function start() {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->socket) {
            die("Erro ao criar socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        if (!socket_bind($this->socket, '0.0.0.0', $this->port)) {
            die("Erro ao vincular socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        if (!socket_listen($this->socket, 5)) {
            die("Erro ao escutar socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        echo "Servidor de Sequência de Inteiros iniciado na porta {$this->port}\n";
        echo "Aguardando conexões...\n\n";
        
        while (true) {
            $client = socket_accept($this->socket);
            if ($client === false) {
                continue;
            }
            
            $this->handleClient($client);
            socket_close($client);
        }
    }
    
    private function handleClient($client) {
        echo "Cliente conectado!\n";
        
        $sequence = [];
        $operation = null;
        
        while (true) {
            $data = socket_read($client, 1024);
            
            if ($data === false || strlen($data) === 0) {
                break;
            }
            
            $lines = explode("\n", trim($data));
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                if (empty($line)) {
                    continue;
                }
                
                // Verificar se é uma operação
                if (in_array(strtoupper($line), ['SUM', 'AVG', 'MAX', 'MIN', 'MULT'])) {
                    $operation = strtoupper($line);
                    break 2; // Sair dos dois loops
                } elseif (is_numeric($line)) {
                    $sequence[] = (int)$line;
                    echo "Recebido: {$line}\n";
                }
            }
        }
        
        if (!empty($sequence)) {
            echo "Sequência recebida: " . implode(', ', $sequence) . "\n";
            echo "Operação: {$operation}\n";
            
            $result = $this->processOperation($sequence, $operation);
            echo "Resultado: {$result}\n\n";
            
            socket_write($client, $result . "\n");
        }
        
        echo "Cliente desconectado\n\n";
    }
    
    private function processOperation($sequence, $operation) {
        if (empty($sequence)) {
            return "ERRO: Sequência vazia";
        }
        
        switch ($operation) {
            case 'SUM':
                return array_sum($sequence);
                
            case 'AVG':
                return round(array_sum($sequence) / count($sequence), 2);
                
            case 'MAX':
                return max($sequence);
                
            case 'MIN':
                return min($sequence);
                
            case 'MULT':
                return array_product($sequence);
                
            default:
                return "ERRO: Operação desconhecida. Use: SUM, AVG, MAX, MIN, MULT";
        }
    }
    
    public function __destruct() {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
}

// Iniciar servidor
$server = new IntegerSequenceServer(8889);
$server->start();