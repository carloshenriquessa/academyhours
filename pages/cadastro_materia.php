<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$mensagem = '';
$erro = '';

// Busca professores
$query_prof = "SELECT id_professor, nome FROM Professores ORDER BY nome";
$resultado_prof = mysqli_query($conexao, $query_prof);
$professores = mysqli_fetch_all($resultado_prof, MYSQLI_ASSOC);

// Busca matérias cadastradas
$query_mat = "SELECT d.nome_disciplina, p.nome AS professor
              FROM Disciplinas d
              LEFT JOIN Horarios h ON h.id_disciplina = d.id_disciplina
              LEFT JOIN Professores p ON h.id_professor = p.id_professor
              ORDER BY d.nome_disciplina";
$resultado_mat = mysqli_query($conexao, $query_mat);
$materias = mysqli_fetch_all($resultado_mat, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['materia'] ?? '';
    $id_professor = $_POST['professor'] ?? null;

    if ($nome) {
        $query = "INSERT INTO Disciplinas (nome_disciplina, id_curso, carga_horaria) VALUES (?, 1, 60)";
        $stmt = mysqli_prepare($conexao, $query);
        mysqli_stmt_bind_param($stmt, "s", $nome);

        if (mysqli_stmt_execute($stmt)) {
            $mensagem = "Matéria cadastrada com sucesso!";
            $resultado_mat = mysqli_query($conexao, $query_mat);
            $materias = mysqli_fetch_all($resultado_mat, MYSQLI_ASSOC);
        } else {
            $erro = "Erro ao cadastrar matéria.";
        }
    } else {
        $erro = "Preencha o nome da matéria!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademyHours - Cadastrar Matéria</title>
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
                <a href="reserva.php">Reservar Salas</a>
                <a href="cadastro.php" class="active">Cadastros</a>
                <a href="../includes/logout.php">Sair</a>
            </nav>
        </div>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <h1>Cadastros</h1>
            <div class="detalhes-card">
                <h2>Cadastrar Matéria</h2>

                <?php if ($mensagem): ?>
                    <p class="msg-sucesso"><?= $mensagem ?></p>
                <?php endif; ?>
                <?php if ($erro): ?>
                    <p class="msg-erro"><?= $erro ?></p>
                <?php endif; ?>

                <div class="reserva-form">
                    <div class="detalhe-item">
                        <div class="detalhe-label">Matéria</div>
                        <input type="text" name="materia" id="materia" 
                               placeholder="Nome da Matéria" class="input-reserva">
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Professor</div>
                        <select name="professor" id="professor" class="input-reserva">
                            <option value="">Selecione o professor</option>
                            <?php foreach ($professores as $prof): ?>
                            <option value="<?= $prof['id_professor'] ?>"><?= $prof['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button onclick="cadastrar()" class="btn-reservar">Cadastrar</button>
                </div>

                <!-- Lista de matérias cadastradas -->
                <?php if (!empty($materias)): ?>
                <h3 style="margin-top:25px; color:#1a3a6b;">Matérias cadastradas</h3>
                <table class="tabela-horarios" style="margin-top:10px;">
                    <thead>
                        <tr>
                            <th>Matéria</th>
                            <th>Professor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materias as $mat): ?>
                        <tr>
                            <td><?= $mat['nome_disciplina'] ?></td>
                            <td><?= $mat['professor'] ?? '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <a href="cadastro.php" class="btn-voltar">← Voltar</a>
            </div>
        </div>

    </div>

    <script>
        function cadastrar() {
            const materia = document.getElementById('materia').value;

            if (!materia) {
                alert('Preencha o nome da matéria!');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'cadastro_materia.php';

            const campos = {
                materia,
                professor: document.getElementById('professor').value
            };

            for (const [key, value] of Object.entries(campos)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>