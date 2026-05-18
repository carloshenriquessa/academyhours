<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$query = "SELECT id_disciplina, nome_disciplina FROM Disciplinas ORDER BY nome_disciplina";
$resultado = mysqli_query($conexao, $query);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademyHours - Meus Cursos</title>
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
                <a href="cursos.php" class="active">Meus cursos</a>
                <?php if ($_SESSION['tipo'] == 2 || $_SESSION['tipo'] == 3): ?>
                <a href="reserva.php">Reservar Salas</a>
                <?php endif; ?>
                <?php if ($_SESSION['tipo'] == 2): ?>
                <a href="cadastro.php">Cadastros</a>
                <?php endif; ?>
                <a href="../includes/logout.php">Sair</a>
            </nav>
        </div>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <h1>Meus cursos</h1>
            <div class="cursos-grid">
                <?php while ($disciplina = mysqli_fetch_assoc($resultado)): ?>
                <a href="curso_detalhes.php?id=<?= $disciplina['id_disciplina'] ?>" class="curso-card">
                    <span><?= $disciplina['nome_disciplina'] ?></span>
                </a>
                <?php endwhile; ?>
            </div>
        </div>

    </div>
</body>
</html>