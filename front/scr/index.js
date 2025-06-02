const urlParams = new URLSearchParams(window.location.search);
const erro = urlParams.get("erro");

const Erros = {
  "login": "Email ou senha inválidos!",
  "timeout": "Sua sessão expirou por inatividade. Por favor, faça login novamente.",
  "bloqueado": "Usuário bloqueado! Por favor, contate o administrador."
};

if (erro) {
    const sMsg = Erros[erro] || "Tipo de erro desconhecido!";
  
    //const divErro = document.getElementById("mensagemErro");
    //if (divErro) {
    //    divErro.textContent = sMsg;
    //    divErro.style.display = "block";
    //    setTimeout(() => {
    //        divErro.style.display = "none";
    //    }, 5000);
    //} else {

    /*/ Espera o DOM carregar antes de chamar a função do popup /*/
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof mostrarMensagem === "function") {
            mostrarMensagem("Erro", sMsg, "erro");
        } else {
            alert(sMsg); // fallback se a função ainda não estiver disponível
        }
    });
}
