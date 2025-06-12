<?php 
require_once '../back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
require_once '../back/conexao_sqlserver.php'; //Chama o arquivo de conexão com o banco de dados
$loginTimestamp = time(); //Redefine o momento de início da sessão
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta</title>
    <link rel="stylesheet" type="text/css" href="css/perfil-usuario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/janelas.css">  
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1><i class="fas fa-user-circle"></i> Minha Conta</h1>
        </div>
        
        <div class="profile-body">
            <!-- Informações Pessoais -->
            <div class="info-section">
                <h3 class="section-title"><i class="fas fa-id-card"></i> Informações Pessoais</h3>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-user"></i> Nome Completo</div>
                    <div class="info-value" id="nome">Carregando...</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-tag"></i> Tipo de Usuário</div>
                    <div class="info-value" id="tipo">
                        <span class="badge badge-primary">Carregando...</span>
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-id-badge"></i> CPF/CNPJ</div>
                    <div class="info-value" id="cpf-cnpj">Carregando...</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-birthday-cake"></i> Data de Nascimento</div>
                    <div class="info-value" id="nascimento">Carregando...</div>
                </div>
            </div>
            
            <!-- Contato -->
            <div class="info-section">
                <h3 class="section-title"><i class="fas fa-address-book"></i> Informações de Contato</h3>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-envelope"></i> E-mail</div>
                    <div class="info-value" id="email">Carregando...</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-phone"></i> Telefone Principal</div>
                    <div class="info-value" id="num-principal">Carregando...</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-phone-alt"></i> Telefone Recado</div>
                    <div class="info-value" id="num-recado">Carregando...</div>
                </div>
            </div>
            
            <!-- Endereço -->
            <div class="info-section">
                <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Endereço</h3>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-mail-bulk"></i> CEP</div>
                    <div class="info-value" id="cep">Carregando...</div>
                </div>

                <div class="info-row">
                    <div class="info-label"><i class="fas fa-city"></i> Cidade/Estado</div>
                    <div class="info-value" id="cidade-estado">Carregando...</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-road"></i> Logradouro</div>
                    <div class="info-value" id="logradouro">Carregando...</div>
                </div>

                <div class="info-row">
                    <div class="info-label"><i class="fas fa-home"></i> Número</div>
                    <div class="info-value" id="numero">Carregando...</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-map-marked-alt"></i> Bairro</div>
                    <div class="info-value" id="bairro">Carregando...</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-building"></i> Complemento</div>
                    <div class="info-value" id="complemento">Carregando...</div>
                </div>
            </div>
        </div>

<a href="index.php?pg=alterar-conta" class="btn btn-primary">
    <i class="fas fa-edit"></i> Alterar Dados
</a>
<button id="btnDesativar" class="btn btn-danger">
    <i class="fas fa-user-slash"></i> Desativar Conta
</button>
<div id="feedback-message" class="alert" style="display: none;"></div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        console.log("Iniciando carregamento de dados...");
        
        // 1. Configuração da requisição
        const response = await fetch('../back/dados_usuario.php', {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Accept': 'application/json'
            }
        });

        console.log("Status da resposta:", response.status);

        // 2. Verificação do conteúdo
        const text = await response.text();
        let data;
        
        try {
            data = JSON.parse(text);
        } catch {
            console.error("Resposta não é JSON:", text);
            throw new Error("Resposta inválida do servidor");
        }

        // 3. Tratamento da resposta
        if (!response.ok || data.status !== 'success') {
            throw new Error(data.message || "Erro ao carregar dados");
        }

        // 4. Atualização da interface
        updateProfile(data.data);

    } catch (error) {
        console.error("Erro completo:", error);
        showError(`Falha: ${error.message}`);
    }
});

    function updateProfile(userData) {
    // Mapeia todos os campos
    const fields = [
        'nome', 'email', 'tipo', 'cpf-cnpj', 'nascimento',
        'num-principal', 'num-recado', 'cep', 'cidade-estado',
        'logradouro', 'numero', 'bairro', 'complemento'
    ];

    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            let value = userData[field] || 'Não informado';
            
            // Formatação especial para o tipo de usuário
            if (field === 'tipo') {
                element.innerHTML = `<span class="badge badge-primary">${value}</span>`;
            } 
            // Formatação para CPF/CNPJ
            else if (field === 'cpf-cnpj') {
                const formatted = value.length === 11 ? 
                    value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4') :
                    value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
                element.textContent = formatted;
            }
            // Formatação para telefones
            else if (field === 'num-principal' || field === 'num-recado') {
                element.textContent = value.replace(/(\d{2})(\d{4,5})(\d{4})/, '($1) $2-$3');
            }
            else {
                element.textContent = value;
            }
        }
    });
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ffebee;
        color: #c62828;
        padding: 15px;
        border-radius: 4px;
        z-index: 1000;
        max-width: 400px;
    `;

    // Define o conteúdo HTML do popup, incluindo ícone, mensagem e botão de fechar
    errorDiv.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24">
                <path fill="#dc3545" d="M11 15h2v2h-2zm0-8h2v6h-2zm1-5C6.47 2 2 6.5 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2m0 18a8 8 0 0 1-8-8a8 8 0 0 1 8-8a8 8 0 0 1 8 8a8 8 0 0 1-8 8z"/>
            </svg>
                
            <!-- Mensagem de erro (dinâmica) -->
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" 
                    style="margin-left: auto; background: none; border: none; cursor: pointer;">
                ×
            </button>
        </div>
    `;

    // Adiciona o popup ao corpo do documento
    document.body.appendChild(errorDiv);
}

document.getElementById('btnDesativar').addEventListener('click', function () {
    mostrarDialogo(
        "Desativação da Conta",
        "Tem certeza de que deseja desativar a sua conta?",
        async () => {
            const btn = document.getElementById('btnDesativar');
            const feedbackEl = document.getElementById('feedback-message');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';

            try {
                const response = await fetch('../back/arquivar_proprio_usuario.php', {
                    method: 'POST',
                    credentials: 'include'
                });

                const data = await response.json();

                if (data.sucesso) {
                    mostrarMensagem("Sucesso", "Sua conta foi desativada com sucesso.", "sucesso");
                    setTimeout(() => {
                        window.location.href = '../index.html';
                    }, 2000);
                } else {
                    mostrarMensagem("Erro", data.message || "Erro ao desativar a conta.", "erro");
                }
            } catch (error) {
                mostrarMensagem("Erro", error.message || "Erro ao desativar a conta.", "erro");
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-user-slash"></i> Desativar Conta';
            }
        },
        () => {
            console.log("Usuário cancelou a desativação da conta.");
        },
        "alerta"
    );
});
</script>
</body>
</html>