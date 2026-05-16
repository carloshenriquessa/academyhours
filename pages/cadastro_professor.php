<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$mensagem = '';
$erro = '';

// Busca especialidades
$query_esp = "SELECT id_especialidade, nome FROM Especialidade ORDER BY nome";
$resultado_esp = mysqli_query($conexao, $query_esp);
$especialidades = mysqli_fetch_all($resultado_esp, MYSQLI_ASSOC);

// Busca professores cadastrados
$query_prof = "SELECT p.nome, e.nome AS especialidade 
               FROM Professores p
               LEFT JOIN Especialidade e ON p.id_especialidade = e.id_especialidade
               ORDER BY p.nome";
$resultado_prof = mysqli_query($conexao, $query_prof);
$professores = mysqli_fetch_all($resultado_prof, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['professor'] ?? '';
    $email = $_POST['email'] ?? '';
    $especialidade = $_POST['especialidade'] ?? null;

    if ($nome && $email) {
        $query = "INSERT INTO Professores (nome, email, id_especialidade) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $nome, $email, $especialidade);

        if (mysqli_stmt_execute($stmt)) {
            $mensagem = "Professor cadastrado com sucesso!";
            // Atualiza lista
            $resultado_prof = mysqli_query($conexao, $query_prof);
            $professores = mysqli_fetch_all($resultado_prof, MYSQLI_ASSOC);
        } else {
            $erro = "Erro ao cadastrar professor. Verifique se o e-mail já está cadastrado.";
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios!";
    }
}
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
                <a href="reserva.php">Reservar Salas</a>
                <a href="cadastro.php" class="active">Cadastros</a>
                <a href="../includes/logout.php">Sair</a>
            </nav>
        </div>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <h1>Cadastros</h1>
            <div class="detalhes-card">
                <h2>Cadastrar Professor</h2>

                <?php if ($mensagem): ?>
                    <p class="msg-sucesso"><?= $mensagem ?></p>
                <?php endif; ?>
                <?php if ($erro): ?>
                    <p class="msg-erro"><?= $erro ?></p>
                <?php endif; ?>

                <div class="reserva-form">
                    <div class="detalhe-item">
                        <div class="detalhe-label">Professor</div>
                        <input type="text" name="professor" id="professor" 
                               placeholder="Nome do Professor" class="input-reserva">
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">E-mail</div>
                        <input type="email" name="email" id="email" 
                               placeholder="E-mail do Professor" class="input-reserva">
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Especialidade</div>
                        <select name="especialidade" id="especialidade" class="input-reserva">
                            <option value="">Selecione a especialidade</option>
                            <?php foreach ($especialidades as $esp): ?>
                            <option value="<?= $esp['id_especialidade'] ?>"><?= $esp['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button onclick="cadastrar()" class="btn-reservar">Cadastrar</button>
                </div>

                <!-- Lista de professores cadastrados -->
                <?php if (!empty($professores)): ?>
                <h3 style="margin-top:25px; color:#1a3a6b;">Professores cadastrados</h3>
                <table class="tabela-horarios" style="margin-top:10px;">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Especialidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($professores as $prof): ?>
                        <tr>
                            <td><?= $prof['nome'] ?></td>
                            <td><?= $prof['especialidade'] ?? '-' ?></td>
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
            const professor = document.getElementById('professor').value;
            const email = document.getElementById('email').value;
            const especialidade = document.getElementById('especialidade').value;

            if (!professor || !email) {
                alert('Preencha pelo menos o nome e e-mail!');
                return;
            }

            // Submete via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'cadastro_professor.php';

            const campos = { professor, email, especialidade };
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