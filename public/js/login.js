document
  .getElementById("login-form")
  .addEventListener("submit", async function (e) {
    e.preventDefault();
    const email = document.getElementById("email").value.trim();
    const senha = document.getElementById("senha").value.trim();
    if (!email || !senha) return;
    try {
      const resp = await fetch(
        "https://api.lumisapp.me/public/api.php/api/login",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email, senha }),
        }
      );
      if (resp.ok) {
        const data = await resp.json();
        if (data.success && data.usuario && data.usuario.id_usuario) {
          localStorage.setItem("id_usuario", data.usuario.id_usuario);
        }
        window.location.href = "index.html";
      } else {
        const data = await resp.json();
        alert(data.mensagem || "Email ou senha inválidos.");
      }
    } catch (err) {
      alert("Erro ao conectar. Tente novamente.");
    }
  });

function togglePasswordVisibility(inputId) {
  const input = document.getElementById(inputId);
  const icon = input.nextElementSibling;

  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}

function abrirRecuperarSenha() {
  document.getElementById("modal-recuperar-senha").style.display = "flex";
}

function fecharRecuperarSenha() {
  document.getElementById("modal-recuperar-senha").style.display = "none";
  document.getElementById("email-recuperacao").value = "";
}

async function enviarRecuperacao(event) {
  event.preventDefault();
  const email = document.getElementById("email-recuperacao").value.trim();

  if (!email) {
    alert("Por favor, digite seu email.");
    return;
  }

  try {
    const resp = await fetch(
      "https://api.lumisapp.me/public/api.php/api/user/recuperar-senha",
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email }),
      }
    );

    const data = await resp.json();

    if (resp.ok) {
      alert("Instruções de recuperação enviadas para seu email!");
      fecharRecuperarSenha();
    } else {
      alert(data.message || "Email não encontrado.");
    }
  } catch (err) {
    alert("Erro ao conectar. Tente novamente.");
  }
}
