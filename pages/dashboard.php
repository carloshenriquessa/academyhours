<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$query = "SELECT h.dia_semana, d.nome_disciplina, p.nome AS professor, h.hora_inicio, h.hora_fim 
          FROM Horarios h
          JOIN Disciplinas d ON h.id_disciplina = d.id_disciplina
          JOIN Professores p ON h.id_professor = p.id_professor
          ORDER BY h.id_horario";

$resultado = mysqli_query($conexao, $query);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademyHours - Dashboard</title>
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
                <a href="dashboard.php" class="active">Visualizar horários</a>
                <a href="cursos.php">Meus cursos</a>
                <a href="reserva.php">Reservar Salas</a>
                <a href="cadastro.php">Cadastros</a>
                <a href="../includes/logout.php">Sair</a>
            </nav>
        </div>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <h1>Horários</h1>
            <table class="tabela-horarios">
                <thead>
                    <tr>
                        <th>Dia</th>
                        <th>Disciplina</th>
                        <th>Professor</th>
                        <th>Horário</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?= $row['dia_semana'] ?></td>
                        <td><?= $row['nome_disciplina'] ?></td>
                        <td><?= $row['professor'] ?></td>
                        <td><?= date('H:i', strtotime($row['hora_inicio'])) ?> | <?= date('H:i', strtotime($row['hora_fim'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>