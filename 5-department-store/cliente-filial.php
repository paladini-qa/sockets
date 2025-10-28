<?php
class BranchSimulator {
    private $socket;
    private $host;
    private $port;
    private $branchId;
    private $occurrences = 1500;
    
    public function __construct($branchId, $host = 'localhost', $port = 8892) {
        $this->branchId = $branchId;
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
    }
    
    public function send($message) {
        socket_write($this->socket, $message . "\n");
    }
    
    public function receive() {
        return trim(socket_read($this->socket, 2048));
    }
    
    public function simulate() {
        echo "Filial {$this->branchId} iniciando simulação de {$this->occurrences} ocorrências...\n";
        
        // Registrar filial
        $this->send("BRANCH:{$this->branchId}");
        $response = $this->receive();
        echo $response . "\n";
        
        $sales = 0;
        $purchases = 0;
        
        // Simular ocorrências
        for ($i = 0; $i < $this->occurrences; $i++) {
            $type = rand(0, 1); // 0 = venda, 1 = compra
            
            if ($type === 0) {
                $amount = rand(10, 1000) + (rand(0, 99) / 100);
                $this->send("SALE:{$amount}");
                $this->receive();
                $sales++;
                
                if (($i + 1) % 100 === 0) {
                    echo "Filial {$this->branchId}: Processadas " . ($i + 1) . " ocorrências...\n";
                }
            } else {
                $amount = rand(50, 500) + (rand(0, 99) / 100);
                $this->send("PURCHASE:{$amount}");
                $this->receive();
                $purchases++;
                
                if (($i + 1) % 100 === 0) {
                    echo "Filial {$this->branchId}: Processadas " . ($i + 1) . " ocorrências...\n";
                }
            }
            
            // Delay para simular tempo real
            usleep(rand(10000, 50000)); // 10ms - 50ms
        }
        
        echo "Filial {$this->branchId}: Vendas: {$sales}, Compras: {$purchases}\n";
        
        // Solicitar resumo
        $this->send("SUMMARY");
        $summary = $this->receive();
        echo "\n{$summary}\n";
        
        // Finalizar
        $this->send("END");
        $this->receive();
        
        echo "Filial {$this->branchId} finalizada.\n";
    }
    
    public function disconnect() {
        socket_close($this->socket);
    }
}

// Verificar argumentos
$branchId = $argv[1] ?? 'FILIAL01';
$occurrences = isset($argv[2]) ? (int)$argv[2] : 1500;

$simulator = new BranchSimulator($branchId);
$simulator->occurrences = $occurrences;
$simulator->connect();
$simulator->simulate();
$simulator->disconnect();