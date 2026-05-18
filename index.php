<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademyHours - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; background-color:#f0f0f0;">>
    <div class="login-container">
        <div class="login-box">
            <h1>AcademyHours</h1>
            <input type="text" id="usuario" placeholder="Usuário" required>
            <input type="password" id="senha" placeholder="Senha" required>
            <label>
                <input type="checkbox"> Lembrar senha
            </label>
            <button type="button" onclick="fazerLogin()">Entrar</button>
            <p id="erro" style="color:red;"></p>
        </div>
    </div>
    <script>
        function fazerLogin() {
            const usuario = document.getElementById('usuario').value;
            const senha = document.getElementById('senha').value;

            fetch('includes/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ usuario, senha })
            })
            .then(res => res.json())
            .then(data => {
                if (data.sucesso) {
                    window.location.href = 'pages/dashboard.php';
                } else {
                    document.getElementById('erro').textContent = data.mensagem;
                }
            });
        }
    </script>
</body>
</html>