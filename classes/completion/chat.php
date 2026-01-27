<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace block_openai_chat\completion;

use block_openai_chat\completion;
defined('MOODLE_INTERNAL') || die;

/**
 * Classe customizada para conectar ao n8n (Assistente Acadêmico).
 * Versão Final: Com Injeção de Contexto, Lista de Matrículas e Detecção de Referer.
 */
class chat extends \block_openai_chat\completion {

    public function __construct($model, $message, $history, $block_settings, $thread_id = null) {
        // Mantemos o construtor original para compatibilidade
        parent::__construct($model, $message, $history, $block_settings);
    }

    /**
     * Método principal chamado pelo chat.js
     */
    public function create_completion($context) {
        global $USER, $COURSE, $DB;

        // =================================================================
        // 1. PERFORMANCE: Desbloqueio de Sessão
        // =================================================================
        // Permite que o navegador continue responsivo enquanto o n8n pensa.
        \core\session\manager::write_close();

        // =================================================================
        // 2. CONFIGURAÇÕES
        // =================================================================
        $webhook_url = get_config('block_openai_chat', 'webhookurl');
        
        if (empty($webhook_url)) {
            return [
                "id" => "error",
                "message" => "Erro Crítico: A URL do Webhook (n8n) não foi configurada no plugin."
            ];
        }

        // =================================================================
        // 3. CAPTURA DO CONTEXTO DA PÁGINA (Onde o aluno está clicando?)
        // =================================================================
        
        $course_id_to_use = 0;

        // TENTATIVA A: O jeito oficial (Parâmetro na requisição)
        $course_id_to_use = optional_param('courseid', 0, PARAM_INT);

        // TENTATIVA B: O jeito "Investigativo" (URL de Origem / Referer)
        // Se a tentativa A falhou ou retornou 1 (Home), olhamos a URL do navegador.
        if ((empty($course_id_to_use) || $course_id_to_use == 1) && isset($_SERVER['HTTP_REFERER'])) {
            $referer_url = $_SERVER['HTTP_REFERER'];
            $query_str = parse_url($referer_url, PHP_URL_QUERY);
            
            if ($query_str) {
                parse_str($query_str, $query_params);
                // Se a URL original tem "id=XX", usamos esse ID com prioridade!
                if (isset($query_params['id']) && is_numeric($query_params['id'])) {
                    $course_id_to_use = (int)$query_params['id'];
                }
            }
        }

        // TENTATIVA C: Fallback (Variável Global)
        if (empty($course_id_to_use)) {
            $course_id_to_use = $COURSE->id;
        }

        // Carrega o objeto do curso para pegar o nome oficial
        try {
            $real_course = get_course($course_id_to_use);
        } catch (\Exception $e) {
            $real_course = get_course(1); // Segurança: Joga para a Home se der erro fatal
        }

        // =================================================================
        // 4. CONTEXTO DO ALUNO (Quem é ele?)
        // =================================================================
        
        // A. Ano de Ingresso Estimado
        $ano_ingresso = date('Y', $USER->firstaccess);

        // B. Lista de Cursos Matriculados (A Solução Definitiva)
        // Se o contexto da página falhar (ID 1), o n8n usará esta lista para saber a turma real.
        $cursos_matriculados = [];
        
        // Busca cursos onde o usuário está inscrito, retornando apenas campos leves
        $my_courses = enrol_get_users_courses($USER->id, true, 'id, fullname, shortname');
        
        foreach ($my_courses as $c) {
            // Não enviamos o curso "Site Home" (Frontpage), pois não é disciplina
            if ($c->id != 1) {
                $cursos_matriculados[] = [
                    'id' => $c->id,
                    'nome' => $c->fullname,
                    'codigo' => $c->shortname
                ];
            }
        }

        // =================================================================
        // 5. MONTAGEM DO PAYLOAD (O Pacote JSON)
        // =================================================================
        $payload = [
            'message' => $this->message,
            
            // Dados do Usuário
            'user' => [
                'id' => $USER->id,
                'fullname' => fullname($USER),
                'email' => $USER->email,
                'firstaccess' => $USER->firstaccess,
                'ano_ingresso_estimado' => $ano_ingresso
            ],
            
            // Onde ele está agora (Contexto da Página)
            'page_context' => [
                'course_id' => $real_course->id,
                'course_name' => $real_course->fullname,
                'course_code' => $real_course->shortname
            ],
            
            // O que ele estuda (Contexto Acadêmico Completo)
            'student_enrollments' => $cursos_matriculados
        ];

        // =================================================================
        // 6. ENVIO PARA O N8N (cURL)
        // =================================================================
        $curl = new \curl();
        $options = [
            'CURLOPT_HTTPHEADER' => ['Content-Type: application/json'],
            'CURLOPT_TIMEOUT' => 60, // Timeout generoso para a IA pensar
            'CURLOPT_CONNECTTIMEOUT' => 10
        ];

        // Envia o POST
        $response_raw = $curl->post($webhook_url, json_encode($payload), $options);

        // Verifica erros de conexão (DNS, Timeout, Firewall)
        $errno = $curl->get_errno();
        if ($errno) {
            return [
                "id" => "curl_error",
                "message" => "Erro de conexão com o Assistente Inteligente. Tente novamente."
            ];
        }

        // =================================================================
        // 7. TRATAMENTO DA RESPOSTA
        // =================================================================
        $response_json = json_decode($response_raw);
        $bot_message = "O assistente não retornou uma resposta válida.";

        // Tentamos extrair a resposta de vários campos possíveis para flexibilidade
        if (isset($response_json->output)) {
            $bot_message = $response_json->output;
        } elseif (isset($response_json->message)) {
            $bot_message = $response_json->message;
        } elseif (isset($response_json->answer)) {
            $bot_message = $response_json->answer;
        } elseif (isset($response_json->text)) {
            $bot_message = $response_json->text;
        } elseif (is_string($response_json)) {
            // Caso o n8n devolva texto puro (raro, mas possível)
            $bot_message = $response_raw;
        } elseif (json_last_error() === JSON_ERROR_NONE) {
            // Se for um JSON válido mas sem campos conhecidos, retorna o JSON para debug
            // (Você pode remover isso em produção se preferir)
            $bot_message = json_encode($response_json);
        }

        // Retorna no formato exato que o plugin espera
        return [
            "id" => uniqid('n8n_'),
            "message" => $bot_message
        ];
    }
}