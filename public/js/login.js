document
  .getElementById("login-form")
  .addEventListener("submit", async function (e) {
    e.preventDefault();
    const email = document.getElementById("email").value.trim();
    const senha = document.getElementById("senha").value.trim();
    if (!email || !senha) return;
    try {
      const resp = await fetch("/LumisApp/public/api/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, senha }),
      });
      if (resp.ok) {
        const data = await resp.json();
        if (data.success && data.usuario && data.usuario.id_usuario) {
          localStorage.setItem("id_usuario", data.usuario.id_usuario);
        }
        window.location.href = "/LumisApp/public/index.html";
      } else {
        const data = await resp.json();
        alert(data.mensagem || "Email ou senha inválidos.");
      }
    } catch (err) {
      alert("Erro ao conectar. Tente novamente.");
    }
  });

function abrirRecuperarSenha() {
  alert("Funcionalidade de recuperação de senha em breve!");
}
