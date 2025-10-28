<?php
class BankServer {
    private $port;
    private $socket;
    private $accounts = [];
    private $accountLocks = []; // Simula locks para operações concorrentes
    
    public function __construct($port = 8891) {
        $this->port = $port;
        // Conta de exemplo para teste
        $this->accounts['0001'] = ['balance' => 1000.00, 'number' => '0001'];
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
        
        echo "Servidor Bancário iniciado na porta {$this->port}\n";
        echo "Aguardando conexões...\n\n";
        
        while (true) {
            $client = socket_accept($this->socket);
            if ($client === false) {
                continue;
            }
            
            // Criar thread/cliente separado para cada conexão
            $this->handleClient($client);
            socket_close($client);
        }
    }
    
    private function handleClient($client) {
        $clientId = uniqid('client_', true);
        echo "Cliente conectado - ID: {$clientId}\n";
        
        while (true) {
            $data = socket_read($client, 1024);
            
            if ($data === false || strlen($data) === 0) {
                break;
            }
            
            $command = trim($data);
            echo "Comando recebido: {$command}\n";
            
            $response = $this->processCommand($command);
            socket_write($client, $response . "\n");
            
            echo "Resposta enviada\n\n";
        }
        
        echo "Cliente desconectado - ID: {$clientId}\n\n";
    }
    
    private function processCommand($command) {
        $parts = explode(' ', $command);
        $operation = strtoupper($parts[0] ?? '');
        
        switch ($operation) {
            case 'DEPOSIT':
                $account = $parts[1] ?? '';
                $amount = floatval($parts[2] ?? 0);
                return $this->deposit($account, $amount);
                
            case 'WITHDRAW':
                $account = $parts[1] ?? '';
                $amount = floatval($parts[2] ?? 0);
                return $this->withdraw($account, $amount);
                
            case 'BALANCE':
                $account = $parts[1] ?? '';
                return $this->balance($account);
                
            case 'CREATE':
                $account = $parts[1] ?? '';
                return $this->createAccount($account);
                
            case 'EXIT':
                return "Desconectado do servidor.";
                
            default:
                return "ERRO: Comando desconhecido. Use: DEPOSIT, WITHDRAW, BALANCE, CREATE, EXIT";
        }
    }
    
    private function deposit($accountNumber, $amount) {
        if (empty($accountNumber)) {
            return "ERRO: Número da conta não informado.";
        }
        
        if ($amount <= 0) {
            return "ERRO: Valor deve ser maior que zero.";
        }
        
        if (!isset($this->accounts[$accountNumber])) {
            return "ERRO: Conta não encontrada.";
        }
        
        // Simular lock para operação concorrente
        $this->lockAccount($accountNumber);
        
        $this->accounts[$accountNumber]['balance'] += $amount;
        $balance = $this->accounts[$accountNumber]['balance'];
        
        $this->unlockAccount($accountNumber);
        
        return "OK: Depósito de R$ " . number_format($amount, 2, ',', '.') . 
               " realizado. Saldo atual: R$ " . number_format($balance, 2, ',', '.');
    }
    
    private function withdraw($accountNumber, $amount) {
        if (empty($accountNumber)) {
            return "ERRO: Número da conta não informado.";
        }
        
        if ($amount <= 0) {
            return "ERRO: Valor deve ser maior que zero.";
        }
        
        if (!isset($this->accounts[$accountNumber])) {
            return "ERRO: Conta não encontrada.";
        }
        
        $this->lockAccount($accountNumber);
        
        $balance = $this->accounts[$accountNumber]['balance'];
        
        if ($balance < $amount) {
            $this->unlockAccount($accountNumber);
            return "ERRO: Saldo insuficiente. Saldo atual: R$ " . 
                   number_format($balance, 2, ',', '.');
        }
        
        $this->accounts[$accountNumber]['balance'] -= $amount;
        $newBalance = $this->accounts[$accountNumber]['balance'];
        
        $this->unlockAccount($accountNumber);
        
        return "OK: Saque de R$ " . number_format($amount, 2, ',', '.') . 
               " realizado. Saldo atual: R$ " . number_format($newBalance, 2, ',', '.');
    }
    
    private function balance($accountNumber) {
        if (empty($accountNumber)) {
            return "ERRO: Número da conta não informado.";
        }
        
        if (!isset($this->accounts[$accountNumber])) {
            return "ERRO: Conta não encontrada.";
        }
        
        $balance = $this->accounts[$accountNumber]['balance'];
        
        return "Saldo da conta {$accountNumber}: R$ " . number_format($balance, 2, ',', '.');
    }
    
    private function createAccount($accountNumber) {
        if (empty($accountNumber)) {
            return "ERRO: Número da conta não informado.";
        }
        
        if (isset($this->accounts[$accountNumber])) {
            return "ERRO: Conta já existe.";
        }
        
        $this->accounts[$accountNumber] = [
            'balance' => 0.00,
            'number' => $accountNumber
        ];
        
        return "OK: Conta {$accountNumber} criada com sucesso. Saldo inicial: R$ 0,00";
    }
    
    private function lockAccount($accountNumber) {
        // Simulação de lock
        while (isset($this->accountLocks[$accountNumber])) {
            usleep(10000); // 10ms
        }
        $this->accountLocks[$accountNumber] = true;
    }
    
    private function unlockAccount($accountNumber) {
        unset($this->accountLocks[$accountNumber]);
    }
    
    public function __destruct() {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
}

$server = new BankServer(8891);
$server->start();