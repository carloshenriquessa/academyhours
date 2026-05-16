<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

// Restrição de acesso - apenas admin
if ($_SESSION['tipo'] != 2) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademyHours - Cadastros</title>
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
                <?php if ($_SESSION['tipo'] == 2): ?>
                <a href="reserva.php">Reservar Salas</a>
                <?php endif; ?>
                <?php if ($_SESSION['tipo'] == 2): ?>
                <a href="cadastro.php" class="active">Cadastros</a>
                <?php endif; ?>
                <a href="../includes/logout.php">Sair</a>
            </nav>
        </div>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <h1>Cadastrar</h1>
            <div class="cadastro-opcoes">
                <a href="cadastro_professor.php" class="cadastro-card">
                    Cadastrar Professor
                </a>
                <a href="cadastro_materia.php" class="cadastro-card">
                    Cadastrar Matéria
                </a>
            </div>
        </div>

    </div>
</body>
</html>