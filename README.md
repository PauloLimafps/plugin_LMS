# Diagrama da Arquitetura

O plugin opera delegando a lógica para o n8n. Abaixo, a visão técnica dos componentes envolvidos:

<div align="center">
 <img width="600" src="https://github.com/PauloLimafps/plugin_LMS/blob/main/Images/Diagrama%20de%20Arquitetura%20(Vis%C3%A3o%20T%C3%A9cnica).png" alt="Diagrama do Workflow n8n" />
</div>

# moodle-block_openai_chat
<img align="right" src="https://github.com/Limekiller/moodle-block_openai_chat/assets/33644013/21f73adc-5bd4-4539-999b-a3b0a83736e0" />

### Bloco de chat com IA para Moodle (Versão Webhook/n8n)
Este bloco permite que os usuários do seu Moodle obtenham suporte via chat 24/7. Esta versão foi modificada para atuar como um conector, enviando as mensagens para um serviço externo (como n8n) em vez de conectar diretamente à API da OpenAI.

Para começar, você precisará de um endpoint de Webhook configurado (ex: n8n, Zapier ou servidor próprio) para receber e processar as mensagens.

# Configurações globais do bloco
As configurações globais do bloco podem ser encontradas indo em Administração do Site > Plugins > Blocos > OpenAI Chat Block. As opções são:

- Webhook URL: Aqui você adiciona a URL do seu fluxo no n8n (método POST) que receberá as mensagens.

- Security Token: Um token de segurança opcional que será enviado no payload para validar a requisição no seu n8n.

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

- Contexto do Usuário: ID, Nome, Email.

- Contexto da Página: O ID e Nome do curso onde o bloco está sendo visualizado.

- Matrículas do Aluno (Student Enrollments): Uma lista completa de todos os cursos em que o usuário está matriculado, permitindo que a IA saiba o contexto acadêmico do aluno mesmo que ele esteja na página inicial.

### Sobre o Prompt e Respostas
Nesta versão modificada, a influência sobre a persona da IA, a "Fonte da Verdade" e o formato das respostas não é mais configurada no Moodle.

Você deve configurar o seu fluxo no n8n para:

- Receber o JSON acima.

- Processar a lógica (ex: consultar Banco Vetorial/RAG).

- Devolver um JSON com a resposta no campo output ou message.

### Workflow n8n: Assistente Acadêmico com RAG


<div align="center">
 <img width="600" src="https://github.com/PauloLimafps/plugin_LMS/blob/main/Images/workflow_n8n.png?raw=true" alt="Diagrama do Workflow n8n" />
</div>


Este repositório contém o arquivo JSON do workflow do n8n responsável por processar as dúvidas dos alunos vindas do Moodle. Ele atua como o "cérebro" da operação, gerenciando filas de mensagens, memória de conversação e recuperação de informações (RAG).

# Visão Geral da Arquitetura
O workflow foi desenhado para lidar com comportamento humano real em chats e garantir respostas baseadas em documentos oficiais.

### Principais Funcionalidades:

- Buffer de Mensagens (via Redis): Agrupa mensagens enviadas em rápida sucessão (ex: "Oi", "tudo bem?", "qual a média?") em um único bloco de contexto antes de enviar para a IA.

- Injeção de Contexto: Processa metadados do aluno (cursos matriculados, ID, nome) para personalizar o atendimento.

- RAG (Retrieval-Augmented Generation): Consulta um banco vetorial (Supabase) via Edge Function para buscar regras acadêmicas específicas.

- Memória Persistente: Utiliza PostgreSQL para manter o histórico da conversa (session_id baseado no ID do usuário).

# Pré-requisitos
Para importar e rodar este workflow, você precisa das seguintes instâncias e credenciais configuradas no n8n:

1. Serviços Externos

- Redis: Necessário para o sistema de fila/buffer de mensagens.

- PostgreSQL: Utilizado pelo LangChain para armazenar o histórico do chat (Chat Memory).

- Supabase: Projeto configurado com pgvector e uma Edge Function para hybrid_search.

- OpenAI: Chave de API para o modelo gpt-4o-mini (ou superior).

2. Credenciais no n8n
Certifique-se de criar as seguintes credenciais no painel do n8n:

- OpenAi account

- Postgres RAG

- Redis account

# Fluxo Detalhado dos Nós
1. Entrada e Normalização
- Webhook (POST /receber-mensagem): Ponto de entrada. Recebe o JSON enviado pelo plugin do Moodle.

- Edit Fields: Normaliza e sanitiza os dados de entrada.

- Extrai: pergunta, usuario_user, usuario_id, email, unique_id etc..

- Formata Arrays: Converte a lista de objetos student_enrollments em listas simples de Nomes e IDs para uso no prompt.

2. O "Debounce" (Lógica Redis)
- Esta seção impede que a IA seja acionada múltiplas vezes se o aluno digitar frases picadas.

- Lista Temp (Redis Push): Adiciona a mensagem atual em uma lista temporária no Redis usando o unique_id do usuário como chave.

- Wait1 (6 segundos): Aguarda um breve período para ver se chegam mais mensagens.

- Buscar Lista Temp: Recupera todas as mensagens acumuladas.

- Condicional (IF): Verifica: "Esta execução é referente à ÚLTIMA mensagem enviada?"

- Se SIM: Prossegue para processar o bloco inteiro.

- Se NÃO: Encerra a execução (o nó Liberar PHP devolve um JSON de controle para não travar o Moodle).

- Mensagem Final: Junta todas as frases acumuladas em um único parágrafo.

- Deletar Lista: Limpa o Redis para a próxima interação.

3. O Agente de IA (LangChain)


- System Prompt: Define a persona ("Assistente Virtual Educado") e regras estritas: só usar a ferramenta RAG se houver uma "Dúvida Clara". Caso contrário, apenas cumprimenta ou pede detalhes.

- Contexto Injetado: O prompt recebe dinamicamente o Curso e as Mensagens acumuladas.

- Postgres Chat Memory: Garante que a IA lembre do que foi dito anteriormente na sessão.

- OpenAI Chat Model: Configurado com temperatura baixa (0.2) para evitar alucinações e manter o rigor nas regras acadêmicas.

4. Ferramentas (Tools)
RAG Medicina (HTTP Request):

- Faz uma chamada POST para o Supabase.

- Endpoint: /functions/v1/hybrid_search.

- Payload: Envia a pergunta do usuário vetorizada para buscar trechos relevantes nos manuais.
