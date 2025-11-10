<?php
class FortuneServer {
    private $fortunes = [];
    private $port;
    private $socket;
    
    public function __construct($port = 8888) {
        $this->port = $port;
        $this->fortunes = [
            "A persistência é o caminho do êxito.",
            "O conhecimento é a única riqueza que aumenta quando compartilhada.",
            "Grandes oportunidades nascem de aproveitar bem as pequenas.",
            "O sucesso é ir de fracasso em fracasso sem perder o entusiasmo.",
            "A vida é o que acontece enquanto você está ocupado fazendo outros planos.",
            "Faça o que você ama e o dinheiro virá depois.",
            "A única forma de fazer um excelente trabalho é amar o que você faz."
        ];
    }
    
    public function start() {
        // Criar socket
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->socket) {
            die("Erro ao criar socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        // Configurar socket para reutilizar endereço
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        // Vincular socket ao endereço e porta
        if (!socket_bind($this->socket, '0.0.0.0', $this->port)) {
            die("Erro ao vincular socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        // Escutar conexões
        if (!socket_listen($this->socket, 5)) {
            die("Erro ao escutar socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        echo "Servidor de Fortunes iniciado na porta {$this->port}\n";
        echo "Aguardando conexões...\n\n";
        
        while (true) {
            // Aceitar conexão
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
        
        while (true) {
            $data = socket_read($client, 1024);
            
            if ($data === false || trim($data) === '') {
                break;
            }
            
            $command = trim($data);
            $response = $this->processCommand($command);
            
            socket_write($client, $response . "\n");
            echo "Comando: {$command}\n";
            echo "Resposta enviada\n\n";
        }
        
        echo "Cliente desconectado\n\n";
    }
    
    private function processCommand($command) {
        if (strpos($command, 'GET-FORTUNE') === 0) {
            return $this->getFortune();
        } elseif (strpos($command, 'ADD-FORTUNE') === 0) {
            $fortune = trim(substr($command, strlen('ADD-FORTUNE')));
            return $this->addFortune($fortune);
        } elseif (strpos($command, 'UPD-FORTUNE') === 0) {
            $parts = explode(' ', trim(substr($command, strlen('UPD-FORTUNE'))), 2);
            if (count($parts) == 2) {
                return $this->updateFortune((int)$parts[0], $parts[1]);
            }
            return "ERRO: Formato inválido. Use: UPD-FORTUNE <pos> <nova frase>";
        } elseif (strpos($command, 'LST-FORTUNE') === 0) {
            return $this->listFortunes();
        } else {
            return "ERRO: Comando desconhecido. Comandos válidos: GET-FORTUNE, ADD-FORTUNE, UPD-FORTUNE, LST-FORTUNE";
        }
    }
    
    private function getFortune() {
        if (empty($this->fortunes)) {
            return "Nenhuma fortune disponível. Use ADD-FORTUNE para adicionar.";
        }
        return $this->fortunes[array_rand($this->fortunes)];
    }
    
    private function addFortune($fortune) {
        if (empty($fortune)) {
            return "ERRO: Fortune vazia.";
        }
        $this->fortunes[] = $fortune;
        return "OK: Fortune adicionada com sucesso! Total: " . count($this->fortunes);
    }
    
    private function updateFortune($pos, $fortune) {
        if ($pos < 0 || $pos >= count($this->fortunes)) {
            return "ERRO: Posição inválida. Faça LST-FORTUNE para ver as posições.";
        }
        $this->fortunes[$pos] = $fortune;
        return "OK: Fortune atualizada na posição {$pos}";
    }
    
    private function listFortunes() {
        if (empty($this->fortunes)) {
            return "Nenhuma fortune armazenada.";
        }
        $result = "Fortunes armazenadas (" . count($this->fortunes) . "):\n";
        foreach ($this->fortunes as $index => $fortune) {
            $result .= "[{$index}] {$fortune}\n";
        }
        return trim($result);
    }
    
    public function __destruct() {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
}

// Iniciar servidor
$server = new FortuneServer(8888);
$server->start();