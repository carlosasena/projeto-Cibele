<?php
// =============================================
// enviar-avaliacao.php - Versão com SMTP Locaweb
// =============================================

// Carrega o PHPMailer
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// =============================================
// CONFIGURAÇÕES - ALTERE AQUI
// =============================================

// Dados do seu e-mail no domínio (precisa existir)
$email_smtp = "noreply@visaepassaporte.com.br";  // E-mail que vai enviar
$senha_smtp = "SUA_SENHA_AQUI";                  // Senha do e-mail
$nome_smtp  = "Visa & Passaporte Consultoria";

// E-mail que vai RECEBER
$email_destino = "cibelealencar@visaepassaporte.com.br";

// =============================================

// Função para redirecionar
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

// Sanitiza dados
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

// Validação
if (empty($nome)) redirecionar("erro", "nome");
if (empty($email)) redirecionar("erro", "email");
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) redirecionar("erro", "email_invalido");

// Valores padrão
$telefone = empty($telefone) ? "Não informado" : $telefone;
$pais     = empty($pais) ? "Não informado" : $pais;
$mensagem = empty($mensagem) ? "Não informada" : $mensagem;

// Mapeamento dos tipos de visto
$tipos_visto = [
    'b1b2'      => 'Turismo – B1/B2 (EUA)',
    'f1'        => 'Estudante – F1 (EUA)',
    'j1'        => 'Intercâmbio – J1 (EUA)',
    'h1b'       => 'Trabalho – H1B (EUA)',
    'europa'    => 'Visto para Europa / Schengen',
    'casamento' => 'Casamento Internacional',
    'familiar'  => 'Reagrupamento Familiar',
    'outro'     => 'Outro / Não sei'
];
$visto_exibicao = $tipos_visto[$visto] ?? $visto;

// =============================================
// CORPO DO E-MAIL
// =============================================

$assunto = "Nova Avaliação de Perfil – Visa & Passaporte – {$nome}";

$corpo_html = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #431222; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f4f5; padding: 20px; border: 1px solid #c9a0a8; }
        .field { margin-bottom: 15px; }
        .field-label { font-weight: bold; color: #431222; }
        .field-value { background: white; padding: 10px; border-radius: 4px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #696566; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Nova Avaliação de Perfil</h2>
            <p>Visa & Passaporte Consultoria</p>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='field-label'>Nome completo:</div>
                <div class='field-value'>" . htmlspecialchars($nome) . "</div>
            </div>
            <div class='field'>
                <div class='field-label'>E-mail:</div>
                <div class='field-value'>" . htmlspecialchars($email) . "</div>
            </div>
            <div class='field'>
                <div class='field-label'>Telefone/WhatsApp:</div>
                <div class='field-value'>" . htmlspecialchars($telefone) . "</div>
            </div>
            <div class='field'>
                <div class='field-label'>País de destino:</div>
                <div class='field-value'>" . htmlspecialchars($pais) . "</div>
            </div>
            <div class='field'>
                <div class='field-label'>Tipo de visto:</div>
                <div class='field-value'>" . htmlspecialchars($visto_exibicao) . "</div>
            </div>
            <div class='field'>
                <div class='field-label'>Mensagem:</div>
                <div class='field-value'>" . nl2br(htmlspecialchars($mensagem)) . "</div>
            </div>
            <hr>
            <div class='field'>
                <div class='field-label'>Recebido em:</div>
                <div class='field-value'>" . date("d/m/Y H:i:s") . "</div>
            </div>
        </div>
        <div class='footer'>
            <p>E-mail enviado automaticamente pelo sistema Visa & Passaporte.</p>
        </div>
    </div>
</body>
</html>
";

// =============================================
// ENVIO VIA SMTP
// =============================================

$mail = new PHPMailer(true);
$enviado = false;

try {
    // Configuração do servidor SMTP da Locaweb
    $mail->isSMTP();
    $mail->Host       = 'smtp.visaepassaporte.com.br';  // SMTP da Locaweb
    $mail->SMTPAuth   = true;
    $mail->Username   = $email_smtp;
    $mail->Password   = $senha_smtp;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Remetente e destinatário
    $mail->setFrom($email_smtp, $nome_smtp);
    $mail->addAddress($email_destino);
    $mail->addReplyTo($email, $nome);

    // Conteúdo
    $mail->isHTML(true);
    $mail->Subject = $assunto;
    $mail->Body    = $corpo_html;
    $mail->AltBody = strip_tags($corpo_html);

    $mail->send();
    $enviado = true;
    
    // Salva backup
    salvarBackup($nome, $email, $telefone, $pais, $visto_exibicao, $mensagem);
    redirecionar("sucesso");
    
} catch (Exception $e) {
    // Erro no envio - salva backup
    error_log("Erro PHPMailer: " . $mail->ErrorInfo);
    salvarBackup($nome, $email, $telefone, $pais, $visto_exibicao, $mensagem);
    redirecionar("sucesso");  // Mesmo com erro, salva backup e mostra sucesso
}

// =============================================
// FUNÇÃO DE BACKUP
// =============================================

function salvarBackup($nome, $email, $telefone, $pais, $visto, $mensagem) {
    $backup_dir = __DIR__ . '/_backup_avaliacoes';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $arquivo_csv = $backup_dir . '/avaliacoes_' . date('Y-m-d') . '.csv';
    $existe = file_exists($arquivo_csv);
    
    if (!$existe) {
        $cabecalho = "Data;Nome;Email;Telefone;Pais Destino;Tipo Visto;Mensagem;IP\n";
        file_put_contents($arquivo_csv, $cabecalho, FILE_APPEND);
    }
    
    $dados = [
        date('Y-m-d H:i:s'),
        str_replace(';', ',', $nome),
        $email,
        $telefone,
        $pais,
        $visto,
        str_replace(["\n", "\r", ";"], ' ', substr($mensagem, 0, 200)),
        $_SERVER['REMOTE_ADDR'] ?? 'Não identificado'
    ];
    
    $linha = implode(';', $dados) . "\n";
    file_put_contents($arquivo_csv, $linha, FILE_APPEND);
}
?>