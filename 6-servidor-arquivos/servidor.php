<?php
class FileServer {
    private $port;
    private $socket;
    private $users = [
        'admin' => 'admin123',
        'user1' => 'pass123',
        'user2' => 'pass456'
    ];
    private $sessions = [];
    private $baseDir;
    
    public function __construct($port = 8893) {
        $this->port = $port;
        $this->baseDir = __DIR__ . '/uploads/';
        
        // Criar diretório de uploads
        if (!file_exists($this->baseDir)) {
            mkdir($this->baseDir, 0755, true);
        }
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
        
        echo "Servidor de Arquivos iniciado na porta {$this->port}\n";
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
        $sessionId = uniqid('session_', true);
        echo "Cliente conectado - Sessão: {$sessionId}\n";
        
        while (true) {
            $data = socket_read($client, 4096);
            
            if ($data === false || strlen($data) === 0) {
                break;
            }
            
            $command = trim($data);
            echo "Comando recebido: " . substr($command, 0, 50) . "...\n";
            
            $response = $this->processCommand($sessionId, $command);
            socket_write($client, $response . "\n");
            
            echo "Resposta enviada\n\n";
        }
        
        unset($this->sessions[$sessionId]);
        echo "Cliente desconectado - Sessão: {$sessionId}\n\n";
    }
    
    private function processCommand($sessionId, $command) {
        $parts = explode(' ', $command, 3);
        $operation = strtoupper($parts[0] ?? '');
        
        switch ($operation) {
            case 'LOGIN':
                $username = $parts[1] ?? '';
                $password = $parts[2] ?? '';
                return $this->login($sessionId, $username, $password);
                
            case 'UPLOAD':
                if (!$this->isAuthenticated($sessionId)) {
                    return "ERRO: Faça login primeiro";
                }
                $filename = $parts[1] ?? '';
                $size = intval($parts[2] ?? 0);
                return $this->uploadFile($sessionId, $filename, $size);
                
            case 'DOWNLOAD':
                if (!$this->isAuthenticated($sessionId)) {
                    return "ERRO: Faça login primeiro";
                }
                $filename = $parts[1] ?? '';
                return $this->downloadFile($sessionId, $filename);
                
            case 'LIST':
                if (!$this->isAuthenticated($sessionId)) {
                    return "ERRO: Faça login primeiro";
                }
                return $this->listFiles($sessionId);
                
            case 'EXIT':
                return "Desconectado do servidor";
                
            default:
                return "ERRO: Comando desconhecido. Use: LOGIN, UPLOAD, DOWNLOAD, LIST, EXIT";
        }
    }
    
    private function login($sessionId, $username, $password) {
        if (!isset($this->users[$username]) || $this->users[$username] !== $password) {
            return "ERRO: Usuário ou senha inválidos";
        }
        
        $this->sessions[$sessionId] = [
            'username' => $username,
            'authenticated' => true,
            'files' => []
        ];
        
        return "OK: Login realizado com sucesso";
    }
    
    private function isAuthenticated($sessionId) {
        return isset($this->sessions[$sessionId]) && $this->sessions[$sessionId]['authenticated'];
    }
    
    private function uploadFile($sessionId, $filename, $size) {
        if (!$this->isAuthenticated($sessionId)) {
            return "ERRO: Não autenticado";
        }
        
        if (empty($filename) || $size <= 0) {
            return "ERRO: Nome de arquivo ou tamanho inválido";
        }
        
        $username = $this->sessions[$sessionId]['username'];
        $userDir = $this->baseDir . $username . '/';
        
        if (!file_exists($userDir)) {
            mkdir($userDir, 0755, true);
        }
        
        $filepath = $userDir . basename($filename);
        
        socket_write($this->socket, "READY\n");
        
        $content = '';
        $bytesRead = 0;
        
        while ($bytesRead < $size) {
            $data = socket_read($this->socket, min(4096, $size - $bytesRead));
            if ($data === false) {
                break;
            }
            $content .= $data;
            $bytesRead += strlen($data);
        }
        
        file_put_contents($filepath, $content);
        
        if (!isset($this->sessions[$sessionId]['files'])) {
            $this->sessions[$sessionId]['files'] = [];
        }
        $this->sessions[$sessionId]['files'][] = $filename;
        
        echo "Arquivo {$filename} salvo ({$size} bytes)\n";
        
        return "OK: Arquivo {$filename} enviado com sucesso";
    }
    
    private function downloadFile($sessionId, $filename) {
        if (!$this->isAuthenticated($sessionId)) {
            return "ERRO: Não autenticado";
        }
        
        $username = $this->sessions[$sessionId]['username'];
        $filepath = $this->baseDir . $username . '/' . basename($filename);
        
        if (!file_exists($filepath)) {
            return "ERRO: Arquivo não encontrado";
        }
        
        $filesize = filesize($filepath);
        $response = "OK:{$filesize}";
        socket_write($this->socket, $response . "\n");
        
        $fileContent = file_get_contents($filepath);
        
        // Enviar arquivo em chunks
        $offset = 0;
        $chunkSize = 4096;
        
        while ($offset < $filesize) {
            $chunk = substr($fileContent, $offset, $chunkSize);
            socket_write($this->socket, $chunk);
            $offset += $chunkSize;
        }
        
        echo "Arquivo {$filename} enviado ({$filesize} bytes)\n";
        
        return "OK"; // Não será enviado, apenas para compatibilidade
    }
    
    private function listFiles($sessionId) {
        if (!$this->isAuthenticated($sessionId)) {
            return "ERRO: Não autenticado";
        }
        
        $username = $this->sessions[$sessionId]['username'];
        $userDir = $this->baseDir . $username . '/';
        
        if (!file_exists($userDir)) {
            return "Nenhum arquivo armazenado";
        }
        
        $files = scandir($userDir);
        $files = array_filter($files, function($f) {
            return $f !== '.' && $f !== '..';
        });
        
        if (empty($files)) {
            return "Nenhum arquivo armazenado";
        }
        
        $list = "Arquivos ({$username}):\n";
        foreach ($files as $file) {
            $size = filesize($userDir . $file);
            $list .= "  - {$file} (" . number_format($size) . " bytes)\n";
        }
        
        return trim($list);
    }
    
    public function __destruct() {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
}

$server = new FileServer(8893);
$server->start();