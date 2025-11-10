# Exercício 2: Servidor de Sequência de Inteiros

Servidor que aceita uma sequência de números inteiros e retorna o resultado de uma operação matemática.

## Funcionalidades

- Aceita múltiplos números inteiros
- Suporta operações: SUM, AVG, MAX, MIN, MULT
- Detecta fim de sequência através de EOF

## Como Executar

### Servidor

```bash
php servidor.php
```

O servidor iniciará na porta **8889**.

### Cliente

```bash
php cliente.php
```

## Operações Suportadas

- **SUM**: Soma todos os números
- **AVG**: Média aritmética dos números
- **MAX**: Maior valor
- **MIN**: Menor valor
- **MULT**: Multiplicação de todos os números

## Exemplo de Uso

```
Digite os números: 10 20 30 40
Escolha a operação:
  1 - SUM (soma)
  2 - AVG (média)
  3 - MAX (máximo)
  4 - MIN (mínimo)
  5 - MULT (multiplicação)
> 2

Resultado: 25
```

## Protocolo

O cliente envia números linha por linha, terminando com o nome da operação:

```
10
20
30
40
AVG
```

O servidor responde com o resultado da operação.

## Diagrama de Atividades

```mermaid
flowchart TD
    Start([Início]) --> Servidor[Servidor inicia na porta 8889]
    Servidor --> AguardaConexao[Aguarda conexão do cliente]
    
    ClienteStart([Cliente inicia]) --> Conecta[Conecta ao servidor]
    Conecta --> SolicitaNumeros[Solicita números ao usuário]
    SolicitaNumeros --> RecebeNumeros[Recebe sequência de números]
    RecebeNumeros --> MenuOperacao[Exibe menu de operações]
    MenuOperacao --> EscolheOp[Usuário escolhe operação]
    EscolheOp --> EnviaNumeros[Envia números linha por linha]
    EnviaNumeros --> EnviaOperacao[Envia nome da operação]
    
    AguardaConexao --> RecebeNumero[Recebe número ou operação]
    RecebeNumero --> VerificaTipo{Tipo de dado?}
    
    VerificaTipo -->|Número| ArmazenaNumero[Armazena número na lista]
    ArmazenaNumero --> RecebeNumero
    
    VerificaTipo -->|Operação| ProcessaOp{Operação?}
    
    ProcessaOp -->|SUM| CalculaSuma[Soma todos os números]
    ProcessaOp -->|AVG| CalculaMedia[Calcula média]
    ProcessaOp -->|MAX| EncontraMax[Encontra valor máximo]
    ProcessaOp -->|MIN| EncontraMin[Encontra valor mínimo]
    ProcessaOp -->|MULT| CalculaMult[Multiplica todos os números]
    
    CalculaSuma --> RetornaResultado[Retorna resultado ao cliente]
    CalculaMedia --> RetornaResultado
    EncontraMax --> RetornaResultado
    EncontraMin --> RetornaResultado
    CalculaMult --> RetornaResultado
    
    RetornaResultado --> AguardaConexao
    
    EnviaOperacao --> RecebeResultado[Recebe resultado]
    RecebeResultado --> ExibeResultado[Exibe resultado]
    ExibeResultado --> FimCliente([Cliente encerra])
    FimCliente --> End([Fim])
```

## Arquivos

- `servidor.php` - Servidor que processa operações matemáticas
- `cliente.php` - Cliente interativo
