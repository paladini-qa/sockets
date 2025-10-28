<?php
class FileClient {
    private $socket;
    private $host;
    private $port;
    private $connected = false;
    
    public function __construct($host = 'localhost', $port = 8893) {
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
        
        $this->connected = true;
    }
    
    public function login($username, $password) {
        $this->send("LOGIN {$username} {$password}");
        return $this->receive();
    }
    
    public function uploadFile($filepath) {
        if (!file_exists($filepath)) {
            return "ERRO: Arquivo não encontrado: {$filepath}";
        }
        
        $filename = basename($filepath);
        $size = filesize($filepath);
        
        echo "Enviando {$filename}...\n";
        
        $this->send("UPLOAD {$filename} {$size}");
        $response = $this->receive();
        
        if (strpos($response, 'READY') === 0) {
            $content = file_get_contents($filepath);
            $this->sendRaw($content);
            $response = $this->receive();
        }
        
        return $response;
    }
    
    public function downloadFile($filename, $savePath) {
        $this->send("DOWNLOAD {$filename}");
        $response = $this->receive();
        
        if (strpos($response, 'OK:') === 0) {
            $size = intval(substr($response, 3));
            
            $fileContent = '';
            $bytesRead = 0;
            
            echo "Baixando {$filename} ({$size} bytes)...\n";
            
            while ($bytesRead < $size) {
                $data = socket_read($this->socket, min(4096, $size - $bytesRead));
                if ($data === false || strlen($data) === 0) {
                    break;
                }
                $fileContent .= $data;
                $bytesRead += strlen($data);
            }
            
            file_put_contents($savePath, $fileContent);
            
            return "OK: Arquivo salvo em {$savePath}";
        }
        
        return $response;
    }
    
    public function listFiles() {
        $this->send("LIST");
        return $this->receive();
    }
    
    private function send($message) {
        socket_write($this->socket, $message . "\n");
    }
    
    private function sendRaw($data) {
        socket_write($this->socket, $data);
    }
    
    private function receive() {
        return trim(socket_read($this->socket, 2048));
    }
    
    public function disconnect() {
        if ($this->connected) {
            $this->send("EXIT");
            socket_close($this->socket);
            $this->connected = false;
        }
    }
    
    public function interactive() {
        $this->showHelp();
        
        // Login
        echo "\nDigite seu usuário: ";
        $username = trim(fgets(STDIN));
        
        echo "Digite sua senha: ";
        $password = trim(fgets(STDIN));
        
        $response = $this->login($username, $password);
        echo $response . "\n";
        
        if (strpos($response, 'ERRO') === 0) {
            return;
        }
        
        echo "\n";
        
        while (true) {
            echo "\n> ";
            $input = trim(fgets(STDIN));
            
            if ($input === 'exit' || $input === 'quit') {
                break;
            }
            
            if ($input === 'help') {
                $this->showHelp();
                continue;
            }
            
            if ($input === 'list') {
                $response = $this->listFiles();
                echo $response . "\n";
                continue;
            }
            
            if (strpos($input, 'upload ') === 0) {
                $filepath = trim(substr($input, 7));
                $response = $this->uploadFile($filepath);
                echo $response . "\n";
                continue;
            }
            
            if (strpos($input, 'download ') === 0) {
                $parts = explode(' ', $input, 3);
                if (count($parts) === 3) {
                    $filename = $parts[1];
                    $savePath = $parts[2];
                    $response = $this->downloadFile($filename, $savePath);
                    echo $response . "\n";
                } else {
                    echo "ERRO: Use 'download <nome-arquivo> <caminho-destino>'\n";
                }
                continue;
            }
            
            echo "Comando inválido. Use 'help' para ver os comandos.\n";
        }
        
        $this->disconnect();
        echo "\nDesconectado.\n";
    }
    
    private function showHelp() {
        echo "\n=== Cliente de Servidor de Arquivos ===\n";
        echo "Comandos após login:\n";
        echo "  upload <arquivo>              - Fazer upload de arquivo\n";
        echo "  download <nome> <destino>     - Baixar arquivo\n";
        echo "  list                          - Listar seus arquivos\n";
        echo "  help                          - Mostra esta ajuda\n";
        echo "  exit ou quit                  - Sair\n";
        echo "\nUsuários de teste:\n";
        echo "  admin / admin123\n";
        echo "  user1 / pass123\n";
        echo "  user2 / pass456\n";
    }
}

$client = new FileClient();
$client->connect();
$client->interactive();