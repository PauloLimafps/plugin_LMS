# moodle-block_openai_chat
<img align="right" src="https://github.com/Limekiller/moodle-block_openai_chat/assets/33644013/21f73adc-5bd4-4539-999b-a3b0a83736e0" />

### Bloco de chat com IA para Moodle (Versão RAG Orchestrator / FastAPI)
Este bloco permite que os usuários do seu Moodle obtenham suporte via chat 24/7. Esta versão atua como um conector (Thin Client), enviando as mensagens para um **Orquestrador RAG (FastAPI)** que processa a inteligência e consulta a base de conhecimento institucional.

A comunicação é protegida por **Autenticação JWT (HS256)**, garantindo que apenas requisições legítimas do seu Moodle sejam processadas.

# Configurações globais do bloco
As configurações globais do bloco podem ser encontradas em *Administração do Site > Plugins > Blocos > OpenAI Chat Block*.

As principais opções são:

- **Webhook URL (FastAPI)**: A URL do endpoint `/chat` do seu orquestrador FastAPI (Ex: `http://sua-api.com/chat`).
  
- **JWT Secret (Chave Compartilhada)**: A chave secreta (HS256) usada para assinar digitalmente as requisições. **Importante**: Esta chave deve ser idêntica à definida no arquivo `.env` do seu servidor FastAPI.

- **Restrict chat usage to logged-in users**: Se marcado, apenas usuários autenticados verão a interface de chat.

- **Assistant name / User name**: Nomes exibidos nos cabeçalhos da interface do chat para identificar a IA e o aluno.

- **Enable logging**: Grava o histórico de interações em *Administração do Site > Relatórios > OpenAI Chat Logs*.

### Segurança e Autenticação (JWT)
Diferente de versões anteriores que usavam tokens simples, esta versão implementa um fluxo de segurança robusto:
1. O plugin gera um token **JWT (JSON Web Token)** assinado com a `JWT Secret`.
2. O payload contém o `sub` (ID do usuário) e metadados de expiração.
3. O FastAPI valida a assinatura antes de realizar qualquer busca semântica ou chamada de IA.

### Estrutura dos Dados (Payload)
O plugin envia um objeto JSON rico para o orquestrador, permitindo uma resposta altamente contextualizada:

- **Mensagem**: O texto digitado pelo aluno.
- **Dados do Usuário**: ID, Nome Completo, Email e metadados de acesso.
- **Contexto da Página**: ID e Nome do curso onde o aluno está no momento.
- **Matrículas (Student Enrollments)**: Lista de todos os cursos onde o aluno está inscrito, ajudando a IA a entender o perfil acadêmico completo do usuário.

### Sobre o Processamento (RAG)
Nesta arquitetura, o Moodle não sabe qual modelo de IA (GPT-4, Claude, etc.) está sendo usado. Toda a lógica de:
- Recuperação de Documentos (RAG).
- Memória de Curto Prazo (Histórico).
- Filtros de Segurança.
...é gerenciada exclusivamente pelo **Orquestrador Backend**. O plugin apenas exibe a resposta enviada pelo servidor.