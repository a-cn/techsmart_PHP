console.warn('lendo scripts gerais...');

// Abre as páginas usando os id´s dos elementos dos menus
document.querySelectorAll('.grid-item').forEach(item => {
    item.addEventListener('click', () => {
        console.log(`clicado em: ${item.id}`);
        if (item.id !== '') {
            window.location.href = `${item.id}.php`;
        }
    });
});     

/*/ Funções gerais para uso em toda a aplicação /*/

// Lista de URLs dos vídeos
const videoUrls = [
    "../../imgs/Login1.mp4",
    "../../imgs/Techno1.mp4",
    "../../imgs/Techno2.mp4",
    "../../imgs/Techno3.mp4",
    "../../imgs/Techno4.mp4",
    "../../imgs/Techno5.mp4",
];

// Função para obter um vídeo aleatório
function obterVideoAleatorio() {
    const indiceAleatorio = Math.floor(Math.random() * videoUrls.length);
    return videoUrls[indiceAleatorio];
}

// Função para atualizar o vídeo no player
function atualizarVideo(videoElement, url) {
    videoElement.src = url;
    videoElement.load();
    videoElement.play();
}

// Função para converter data de pt-br para iso para processamento e gravação
function parseDateBR(dataBR) {
  if (!/^\d{2}\/\d{2}\/\d{4}$/.test(dataBR)) return null; // Verifica formato básico dd/mm/yyyy

  const [dia, mes, ano] = dataBR.split("/").map(Number);
  const data = new Date(ano, mes - 1, dia); // mês zero-based do javascript

  // Validação: o objeto Date deve manter os mesmos valores que foram inseridos
  if (
    data.getFullYear() !== ano ||
    data.getMonth() !== mes - 1 ||
    data.getDate() !== dia
  ) {
    return null; // Data inválida (ex: 31/02/2025 viraria 02/03/2025)
  }

  return data;
}

function preencherFormulario(formulario, dados) {
    const form = document.getElementById(formulario);
    if (!form) {
        console.warn("Formulário não encontrado:", formulario);
        return;
    }

    Object.keys(dados).forEach((campo) => {
        const elemento = form.querySelector(`[name="${campo}"]`);
        if (!elemento) {
            console.warn("Campo não encontrado:", campo);
            return;
        }

        const valorOriginal = dados[campo];
        const valor = valorOriginal != null ? String(valorOriginal) : "";

        if (elemento.type === "checkbox") {
            // Checkbox marcado apenas se valor for boolean true, número 1 ou string "1"
            elemento.checked = valorOriginal === true || valorOriginal === 1 || valorOriginal === "1";
        } else if (elemento.tagName === "SELECT") {
            // Select: tenta encontrar a opção pelo value OU pelo texto exibido
            const matchedOption = Array.from(elemento.options).find(opt =>
                opt.value === valor || opt.text.trim().toLowerCase() === valor.trim().toLowerCase()
            );

            if (matchedOption) {
                elemento.value = matchedOption.value;
            } else {
                console.warn(`Valor "${valor}" não encontrado no select "${campo}"`);
            }

        } else if (elemento.type === "date") {
            // Datas: converte do formato BR para ISO (yyyy-mm-dd)
            const data = parseDateBR(valor);
            if (data) {
                const iso = data.toISOString().split("T")[0];
                elemento.value = iso;
            } else {
                console.warn("Data inválida recebida:", valor);
                elemento.value = "";
            }

        } else {
            // Inputs comuns e textareas
            elemento.value = valor;
        }
    });
}


/*/ Função para preencher o formulário automaticamente
function preencherFormulario(formulario, dados) {
    let form = document.getElementById(formulario);
    if (!form) {
        console.warn("Formulário não encontrado:", formulario);
        return;
    }

    Object.keys(dados).forEach((campo) => {
        let elemento = form.querySelector(`[name="${campo}"]`);
        if (elemento) {
            let valor = dados[campo] ?? ""; // Define um valor padrão caso seja null ou undefined
            
            if (elemento.type === "checkbox") {
                // Para checkboxes, verifica se o valor é verdadeiro ou compatível
                elemento.checked = Boolean(valor) && valor !== "0";
            } else if (elemento.tagName === "SELECT") {
                // Para selects, verifica se a opção existe antes de definir
                let optionExists = Array.from(elemento.options).some(opt => opt.value === valor);
                if (optionExists) {
                    elemento.value = valor;
                }
            } else if (elemento.type === "date") {
                if (valor) {
                    let data = parseDateBR(valor);
            
                    // Certifica-se de que a data é válida
                    if (!isNaN(data.getTime())) {
                        let ano = data.getFullYear();
                        let mes = String(data.getMonth() + 1).padStart(2, '0');
                        let dia = String(data.getDate()).padStart(2, '0');
            
                        let dataFormatada = `${ano}-${mes}-${dia}`; // Formato YYYY-MM-DD
                        console.log(`Definindo valor do input date: ${dataFormatada}`); // Depuração
                        elemento.value = dataFormatada;
                    } else {
                        console.warn("Data inválida recebida:", valor);
                        elemento.value = "";
                    }
                } else {
                    elemento.value = "";
                }
            } else if (elemento.type === "date") {
                // Para inputs tipo date, verifica se o valor é válido antes de formatar
                if (valor) {
                    let dataFormatada = new Date(valor).toISOString().split("T")[0]; // Obtém somente a parte da data
                    elemento.value = dataFormatada;
                } else {
                    elemento.value = ""; // Se vazio, mantém o campo limpo
                }
            } else {
                // Para inputs e textareas, trata valores vazios
                elemento.value = valor;
            }
        } else {
            console.warn("Campo não encontrado:", campo);
        }
    });
}/*/

function limpaCadastro(event){
    if (event) event.preventDefault(); // Evita que o formulário seja submetido
    document.getElementById("form-cadastro").reset();
}

function alternaCadastroConsulta(divCadastro, divConsulta){
    document.getElementById(divCadastro).classList.toggle("oculta");
    document.getElementById(divConsulta).classList.toggle("oculta");
}

function limpaCadastroAlternaEdicao(divCadastro, divConsulta){
    limpaCadastro();
    alternaCadastroConsulta(divCadastro, divConsulta)
}

function obterCamposFormulario(formId) {
    const form = document.getElementById(formId);
    const dadosFormulario = {};

    // Percorre todos os elementos dentro do formulário
    form.querySelectorAll("input, textarea").forEach((campo) => {
        if (campo.name) { // Garantimos que o campo tenha um atributo 'name'
            dadosFormulario[campo.name] = campo.value;
        }
    });
    return dadosFormulario;
}

function getSelectedRowData(oDataTable) {
    var selectedRow = oDataTable.row({ selected: true }).node(); // Obtém o elemento da linha selecionada
    if (!selectedRow) {
        return null;
    }
    var rowData = {}; // Cria um objeto para armazenar os dados
    selectedRow.querySelectorAll("td").forEach(td => {
        if (td.getAttribute("name")) { // Verifica se a célula tem um atributo 'name'
            rowData[td.getAttribute("name")] = td.innerText; // Armazena os valores no objeto
        }
    });
    console.log("Dados armazenados:", rowData);
    return rowData;
}

