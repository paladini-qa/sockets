<?php
class HangmanServer {
    private $port;
    private $socket;
    private $words = [
        'PROGRAMACAO', 'COMPUTADOR', 'ALGORITMO', 'SOFTWARE',
        'TECNOLOGIA', 'BANCO_DADOS', 'INTERNET', 'DEVELOPER',
        'LINGUAGEM', 'FRAMEWORK', 'APLICATIVO', 'INTERFACE'
    ];
    private $sessions = [];
    
    public function __construct($port = 8890) {
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
        
        echo "Servidor de Jogo da Forca iniciado na porta {$this->port}\n";
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
            $data = socket_read($client, 1024);
            
            if ($data === false || strlen($data) === 0) {
                break;
            }
            
            $command = trim($data);
            echo "Comando recebido: {$command}\n";
            
            $response = $this->processCommand($sessionId, $command);
            socket_write($client, $response . "\n");
            
            echo "Resposta enviada\n\n";
        }
        
        unset($this->sessions[$sessionId]);
        echo "Cliente desconectado - Sessão: {$sessionId}\n\n";
    }
    
    private function processCommand($sessionId, $command) {
        if ($command === 'START') {
            return $this->startGame($sessionId);
        } elseif (strpos($command, 'GUESS:') === 0) {
            $letter = substr($command, 6);
            return $this->makeGuess($sessionId, $letter);
        } elseif (strpos($command, 'WORD:') === 0) {
            $word = substr($command, 5);
            return $this->guessWord($sessionId, $word);
        } elseif ($command === 'STATUS') {
            return $this->getStatus($sessionId);
        } else {
            return json_encode(['error' => 'Comando inválido']);
        }
    }
    
    private function startGame($sessionId) {
        $word = strtoupper($this->words[array_rand($this->words)]);
        
        $this->sessions[$sessionId] = [
            'word' => $word,
            'hidden' => str_repeat('_', strlen($word)),
            'guessed' => [],
            'attempts' => 0,
            'maxAttempts' => 6,
            'status' => 'playing',
            'hangmanParts' => []
        ];
        
        echo "Nova sessão iniciada - Palavra: {$word}\n";
        
        return json_encode([
            'wordLength' => strlen($word),
            'message' => 'Jogo iniciado! Use GUESS:<letra> para chutar uma letra ou WORD:<palavra> para chutar a palavra completa.',
            'hangman' => $this->drawHangman(0)
        ]);
    }
    
    private function makeGuess($sessionId, $letter) {
        if (!isset($this->sessions[$sessionId])) {
            return json_encode(['error' => 'Jogo não iniciado. Use START primeiro.']);
        }
        
        $session = &$this->sessions[$sessionId];
        
        if ($session['status'] !== 'playing') {
            return json_encode([
                'error' => $session['status'] === 'won' ? 'Você já ganhou!' : 'Você já perdeu!',
                'word' => $session['word']
            ]);
        }
        
        $letter = strtoupper(trim($letter));
        
        if (strlen($letter) !== 1 || !ctype_alpha($letter)) {
            return json_encode(['error' => 'Digite apenas uma letra.']);
        }
        
        if (in_array($letter, $session['guessed'])) {
            return json_encode(['error' => 'Letra já tentada']);
        }
        
        $session['guessed'][] = $letter;
        
        $found = false;
        $newHidden = '';
        
        for ($i = 0; $i < strlen($session['word']); $i++) {
            if ($session['word'][$i] === $letter) {
                $newHidden .= $letter;
                $found = true;
            } else {
                $newHidden .= $session['hidden'][$i];
            }
        }
        
        if (!$found) {
            $session['attempts']++;
            $session['hangmanParts'][] = "Perdeu uma tentativa";
        }
        
        $session['hidden'] = $newHidden;
        
        if ($session['hidden'] === $session['word']) {
            $session['status'] = 'won';
            return json_encode([
                'win' => true,
                'message' => 'Parabéns! Você ganhou!',
                'word' => $session['word'],
                'hidden' => $session['hidden'],
                'hangman' => $this->drawHangman($session['attempts'])
            ]);
        }
        
        if ($session['attempts'] >= $session['maxAttempts']) {
            $session['status'] = 'lost';
            return json_encode([
                'lose' => true,
                'message' => 'Você perdeu! A palavra era: ' . $session['word'],
                'word' => $session['word'],
                'hangman' => $this->drawHangman($session['attempts'])
            ]);
        }
        
        return json_encode([
            'hidden' => $session['hidden'],
            'guessed' => $session['guessed'],
            'attempts' => $session['attempts'],
            'maxAttempts' => $session['maxAttempts'],
            'hangman' => $this->drawHangman($session['attempts'])
        ]);
    }
    
    private function guessWord($sessionId, $word) {
        if (!isset($this->sessions[$sessionId])) {
            return json_encode(['error' => 'Jogo não iniciado. Use START primeiro.']);
        }
        
        $session = &$this->sessions[$sessionId];
        
        if (strtoupper($word) === $session['word']) {
            $session['status'] = 'won';
            return json_encode([
                'win' => true,
                'message' => 'Parabéns! Você acertou a palavra!',
                'word' => $session['word']
            ]);
        }
        
        $session['attempts'] += 2; // Penalidade maior por erro
        
        if ($session['attempts'] >= $session['maxAttempts']) {
            $session['status'] = 'lost';
            return json_encode([
                'lose' => true,
                'message' => 'Palavra incorreta! Você perdeu! A palavra era: ' . $session['word'],
                'word' => $session['word'],
                'hangman' => $this->drawHangman($session['attempts'])
            ]);
        }
        
        return json_encode([
            'error' => 'Palavra incorreta!',
            'attempts' => $session['attempts'],
            'maxAttempts' => $session['maxAttempts'],
            'hangman' => $this->drawHangman($session['attempts'])
        ]);
    }
    
    private function getStatus($sessionId) {
        if (!isset($this->sessions[$sessionId])) {
            return json_encode(['error' => 'Jogo não iniciado. Use START primeiro.']);
        }
        
        $session = $this->sessions[$sessionId];
        return json_encode($session);
    }
    
    private function drawHangman($attempts) {
        $parts = [
            "  +---+\n  |   |",
            "  |   O",
            "  |   |",
            "  |  /|",
            "  |  /|\\",
            "  |  /",
            "  |  / \\"
        ];
        
        $result = "\n  +---+\n";
        $result .= "  |   |\n";
        
        if ($attempts >= 1) $result .= "  |   O\n";
        else $result .= "  |\n";
        
        if ($attempts >= 2) {
            if ($attempts >= 4) $result .= "  |  /|\\\n";
            else $result .= "  |   |\n";
        } else $result .= "  |\n";
        
        if ($attempts >= 5) {
            if ($attempts >= 6) $result .= "  |  / \\\n";
            else $result .= "  |  /\n";
        } else $result .= "  |\n";
        
        $result .= "  |\n";
        $result .= "__|__\n";
        
        return $result;
    }
    
    public function __destruct() {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
}

$server = new HangmanServer(8890);
$server->start();