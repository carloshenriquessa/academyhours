<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

// Restrição de acesso - admin e professor
if ($_SESSION['tipo'] != 2 && $_SESSION['tipo'] != 3) {
    header('Location: dashboard.php');
    exit;
}

$nome_sala = $_GET['nome'] ?? 'Sala';
$mensagem = '';
$erro = '';

// Busca professores do banco
$query_prof = "SELECT id_professor, nome FROM Professores ORDER BY nome";
$resultado_prof = mysqli_query($conexao, $query_prof);
$professores = mysqli_fetch_all($resultado_prof, MYSQLI_ASSOC);

// Busca reservas da sala
$query_res = "SELECT r.dia_semana, r.hora_inicio, r.hora_fim, p.nome AS professor
              FROM Reservas r
              JOIN Professores p ON r.id_professor = p.id_professor
              WHERE r.nome_sala = ?
              ORDER BY r.dia_semana, r.hora_inicio";
$stmt_res = mysqli_prepare($conexao, $query_res);
mysqli_stmt_bind_param($stmt_res, "s", $nome_sala);
mysqli_stmt_execute($stmt_res);
$resultado_reservas = mysqli_stmt_get_result($stmt_res);
$reservas = mysqli_fetch_all($resultado_reservas, MYSQLI_ASSOC);

$horarios = ['07:00', '08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00', '19:00', '20:00', '21:00'];

// Salva reserva no banco
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_professor = $_POST['professor'] ?? '';
    $dia = $_POST['dia'] ?? '';
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fim = $_POST['hora_fim'] ?? '';
    $sala = $_POST['nome_sala'] ?? $nome_sala;

    if ($id_professor && $dia && $hora_inicio && $hora_fim) {
        if ($hora_inicio >= $hora_fim) {
            $erro = "O horário de término deve ser maior que o de início!";
        } else {
            // Verifica conflito de horário
            $query_conflito = "SELECT id_reserva FROM Reservas 
                               WHERE nome_sala = ? AND dia_semana = ?
                               AND hora_inicio < ? AND hora_fim > ?";
            $stmt_c = mysqli_prepare($conexao, $query_conflito);
            mysqli_stmt_bind_param($stmt_c, "ssss", $sala, $dia, $hora_fim, $hora_inicio);
            mysqli_stmt_execute($stmt_c);
            $res_c = mysqli_stmt_get_result($stmt_c);

            if (mysqli_num_rows($res_c) > 0) {
                $erro = "Já existe uma reserva nesse horário para esta sala!";
            } else {
                $query_insert = "INSERT INTO Reservas (nome_sala, id_professor, dia_semana, hora_inicio, hora_fim)
                                 VALUES (?, ?, ?, ?, ?)";
                $stmt_i = mysqli_prepare($conexao, $query_insert);
                mysqli_stmt_bind_param($stmt_i, "sisss", $sala, $id_professor, $dia, $hora_inicio, $hora_fim);

                if (mysqli_stmt_execute($stmt_i)) {
                    $mensagem = "Sala reservada com sucesso!";
                    mysqli_stmt_execute($stmt_res);
                    $resultado_reservas = mysqli_stmt_get_result($stmt_res);
                    $reservas = mysqli_fetch_all($resultado_reservas, MYSQLI_ASSOC);
                } else {
                    $erro = "Erro ao salvar reserva!";
                }
            }
        }
    } else {
        $erro = "Preencha todos os campos!";
    }
}
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
                <?php if ($_SESSION['tipo'] == 2 || $_SESSION['tipo'] == 3): ?>
                <a href="reserva.php" class="active">Reservar Salas</a>
                <?php endif; ?>
                <?php if ($_SESSION['tipo'] == 2): ?>
                <a href="cadastro.php">Cadastros</a>
                <?php endif; ?>
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
                        <select name="professor" id="professor" class="input-reserva">
                            <option value="">Selecione o professor</option>
                            <?php foreach ($professores as $prof): ?>
                            <option value="<?= $prof['id_professor'] ?>"><?= $prof['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Data</div>
                        <input type="date" id="data" class="input-reserva"
                               min="<?= date('Y-m-d') ?>"
                               onchange="atualizarDia(this.value)">
                        <input type="hidden" name="dia" id="dia_semana">
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Início</div>
                        <select name="hora_inicio" id="hora_inicio" class="input-reserva">
                            <option value="">Selecione o início</option>
                            <?php foreach ($horarios as $h): ?>
                            <option value="<?= $h ?>"><?= $h ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="detalhe-item">
                        <div class="detalhe-label">Término</div>
                        <select name="hora_fim" id="hora_fim" class="input-reserva">
                            <option value="">Selecione o término</option>
                            <?php foreach ($horarios as $h): ?>
                            <option value="<?= $h ?>"><?= $h ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button onclick="reservar()" class="btn-reservar">Reservar data</button>
                </div>

                <!-- Lista de reservas da sala -->
                <?php if (!empty($reservas)): ?>
                <h3 style="margin-top:25px; color:#1a3a6b;">Reservas desta sala</h3>
                <table class="tabela-horarios" style="margin-top:10px;">
                    <thead>
                        <tr>
                            <th>Dia</th>
                            <th>Professor</th>
                            <th>Início</th>
                            <th>Término</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $res): ?>
                        <tr>
                            <td><?= $res['dia_semana'] ?></td>
                            <td><?= $res['professor'] ?></td>
                            <td><?= date('H:i', strtotime($res['hora_inicio'])) ?></td>
                            <td><?= date('H:i', strtotime($res['hora_fim'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <a href="reserva.php" class="btn-voltar">← Voltar</a>
            </div>
        </div>

    </div>

    <script>
        function atualizarDia(valor) {
            const dias = ['Domingo', 'Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sábado'];
            const data = new Date(valor + 'T00:00:00');
            const nomeDia = dias[data.getDay()];
            document.getElementById('dia_semana').value = nomeDia;
        }

        function reservar() {
            const professor = document.getElementById('professor').value;
            const dia = document.getElementById('dia_semana').value;
            const data = document.getElementById('data').value;
            const inicio = document.getElementById('hora_inicio').value;
            const fim = document.getElementById('hora_fim').value;

            if (!professor || !data || !inicio || !fim) {
                alert('Preencha todos os campos!');
                return;
            }

            if (inicio >= fim) {
                alert('O horário de término deve ser maior que o de início!');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'reserva_detalhes.php?nome=<?= urlencode($nome_sala) ?>';

            const campos = {
                professor,
                dia: dia,
                hora_inicio: inicio,
                hora_fim: fim,
                nome_sala: '<?= $nome_sala ?>'
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