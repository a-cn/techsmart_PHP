const urlParams = new URLSearchParams(window.location.search);
const erro = urlParams.get("erro");
const mensagem = urlParams.get("mensagem");

const Erros = {
    "login": "Email ou senha inválidos!",
    "timeout": "Sua sessão expirou por inatividade. Por favor, faça login novamente.",
    "bloqueado": "Usuário bloqueado! Por favor, contate o administrador."
};

// Verificamos se o erro possui ',' e dividimos em Titulo e Mensagem, senão "Erro" será o Titulo e erro a Mensagem  
let msgs = (erro && typeof erro === 'string') ? erro.split(',') : [];
let Tit  = msgs[1] ? msgs[0] : "Erro";
let Msg  = msgs[1] ? msgs[1] : msgs[0];

// Verificamos se a mensagem possui ',' e dividimos em Titulo e Mensagem, senão "Mensagem" será o Titulo e mensagem a Mensagem
let msgParams = (mensagem && typeof mensagem === 'string') ? mensagem.split(',') : [];
let msgTit = msgParams[1] ? msgParams[0] : "Mensagem";
let msgText = msgParams[1] ? msgParams[1] : msgParams[0];

if (erro) {
    let sMsg = Erros[erro] || (msgs[1] ? Msg : "Tipo de erro desconhecido!"); 
    /*/ Espera o DOM carregar antes de chamar a função do popup /*/
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof mostrarMensagem === "function") {
            mostrarMensagem(Tit, sMsg, "erro");
        } else {
            alert(sMsg); // fallback se a função ainda não estiver disponível
        }
        // Removemos o parâmetro erro da URL para evitar recursividade
        urlParams.delete("erro");
        let newUrl = window.location.origin + window.location.pathname + '?' + urlParams.toString();
        history.replaceState(null, "", newUrl);
    });
}

if (mensagem) {
    /*/ Espera o DOM carregar antes de chamar a função do popup /*/
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof mostrarMensagem === "function") {
            mostrarMensagem(msgTit, msgText, "sucesso");
        } else {
            alert(msgText); // fallback se a função ainda não estiver disponível
        }
        // Removemos o parâmetro mensagem da URL para evitar recursividade
        urlParams.delete("mensagem");
        let newUrl = window.location.origin + window.location.pathname + '?' + urlParams.toString();
        history.replaceState(null, "", newUrl);
    });
}