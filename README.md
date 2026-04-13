# 🧩 Bloco de Chat com IA para Moodle (Versão RAG Orchestrator)

<img align="right" src="https://github.com/Limekiller/moodle-block_openai_chat/assets/33644013/21f73adc-5bd4-4539-999b-a3b0a83736e0" />

Este componente representa uma evolução técnica significativa do plugin `block_openai_chat`, convertendo a interface de chat em um **conector especializado para Retrieval-Augmented Generation (RAG)** dentro de um ecossistema Docker.

A solução opera como um **Thin Client**: a inteligência não é processada no servidor Moodle, mas sim delegada a um **Orquestrador FastAPI**, garantindo que o plugin gerencie apenas a interface, a segurança da sessão e o contexto educacional.

---

## 🚀 Evoluções e Diferenciais Técnicos

Esta versão foi redesenhada para suportar fluxos de trabalho corporativos e acadêmicos que exigem alta precisão e segurança.

### 1. Arquitetura de Segurança com JWT (HS256)
- **Diferencial**: Diferente de implementações baseadas em tokens estáticos, esta versão utiliza **JSON Web Tokens**.
- **Impacto**: Cada interação é assinada digitalmente, assegurando que apenas requisições originadas em instâncias autorizadas do Moodle sejam processadas pelo backend de IA.

### 2. Enriquecimento de Contexto Operacional
- **Diferencial**: Além da pergunta direta, o plugin extrai e envia metadados profundos da sessão do aluno.
- **Impacto**: A IA recebe automaticamente o contexto do curso atual e a lista completa de matrículas do estudante, permitindo respostas personalizadas baseadas no perfil acadêmico real.

### 3. Conexão Nativa com Bases de Conhecimento
- O design é otimizado para lidar com respostas estruturadas e extensas provenientes de buscas semânticas em documentos (PDFs, manuais e guias institucionais) armazenados no Weaviate.

---

## 🛠️ Parâmetros de Configuração

As opções de ajuste estão disponíveis em: *Administração do Site > Plugins > Blocos > OpenAI Chat Block*.

- **Webhook URL (Gateway)**: Endereço do endpoint de processamento (Ex: `http://api-ia-cluster:8000/chat`).
- **JWT Secret**: Chave de segurança para assinatura das requisições. Deve haver paridade entre esta chave e a do arquivo `.env` no orquestrador.
- **Nome do Assistente**: Identificação visual da IA na interface de chat.
- **Logs de Suporte**: Registro das interações para monitoramento e análise pedagógica.

---

## 🔒 Fluxo de Comunicação e Segurança

A estrutura remove a necessidade de conexão direta entre o Moodle e provedores de IA. O caminho percorrido pela informação é:
1. O Moodle assina a mensagem com a `JWT_SECRET`.
2. O Gateway (Traefik) valida a integridade do tráfego e protege a infraestrutura.
3. O Backend valida a assinatura antes de autorizar qualquer consulta de dados ou chamada de modelo.

Essa abordagem protege chaves de API e documentos proprietários contra acessos indevidos.

---

## 📂 Integração com o Backend

Para o funcionamento pleno, o plugin deve estar conectado ao orquestrador dedicado:
- **Repositório Backend**: [Projeto FastAPI RAG](https://github.com/PauloLimafps/Projeto_FastAPI)

---
*💡 Tecnologia focada no enriquecimento da experiência de aprendizado através de IA contextualizada.*