<?php
// =============================================
// enviar-avaliacao.php
// Processa o formulário de avaliação e envia por e-mail
// =============================================

// Ativar exibição de erros apenas para desenvolvimento (remover em produção)
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// --- CONFIGURAÇÕES — edite aqui ---
$email_destino   = "cibelealencar@visaepassaporte.com.br";
$email_remetente = "noreply@visaepassaporte.com.br";
$assunto_base    = "Nova Avaliação de Perfil – Visa & Passaporte";
// ----------------------------------

// Função para log seguro
function logErro($mensagem) {
    error_log("[VisaPassaporte] " . $mensagem);
}

// Função para redirecionar com status
function redirecionar($status, $campo = '') {
    $url = "avaliacao.html?status=" . $status;
    if ($campo) {
        $url .= "&campo=" . $campo;
    }
    header("Location: " . $url);
    exit;
}

// Só aceita POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecionar("erro_metodo");
}

// --- Coleta e sanitiza os dados ---
function limpar($valor) {
    if ($valor === null || $valor === '') {
        return '';
    }
    $valor = trim($valor);
    $valor = stripslashes($valor);
    $valor = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    return $valor;
}

$nome     = limpar($_POST["nome"] ?? "");
$email    = limpar($_POST["email"] ?? "");
$telefone = limpar($_POST["telefone"] ?? "");
$pais     = limpar($_POST["pais"] ?? "");
$visto    = limpar($_POST["visto"] ?? "");
$mensagem = limpar($_POST["mensagem"] ?? "");

// --- Validação básica ---
if (empty($nome)) {
    redirecionar("erro", "nome");
}

if (empty($email)) {
    redirecionar("erro", "email");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirecionar("erro", "email_invalido");
}

// Valores padrão para campos opcionais
if (empty($telefone)) $telefone = "Não informado";
if (empty($pais)) $pais = "Não informado";
if (empty($visto)) $visto = "Não informado";
if (empty($mensagem)) $mensagem = "Não informada";

// --- Monta o corpo do e-mail ---
$corpo = "Nova avaliação de perfil recebida pelo site.\n\n";
$corpo .= "============================================\n";
$corpo .= "DADOS DO SOLICITANTE\n";
$corpo .= "============================================\n";
$corpo .= "Nome completo:      $nome\n";
$corpo .= "E-mail:             $email\n";
$corpo .= "Telefone/WhatsApp:  $telefone\n";
$corpo .= "País de destino:    $pais\n";
$corpo .= "Tipo de visto:      $visto\n\n";
$corpo .= "MENSAGEM:\n";
$corpo .= "$mensagem\n\n";
$corpo .= "============================================\n";
$corpo .= "Recebido em: " . date("d/m/Y H:i:s") . "\n";
$corpo .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Não identificado') . "\n";
$corpo .= "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Não identificado') . "\n";

// --- Cabeçalhos do e-mail ---
$headers   = [];
$headers[] = "From: Visa & Passaporte <{$email_remetente}>";
$headers[] = "Reply-To: {$nome} <{$email}>";
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-Type: text/plain; charset=UTF-8";
$headers[] = "X-Mailer: PHP/" . phpversion();

$assunto = "{$assunto_base} – {$nome}";

// Tentativa 1: Usar mail() do PHP
if (function_exists('mail')) {
    $enviado = mail($email_destino, $assunto, $corpo, implode("\r\n", $headers));
    
    if ($enviado) {
        logErro("E-mail enviado com sucesso para: $email_destino");
        redirecionar("sucesso");
    } else {
        logErro("Falha no envio via mail() para: $email_destino");
    }
}

// Tentativa 2: Se mail() falhou, tentar salvar em arquivo (fallback)
$fallback_dir = __DIR__ . '/_avaliacoes';
if (!is_dir($fallback_dir)) {
    mkdir($fallback_dir, 0755, true);
}

$arquivo = $fallback_dir . '/avaliacao_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.txt';
$conteudo = "ASSUNTO: $assunto\n\n";
$conteudo .= $corpo;
$conteudo .= "\n\n--- CABEÇALHOS ---\n";
$conteudo .= implode("\n", $headers);

if (file_put_contents($arquivo, $conteudo)) {
    logErro("Fallback: avaliação salva em arquivo: $arquivo");
    redirecionar("sucesso");
} else {
    logErro("ERRO CRÍTICO: Falha ao salvar avaliação em arquivo");
    redirecionar("erro_fatal");
}

// Se chegou até aqui, tudo falhou
redirecionar("erro_fatal");
?>