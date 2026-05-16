<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$salas = [
    ['id' => 1, 'nome' => 'Anfiteatro I'],
    ['id' => 2, 'nome' => 'Anfiteatro II'],
    ['id' => 3, 'nome' => 'Sala de Robótica'],
    ['id' => 4, 'nome' => 'Sala Informática'],
    ['id' => 5, 'nome' => 'Quadra de Esportes'],
    ['id' => 6, 'nome' => 'Laboratório'],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademyHours - Reservar Salas</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">

        <!-- Menu Lateral -->
        <div class="sidebar">
            <div class="sidebar-logo">
                <h2>Academy<br>Hours</h2>
            </div>
            <nav>
                <a href="dashboard.php">Visualizar horários</a>
                <a href="cursos.php">Meus cursos</a>
                <a href="reserva.php" class="active">Reservar Salas</a>
                <a href="cadastro.php">Cadastros</a>
                <a href="../includes/logout.php">Sair</a>
            </nav>
        </div>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <h1>Reservar Sala</h1>
            <h2 class="subtitulo">Salas disponíveis</h2>
            <div class="cursos-grid">
                <?php foreach ($salas as $sala): ?>
                <a href="reserva_detalhes.php?id=<?= $sala['id'] ?>&nome=<?= urlencode($sala['nome']) ?>" class="curso-card">
                    <span><?= $sala['nome'] ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</body>
</html>