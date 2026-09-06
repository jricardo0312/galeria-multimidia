# 📸 Galeria Multimídia Privada (PHP + Docker + MySQL)

Sistema web leve para gestão de álbuns de fotos e vídeos, com suporte a uploads em lote, layout dinâmico responsivo (Masonry) e controle de acesso restrito via perfis de usuário.

## 🚀 Tecnologias Utilizadas

* **Linguagem:** PHP 8.2 (PDO, GD, FFmpeg)
* **Banco de Dados:** MySQL 8.0
* **Servidor Web:** Apache (Docker)
* **Frontend:** Tailwind CSS (Masonry Layout via CSS Columns)
* **Infraestrutura:** Docker & Docker Compose

## 🔒 Níveis de Acesso

1. **Administrador (`ADMIN_USER`):** Acesso total. Cria álbuns, realiza uploads em lote, altera a visibilidade de mídias (público/privado) e exclui arquivos.
2. **Convidados (`GUEST_USER`):** Acesso restrito. Visualiza apenas mídias explicitamente marcadas como públicas pelo administrador.
3. **Visitantes Anônimos:** Redirecionados obrigatoriamente para a tela de autenticação.

## 📋 Pré-requisitos

* Docker Engine e Docker Compose instalados.
* Ambiente Linux/Unix (testado em Linux Mint).

## 🛠️ Instalação e Execução

1. **Clone o repositório:**
   ```bash
   git clone [https://github.com/seu-usuario/galeria-multimidia.git](https://github.com/seu-usuario/galeria-multimidia.git)
   cd galeria-multimidia