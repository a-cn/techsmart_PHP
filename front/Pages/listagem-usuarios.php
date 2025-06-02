<?php
require_once '../../Back/verifica_sessao.php'; //Garante que somente usuários logados possam acessar a página
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Usuários</title>
    <link rel="stylesheet" href="../CSS/listagem-usuarios.css">
</head>
<body>
    
<?php include 'sidebar-header.php'; ?> <!-- Inclui o cabeçalho e a barra de navegação -->
    <h1>Lista de Usuários</h1>

    <!-- Barra de Pesquisa -->
    <div class="search-container">
        <form id="searchForm">
            <input type="text" id="searchInput" placeholder="Pesquisar por nome, CPF/CNPJ, ou e-mail">
            <button type="submit">Pesquisar</button>
            <a href="cadastro-usuario.php" class="btn-incluir">Cadastrar Colaborador</a>
        </form>
    </div>

    <!-- Tabela -->
    <table>
        <thead>
            <tr>
                <th>Ações</th>
                <th>Nome</th>
                <th>CPF/CNPJ</th>
                <th>CEP</th>
                <th>Endereço</th>
                <th>Complemento</th>
                <th>E-mail</th>
                <th>N° Principal</th>
                <th>N° Recado</th>
                <th>Permissão User</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <tr><td colspan="10">Carregando...</td></tr>
        </tbody>
    </table>

    <!-- Modal de Edição -->
    <div id="editModal" style="display:none; position:fixed; top:10%; left:50%; transform:translateX(-50%); background:#fff; padding:20px; border:1px solid #ccc; z-index:1000;">
        <h2>Editar Usuário</h2>
        <form id="editForm">
            <input type="hidden" name="usuario_id" id="editId">

            <label>Nome:</label>
            <input type="text" name="nome" id="editNome"><br>

            <label>CPF/CNPJ:</label>
            <input type="text" name="cpf_cnpj" id="editCpf"><br>

            <label>Email:</label>
            <input type="email" name="email" id="editEmail"><br>

            <label>CEP:</label>
            <input type="text" name="cep" id="editCep"><br>

            <label>Endereço:</label>
            <input type="text" name="logradouro" id="editLogradouro"><br>

            <label>Complemento:</label>
            <input type="text" name="complemento" id="editComplemento"><br>

            <label>Telefone Principal:</label>
            <input type="text" name="num_principal" id="editPrincipal"><br>

            <label>Telefone Recado:</label>
            <input type="text" name="num_recado" id="editRecado"><br>

            <button type="submit">Salvar</button>
            <button type="button" onclick="fecharModal()">Cancelar</button>
        </form>
    </div>

    <script>
    // Elementos DOM
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const userTableBody = document.getElementById('userTableBody');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');

    // Estado da aplicação
    let currentUsers = [];
    let arquivados = JSON.parse(localStorage.getItem('usuariosArquivados')) || [];

    // Função principal para carregar usuários
    async function carregarUsuarios(query = '') {
    try {
        showLoading();
        
        const response = await fetch(`../../Back/listagem_usuarios.php?search=${encodeURIComponent(query)}`);
        
        // Verifica se a resposta foi bem sucedida
        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            const errorMsg = errorData?.error || `Erro HTTP: ${response.status}`;
            throw new Error(errorMsg);
        }

        const data = await response.json();
        
        // Verifica se é um array (sucesso) ou objeto de erro
        if (Array.isArray(data)) {
            currentUsers = data;
            const usuariosAtivos = data.filter(u => !arquivados.includes(u.usuario_id));
            renderizarUsuarios(usuariosAtivos);
        } else if (data.message) {
            // Caso de "Nenhum usuário encontrado"
            renderizarUsuarios([]);
            showAlert('info', data.message);
        } else {
            throw new Error('Resposta inesperada do servidor');
        }
    } catch (error) {
        console.error('Erro detalhado:', error);
        showError(`Erro ao carregar usuários: ${error.message}`);
        
        // Mostra dados brutos para debug (remova em produção)
        try {
            const debugResponse = await fetch(`../../Back/listagem_usuarios.php?search=${encodeURIComponent(query)}`);
            const debugText = await debugResponse.text();
            console.log('Resposta bruta do servidor:', debugText);
        } catch (debugError) {
            console.error('Erro ao obter resposta para debug:', debugError);
        }
    }
}

    // Função para mostrar estado de carregamento
    function showLoading() {
        userTableBody.innerHTML = '<tr><td colspan="10" class="loading">Carregando usuários...</td></tr>';
    }

    // Função para mostrar erro
    function showError(message) {
        userTableBody.innerHTML = `<tr><td colspan="10" class="error">${message}</td></tr>`;
    }

    // Função para renderizar a lista de usuários
    function renderizarUsuarios(usuarios) {
        if (usuarios.length === 0) {
            userTableBody.innerHTML = '<tr><td colspan="10" class="empty">Nenhum usuário encontrado.</td></tr>';
            return;
        }

        userTableBody.innerHTML = '';
        
        usuarios.forEach(usuario => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="actions">
                    <button type="button" class="btn-edit" onclick="abrirModal(${usuario.usuario_id})">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <form class="form-delete" onsubmit="return confirmarExclusao(event, ${usuario.usuario_id})">
                        <button type="submit" class="btn-delete">
                            <i class="fas fa-trash-alt"></i> Excluir
                        </button>
                    </form>
                </td>
                <td>${usuario.nome || '<span class="nao-informado">Não Informado</span>'}</td>
                <td>${formatarCpfCnpj(usuario.cpf_cnpj) || '<span class="nao-informado">Não Informado</span>'}</td>
                <td>${formatarCep(usuario.cep) || '<span class="nao-informado">Não Informado</span>'}</td>
                <td>${usuario.logradouro || '<span class="nao-informado">Não Informado</span>'}</td>
                <td>${usuario.complemento || '<span class="nao-informado">Não Informado</span>'}</td>
                <td>${usuario.email || '<span class="nao-informado">Não Informado</span>'}</td>
                <td>${formatarTelefone(usuario.num_principal) || '<span class="nao-informado">Não Informado</span>'}</td>
                <td>${formatarTelefone(usuario.num_recado) || '<span class="nao-informado">Não Informado</span>'}</td>
                <td>${formatarPermissao(usuario.permissao_user) || '<span class="nao-informado">Não Informado</span>'}</td>
            `;
            userTableBody.appendChild(row);
        });
    }

    // Função para abrir o modal de edição
    async function abrirModal(usuarioId) {
    try {
        const usuario = currentUsers.find(u => u.usuario_id == usuarioId);
        
        if (!usuario) {
            throw new Error('Usuário não encontrado');
        }

        // Preenche o formulário com os nomes corretos
        document.getElementById('editId').value = usuario.usuario_id;
        document.getElementById('editNome').value = usuario.nome || '';
        document.getElementById('editCpf').value = formatarCpfCnpj(usuario.cpf_cnpj) || '';
        document.getElementById('editEmail').value = usuario.email || '';
        document.getElementById('editCep').value = formatarCep(usuario.cep) || '';
        document.getElementById('editLogradouro').value = usuario.logradouro || '';
        document.getElementById('editComplemento').value = usuario.complemento || '';
        document.getElementById('editPrincipal').value = formatarTelefone(usuario.num_principal) || '';
        document.getElementById('editRecado').value = formatarTelefone(usuario.num_recado) || '';

        editModal.style.display = 'block';
        setTimeout(() => editModal.classList.add('show'), 10);
        document.body.style.overflow = 'hidden';
        
    } catch (error) {
        console.error('Erro ao abrir modal:', error);
        showAlert('error', 'Não foi possível carregar os dados do usuário para edição.');
    }
}

    // Função para fechar o modal
    function fecharModal() {
        editModal.classList.remove('show');
        setTimeout(() => {
            editModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            editForm.reset();
        }, 300);
    }

    // Função para confirmar exclusão (arquivamento invisível)
    async function confirmarExclusao(event, usuarioId) {
        event.preventDefault();
        
        const confirmed = await showConfirmDialog(
            'Confirmar Exclusão',
            'Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.'
        );
        
		if (confirmed) {
			try {
				fetch('../../Back/arquivar_usuario.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({ usuario_id: usuarioId })
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						showAlert('success', 'Usuário arquivado com sucesso!');
						carregarUsuarios(searchInput.value);
					} else {
						showAlert('error', 'Erro ao arquivar usuário.');
					}
				})
				.catch(error => {
					console.error('Erro na requisição:', error);
					showAlert('error', 'Erro ao arquivar usuário.');
				});
			} catch (error) {
				console.error('Erro inesperado:', error);
				showAlert('error', 'Erro ao arquivar usuário.');
			}
		}
    }

    // Funções auxiliares de formatação
    function formatarCpfCnpj(value) {
        if (!value) return '';
        const numeros = value.replace(/\D/g, '');
        
        if (numeros.length === 11) {
            return numeros.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        } else if (numeros.length === 14) {
            return numeros.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        }
        return value;
    }

    function formatarCep(value) {
        if (!value) return '';
        const numeros = value.replace(/\D/g, '');
        return numeros.replace(/(\d{5})(\d{3})/, '$1-$2');
    }

    function formatarTelefone(value) {
        if (!value) return '';
        const numeros = value.replace(/\D/g, '');
        
        if (numeros.length === 11) {
            return numeros.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (numeros.length === 10) {
            return numeros.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        return value;
    }

    function formatarPermissao(value) {
		// Como agora recebemos o texto diretamente (Administrador, Colaborador, Cliente)
		// Podemos retornar o próprio valor ou fazer mapeamentos adicionais se necessário
		return value || 'Não Informado';
	}

    // Função para mostrar alertas
    function showAlert(type, message) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.classList.add('show');
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }, 10);
    }

    // Função para mostrar diálogo de confirmação
    function showConfirmDialog(title, message) {
        return new Promise(resolve => {
            const dialog = document.createElement('div');
            dialog.className = 'confirm-dialog';
            
            dialog.innerHTML = `
                <div class="dialog-content">
                    <h3>${title}</h3>
                    <p>${message}</p>
                    <div class="dialog-buttons">
                        <button class="btn-cancel">Cancelar</button>
                        <button class="btn-confirm">Confirmar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dialog);
            setTimeout(() => dialog.classList.add('show'), 10);
            
            dialog.querySelector('.btn-cancel').addEventListener('click', () => {
                dialog.classList.remove('show');
                setTimeout(() => dialog.remove(), 300);
                resolve(false);
            });
            
            dialog.querySelector('.btn-confirm').addEventListener('click', () => {
                dialog.classList.remove('show');
                setTimeout(() => dialog.remove(), 300);
                resolve(true);
            });
        });
    }

    // Event Listeners
    searchForm.addEventListener('submit', e => {
        e.preventDefault();
        carregarUsuarios(searchInput.value);
    });

    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitButton = e.target.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

        try {
            const formData = {
                usuario_id: document.getElementById('editId').value,
                nome: document.getElementById('editNome').value.trim(),
                cpf_cnpj: document.getElementById('editCpf').value.replace(/\D/g, ''),
                email: document.getElementById('editEmail').value.trim(),
                cep: document.getElementById('editCep').value.replace(/\D/g, ''),
                logradouro: document.getElementById('editLogradouro').value.trim(),
                complemento: document.getElementById('editComplemento').value.trim(),
                num_principal: document.getElementById('editPrincipal').value.replace(/\D/g, ''),
                num_recado: document.getElementById('editRecado').value.replace(/\D/g, '')
            };

            const response = await fetch('../../Back/editar_usuario.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            // Verifica se a resposta é JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Resposta inesperada: ${text.substring(0, 100)}...`);
            }

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Erro ao atualizar usuário');
            }

            showAlert('success', 'Usuário atualizado com sucesso!');
            fecharModal();
            carregarUsuarios(searchInput.value);
            
        } catch (error) {
            console.error('Erro ao atualizar usuário:', error);
            showAlert('error', `Erro ao salvar: ${error.message}`);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    });

    // Fechar modal ao clicar fora ou pressionar ESC
    window.addEventListener('click', e => {
        if (e.target === editModal) {
            fecharModal();
        }
    });

    window.addEventListener('keydown', e => {
        if (e.key === 'Escape' && editModal.style.display === 'block') {
            fecharModal();
        }
    });

    // Carregar usuários ao iniciar
    document.addEventListener('DOMContentLoaded', () => {
        carregarUsuarios();
        
        // Adiciona máscaras aos campos
        if (typeof IMask !== 'undefined') {
            new IMask(document.getElementById('editCpf'), {
                mask: [
                    { mask: '000.000.000-00' },
                    { mask: '00.000.000/0000-00' }
                ]
            });
            
            new IMask(document.getElementById('editCep'), {
                mask: '00000-000'
            });
            
            new IMask(document.getElementById('editPrincipal'), {
                mask: '(00) 00000-0000'
            });
            
            new IMask(document.getElementById('editRecado'), {
                mask: '(00) 00000-0000'
            });
        }
    });
</script>

</body>
</html>