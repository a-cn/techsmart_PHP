<?php
//CÓDIGO DE VALIDAÇÃO DE LOGIN, PUXANDO DO BANCO DE DADOS

//Verifica se o formulário foi enviado
if (!empty($_POST)){
    require_once 'conexao_sqlserver.php'; //Puxa o arquivo de conexão com o banco

	$login = $_POST["login"];
	$email = $login["email"];
	$senha = $login["senha"];
	
	//Procurar o usuário pelo email informado
	$tsql = "SELECT [usuario_id], [nome], [cpf_cnpj], [email], [senha], [fk_tipo_usuario], [ativo] FROM dbo.Usuario WHERE [email] = ?";
	$getUsuario = sqlsrv_query($conn, $tsql, array($email), array( "Scrollable" => 'static' ));
	$numUsuarios = sqlsrv_num_rows($getUsuario);
	if ($numUsuarios > 0){
		$rowUsuario = sqlsrv_fetch_array($getUsuario, SQLSRV_FETCH_ASSOC);
		sqlsrv_free_stmt($getUsuario);
		
		// Procurar o tipo do usuário
		$tipoUsuario = $rowUsuario['fk_tipo_usuario'];
		$tsql = "SELECT [descricao] FROM dbo.Tipo_Usuario WHERE [tipo_usuario_id] = ?";
		$getTipoUsuario = sqlsrv_query($conn, $tsql, array($tipoUsuario), array( "Scrollable" => 'static' ));
		if ($getTipoUsuario){
			$rowTipoUsuario = sqlsrv_fetch_array($getTipoUsuario, SQLSRV_FETCH_ASSOC);
			sqlsrv_free_stmt($getTipoUsuario);
			$descricaoTipoUsuario = strtolower($rowTipoUsuario['descricao']); //Transforma o resultado em letras minúsculas
		}

		//Verifica se o usuário está ativo
		if ($rowUsuario['ativo'] == 0) {
			header("Location: ../index.html?erro=bloqueado");
			exit;
		}

		//Compara a senha digitada com o registro no banco de dados
		if (password_verify($senha, $rowUsuario['senha'])){
			//Cria a sessão e guarda os dados do usuário na sessão
			session_start();
			$_SESSION['usuario_id'] = $rowUsuario['usuario_id'];
            $_SESSION['email'] = $rowUsuario['email'];
            $_SESSION['nome'] = $rowUsuario['nome'];
            $_SESSION['tipo_usuario'] = $descricaoTipoUsuario; //Já está em minúsculas (convertido anteriormente)
			$_SESSION['login_timestamp'] = time(); //Define o momento de início da sessão

			/*/ Redireciona para a página principal /*/
			header("location: ../front/index.php"); 			
			exit;
		} else {
			//Retorna para a página de Login com uma flag de erro
			header("Location: ../index.html?erro=login");
			exit;
		}
	}
}
?>