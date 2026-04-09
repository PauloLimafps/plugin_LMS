<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English strings
 *
 * @package    block_openai_chat
 * @copyright  2022 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Assistente RAG';
$string['openai_chat'] = 'Assistente RAG';
$string['openai_chat:addinstance'] = 'Adicionar novo bloco do Assistente';
$string['openai_chat:myaddinstance'] = 'Adicionar novo bloco do Assistente no Painel';

// Configurações (Essas chaves devem bater com o settings.php)
$string['headerconfig'] = 'Conexão com o Orquestrador RAG';
$string['descconfig'] = 'Configure a conexão com o servidor FastAPI.';
$string['webhookurl'] = 'URL do Webhook (FastAPI)';
$string['jwtsecret'] = 'JWT Secret (Compartilhada)';
$string['jwtsecret_desc'] = 'Chave secreta usada para assinar as requisições. Deve ser idêntica à definida no .env do FastAPI.';