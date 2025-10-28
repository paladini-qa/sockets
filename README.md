# Exercícios de Sockets em PHP

Este projeto contém 7 exercícios completos de programação com sockets implementados em PHP, organizados em pastas separadas com servidores e clientes para cada implementação.

## 📁 Estrutura do Projeto

```
sockets/
├── 1-fortune-cookie/      # Exercício 1: Servidor de Fortunes
│   ├── servidor.php
│   ├── cliente.php
│   └── README.md
├── 2-integer-sequence/    # Exercício 2: Servidor de Sequência de Inteiros
│   ├── servidor.php
│   ├── cliente.php
│   └── README.md
├── 3-hangman/            # Exercício 3: Jogo da Forca Remoto
│   ├── servidor.php
│   ├── cliente.php
│   └── README.md
├── 4-bank/               # Exercício 4: Servidor Bancário
│   ├── servidor.php
│   ├── cliente.php
│   └── README.md
├── 5-department-store/   # Exercício 5: Rede de Lojas de Departamento
│   ├── servidor-central.php
│   ├── cliente-filial.php
│   ├── simular-multiplas-filiais.php
│   └── README.md
├── 6-file-server/        # Exercício 6: Servidor de Arquivos
│   ├── servidor.php
│   ├── cliente.php
│   └── README.md
└── 7-multicast-chat/     # Exercício 7: Chat Multicast
    ├── servidor.php
    ├── cliente.php
    └── README.md
```

## 🚀 Como Executar

### Pré-requisitos

- PHP 7.4+ instalado
- Extensão sockets habilitada

Verifique se a extensão sockets está habilitada:

```bash
php -m | grep sockets
```

### Início Rápido - Teste do Exercício 1

**Terminal 1:**

```bash
cd 1-fortune-cookie
php servidor.php
```

**Terminal 2:**

```bash
cd 1-fortune-cookie
php cliente.php
```

No cliente, digite: `GET-FORTUNE`

### Executar Todos os Exercícios

| Exercício   | Porta | Comando Servidor                                    | Comando Cliente                                            |
| ----------- | ----- | --------------------------------------------------- | ---------------------------------------------------------- |
| 1. Fortune  | 8888  | `cd 1-fortune-cookie && php servidor.php`           | `cd 1-fortune-cookie && php cliente.php`                   |
| 2. Inteiros | 8889  | `cd 2-integer-sequence && php servidor.php`         | `cd 2-integer-sequence && php cliente.php`                 |
| 3. Forca    | 8890  | `cd 3-hangman && php servidor.php`                  | `cd 3-hangman && php cliente.php`                          |
| 4. Banco    | 8891  | `cd 4-bank && php servidor.php`                     | `cd 4-bank && php cliente.php`                             |
| 5. Lojas    | 8892  | `cd 5-department-store && php servidor-central.php` | `cd 5-department-store && php cliente-filial.php FILIAL01` |
| 6. Arquivos | 8893  | `cd 6-file-server && php servidor.php`              | `cd 6-file-server && php cliente.php`                      |
| 7. Chat     | 8894  | `cd 7-multicast-chat && php servidor.php`           | `cd 7-multicast-chat && php cliente.php Usuario`           |

### Execução Básica

Cada exercício possui seus próprios arquivos de servidor e cliente:

1. **Abra um terminal para o servidor**
2. **Abra outro terminal para o cliente**
3. **Execute o servidor primeiro**: `php servidor.php` (ou o nome específico do arquivo)
4. **Depois execute o cliente**: `php cliente.php` (ou o nome específico do arquivo)

## 📋 Requisitos

- **PHP 7.4 ou superior**
- **Extensão sockets habilitada** (verifique com `php -m | grep sockets`)
- **Extensão JSON** (geralmente já incluída)

Para verificar se a extensão sockets está habilitada:

```bash
php -m | grep sockets
```

Se não estiver, edite o arquivo `php.ini` e descomente:

```ini
extension=sockets
```

## 📖 Exercícios Implementados

### 1️⃣ Servidor de Fortunes

Servidor que retorna frases aleatórias (fortune cookies). Suporta adicionar, atualizar e listar fortunes.

- **Arquivos**: `1-fortune-cookie/`

### 2️⃣ Servidor de Sequência de Inteiros

Servidor que recebe uma sequência de números e executa operações matemáticas (soma, média, máximo, mínimo, multiplicação).

- **Arquivos**: `2-integer-sequence/`

### 3️⃣ Jogo da Forca Remoto

Jogo da forca multiplayer onde o servidor define a palavra e clientes tentam adivinhar.

- **Arquivos**: `3-hangman/`

### 4️⃣ Servidor Bancário

Sistema bancário com múltiplos clientes simultâneos. Suporta depósitos, saques e consultas de saldo.

- **Arquivos**: `4-bank/`

### 5️⃣ Rede de Lojas de Departamento

Sistema central que recebe dados de múltiplas filiais, simulando vendas e compras em tempo real.

- **Arquivos**: `5-department-store/`

### 6️⃣ Servidor de Arquivos

Sistema com login que permite upload e download de arquivos por usuário.

- **Arquivos**: `6-file-server/`

### 7️⃣ Chat UDP

Chat em tempo real usando UDP para comunicação em grupo.

- **Arquivos**: `7-multicast-chat/`

## 📚 Documentação

Cada exercício possui um README.md próprio com:

- Instruções de uso
- Exemplos de comandos
- Descrição do protocolo
- Casos de teste

Consulte o README específico de cada exercício para mais detalhes.

## 📝 Comandos Mais Usados

### Fortune Cookie (1)

```
GET-FORTUNE
LST-FORTUNE
ADD-FORTUNE Sua frase aqui
```

### Inteiros (2)

```
10 20 30 40 [selecionar operação 1-5]
```

### Forca (3)

```
start
[a letra]
word:ADIVINHE
```

### Banco (4)

```
CREATE 0001
DEPOSIT 0001 500
WITHDRAW 0001 200
BALANCE 0001
```

### Arquivos (6)

```
login: admin / admin123
upload arquivo.txt
list
download arquivo.txt /tmp/copia.txt
```

### Chat (7)

```
Digite: Olá pessoal!
exit
```

## 💡 Dicas

1. **Execute os servidores em terminais separados** para facilitar o acompanhamento dos logs
2. **Teste com múltiplos clientes** nos exercícios que suportam isso (4, 5, 7)
3. **Use o comando `help`** nos clientes interativos para ver os comandos disponíveis
4. **Verifique as portas** antes de executar se houver conflitos
5. **Os servidores exibem logs no console**
6. **Os clientes são interativos** - use `help` para ver comandos
7. **Teste com múltiplos clientes** para verificar concorrência
8. **Alguns exercícios suportam `exit`** para sair graciosamente

## ⚠️ Notas Importantes

- **Sempre execute o servidor antes do cliente**
- **Use terminais separados para servidor e cliente**
- **Para exercícios multi-usuário (4, 7), abra múltiplos clientes**
- **Use Ctrl+C para parar os servidores**

## 🎯 Características

- ✅ Código organizado e comentado
- ✅ Tratamento de erros
- ✅ Protocolos estruturados
- ✅ Suporte a múltiplos clientes onde aplicável
- ✅ Interface interativa nos clientes
- ✅ Documentação completa

## 🐛 Resolução de Problemas

### Porta já em uso

Se uma porta já estiver em uso, feche o servidor anterior ou altere a porta no código.

### Conexão recusada

Verifique se o servidor está rodando antes de executar o cliente.

### Extensão sockets não encontrada

Reinstale o PHP com suporte a sockets ou adicione a extensão manualmente.
