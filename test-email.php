<?php
// =============================================
// test-email.php
// Teste de envio de e-mail para Locaweb
// =============================================

echo "<!DOCTYPE html>\n";
echo "<html lang='pt-br'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <title>Teste de E-mail - Visa & Passaporte</title>\n";
echo "    <style>\n";
echo "        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }\n";
echo "        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb; }\n";
echo "        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb; }\n";
echo "        .info { background: #e2e3e5; color: #383d41; padding: 15px; border-radius: 5px; margin-top: 20px; }\n";
echo "        pre { background: #f4f4f4; padding: 10px; overflow-x: auto; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<h1>Teste de Envio de E-mail</h1>\n";
echo "<h2>Configurações para Locaweb</h2>\n";

// =============================================
// CONFIGURAÇÕES - ALTERE AQUI
// =============================================
$dominio = "visaepassaporte.com.br";  // ALTERE PARA SEU DOMÍNIO
$email_destino = "cibelealencar@visaepassaporte.com.br";
$email_remetente = "teste@{$dominio}";
$nome_remetente = "Teste Visa & Passaporte";
$quebra_linha = "\n";  // Linux usa \n

// =============================================
// MONTA O E-MAIL
// =============================================
$assunto = "Teste de Sistema - Visa & Passaporte - " . date("d/m/Y H:i:s");

$mensagem = "Este é um e-mail de teste do sistema de avaliação.\n\n";
$mensagem .= "Se você recebeu este e-mail, o sistema de envio está funcionando corretamente.\n\n";
$mensagem .= "============================================\n";
$mensagem .= "INFORMAÇÕES TÉCNICAS\n";
$mensagem .= "============================================\n";
$mensagem .= "Servidor: " . ($_SERVER['SERVER_NAME'] ?? 'Não identificado') . "\n";
$mensagem .= "Data/hora: " . date("d/m/Y H:i:s") . "\n";
$mensagem .= "IP do servidor: " . ($_SERVER['SERVER_ADDR'] ?? 'Não identificado') . "\n";
$mensagem .= "PHP Version: " . phpversion() . "\n";
$mensagem .= "============================================\n";

// Headers no formato Locaweb
$headers = "From: {$nome_remetente} <{$email_remetente}>{$quebra_linha}";
$headers .= "Return-Path: {$email_remetente}{$quebra_linha}";
$headers .= "X-Mailer: PHP/" . phpversion() . $quebra_linha;
$headers .= "X-Priority: 3{$quebra_linha}";

// Parâmetro -r obrigatório na Locaweb
$parametros_extra = "-r{$email_remetente}";

// =============================================
// EXIBE CONFIGURAÇÕES
// =============================================
echo "<div class='info'>\n";
echo "<h3>Configurações utilizadas:</h3>\n";
echo "<ul>\n";
echo "    <li><strong>E-mail destino:</strong> {$email_destino}</li>\n";
echo "    <li><strong>E-mail remetente:</strong> {$email_remetente}</li>\n";
echo "    <li><strong>Nome remetente:</strong> {$nome_remetente}</li>\n";
echo "    <li><strong>Quebra de linha:</strong> " . ($quebra_linha === "\n" ? "\\n (Linux)" : "\\r\\n (Windows)") . "</li>\n";
echo "    <li><strong>Parâmetro -r:</strong> {$parametros_extra}</li>\n";
echo "    <li><strong>Função mail():</strong> " . (function_exists('mail') ? "Disponível" : "INDISPONÍVEL") . "</li>\n";
echo "</ul>\n";
echo "</div>\n";

// =============================================
// TENTA ENVIAR
// =============================================
echo "<h3>Resultado do teste:</h3>\n";

if (function_exists('mail')) {
    $enviado = mail($email_destino, $assunto, $mensagem, $headers, $parametros_extra);

    if ($enviado) {
        echo "<div class='success'>\n";
        echo "    ✅ <strong>E-mail enviado com sucesso!</strong><br>\n";
        echo "    Verifique a caixa de entrada de <strong>{$email_destino}</strong> (incluindo a pasta de SPAM).\n";
        echo "</div>\n";
    } else {
        echo "<div class='error'>\n";
        echo "    ❌ <strong>Falha no envio do e-mail.</strong><br>\n";
        echo "    O servidor retornou um erro ao tentar enviar.\n";
        echo "</div>\n";

        $erro = error_get_last();
        if ($erro) {
            echo "<div class='info'>\n";
            echo "<h4>Detalhes do erro:</h4>\n";
            echo "<pre>\n";
            echo htmlspecialchars($erro['message']) . "\n";
            echo "</pre>\n";
            echo "</div>\n";
        }
    }
} else {
    echo "<div class='error'>\n";
    echo "    ❌ <strong>Função mail() não está disponível neste servidor.</strong><br>\n";
    echo "    Entre em contato com o suporte da Locaweb.\n";
    echo "</div>\n";
}

// =============================================
// INFORMAÇÕES DO SERVIDOR
// =============================================
echo "<div class='info'>\n";
echo "<h3>Informações do servidor:</h3>\n";
echo "<pre>\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Não disponível') . "\n";
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'Não disponível') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Não disponível') . "\n";
echo "Server IP: " . ($_SERVER['SERVER_ADDR'] ?? 'Não disponível') . "\n";
echo "</pre>\n";
echo "</div>\n";

echo "<p><a href='avaliacao.html'>Voltar para página de avaliação</a></p>\n";

echo "</body>\n";
echo "</html>\n";
?>