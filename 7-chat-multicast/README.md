# Exercício 7: Chat UDP

Sistema de chat usando UDP para comunicação em grupo.

## Funcionalidades

- Chat multicast usando UDP
- Múltiplos usuários simultâneos
- Entrada e saída de usuários
- Mensagens em tempo real

## Como Executar

### Servidor

```bash
php servidor.php
```

O servidor iniciará no multicast `224.0.0.1:8894`.

### Cliente (múltiplos)

```bash
php cliente.php Alice
php cliente.php Bob
php cliente.php Charlie
```

## Protocolo

O sistema usa UDP multicast para enviar mensagens a todos os participantes conectados ao grupo.

## Exemplo de Uso

```
=== Chat Multicast ===
Usuário: Alice
Digite 'exit' para sair

>>> Bob entrou no chat
[Bob]: Olá pessoal!
[Alice]: Olá Bob!
>>> Charlie entrou no chat
[Charlie]: E aí pessoal!
>>> Bob saiu do chat
```

## Comandos

- Digite mensagem normalmente - Envia mensagem
- `exit` - Sair do chat

## Características

- **UDP**: Mensagens são enviadas via UDP (protocolo sem conexão)
- **Tempo Real**: Mensagens são entregues imediatamente
- **Notificações**: Usuários são notificados quando alguém entra/sai
- **Múltiplos Participantes**: Suporta vários usuários simultâneos
- **Cross-Platform**: Funciona em Windows e Linux

## Diagrama de Atividades

```mermaid
flowchart TD
    Start([Início]) --> Servidor[Servidor inicia no multicast 224.0.0.1:8894]
    Servidor --> CriaSocket[Cria socket UDP]
    CriaSocket --> AguardaMensagens[Aguarda mensagens UDP]
    
    ClienteStart([Cliente inicia]) --> RecebeNome[Recebe nome do usuário]
    RecebeNome --> ConectaMulticast[Conecta ao grupo multicast]
    ConectaMulticast --> EnviaEntrada[Envia mensagem de entrada]
    EnviaEntrada --> IniciaThread[Inicia thread de recebimento]
    IniciaThread --> LoopChat[Loop principal do chat]
    
    LoopChat --> AguardaInput[Aguarda input do usuário]
    AguardaInput --> VerificaComando{Comando?}
    
    VerificaComando -->|exit| EnviaSaida[Envia mensagem de saída]
    EnviaSaida --> FimCliente([Cliente encerra])
    
    VerificaComando -->|mensagem| FormataMensagem[Formata mensagem com nome]
    FormataMensagem --> EnviaUDP[Envia via UDP multicast]
    EnviaUDP --> LoopChat
    
    AguardaMensagens --> RecebeUDP[Recebe mensagem UDP]
    RecebeUDP --> VerificaTipo{Tipo de mensagem?}
    
    VerificaTipo -->|ENTRADA| RegistraUsuario[Registra novo usuário]
    RegistraUsuario --> BroadcastEntrada[Broadcast entrada para todos]
    
    VerificaTipo -->|SAIDA| RemoveUsuario[Remove usuário]
    RemoveUsuario --> BroadcastSaida[Broadcast saída para todos]
    
    VerificaTipo -->|MENSAGEM| BroadcastMensagem[Broadcast mensagem para todos]
    
    BroadcastEntrada --> AguardaMensagens
    BroadcastSaida --> AguardaMensagens
    BroadcastMensagem --> AguardaMensagens
    
    IniciaThread --> RecebeMensagemThread[Recebe mensagens do grupo]
    RecebeMensagemThread --> VerificaOrigem{É própria mensagem?}
    
    VerificaOrigem -->|Não| ExibeMensagem[Exibe mensagem na tela]
    VerificaOrigem -->|Sim| IgnoraMensagem[Ignora mensagem]
    
    ExibeMensagem --> RecebeMensagemThread
    IgnoraMensagem --> RecebeMensagemThread
    
    FimCliente --> End([Fim])
```

## Arquivos

- `servidor.php` - Servidor UDP que gerencia mensagens
- `cliente.php` - Cliente de chat

## Observação

Este exercício usa UDP broadcast simples ao invés de multicast puro para garantir compatibilidade cross-platform. O servidor central retransmite mensagens para todos os clientes conectados.
