<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$nome_sala = $_GET['nome'] ?? 'Sala';
$mensagem = '';
$erro = '';

// Busca professores do banco
$query_prof = "SELECT id_professor, nome FROM Professores ORDER BY nome";
$resultado_prof = mysqli_query($conexao, $query_prof);
$professores = mysqli_fetch_all($resultado_prof, MYSQLI_ASSOC);

$dias = ['Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sábado'];
$horarios = ['07:00', '08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00', '19:00', '20:00', '21:00'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademyHours - Reservar <?= $nome_sala ?></title>
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
            <div class="detalhes-card">
                <h2><?= $nome_sala ?></h2>

                <?php if ($mensagem): ?>
                    <p class="msg-sucesso"><?= $mensagem ?></p>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <p class="msg-erro"><?= $erro ?></p>
                <?php endif; ?>

                <div class="reserva-form">
                    <div class="detalhe-item">
                        <div class="detalhe-label">Professor</div>
                        <select id="professor" class="input-reserva">
                            <option value="">Selecione o professor</option>
                            <?php foreach ($professores as $prof): ?>
                            <option value="<?= $prof['nome'] ?>"><?= $prof['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Dia</div>
                        <select id="dia" class="input-reserva">
                            <option value="">Selecione o dia</option>
                            <?php foreach ($dias as $dia): ?>
                            <option value="<?= $dia ?>"><?= $dia ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Início</div>
                        <select id="hora_inicio" class="input-reserva">
                            <option value="">Selecione o início</option>
                            <?php foreach ($horarios as $h): ?>
                            <option value="<?= $h ?>"><?= $h ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Término</div>
                        <select id="hora_fim" class="input-reserva">
                            <option value="">Selecione o término</option>
                            <?php foreach ($horarios as $h): ?>
                            <option value="<?= $h ?>"><?= $h ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button onclick="reservar()" class="btn-reservar">Reservar data</button>
                </div>
                <a href="reserva.php" class="btn-voltar">← Voltar</a>
            </div>
        </div>

    </div>

    <script>
        function reservar() {
            const professor = document.getElementById('professor').value;
            const dia = document.getElementById('dia').value;
            const inicio = document.getElementById('hora_inicio').value;
            const fim = document.getElementById('hora_fim').value;

            if (!professor || !dia || !inicio || !fim) {
                alert('Preencha todos os campos!');
                return;
            }

            if (inicio >= fim) {
                alert('O horário de término deve ser maior que o de início!');
                return;
            }

            alert(`Sala reservada com sucesso!\nProfessor: ${professor}\nDia: ${dia}\nHorário: ${inicio} até ${fim}`);
        }
    </script>
</body>
</html>