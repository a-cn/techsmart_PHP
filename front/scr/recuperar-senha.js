//PERMITE ESCONDER/MOSTRAR A BARRA LATERAL
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

// Função para buscar a pergunta de segurança
function buscarPergunta() {
    const email = document.getElementById('email').value;
    const errorMessage = document.getElementById('error-message');
    
    // Limpar mensagens de erro
    errorMessage.textContent = '';
    errorMessage.style.display = 'none';
    
    // Validar email
    if (!email || !email.includes('@')) {
        mostrarMensagem('Erro', 'Por favor, insira um email válido.', 'erro');
        return;
    }
    
    // Mostrar loading
    document.getElementById('loading').style.display = 'block';
    document.getElementById('step1').classList.remove('active');
    
    // Fazer requisição AJAX para buscar a pergunta
    fetch('../back/buscar_pergunta_seguranca.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';
        
        if (data.success) {
            // Armazenar dados do usuário
            window.usuarioData = {
                email: email,
                perguntaId: data.pergunta_id,
                pergunta: data.pergunta
            };
            
            // Exibir a pergunta
            document.getElementById('perguntaSeguranca').value = data.pergunta;
            
            // Ir para o próximo passo
            document.getElementById('step2').classList.add('active');
        } else {
            mostrarMensagem('Erro', data.message || 'Email não encontrado no sistema.', 'erro');
            document.getElementById('step1').classList.add('active');
        }
    })
    .catch(error => {
        document.getElementById('loading').style.display = 'none';
        mostrarMensagem('Erro', 'Erro ao buscar informações. Tente novamente.', 'erro');
        document.getElementById('step1').classList.add('active');
    });
}

// Função para validar a resposta
function validarResposta() {
    const resposta = document.getElementById('respostaSeguranca').value;
    const errorMessage = document.getElementById('error-message');
    
    // Limpar mensagens de erro
    errorMessage.textContent = '';
    errorMessage.style.display = 'none';
    
    if (!resposta.trim()) {
        mostrarMensagem('Erro', 'Por favor, insira a resposta da pergunta de segurança.', 'erro');
        return;
    }
    
    // Mostrar loading
    document.getElementById('loading').style.display = 'block';
    
    // Fazer requisição AJAX para validar a resposta
    fetch('../back/validar_resposta_seguranca.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(window.usuarioData.email) + 
              '&resposta=' + encodeURIComponent(resposta)
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';
        
        if (data.success) {
            // Ir para o próximo passo
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step3').classList.add('active');
        } else {
            mostrarMensagem('Erro', data.message || 'Resposta incorreta. Tente novamente.', 'erro');
        }
    })
    .catch(error => {
        document.getElementById('loading').style.display = 'none';
        mostrarMensagem('Erro', 'Erro ao validar resposta. Tente novamente.', 'erro');
    });
}

// Função para voltar ao passo do email
function voltarParaEmail() {
    document.getElementById('step2').classList.remove('active');
    document.getElementById('step1').classList.add('active');
    document.getElementById('error-message').style.display = 'none';
}

// Função para voltar ao passo da resposta
function voltarParaResposta() {
    document.getElementById('step3').classList.remove('active');
    document.getElementById('step2').classList.add('active');
    document.getElementById('error-message').style.display = 'none';
}

// Validação final do formulário
function validateForm() {
    const novaSenha = document.getElementById('novaSenha').value;
    const confirmarSenha = document.getElementById('confirmarSenha').value;
    const errorMessage = document.getElementById('error-message');
    
    // Limpar mensagens de erro anteriores
    errorMessage.textContent = '';
    errorMessage.style.display = 'none';
    
    // Mostrar loading
    document.getElementById('loading').style.display = 'block';
    document.getElementById('loading').innerHTML = '<p>Atualizando senha...</p>';
    
    // Preparar dados para envio
    const formData = new FormData();
    formData.append('email', window.usuarioData.email);
    formData.append('pergunta_id', window.usuarioData.perguntaId);
    formData.append('novaSenha', novaSenha);
    formData.append('confirmarSenha', confirmarSenha);
    
    // Fazer requisição AJAX para atualizar a senha
    fetch('../back/recuperar_senha.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';
        
        if (data.success) {
            mostrarMensagem('Sucesso', 'Senha redefinida com sucesso!', 'sucesso', () => {
                window.location.href = '../index.html';
            });
        } else {
            mostrarMensagem('Erro', data.message || 'Erro ao atualizar senha. Tente novamente.', 'erro');
        }
    })
    .catch(error => {
        document.getElementById('loading').style.display = 'none';
        mostrarMensagem('Erro', 'Erro ao atualizar senha. Tente novamente.', 'erro');
    });
    
    return false; // Impedir o envio tradicional do formulário
}

// Função para mostrar regras de senha quando o usuário digita
function mostrarRegrasSenha() {
    const novaSenha = document.getElementById('novaSenha').value;
    const regraSenha = document.getElementById('regraSenha');
    
    if (novaSenha.length > 0) {
        regraSenha.style.display = 'block';
    } else {
        regraSenha.style.display = 'none';
    }
}

// Inicialização quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar evento para mostrar regras de senha
    const novaSenhaInput = document.getElementById('novaSenha');
    if (novaSenhaInput) {
        novaSenhaInput.addEventListener('input', mostrarRegrasSenha);
    }
    
    // Verificar se há mensagens de erro ou sucesso na URL
    const urlParams = new URLSearchParams(window.location.search);
    const erro = urlParams.get('erro');
    const mensagem = urlParams.get('mensagem');
    
    if (erro) {
        mostrarMensagem('Erro', decodeURIComponent(erro), 'erro');
    }
    
    if (mensagem) {
        mostrarMensagem('Sucesso', decodeURIComponent(mensagem), 'sucesso');
    }
});