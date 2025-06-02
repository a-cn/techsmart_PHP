
//Função de validação completa do formulário de cadastro de movimentação
function validaMovimentacao() {
    // Validações Compostas/Complexas
    /*/
    const nascimento = document.getElementById("data_nascimento").value;
    if (!nascimento && tipoPessoa.value === "cpf") {
        mostrarMensagem('Aviso', 'O Campo Data de Nascimento é obrigatório.', 'alerta');
        return false;
    }
    /*/
    // Validações Simples
    const camposComuns = [
        { id: "pedido_id", nome: "Pedido" },
        { id: "produtofinal_id", nome: "Produto Final" },
        { id: "nome", nome: "Produto Nome" },
        { id: "quantidade", nome: "Quantidade" },
        { id: "data_hora", nome: "Data/Hora" },
        { id: "tipo_movimentacao", nome: "Tipo de Movimentação" },
    ];
    
    for (let campo of camposComuns) {
        const valor = document.getElementById(campo.id).value.trim();
        if (valor === "") {
            mostrarMensagem('Aviso', `O Campo ${campo.nome} é obrigatório!`, 'alerta');
            return false;
        }
    }

    return true;
}
