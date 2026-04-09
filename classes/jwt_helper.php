<?php
namespace block_openai_chat;

defined('MOODLE_INTERNAL') || die;

/**
 * jwt_helper — Classe utilitária para geração de tokens JWT (HS256).
 * 
 * Esta implementação não requer bibliotecas externas/Composer, sendo compatível
 * com qualquer ambiente Moodle padrão.
 */
class jwt_helper {

    /**
     * Gera um token JWT assinado.
     * 
     * @param int $user_id  ID do usuário (será o 'sub' no payload)
     * @param int $ttl      Tempo de vida do token em segundos (padrão 5 min)
     * @return string       O token JWT completo (header.payload.signature)
     */
    public static function generate(int $user_id, int $ttl = 300): string {
        $secret = get_config('block_openai_chat', 'jwtsecret');

        if (empty($secret)) {
            throw new \Exception('Chave JWT (Secret) não configurada no Moodle.');
        }

        $now = time();
        $header = self::base64url_encode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]));

        $payload = self::base64url_encode(json_encode([
            'iss' => 'moodle-openai-chat',   // Emissor
            'sub' => (string)$user_id,        // Subject (ID do Aluno)
            'iat' => $now,                    // Issued at
            'exp' => $now + $ttl              // Expiration
        ]));

        $signature = self::base64url_encode(
            hash_hmac('sha256', "$header.$payload", $secret, true)
        );

        return "$header.$payload.$signature";
    }

    /**
     * Encode de string para formato compatível com URL (padrão JWT).
     */
    private static function base64url_encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
