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

/*/ Função para preencher o formulário automaticamente /*/
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

function validarCNPJ(cnpj) {
    cnpj = cnpj.replace(/\D/g, ''); // Remove caracteres não numéricos

    if (cnpj.length !== 14) return false;

    // Verifica se todos os dígitos são iguais, o que torna o CNPJ inválido
    if (/^(\d)\1+$/.test(cnpj)) return false;

    // Cálculo dos dígitos verificadores
    let tamanho = cnpj.length - 2;
    let numeros = cnpj.substring(0, tamanho);
    let digitos = cnpj.substring(tamanho);
    let soma = 0;
    let pos = tamanho - 7;

    for (let i = tamanho; i >= 1; i--) {
        soma += numeros[tamanho - i] * pos--;
        if (pos < 2) pos = 9;
    }

    let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    if (resultado != digitos[0]) return false;

    tamanho++;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;

    for (let i = tamanho; i >= 1; i--) {
        soma += numeros[tamanho - i] * pos--;
        if (pos < 2) pos = 9;
    }

    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    if (resultado != digitos[1]) return false;

    return true;
}

