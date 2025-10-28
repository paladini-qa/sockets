# Exercício 6: Servidor de Arquivos

Servidor que permite login de usuários e armazenamento de arquivos pessoais.

## Funcionalidades

- Sistema de login com autenticação
- Upload de arquivos
- Download de arquivos
- Listagem de arquivos do usuário
- Armazenamento separado por usuário

## Como Executar

### Servidor

```bash
php servidor.php
```

O servidor iniciará na porta **8893** e criará o diretório `uploads/` automaticamente.

### Cliente

```bash
php cliente.php
```

## Usuários Padrão

- `admin` / `admin123`
- `user1` / `pass123`
- `user2` / `pass456`

## Comandos

Após login:

- `upload <arquivo>` - Fazer upload de arquivo
- `download <nome> <destino>` - Baixar arquivo
- `list` - Listar seus arquivos
- `exit` - Sair

## Exemplo de Uso

```
=== Cliente de Servidor de Arquivos ===
Digite seu usuário: admin
Digite sua senha: admin123

OK: Login realizado com sucesso

> upload exemplo.txt
Enviando exemplo.txt...
OK: Arquivo exemplo.txt enviado com sucesso

> list
Arquivos (admin):
  - exemplo.txt (1,234 bytes)

> download exemplo.txt /tmp/baixado.txt
Baixando exemplo.txt (1234 bytes)...
OK: Arquivo salvo em /tmp/baixado.txt
```

## Estrutura de Armazenamento

```
uploads/
├── admin/
│   ├── arquivo1.txt
│   └── arquivo2.pdf
├── user1/
│   └── docs.txt
└── user2/
    └── relatorio.xlsx
```

## Protocolo

- `LOGIN <usuario> <senha>` - Autenticação
- `UPLOAD <nome> <tamanho>` - Upload (binário)
- `DOWNLOAD <nome>` - Download (binário)
- `LIST` - Listar arquivos

## Arquivos

- `servidor.php` - Servidor de arquivos
- `cliente.php` - Cliente interativo
- `uploads/` - Diretório criado automaticamente para armazenamento
