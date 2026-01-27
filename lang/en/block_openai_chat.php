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
 * Language strings
 *
 * @package    block_openai_chat
 * @copyright  2024 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Assistente Acadêmico (RAG)';
$string['openai_chat:addinstance'] = 'Adicionar novo bloco do Assistente';
$string['openai_chat:myaddinstance'] = 'Adicionar novo bloco do Assistente no Painel';

// Configurações (Essas chaves devem bater com o settings.php)
$string['headerconfig'] = 'Conexão com n8n';
$string['descconfig'] = 'Configure a conexão com o orquestrador de automação (n8n).';
$string['webhookurl'] = 'URL do Webhook';
$string['securitytoken'] = 'Token de Acesso';