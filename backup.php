<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "academyhours";

// Pastas de backup
$pasta_local = "C:/xampp/backups/academyhours/";
$data_atual = date('Y-m-d');
$hora_atual = date('H-i-s');
$dia_semana = date('N'); // 7 = domingo

// Cria a pasta se não existir
if (!file_exists($pasta_local)) {
    mkdir($pasta_local, 0777, true);
}

// Define o tipo de backup
$tipo = ($dia_semana == 7) ? "integral" : "incremental";
$nome_arquivo = $pasta_local . "backup_{$tipo}_{$data_atual}_{$hora_atual}.sql";

// Comando mysqldump
$comando = "\"C:/xampp/mysql/bin/mysqldump.exe\" --host={$host} --user={$usuario} --password={$senha} {$banco} > \"{$nome_arquivo}\"";

// Executa o backup
system($comando, $retorno);

if ($retorno === 0) {
    echo "Backup {$tipo} realizado com sucesso!\n";
    echo "Arquivo: {$nome_arquivo}\n";
    echo "Data: " . date('d/m/Y H:i:s') . "\n";

    // Remove backups incrementais com mais de 7 dias
    if ($tipo === "incremental") {
        $arquivos = glob($pasta_local . "backup_incremental_*.sql");
        foreach ($arquivos as $arquivo) {
            if (filemtime($arquivo) < strtotime('-7 days')) {
                unlink($arquivo);
                echo "Arquivo antigo removido: $arquivo\n";
            }
        }
    }
} else {
    echo "Erro ao realizar backup!\n";
}
?>