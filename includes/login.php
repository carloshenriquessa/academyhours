<?php
session_start();
require_once '../conexao.php';

$dados = json_decode(file_get_contents('php://input'), true);
$usuario = $dados['usuario'];
$senha = $dados['senha'];

$query = "SELECT * FROM Usuarios WHERE login = ?";
$stmt = mysqli_prepare($conexao, $query);
mysqli_stmt_bind_param($stmt, "s", $usuario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($resultado);

if ($user && password_verify($senha, $user['senha'])) {
    $_SESSION['usuario'] = $user['login'];
    $_SESSION['tipo'] = $user['id_tipo_usuario'];
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário ou senha incorretos!']);
}
?>