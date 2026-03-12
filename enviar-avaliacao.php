<?php
// =============================================
// enviar-avaliacao.php
// Processa o formulário de avaliação e envia por e-mail
// =============================================

// --- CONFIGURAÇÕES — edite aqui ---
$email_destino   = "cibelealencar@visaepassaporte.com.br";
$email_remetente = "noreply@visaepassaporte.com.br"; // Substitua pelo seu domínio
$assunto_base    = "Nova Avaliação de Perfil – Visa & Passaporte";
// ----------------------------------

// Só aceita POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: avaliacao.html");
    exit;
}

// --- Coleta e sanitiza os dados ---
function limpar($valor) {
    return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES, 'UTF-8');
}

$nome     = limpar($_POST["nome"]     ?? "");
$email    = limpar($_POST["email"]    ?? "");
$telefone = limpar($_POST["telefone"] ?? "Não informado");
$pais     = limpar($_POST["pais"]     ?? "Não informado");
$visto    = limpar($_POST["visto"]    ?? "Não informado");
$mensagem = limpar($_POST["mensagem"] ?? "Não informada");

// --- Validação básica ---
if (empty($nome) || empty($email)) {
    header("Location: avaliacao.html?status=erro&campo=obrigatorio");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: avaliacao.html?status=erro&campo=email");
    exit;
}

// --- Monta o corpo do e-mail ---
$corpo = "
Nova avaliação de perfil recebida pelo site.

============================================
DADOS DO SOLICITANTE
============================================
Nome completo:      $nome
E-mail:             $email
Telefone/WhatsApp:  $telefone
País de destino:    $pais
Tipo de visto:      $visto

MENSAGEM:
$mensagem
============================================
Recebido em: " . date("d/m/Y H:i:s") . "
IP: " . $_SERVER['REMOTE_ADDR'] . "
";

// --- Cabeçalhos do e-mail ---
$headers  = "From: Visa & Passaporte <$email_remetente>\r\n";
$headers .= "Reply-To: $nome <$email>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$assunto = "$assunto_base – $nome";

// --- Envia ---
$enviado = mail($email_destino, $assunto, $corpo, $headers);

// --- Redireciona com status ---
if ($enviado) {
    header("Location: avaliacao.html?status=sucesso");
} else {
    // Log do erro (opcional)
    error_log("Falha no envio de e-mail para $email_destino");
    header("Location: avaliacao.html?status=erro");
}
exit;
?>