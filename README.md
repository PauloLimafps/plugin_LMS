# moodle-block_openai_chat
<img align="right" src="https://github.com/Limekiller/moodle-block_openai_chat/assets/33644013/21f73adc-5bd4-4539-999b-a3b0a83736e0" />

### Bloco de chat com IA para Moodle (Versão Webhook/n8n)
Este bloco permite que os usuários do seu Moodle obtenham suporte via chat 24/7. Esta versão foi modificada para atuar como um conector (Thin Client), enviando as mensagens para um serviço externo (como n8n) em vez de conectar diretamente à API da OpenAI.

Para começar, você precisará de um endpoint de Webhook configurado (ex: n8n, Zapier ou servidor próprio) para receber e processar as mensagens.

# Configurações globais do bloco
As configurações globais do bloco podem ser encontradas indo em Administração do Site > Plugins > Blocos > OpenAI Chat Block. As opções são:

- Webhook URL: (Novo) Aqui você adiciona a URL do seu fluxo no n8n (método POST) que receberá as mensagens.

- Security Token: (Novo) Um token de segurança opcional que será enviado no payload para validar a requisição no seu n8n.

- Restrict chat usage to logged-in users: Se esta caixa estiver marcada, apenas usuários logados poderão usar a caixa de bate-papo.

- Assistant name: O nome que a IA usará para si mesma na conversa. É usado para os cabeçalhos da interface do chat.

- User name: O nome que a IA usará para o usuário na conversa. É usado para os cabeçalhos da interface do chat.

- Enable logging: Marcar esta caixa gravará todas as mensagens enviadas pelos usuários junto com a resposta da IA. Quando o registro está ativado, um ícone de gravação é exibido no bloco para indicar aos usuários que suas mensagens estão sendo salvas. As interações podem ser encontradas em Administração do Site > Relatórios > OpenAI Chat Logs.

### Configurações removidas
- As configurações de API Key da OpenAI, Source of Truth, System Prompts e Model Selection foram removidas deste plugin, pois agora essa lógica deve ser gerenciada dentro do seu fluxo do n8n.

- Configurações individuais do bloco
Existem algumas configurações que podem ser alteradas base bloco a bloco. Você pode acessar essas configurações entrando no modo de edição em seu site, clicando na engrenagem no bloco e indo em "Configurar OpenAI Chat Block".

- Block title: O título para este bloco.

- Show labels: Se os nomes escolhidos para "Assistant name" e "User name" devem aparecer na interface do chat.

### Estrutura dos Dados (Payload)
Diferente da versão original que construía o prompt internamente, esta versão envia um objeto JSON rico para o seu Webhook contendo o contexto completo do aluno.

O payload enviado para o seu n8n seguirá este formato:

- Mensagem: O texto digitado pelo usuário.

- Contexto do Usuário: ID, Nome, Email, Data de Cadastro e Ano de Ingresso Estimado.

-Contexto da Página: O ID e Nome do curso onde o bloco está sendo visualizado (se aplicável).

- Matrículas do Aluno (Student Enrollments): Uma lista completa de todos os cursos em que o usuário está matriculado, permitindo que a IA saiba o contexto acadêmico do aluno mesmo que ele esteja na página inicial.

### Sobre o Prompt e Respostas
Nesta versão modificada, a influência sobre a "persona" da IA, a "Fonte da Verdade" (Source of Truth) e o formato das respostas não é mais configurada no Moodle.

Você deve configurar o seu fluxo no n8n para:

- Receber o JSON acima.

- Processar a lógica (ex: consultar Banco Vetorial/RAG).

- Devolver um JSON com a resposta no campo output ou message.