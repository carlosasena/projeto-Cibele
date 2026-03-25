<?php
// =============================================
// test-email.php
// Teste de envio de e-mail – Locaweb
// Acesse via navegador para verificar se o servidor consegue enviar e-mails
// REMOVA ESTE ARQUIVO após confirmar que o envio funciona em produção
// =============================================

// Exibe erros para facilitar o diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

// =============================================
// CONFIGURAÇÕES — altere conforme necessário
// =============================================
$dominio         = 'visaepassaporte.com.br';
$email_destino   = 'cibelealencar@visaepassaporte.com.br';
$email_remetente = 'noreply@' . $dominio;
$nome_remetente  = 'Teste Visa e Passaporte';

// =============================================
// MONTA O E-MAIL
// =============================================
$assunto_texto = 'Teste de Sistema - Visa & Passaporte - ' . date('d/m/Y H:i:s');
$assunto       = '=?UTF-8?B?' . base64_encode($assunto_texto) . '?=';

$mensagem  = "Este e um e-mail de teste do sistema de avaliacao.\n\n";
$mensagem .= "Se voce recebeu este e-mail, o sistema de envio esta funcionando corretamente.\n\n";
$mensagem .= "============================================\n";
$mensagem .= "INFORMACOES TECNICAS\n";
$mensagem .= "============================================\n";
$mensagem .= "Servidor:    " . ($_SERVER['SERVER_NAME'] ?? 'Nao identificado') . "\n";
$mensagem .= "Data/hora:   " . date('d/m/Y H:i:s') . "\n";
$mensagem .= "IP servidor: " . ($_SERVER['SERVER_ADDR'] ?? 'Nao identificado') . "\n";
$mensagem .= "PHP Version: " . phpversion() . "\n";
$mensagem .= "============================================\n";

// Headers no formato Locaweb (separador \n simples, não \r\n)
$headers  = "From: {$nome_remetente} <{$email_remetente}>\n";
$headers .= "Reply-To: {$email_remetente}\n";
$headers .= "MIME-Version: 1.0\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\n";
$headers .= "Content-Transfer-Encoding: 8bit\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Parâmetro -r obrigatório na Locaweb (envelope sender)
$parametros_extra = '-r' . $email_remetente;

// =============================================
// EXIBE O DIAGNÓSTICO
// =============================================
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de E-mail – Visa &amp; Passaporte</title>
    <style>
        body        { font-family: Arial, sans-serif; max-width: 820px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        h1          { color: #431222; }
        h3          { margin-top: 0; }
        .success    { background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; border: 1px solid #c3e6cb; margin: 10px 0; }
        .error      { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; border: 1px solid #f5c6cb; margin: 10px 0; }
        .info       { background: #e9ecef; color: #383d41; padding: 15px; border-radius: 6px; margin: 10px 0; }
        pre         { background: #fff; border: 1px solid #ccc; padding: 12px; border-radius: 4px; overflow-x: auto; font-size: 0.875rem; }
        a.btn       { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #431222; color: #fff; border-radius: 4px; text-decoration: none; }
        a.btn:hover { background: #29030D; }
    </style>
</head>
<body>

<h1>Teste de Envio de E-mail – Locaweb</h1>

<div class="info">
    <h3>Configurações utilizadas</h3>
    <ul>
        <li><strong>E-mail destino:</strong>   <?= htmlspecialchars($email_destino) ?></li>
        <li><strong>E-mail remetente:</strong> <?= htmlspecialchars($email_remetente) ?></li>
        <li><strong>Nome remetente:</strong>   <?= htmlspecialchars($nome_remetente) ?></li>
        <li><strong>Separador headers:</strong> \n (Linux/Locaweb)</li>
        <li><strong>Parâmetro -r:</strong>     <?= htmlspecialchars($parametros_extra) ?></li>
        <li><strong>Função mail():</strong>    <?= function_exists('mail') ? '✅ Disponível' : '❌ INDISPONÍVEL' ?></li>
    </ul>
</div>

<h3>Resultado do teste:</h3>

<?php
if (function_exists('mail')) {
    $enviado = mail($email_destino, $assunto, $mensagem, $headers, $parametros_extra);

    if ($enviado) {
        echo '<div class="success">';
        echo '✅ <strong>E-mail enviado com sucesso!</strong><br>';
        echo 'Verifique a caixa de entrada de <strong>' . htmlspecialchars($email_destino) . '</strong> (incluindo SPAM).';
        echo '</div>';
    } else {
        $erro = error_get_last();
        echo '<div class="error">';
        echo '❌ <strong>Falha no envio do e-mail.</strong><br>';
        echo 'O servidor retornou erro ao tentar enviar.';
        if ($erro) {
            echo '<br><br><strong>Detalhe:</strong><pre>' . htmlspecialchars($erro['message']) . '</pre>';
        }
        echo '</div>';
    }
} else {
    echo '<div class="error">';
    echo '❌ <strong>Função mail() não disponível neste servidor.</strong><br>';
    echo 'Entre em contato com o suporte da Locaweb.';
    echo '</div>';
}
?>

<div class="info">
    <h3>Informações do servidor</h3>
    <pre><?php
echo 'PHP Version:      ' . phpversion()                                    . "\n";
echo 'Server Software:  ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A')          . "\n";
echo 'HTTP Host:        ' . ($_SERVER['HTTP_HOST']        ?? 'N/A')          . "\n";
echo 'Document Root:    ' . ($_SERVER['DOCUMENT_ROOT']    ?? 'N/A')          . "\n";
echo 'Server IP:        ' . ($_SERVER['SERVER_ADDR']      ?? 'N/A')          . "\n";
    ?></pre>
</div>

<a class="btn" href="avaliacao.html">← Voltar para Avaliação</a>

<p style="margin-top:30px; color:#999; font-size:0.8rem;">
    ⚠️ <strong>Atenção:</strong> remova este arquivo do servidor após confirmar que os e-mails estão sendo recebidos.
</p>

</body>
</html>
