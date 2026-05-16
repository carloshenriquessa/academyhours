<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? 0;

$query = "SELECT d.nome_disciplina, p.nome AS professor,
          h.hora_inicio, h.hora_fim, h.dia_semana
          FROM Disciplinas d
          LEFT JOIN Horarios h ON h.id_disciplina = d.id_disciplina
          LEFT JOIN Professores p ON h.id_professor = p.id_professor
          WHERE d.id_disciplina = ?";

$stmt = mysqli_prepare($conexao, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$dados = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
$nome_disciplina = $dados[0]['nome_disciplina'] ?? 'Disciplina';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademyHours - <?= $nome_disciplina ?></title>
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
                <?php if ($_SESSION['tipo'] == 2): ?>
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
            <div class="detalhes-card">
                <h2><?= $nome_disciplina ?></h2>
                <?php if (empty($dados)): ?>
                    <p style="color:#888;">Nenhum horário cadastrado para esta disciplina.</p>
                <?php else: ?>
                    <?php foreach ($dados as $row): ?>
                    <?php if ($row['professor']): ?>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Professor</div>
                        <div class="detalhe-valor"><?= $row['professor'] ?></div>
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Horário</div>
                        <div class="detalhe-valor">
                            <?= date('H:i', strtotime($row['hora_inicio'])) ?> até 
                            <?= date('H:i', strtotime($row['hora_fim'])) ?>
                        </div>
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Dia</div>
                        <div class="detalhe-valor"><?= $row['dia_semana'] ?></div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="cursos.php" class="btn-voltar">← Voltar</a>
            </div>
        </div>

    </div>
</body>
</html>