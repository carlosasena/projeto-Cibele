<?php
// =============================================
// enviar-avaliacao.php
// Processa o formulário de avaliação e envia por e-mail
// Compatível com Locaweb
// =============================================

// Ativar exibição de erros apenas para desenvolvimento (remover em produção)
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// --- CONFIGURAÇÕES — edite aqui ---
$email_destino   = 'cibelealencar@visaepassaporte.com.br';
$email_remetente = 'noreply@visaepassaporte.com.br';
$assunto_base    = 'Nova Avaliacao de Perfil - Visa & Passaporte';
// ----------------------------------

// Função para log seguro
function logErro($mensagem)
{
    error_log('[VisaPassaporte] ' . $mensagem);
}

// Função para redirecionar com status
function redirecionar($status, $campo = '')
{
    $url = 'avaliacao.html?status=' . $status;
    if ($campo) {
        $url .= '&campo=' . urlencode($campo);
    }
    header('Location: ' . $url);
    exit;
}

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('erro_metodo');
}

// --- Coleta e sanitiza os dados ---
function limpar($valor)
{
    if ($valor === null || $valor === '') {
        return '';
    }
    $valor = trim($valor);
    $valor = stripslashes($valor);
    $valor = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    return $valor;
}

$nome     = limpar($_POST['nome']     ?? '');
$email    = limpar($_POST['email']    ?? '');
$telefone = limpar($_POST['telefone'] ?? '');
$pais     = limpar($_POST['pais']     ?? '');
$visto    = limpar($_POST['visto']    ?? '');
$mensagem = limpar($_POST['mensagem'] ?? '');

// --- Validação básica ---
if (empty($nome)) {
    redirecionar('erro', 'nome');
}

if (empty($email)) {
    redirecionar('erro', 'email');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirecionar('erro', 'email_invalido');
}

// Valores padrão para campos opcionais
if (empty($telefone)) $telefone = 'Nao informado';
if (empty($pais))     $pais     = 'Nao informado';
if (empty($visto))    $visto    = 'Nao informado';
if (empty($mensagem)) $mensagem = 'Nao informada';

// --- Monta o corpo do e-mail ---
$corpo  = "Nova avaliacao de perfil recebida pelo site.\n\n";
$corpo .= "============================================\n";
$corpo .= "DADOS DO SOLICITANTE\n";
$corpo .= "============================================\n";
$corpo .= "Nome completo:      {$nome}\n";
$corpo .= "E-mail:             {$email}\n";
$corpo .= "Telefone/WhatsApp:  {$telefone}\n";
$corpo .= "Pais de destino:    {$pais}\n";
$corpo .= "Tipo de visto:      {$visto}\n\n";
$corpo .= "MENSAGEM:\n";
$corpo .= "{$mensagem}\n\n";
$corpo .= "============================================\n";
$corpo .= "Recebido em: " . date('d/m/Y H:i:s') . "\n";
$corpo .= "IP: "          . ($_SERVER['REMOTE_ADDR']     ?? 'Nao identificado') . "\n";
$corpo .= "User Agent: "  . ($_SERVER['HTTP_USER_AGENT'] ?? 'Nao identificado') . "\n";

// --- Assunto codificado em Base64 UTF-8 (RFC 2047) ---
// Evita caracteres especiais quebrarem o cabeçalho na Locaweb
$assunto_texto = "{$assunto_base} - {$nome}";
$assunto = '=?UTF-8?B?' . base64_encode($assunto_texto) . '?=';

// --- Cabeçalhos do e-mail ---
// IMPORTANTE: Locaweb usa \n simples nos headers (não \r\n)
$headers  = "From: Visa e Passaporte <{$email_remetente}>\n";
$headers .= "Reply-To: {$email}\n";
$headers .= "MIME-Version: 1.0\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\n";
$headers .= "Content-Transfer-Encoding: 8bit\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Parâmetro -r obrigatório na Locaweb para definir o envelope sender
$parametros_extra = '-r' . $email_remetente;

// =============================================
// SALVA BACKUP EM CSV (SEMPRE, independente do e-mail)
// =============================================
$backup_dir = __DIR__ . '/_backup_avaliacoes';

if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

$arquivo_csv = $backup_dir . '/avaliacoes_' . date('Y-m-d') . '.csv';

if (!file_exists($arquivo_csv)) {
    $cabecalho = "Data;Nome;Email;Telefone;Pais Destino;Tipo Visto;Mensagem;IP\n";
    file_put_contents($arquivo_csv, $cabecalho, FILE_APPEND | LOCK_EX);
}

$dados_csv = [
    date('Y-m-d H:i:s'),
    str_replace(';', ',', $nome),
    $email,
    $telefone,
    $pais,
    $visto,
    str_replace(["\n", "\r", ";"], ' ', substr($mensagem, 0, 200)),
    $_SERVER['REMOTE_ADDR'] ?? 'Nao identificado',
];

$linha = implode(';', $dados_csv) . "\n";
file_put_contents($arquivo_csv, $linha, FILE_APPEND | LOCK_EX);

// =============================================
// TENTA ENVIAR E-MAIL
// =============================================
$enviado = false;

if (function_exists('mail')) {
    $enviado = mail($email_destino, $assunto, $corpo, $headers, $parametros_extra);

    if ($enviado) {
        logErro("E-mail enviado com sucesso para: {$email_destino}");
    } else {
        logErro("Falha no envio via mail() para: {$email_destino}");
    }
} else {
    logErro('Funcao mail() indisponivel no servidor.');
}

if (!$enviado) {
    logErro('E-mail nao enviado, mas dados salvos no backup CSV.');
}

// Redireciona para sucesso (dados foram salvos no backup)
redirecionar('sucesso');
