<?php
class CentralStoreServer {
    private $port;
    private $socket;
    private $branches = [];
    private $totalSales = 0;
    private $totalPurchases = 0;
    
    public function __construct($port = 8892) {
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
        
        if (!socket_listen($this->socket, 10)) {
            die("Erro ao escutar socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        echo "Sistema Central iniciado na porta {$this->port}\n";
        echo "Aguardando conexões de filiais...\n\n";
        
        while (true) {
            $client = socket_accept($this->socket);
            if ($client === false) {
                continue;
            }
            
            $this->handleBranch($client);
            socket_close($client);
        }
    }
    
    private function handleBranch($client) {
        $branchId = null;
        echo "Filial conectada\n";
        
        while (true) {
            $data = socket_read($client, 1024);
            
            if ($data === false || strlen($data) === 0) {
                break;
            }
            
            $line = trim($data);
            
            if (strpos($line, 'BRANCH:') === 0) {
                $branchId = trim(substr($line, 7));
                if (!isset($this->branches[$branchId])) {
                    $this->branches[$branchId] = ['sales' => 0, 'purchases' => 0];
                }
                socket_write($client, "OK: Filial {$branchId} registrada\n");
                echo "Filial {$branchId} registrada\n\n";
                continue;
            }
            
            if (strpos($line, 'SALE:') === 0) {
                $amount = floatval(substr($line, 5));
                if ($branchId) {
                    $this->branches[$branchId]['sales']++;
                    $this->totalSales++;
                    echo "Filial {$branchId}: Venda registrada ({$this->totalSales} total)\n";
                }
                socket_write($client, "OK\n");
                continue;
            }
            
            if (strpos($line, 'PURCHASE:') === 0) {
                $amount = floatval(substr($line, 9));
                if ($branchId) {
                    $this->branches[$branchId]['purchases']++;
                    $this->totalPurchases++;
                    echo "Filial {$branchId}: Compra registrada ({$this->totalPurchases} total)\n";
                }
                socket_write($client, "OK\n");
                continue;
            }
            
            if ($line === 'SUMMARY') {
                $summary = $this->getSummary();
                socket_write($client, $summary . "\n");
                continue;
            }
            
            if ($line === 'END') {
                echo "Filial {$branchId} finalizada\n\n";
                socket_write($client, "OK\n");
                break;
            }
        }
        
        echo "Filial desconectada\n\n";
    }
    
    private function getSummary() {
        $summary = "=== RESUMO GERAL ===\n";
        $summary .= "Total de vendas: {$this->totalSales}\n";
        $summary .= "Total de compras: {$this->totalPurchases}\n";
        $summary .= "\nPor Filial:\n";
        
        foreach ($this->branches as $id => $data) {
            $summary .= "  Filial {$id}:\n";
            $summary .= "    Vendas: {$data['sales']}\n";
            $summary .= "    Compras: {$data['purchases']}\n";
        }
        
        return $summary;
    }
    
    public function __destruct() {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
}

$server = new CentralStoreServer(8892);
$server->start();