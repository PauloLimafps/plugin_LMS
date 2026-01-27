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
 * Plugin settings
 *
 * @package    block_openai_chat
 * @copyright  2024 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Título da seção de configuração
    $settings->add(new admin_setting_heading('block_openai_chat/headerconfig',
        get_string('headerconfig', 'block_openai_chat'),
        get_string('descconfig', 'block_openai_chat')));

    // Campo 1: URL do Webhook do n8n
    $settings->add(new admin_setting_configtext('block_openai_chat/webhookurl',
        'URL do Webhook (n8n)',
        'Cole aqui a URL do seu workflow (Production URL).',
        '', // Valor padrão vazio
        PARAM_URL));

    // Campo 2: Token de Segurança (Opcional)
    $settings->add(new admin_setting_configtext('block_openai_chat/securitytoken',
        'Token de Segurança',
        'Senha simples para validação no n8n.',
        '',
        PARAM_TEXT));
}