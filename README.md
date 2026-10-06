# 📚 BookMarket Research

> **Sistema open source de pesquisa e validação de mercado para vendedores de livros em Moçambique.**  
> Funciona em **cPanel sem base de dados** — usa ficheiros JSON com controlo de concorrência e sincronização offline/online.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)](https://php.net)
[![cPanel Ready](https://img.shields.io/badge/cPanel-Ready-orange.svg)](#-instalação-no-cpanel)

---

## ✨ Como Funciona

```
Qualquer pessoa abre o link público  →  Preenche o formulário  →  Dados chegam ao servidor
                                                                            ↓
                                                        Tu acompanhas os resultados em /admin.php
```

- **Entrevistadores no campo:** partilhas o link `seudominio.com/` com qualquer pessoa
- **Tu (admin):** acedes a `seudominio.com/admin.php` e vês tudo em tempo real
- **Funciona offline:** o formulário guarda rascunhos localmente e sincroniza quando há ligação

---

## 🏗️ Estrutura do Projeto

```text
bookmarket-research/
│
├── index.php                    # 🌐 Formulário público (Mobile-First)
├── admin.php                    # 🛡️ Painel de administração e dashboard
├── api.php                      # ⚡ API REST PHP (sem MySQL, usa JSON)
├── LICENSE                      # MIT
├── README.md                    # Este ficheiro
│
├── assets/
│   ├── style.css                # Design responsivo e moderno
│   ├── app.js                   # Lógica do questionário + sync offline
│   └── dashboard.js             # Dashboard em tempo real + Chart.js
│
└── data/                        # Dados protegidos (não acessíveis via web)
    ├── .htaccess                # Bloqueio de acesso direto a JSON/logs
    ├── interviews.json          # ⚠️ Criado automaticamente (no .gitignore)
    ├── settings.json            # ⚠️ Criado automaticamente (no .gitignore)
    ├── interviews.json.example  # Modelo de referência
    └── settings.json.example   # Modelo de referência
```

---

## 🚀 Instalação no cPanel

### 1. Upload dos ficheiros

1. Abre o **cPanel → File Manager**
2. Navega para `public_html/` (ou cria `public_html/bookmarket/`)
3. Faz **Upload** de todos os ficheiros mantendo a estrutura de pastas
4. **OU** faz upload do ZIP e extrai aqui

### 2. Permissões da pasta `data/`

> ⚠️ **Obrigatório** — sem isto o sistema não consegue guardar dados.

Seleciona a pasta `data/` → **Permissions** → define `755`  
*(Se não funcionar, tenta `777` — depende do host)*

### 3. Primeiro acesso

| Página | URL |
|---|---|
| 📝 Formulário público | `https://seudominio.com/bookmarket/` |
| 🛡️ Painel admin | `https://seudominio.com/bookmarket/admin.php` |

**Credenciais padrão:**
- **Utilizador:** `admin`
- **Senha:** `admin123`

> 🔴 **Muda a senha** no primeiro login em **Configurações** no painel admin!

---

## 💻 Desenvolvimento Local

```bash
# Clonar o repositório
git clone https://github.com/zedeckmuacy/bookmarket-research.git
cd bookmarket-research

# Iniciar servidor PHP embutido
php -S localhost:7000

# Abrir no browser
# http://localhost:7000        → Formulário
# http://localhost:7000/admin.php → Admin (admin / admin123)
```

> Os ficheiros `data/interviews.json` e `data/settings.json` são criados automaticamente pela API no primeiro acesso.

---

## 🔒 Segurança

- `data/.htaccess` bloqueia acesso direto a todos os `.json` e `.log` via browser
- Autenticação com `password_hash()` PHP (bcrypt)
- CSRF token em todas as sessões admin
- Rate limiting de 60 req/min por IP
- Sanitização de inputs com `htmlspecialchars()`

**Teste:** `https://seudominio.com/bookmarket/data/interviews.json` → deve retornar **403 Forbidden** ✅

---

## 🛠️ Tecnologias

| | |
|---|---|
| Backend | PHP 8.0+ (sem extensões especiais) |
| Frontend | HTML5 + CSS3 + JavaScript ES6+ (vanilla) |
| Gráficos | [Chart.js v4](https://www.chartjs.org/) via CDN |
| Ícones | [Lucide Icons](https://lucide.dev/) via CDN |
| Persistência | JSON com `flock()` / `LOCK_EX` |
| Offline | LocalStorage + polling automático |

---

## 🤝 Contribuir

Contribuições são bem-vindas!

1. Faz **Fork** do repositório
2. Cria um branch: `git checkout -b feature/minha-feature`
3. Commita as mudanças: `git commit -m 'feat: adiciona X'`
4. Abre um **Pull Request**

---

## 📄 Licença

Distribuído sob licença **MIT**. Vê o ficheiro [LICENSE](LICENSE) para mais detalhes.

---

> Desenvolvido por **[Zedeck's IT](https://github.com/zedeckmuacy)** 🇲🇿