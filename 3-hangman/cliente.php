<?php
class HangmanClient {
    private $socket;
    private $host;
    private $port;
    
    public function __construct($host = 'localhost', $port = 8890) {
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
    
    public function sendCommand($command) {
        socket_write($this->socket, $command . "\n");
    }
    
    public function receiveResponse() {
        $response = socket_read($this->socket, 2048);
        return json_decode(trim($response), true);
    }
    
    public function disconnect() {
        socket_close($this->socket);
    }
    
    public function interactive() {
        $this->connect();
        echo "Conectado ao servidor de Jogo da Forca!\n";
        
        $this->showHelp();
        
        $started = false;
        
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
            
            if ($input === 'start') {
                $this->sendCommand('START');
                $response = $this->receiveResponse();
                $this->displayResponse($response);
                $started = true;
                continue;
            }
            
            if (!$started) {
                echo "Inicie o jogo com 'start'\n";
                continue;
            }
            
            if (strpos($input, 'guess:') === 0) {
                $this->sendCommand($input);
            } elseif (strpos($input, 'word:') === 0) {
                $this->sendCommand($input);
            } elseif ($input === 'status') {
                $this->sendCommand($input);
            } else {
                // Assumir que é uma letra
                if (strlen($input) === 1) {
                    $this->sendCommand('GUESS:' . $input);
                } else {
                    echo "Comando inválido. Use help para ver os comandos.\n";
                    continue;
                }
            }
            
            $response = $this->receiveResponse();
            
            if ($response === null) {
                echo "Resposta inválida do servidor\n";
                continue;
            }
            
            $this->displayResponse($response);
            
            if (isset($response['win']) || isset($response['lose'])) {
                echo "\nFim de jogo! Digite 'start' para jogar novamente ou 'exit' para sair.\n";
                $started = false;
            }
        }
        
        $this->disconnect();
        echo "\nDesconectado.\n";
    }
    
    private function displayResponse($response) {
        if (isset($response['error'])) {
            echo "\nERRO: {$response['error']}\n";
            return;
        }
        
        if (isset($response['win'])) {
            echo "\n" . str_repeat('=', 50) . "\n";
            echo "VITÓRIA! " . $response['message'] . "\n";
            echo str_repeat('=', 50) . "\n";
            if (isset($response['word'])) {
                echo "Palavra: " . $response['word'] . "\n";
            }
            if (isset($response['hangman'])) {
                echo $response['hangman'] . "\n";
            }
            return;
        }
        
        if (isset($response['lose'])) {
            echo "\n" . str_repeat('=', 50) . "\n";
            echo "DERROTA! " . $response['message'] . "\n";
            echo str_repeat('=', 50) . "\n";
            if (isset($response['word'])) {
                echo "Palavra: " . $response['word'] . "\n";
            }
            if (isset($response['hangman'])) {
                echo $response['hangman'] . "\n";
            }
            return;
        }
        
        if (isset($response['message'])) {
            echo "\n" . $response['message'] . "\n";
        }
        
        if (isset($response['wordLength'])) {
            echo "Tamanho da palavra: {$response['wordLength']} letras\n";
        }
        
        if (isset($response['hidden'])) {
            echo "Palavra: " . implode(' ', str_split($response['hidden'])) . "\n";
        }
        
        if (isset($response['guessed'])) {
            echo "Letras tentadas: " . implode(', ', $response['guessed']) . "\n";
        }
        
        if (isset($response['attempts'])) {
            echo "Tentativas: {$response['attempts']}/{$response['maxAttempts']}\n";
        }
        
        if (isset($response['hangman'])) {
            echo $response['hangman'] . "\n";
        }
    }
    
    private function showHelp() {
        echo "\n=== Jogo da Forca ===\n";
        echo "Comandos:\n";
        echo "  start              - Iniciar novo jogo\n";
        echo "  <letra>            - Chutar uma letra (ou use guess:<letra>)\n";
        echo "  word:<palavra>     - Chutar palavra completa\n";
        echo "  status             - Ver status do jogo\n";
        echo "  help               - Mostra esta ajuda\n";
        echo "  exit ou quit       - Sair\n";
    }
}

$client = new HangmanClient();
$client->interactive();

