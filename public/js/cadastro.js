document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("cadastroForm");
  form.addEventListener("submit", async function (e) {
    e.preventDefault();
    const nome = document.getElementById("nome").value.trim();
    const email = document.getElementById("email").value.trim();
    const senha = document.getElementById("senha").value;
    const confirmarSenha = document.getElementById("confirmarSenha").value;

    if (!nome || !email || !senha || !confirmarSenha) {
      alert("Preencha todos os campos.");
      return;
    }
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
      alert("E-mail inválido.");
      return;
    }
    if (senha.length < 6) {
      alert("A senha deve ter pelo menos 6 caracteres.");
      return;
    }
    if (senha !== confirmarSenha) {
      alert("As senhas não coincidem.");
      return;
    }

    try {
      const response = await fetch("/LumisApp/public/api/user/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nome, email, senha }),
      });
      const data = await response.json();
      if (response.ok && data.success) {
        alert("Cadastro realizado com sucesso!");
        if (data.id_usuario) {
          localStorage.setItem("id_usuario", data.id_usuario);
          window.location.href = "index.html";
        } else {
          window.location.href = "login.html";
        }
      } else {
        alert(data.message || "Erro ao cadastrar.");
      }
    } catch (err) {
      alert("Erro de conexão. Tente novamente.");
    }
  });
});
