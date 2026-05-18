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

$mensagem = '';
$erro = '';
$materia_editar = null;

// Busca professores
$query_prof = "SELECT id_professor, nome FROM Professores ORDER BY nome";
$resultado_prof = mysqli_query($conexao, $query_prof);
$professores = mysqli_fetch_all($resultado_prof, MYSQLI_ASSOC);

// Verifica se é edição
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $query_ed = "SELECT * FROM Disciplinas WHERE id_disciplina = ?";
    $stmt_ed = mysqli_prepare($conexao, $query_ed);
    mysqli_stmt_bind_param($stmt_ed, "i", $id_editar);
    mysqli_stmt_execute($stmt_ed);
    $materia_editar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_ed));
}

// Exclui matéria
if (isset($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    $query_del = "DELETE FROM Disciplinas WHERE id_disciplina = ?";
    $stmt_del = mysqli_prepare($conexao, $query_del);
    mysqli_stmt_bind_param($stmt_del, "i", $id_excluir);
    if (mysqli_stmt_execute($stmt_del)) {
        $mensagem = "Matéria removida com sucesso!";
    } else {
        $erro = "Erro ao remover matéria. Ela pode estar vinculada a horários!";
    }
}

// Cadastra ou edita matéria
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['materia'] ?? '';
    $id_disciplina = $_POST['id_disciplina'] ?? null;

    if ($nome) {
        if ($id_disciplina) {
            // Editar
            $query = "UPDATE Disciplinas SET nome_disciplina=? WHERE id_disciplina=?";
            $stmt = mysqli_prepare($conexao, $query);
            mysqli_stmt_bind_param($stmt, "si", $nome, $id_disciplina);
            $acao = "atualizada";
        } else {
            // Cadastrar
            $query = "INSERT INTO Disciplinas (nome_disciplina, id_curso, carga_horaria) VALUES (?, 1, 60)";
            $stmt = mysqli_prepare($conexao, $query);
            mysqli_stmt_bind_param($stmt, "s", $nome);
            $acao = "cadastrada";
        }

        if (mysqli_stmt_execute($stmt)) {
            $mensagem = "Matéria $acao com sucesso!";
            $materia_editar = null;
        } else {
            $erro = "Erro ao salvar matéria.";
        }
    } else {
        $erro = "Preencha o nome da matéria!";
    }
}

// Busca matérias cadastradas
$query_mat = "SELECT d.id_disciplina, d.nome_disciplina, p.nome AS professor
              FROM Disciplinas d
              LEFT JOIN Horarios h ON h.id_disciplina = d.id_disciplina
              LEFT JOIN Professores p ON h.id_professor = p.id_professor
              ORDER BY d.nome_disciplina";
$resultado_mat = mysqli_query($conexao, $query_mat);
$materias = mysqli_fetch_all($resultado_mat, MYSQLI_ASSOC);
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
                <?php if ($_SESSION['tipo'] == 2 || $_SESSION['tipo'] == 3): ?>
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
            <h1>Cadastros</h1>
            <div class="detalhes-card">
                <h2><?= $materia_editar ? 'Editar Matéria' : 'Cadastrar Matéria' ?></h2>

                <?php if ($mensagem): ?>
                    <p class="msg-sucesso"><?= $mensagem ?></p>
                <?php endif; ?>
                <?php if ($erro): ?>
                    <p class="msg-erro"><?= $erro ?></p>
                <?php endif; ?>

                <div class="reserva-form">
                    <?php if ($materia_editar): ?>
                    <input type="hidden" id="id_disciplina" value="<?= $materia_editar['id_disciplina'] ?>">
                    <?php endif; ?>

                    <div class="detalhe-item">
                        <div class="detalhe-label">Matéria</div>
                        <input type="text" id="materia" placeholder="Nome da Matéria"
                               class="input-reserva"
                               value="<?= $materia_editar['nome_disciplina'] ?? '' ?>">
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Professor</div>
                        <select id="professor" class="input-reserva">
                            <option value="">Selecione o professor</option>
                            <?php foreach ($professores as $prof): ?>
                            <option value="<?= $prof['id_professor'] ?>"><?= $prof['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button onclick="salvar()" class="btn-reservar">
                            <?= $materia_editar ? 'Salvar alterações' : 'Cadastrar' ?>
                        </button>
                        <?php if ($materia_editar): ?>
                        <a href="cadastro_materia.php" class="btn-cancelar">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de matérias -->
                <?php if (!empty($materias)): ?>
                <h3 style="margin-top:25px; color:#1a3a6b;">Matérias cadastradas</h3>
                <table class="tabela-horarios" style="margin-top:10px;">
                    <thead>
                        <tr>
                            <th>Matéria</th>
                            <th>Professor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materias as $mat): ?>
                        <tr>
                            <td><?= $mat['nome_disciplina'] ?></td>
                            <td><?= $mat['professor'] ?? '-' ?></td>
                            <td>
                                <a href="cadastro_materia.php?editar=<?= $mat['id_disciplina'] ?>"
                                   class="btn-acao btn-editar">Editar</a>
                                <a href="cadastro_materia.php?excluir=<?= $mat['id_disciplina'] ?>"
                                   class="btn-acao btn-excluir"
                                   onclick="return confirm('Deseja remover esta matéria?')">Excluir</a>
                            </td>
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
        function salvar() {
            const materia = document.getElementById('materia').value;
            const professor = document.getElementById('professor').value;
            const id_disciplina = document.getElementById('id_disciplina')?.value ?? '';

            if (!materia) {
                alert('Preencha o nome da matéria!');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'cadastro_materia.php';

            const campos = { materia, professor, id_disciplina };
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