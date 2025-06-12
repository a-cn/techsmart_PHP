document.addEventListener('DOMContentLoaded', () => {

    function fecharPopup(callback) {
        const janela = document.getElementById("janela-mensagens");
        janela.style.display = "none";
        document.getElementById("overlay").style.display = "none";
        if (typeof callback === "function") {
            callback();
        }
    }

    // Exibe mensagens simples com variação de estilo
    window.mostrarMensagem = function (titulo, texto, tipo = "", callback = null) {
        const janela = document.getElementById("janela-mensagens");

        // Limpa classes anteriores e define o estilo base
        janela.className = "popup-bordeglass";
        if (["erro", "sucesso", "alerta"].includes(tipo)) {
            janela.classList.add(tipo);
        }

        document.getElementById("popup-titulo").textContent = titulo;
        document.getElementById("popup-texto").textContent = texto;

        // Oculta botões "sim" e "não", mostra apenas "fechar"
        document.getElementById("btn-sim").style.display = "none";
        document.getElementById("btn-nao").style.display = "none";
        document.getElementById("btn-fechar").style.display = "inline-block";

        // Configura o botão fechar com o callback
        const btnFechar = document.getElementById("btn-fechar");
        btnFechar.onclick = () => fecharPopup(callback);

        // Exibe
        janela.style.display = "block";
        document.getElementById("overlay").style.display = "block";
    }

    // Exibe um diálogo com "Sim" e "Não"
    window.mostrarDialogo = function (titulo, texto, aoConfirmar, aoCancelar, tipo = "") {
        const janela = document.getElementById("janela-mensagens");

        // Define o estilo base e limpa classes anteriores
        janela.className = "popup-bordeglass";
        if (["erro", "sucesso", "alerta"].includes(tipo)) {
            janela.classList.add(tipo);
        }

        document.getElementById("popup-titulo").textContent = titulo;
        document.getElementById("popup-texto").textContent = texto;

        // Exibe botões "Sim" e "Não", oculta "Fechar"
        document.getElementById("btn-sim").style.display = "inline-block";
        document.getElementById("btn-nao").style.display = "inline-block";
        document.getElementById("btn-fechar").style.display = "none";

        // Adiciona eventos aos botões
        const btnSim = document.getElementById("btn-sim");
        const btnNao = document.getElementById("btn-nao");

        btnSim.onclick = () => {
            if (typeof aoConfirmar === "function") aoConfirmar();
            fecharPopup();
        };

        btnNao.onclick = () => {
            if (typeof aoCancelar === "function") aoCancelar();
            fecharPopup();
        };

        // Exibe popup e overlay
        janela.style.display = "block";
        document.getElementById("overlay").style.display = "block";
    }
});
