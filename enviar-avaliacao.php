<?php
// =============================================
// enviar-avaliacao.php
// Processa o formulário de avaliação - Versão Locaweb
// =============================================

// Configurações de erro (desative em produção)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// =============================================
// CONFIGURAÇÕES DO SISTEMA
// =============================================

// Domínio da hospedagem (ALTERE PARA SEU DOMÍNIO)
$dominio = "visaepassaporte.com.br";

// E-mails
$email_destino   = "cibelealencar@visaepassaporte.com.br";
$email_remetente = "noreply@{$dominio}";
$nome_remetente  = "Visa & Passaporte Consultoria";
$assunto_base    = "Nova Avaliação de Perfil – Visa & Passaporte";

// Quebra de linha para Linux (Locaweb)
$quebra_linha = "\n";

// =============================================
// FUNÇÕES AUXILIARES
// =============================================

/**
 * Redireciona com status
 */
function redirecionar($status, $campo = '')
{
    $url = "avaliacao.html?status=" . $status;
    if ($campo) {
        $url .= "&campo=" . $campo;
    }
    header("Location: " . $url);
    exit;
}

/**
 * Sanitiza dados do formulário
 */
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

/**
 * Salva backup em CSV
 */
function salvarBackup($nome, $email, $telefone, $pais, $visto, $mensagem)
{
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

// =============================================
// VALIDAÇÃO DO FORMULÁRIO
// =============================================

// Só aceita POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecionar("erro_metodo");
}

// Coleta os dados
$nome     = limpar($_POST["nome"] ?? "");
$email    = limpar($_POST["email"] ?? "");
$telefone = limpar($_POST["telefone"] ?? "");
$pais     = limpar($_POST["pais"] ?? "");
$visto    = limpar($_POST["visto"] ?? "");
$mensagem = limpar($_POST["mensagem"] ?? "");

// Validações obrigatórias
if (empty($nome)) {
    redirecionar("erro", "nome");
}

if (empty($email)) {
    redirecionar("erro", "email");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirecionar("erro", "email_invalido");
}

// Campos opcionais com valor padrão
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

$assunto = "{$assunto_base} – {$nome}";

// Corpo em HTML
$corpo_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #431222;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f4f5;
            padding: 20px;
            border: 1px solid #c9a0a8;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .field {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            color: #431222;
            margin-bottom: 5px;
        }
        .field-value {
            background: white;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ede6e7;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #696566;
        }
        hr {
            border: none;
            border-top: 1px solid #c9a0a8;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nova Avaliação de Perfil</h2>
            <p>Visa & Passaporte Consultoria</p>
        </div>
        <div class="content">
            <div class="field">
                <div class="field-label">Nome completo:</div>
                <div class="field-value">' . htmlspecialchars($nome) . '</div>
            </div>
            <div class="field">
                <div class="field-label">E-mail:</div>
                <div class="field-value">' . htmlspecialchars($email) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Telefone/WhatsApp:</div>
                <div class="field-value">' . htmlspecialchars($telefone) . '</div>
            </div>
            <div class="field">
                <div class="field-label">País de destino:</div>
                <div class="field-value">' . htmlspecialchars($pais) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Tipo de visto:</div>
                <div class="field-value">' . htmlspecialchars($visto_exibicao) . '</div>
            </div>
            <div class="field">
                <div class="field-label">Mensagem:</div>
                <div class="field-value">' . nl2br(htmlspecialchars($mensagem)) . '</div>
            </div>
            <hr>
            <div class="field">
                <div class="field-label">Recebido em:</div>
                <div class="field-value">' . date("d/m/Y H:i:s") . '</div>
            </div>
        </div>
        <div class="footer">
            <p>E-mail enviado automaticamente pelo sistema Visa & Passaporte.</p>
        </div>
    </div>
</body>
</html>';

// Corpo em texto plano (fallback)
$corpo_texto = "Nova avaliação de perfil recebida pelo site.\n\n";
$corpo_texto .= "============================================\n";
$corpo_texto .= "DADOS DO SOLICITANTE\n";
$corpo_texto .= "============================================\n";
$corpo_texto .= "Nome completo:      $nome\n";
$corpo_texto .= "E-mail:             $email\n";
$corpo_texto .= "Telefone/WhatsApp:  $telefone\n";
$corpo_texto .= "País de destino:    $pais\n";
$corpo_texto .= "Tipo de visto:      $visto_exibicao\n\n";
$corpo_texto .= "MENSAGEM:\n";
$corpo_texto .= "$mensagem\n\n";
$corpo_texto .= "============================================\n";
$corpo_texto .= "Recebido em: " . date("d/m/Y H:i:s") . "\n";
$corpo_texto .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Não identificado') . "\n";

// =============================================
// HEADERS PARA LOCAWEB
// =============================================

$headers = "MIME-Version: 1.0{$quebra_linha}";
$headers .= "Content-type: text/html; charset=UTF-8{$quebra_linha}";
$headers .= "From: {$nome_remetente} <{$email_remetente}>{$quebra_linha}";
$headers .= "Reply-To: {$nome} <{$email}>{$quebra_linha}";
$headers .= "Return-Path: {$email_remetente}{$quebra_linha}";
$headers .= "X-Mailer: PHP/" . phpversion() . $quebra_linha;
$headers .= "X-Priority: 3{$quebra_linha}";

// Parâmetro -r obrigatório na Locaweb
$parametros_extra = "-r{$email_remetente}";

// =============================================
// TENTATIVA DE ENVIO
// =============================================

$enviado = false;

if (function_exists('mail')) {
    // Tenta enviar com HTML
    $enviado = mail($email_destino, $assunto, $corpo_html, $headers, $parametros_extra);

    // Se falhar, tenta com texto plano
    if (!$enviado) {
        $headers_texto = "MIME-Version: 1.0{$quebra_linha}";
        $headers_texto .= "Content-type: text/plain; charset=UTF-8{$quebra_linha}";
        $headers_texto .= "From: {$nome_remetente} <{$email_remetente}>{$quebra_linha}";
        $headers_texto .= "Reply-To: {$nome} <{$email}>{$quebra_linha}";
        $headers_texto .= "Return-Path: {$email_remetente}{$quebra_linha}";

        $enviado = mail($email_destino, $assunto, $corpo_texto, $headers_texto, $parametros_extra);
    }

    if ($enviado) {
        // Salva backup
        salvarBackup($nome, $email, $telefone, $pais, $visto_exibicao, $mensagem);
        redirecionar("sucesso");
    } else {
        error_log("[VisaPassaporte] Erro no envio: " . print_r(error_get_last(), true));
    }
}

// =============================================
// FALLBACK - SALVA EM ARQUIVO
// =============================================

if (!$enviado) {
    $backup_dir = __DIR__ . '/_backup_avaliacoes';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }

    $arquivo = $backup_dir . '/avaliacao_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.html';
    $conteudo = "<!DOCTYPE html>\n<html>\n<head>\n<meta charset='UTF-8'>\n<title>Avaliação - {$nome}</title>\n</head>\n<body>\n";
    $conteudo .= "<h2>Nova Avaliação de Perfil</h2>\n";
    $conteudo .= "<p><strong>Data:</strong> " . date("d/m/Y H:i:s") . "</p>\n";
    $conteudo .= "<p><strong>Nome:</strong> {$nome}</p>\n";
    $conteudo .= "<p><strong>E-mail:</strong> {$email}</p>\n";
    $conteudo .= "<p><strong>Telefone:</strong> {$telefone}</p>\n";
    $conteudo .= "<p><strong>País de destino:</strong> {$pais}</p>\n";
    $conteudo .= "<p><strong>Tipo de visto:</strong> {$visto_exibicao}</p>\n";
    $conteudo .= "<p><strong>Mensagem:</strong><br>" . nl2br(htmlspecialchars($mensagem)) . "</p>\n";
    $conteudo .= "</body>\n</html>";

    if (file_put_contents($arquivo, $conteudo)) {
        salvarBackup($nome, $email, $telefone, $pais, $visto_exibicao, $mensagem);
        redirecionar("sucesso");
    } else {
        redirecionar("erro_fatal");
    }
}

// Se chegou até aqui, erro fatal
redirecionar("erro_fatal");
?>