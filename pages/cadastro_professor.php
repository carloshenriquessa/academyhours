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
$professor_editar = null;

// Busca especialidades
$query_esp = "SELECT id_especialidade, nome FROM Especialidade ORDER BY nome";
$resultado_esp = mysqli_query($conexao, $query_esp);
$especialidades = mysqli_fetch_all($resultado_esp, MYSQLI_ASSOC);

// Verifica se é edição
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $query_ed = "SELECT * FROM Professores WHERE id_professor = ?";
    $stmt_ed = mysqli_prepare($conexao, $query_ed);
    mysqli_stmt_bind_param($stmt_ed, "i", $id_editar);
    mysqli_stmt_execute($stmt_ed);
    $professor_editar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_ed));
}

// Exclui professor
if (isset($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    $query_del = "DELETE FROM Professores WHERE id_professor = ?";
    $stmt_del = mysqli_prepare($conexao, $query_del);
    mysqli_stmt_bind_param($stmt_del, "i", $id_excluir);
    if (mysqli_stmt_execute($stmt_del)) {
        $mensagem = "Professor removido com sucesso!";
    } else {
        $erro = "Erro ao remover professor. Ele pode estar vinculado a horários!";
    }
}

// Cadastra ou edita professor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['professor'] ?? '';
    $email = $_POST['email'] ?? '';
    $especialidade = $_POST['especialidade'] ?? null;
    $id_prof = $_POST['id_professor'] ?? null;

    if ($nome && $email) {
        if ($id_prof) {
            // Editar
            $query = "UPDATE Professores SET nome=?, email=?, id_especialidade=? WHERE id_professor=?";
            $stmt = mysqli_prepare($conexao, $query);
            mysqli_stmt_bind_param($stmt, "ssii", $nome, $email, $especialidade, $id_prof);
            $acao = "atualizado";
        } else {
            // Cadastrar
            $query = "INSERT INTO Professores (nome, email, id_especialidade) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conexao, $query);
            mysqli_stmt_bind_param($stmt, "ssi", $nome, $email, $especialidade);
            $acao = "cadastrado";
        }

        if (mysqli_stmt_execute($stmt)) {
            $mensagem = "Professor $acao com sucesso!";
            $professor_editar = null;
        } else {
            $erro = "Erro ao salvar professor. Verifique se o e-mail já está cadastrado.";
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios!";
    }
}

// Busca professores cadastrados
$query_prof = "SELECT p.id_professor, p.nome, p.email, e.nome AS especialidade 
               FROM Professores p
               LEFT JOIN Especialidade e ON p.id_especialidade = e.id_especialidade
               ORDER BY p.nome";
$resultado_prof = mysqli_query($conexao, $query_prof);
$professores = mysqli_fetch_all($resultado_prof, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademyHours - Cadastrar Professor</title>
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
                <h2><?= $professor_editar ? 'Editar Professor' : 'Cadastrar Professor' ?></h2>

                <?php if ($mensagem): ?>
                    <p class="msg-sucesso"><?= $mensagem ?></p>
                <?php endif; ?>
                <?php if ($erro): ?>
                    <p class="msg-erro"><?= $erro ?></p>
                <?php endif; ?>

                <div class="reserva-form">
                    <?php if ($professor_editar): ?>
                    <input type="hidden" id="id_professor" value="<?= $professor_editar['id_professor'] ?>">
                    <?php endif; ?>

                    <div class="detalhe-item">
                        <div class="detalhe-label">Professor</div>
                        <input type="text" id="professor" placeholder="Nome do Professor" 
                               class="input-reserva"
                               value="<?= $professor_editar['nome'] ?? '' ?>">
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">E-mail</div>
                        <input type="email" id="email" placeholder="E-mail do Professor" 
                               class="input-reserva"
                               value="<?= $professor_editar['email'] ?? '' ?>">
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Especialidade</div>
                        <select id="especialidade" class="input-reserva">
                            <option value="">Selecione a especialidade</option>
                            <?php foreach ($especialidades as $esp): ?>
                            <option value="<?= $esp['id_especialidade'] ?>"
                                <?= isset($professor_editar['id_especialidade']) && $professor_editar['id_especialidade'] == $esp['id_especialidade'] ? 'selected' : '' ?>>
                                <?= $esp['nome'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button onclick="salvar()" class="btn-reservar">
                            <?= $professor_editar ? 'Salvar alterações' : 'Cadastrar' ?>
                        </button>
                        <?php if ($professor_editar): ?>
                        <a href="cadastro_professor.php" class="btn-cancelar">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de professores -->
                <?php if (!empty($professores)): ?>
                <h3 style="margin-top:25px; color:#1a3a6b;">Professores cadastrados</h3>
                <table class="tabela-horarios" style="margin-top:10px;">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Especialidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($professores as $prof): ?>
                        <tr>
                            <td><?= $prof['nome'] ?></td>
                            <td><?= $prof['email'] ?></td>
                            <td><?= $prof['especialidade'] ?? '-' ?></td>
                            <td>
                                <a href="cadastro_professor.php?editar=<?= $prof['id_professor'] ?>" 
                                   class="btn-acao btn-editar">Editar</a>
                                <a href="cadastro_professor.php?excluir=<?= $prof['id_professor'] ?>" 
                                   class="btn-acao btn-excluir"
                                   onclick="return confirm('Deseja remover este professor?')">Excluir</a>
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
            const professor = document.getElementById('professor').value;
            const email = document.getElementById('email').value;
            const especialidade = document.getElementById('especialidade').value;
            const id_professor = document.getElementById('id_professor')?.value ?? '';

            if (!professor || !email) {
                alert('Preencha pelo menos o nome e e-mail!');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'cadastro_professor.php';

            const campos = { professor, email, especialidade, id_professor };
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