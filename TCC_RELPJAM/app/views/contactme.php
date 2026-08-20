<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>relpjam — Contato</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>

  :root{
    --gray-900:#0F172A;
    --accent:#F97316;
    --secondary:#0F172A;

    --panel:#16213A;
    --panel-2:#1C2942;
    --border:#2A3B5C;

    --text:#F8FAFC;
    --muted:#93A2C3;

    --accent-dim:rgba(249,115,22,.14);
  }

  *{
    box-sizing:border-box;
    margin:0;
    padding:0;
  }

  html,
  body{
    height:100%;
  }

  body{
    background:
      radial-gradient(
        1100px 500px at 85% -10%,
        rgba(249,115,22,.14),
        transparent 60%
      ),
      var(--gray-900);

    color:var(--text);

    font-family:'Inter',sans-serif;

    min-height:100vh;

    -webkit-font-smoothing:antialiased;
  }

  h1,
  h2,
  h3,
  .display{
    font-family:'Space Grotesk',sans-serif;
    letter-spacing:-0.01em;
  }

  /* =========================================
     LAYOUT
  ========================================= */

  .shell{
    max-width:1120px;
    margin:0 auto;
    padding:0 24px 80px;
  }

  header.top{
    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:26px 24px;

    max-width:1120px;
    margin:0 auto;

    border-bottom:1px solid var(--border);
  }

  .logo{
    display:flex;
    align-items:center;
    gap:10px;

    text-decoration:none;
  }

  .logo img{
    height:120px;
    width:auto;
    display:block;
    object-fit:contain;
  }

  /* =========================================
     NAVEGAÇÃO
  ========================================= */

  nav.tabs{
    display:flex;
    gap:6px;

    background:var(--panel);

    border:1px solid var(--border);

    border-radius:999px;

    padding:4px;
  }

  nav.tabs button{
    border:none;
    background:var(--accent);

    color:var(--gray-900);

    font-family:'Inter',sans-serif;
    font-weight:600;
    font-size:14px;

    padding:9px 18px;

    border-radius:999px;

    cursor:pointer;
  }

  /* =========================================
     HERO
  ========================================= */

  .hero{
    padding:64px 0 40px;

    border-bottom:1px solid var(--border);

    margin-bottom:48px;

    position:relative;
  }

  .eyebrow{
    display:inline-flex;
    align-items:center;
    gap:8px;

    font-size:12px;
    font-weight:600;

    letter-spacing:.14em;

    text-transform:uppercase;

    color:var(--accent);

    margin-bottom:18px;
  }

  .eyebrow::before{
    content:'';

    width:16px;
    height:2px;

    background:var(--accent);

    display:inline-block;
  }

  .hero h1{
    font-size:clamp(32px,5vw,50px);

    font-weight:700;

    line-height:1.06;

    max-width:640px;
  }

  .hero p.lead{
    margin-top:16px;

    color:var(--muted);

    font-size:16px;

    max-width:600px;

    line-height:1.6;
  }

  /* =========================================
     CONTATO
  ========================================= */

  .contact-grid{
    display:grid;

    grid-template-columns:1fr 1fr;

    gap:20px;

    margin-bottom:48px;
  }

  @media (max-width:720px){

    .contact-grid{
      grid-template-columns:1fr;
    }

  }

  .contact-card{
    background:var(--panel);

    border:1px solid var(--border);

    border-radius:16px;

    padding:26px;

    position:relative;

    overflow:hidden;

    text-decoration:none;

    color:var(--text);

    display:flex;

    flex-direction:column;

    gap:14px;

    transition:
      .2s border-color,
      .2s transform;
  }

  .contact-card:hover{
    border-color:var(--accent);

    transform:translateY(-2px);
  }

  .contact-card .icon{
    width:44px;
    height:44px;

    border-radius:11px;

    background:var(--accent-dim);

    display:flex;

    align-items:center;
    justify-content:center;

    color:var(--accent);
  }

  .contact-card .label{
    font-size:12px;

    text-transform:uppercase;

    letter-spacing:.1em;

    color:var(--muted);

    font-weight:600;
  }

  .contact-card .value{
    font-family:'Space Grotesk',sans-serif;

    font-size:19px;

    font-weight:600;

    word-break:break-word;
  }

  .contact-card .cta{
    margin-top:auto;

    font-size:13px;

    color:var(--accent);

    font-weight:600;

    display:flex;

    align-items:center;

    gap:6px;
  }

  /* =========================================
     FORMULÁRIO
  ========================================= */

  .form-panel{
    background:var(--panel);

    border:1px solid var(--border);

    border-radius:16px;

    padding:32px;
  }

  .form-panel h2{
    font-size:22px;

    margin-bottom:6px;
  }

  .form-panel .sub{
    color:var(--muted);

    font-size:14px;

    margin-bottom:24px;
  }

  .field{
    margin-bottom:16px;
  }

  .field label{
    display:block;

    font-size:13px;

    font-weight:600;

    color:var(--muted);

    margin-bottom:7px;
  }

  .field input,
  .field textarea{
    width:100%;

    background:var(--gray-900);

    border:1px solid var(--border);

    border-radius:10px;

    padding:12px 14px;

    color:var(--text);

    font-family:'Inter',sans-serif;

    font-size:14px;

    resize:vertical;
  }

  .field input:focus,
  .field textarea:focus{
    outline:2px solid var(--accent);

    outline-offset:1px;

    border-color:var(--accent);
  }

  .field-row{
    display:grid;

    grid-template-columns:1fr 1fr;

    gap:14px;
  }

  @media (max-width:560px){

    .field-row{
      grid-template-columns:1fr;
    }

  }  /* =========================================
     BOTÃO
  ========================================= */

  .btn{
    background:var(--accent);

    color:var(--gray-900);

    border:none;

    border-radius:10px;

    padding:13px 22px;

    font-family:'Inter',sans-serif;

    font-weight:700;

    font-size:14px;

    cursor:pointer;

    transition:.15s;

    display:inline-flex;

    align-items:center;

    gap:8px;
  }

  .btn:hover{
    filter:brightness(1.08);

    transform:translateY(-1px);
  }

  .btn:disabled{
    opacity:.6;

    cursor:default;

    transform:none;
  }

  /* =========================================
     STATUS
  ========================================= */

  .status-msg{
    margin-top:14px;

    font-size:13px;

    padding:10px 12px;

    border-radius:8px;

    display:none;
  }

  .status-msg.ok{
    display:block;

    background:rgba(34,197,94,.12);

    color:#4ADE80;

    border:1px solid rgba(34,197,94,.3);
  }

  .status-msg.err{
    display:block;

    background:rgba(239,68,68,.12);

    color:#F87171;

    border:1px solid rgba(239,68,68,.3);
  }

  /* =========================================
     AVISO
  ========================================= */

  .note{
    font-size:12px;

    color:var(--muted);

    margin-top:18px;

    text-align:center;
  }

  .note code{
    background:var(--panel-2);

    padding:2px 6px;

    border-radius:5px;

    color:var(--accent);
  }

  /* =========================================
     FOOTER
  ========================================= */

  footer{
    text-align:center;

    padding:40px 24px;

    color:var(--muted);

    font-size:13px;

    border-top:1px solid var(--border);
  }

  footer .accent{
    color:var(--accent);
  }

  /* =========================================
     RESPONSIVO
  ========================================= */

  @media (max-width:600px){

    header.top{
      padding:18px 16px;
    }

    .logo img{
      height:85px;
    }

    nav.tabs button{
      padding:8px 14px;

      font-size:13px;
    }

    .shell{
      padding-left:16px;
      padding-right:16px;
    }

    .hero{
      padding-top:45px;
    }

    .form-panel{
      padding:22px;
    }

    .contact-card{
      padding:22px;
    }

  }

</style>
</head><body>

<header class="top">

  <div class="logo">

    <a
      href="/TCC_RELPJAM/app/views/home.php"
      class="logo"
    >

      <img
        src="/TCC_RELPJAM/public/images/logotop.png"
        alt="Logo relpjam"
      >

    </a>

  </div>

  <nav class="tabs">

    <button
      type="button"
      class="tab-btn active"
    >
      Contato
    </button>

  </nav>

</header>


<div class="shell">

  <!-- =========================================
       TELA DE CONTATO
  ========================================== -->

  <section
    id="screen-contato"
    class="screen active"
  >

    <div class="hero">

      <span class="eyebrow">
        Fale com a relpjam
      </span>

      <h1>
        Alguma dúvida, parceria<br>
        ou sugestão?
      </h1>

      <p class="lead">
        Nossa equipe está à disposição para ajudar.
        Entre em contato por e-mail, Instagram ou
        envie uma mensagem diretamente pelo formulário.
      </p>

    </div>


    <!-- =====================================
         CARDS DE CONTATO
    ====================================== -->

    <div class="contact-grid">

      <!-- E-MAIL -->

      <a
        class="contact-card"
        href="mailto:relpjamtcc@gmail.com"
      >

        <div class="icon">

          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >

            <rect
              x="2"
              y="4"
              width="20"
              height="16"
              rx="2"
            />

            <path d="m22 6-10 7L2 6"/>

          </svg>

        </div>


        <div>

          <div class="label">
            E-mail
          </div>

          <div class="value">
            relpjamtcc@gmail.com
          </div>

        </div>


        <div class="cta">
          Enviar e-mail →
        </div>

      </a>


      <!-- INSTAGRAM -->

      <a
        class="contact-card"
        href="https://instagram.com/relpjammarketplace_oficial"
        target="_blank"
        rel="noopener noreferrer"
      >

        <div class="icon">

          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >

            <rect
              x="2"
              y="2"
              width="20"
              height="20"
              rx="5"
            />

            <circle
              cx="12"
              cy="12"
              r="4"
            />

            <circle
              cx="17.5"
              cy="6.5"
              r="1"
              fill="currentColor"
              stroke="none"
            />

          </svg>

        </div>


        <div>

          <div class="label">
            Instagram
          </div>

          <div class="value">
            @relpjammarketplace_oficial
          </div>

        </div>


        <div class="cta">
          Seguir perfil →
        </div>

      </a>

    </div>


    <!-- =====================================
         FORMULÁRIO
    ====================================== -->

    <div class="form-panel">

      <h2>
        Ou envie uma mensagem
      </h2>

      <p class="sub">
        Preencha o formulário abaixo.
        Sua mensagem será enviada diretamente
        para o banco de dados da relpjam.
      </p>


      <form id="contact-form">

        <!-- NOME + EMAIL -->

        <div class="field-row">

          <div class="field">

            <label for="nome">
              Nome
            </label>

            <input
              id="nome"
              name="nome"
              type="text"
              placeholder="Seu nome"
              autocomplete="name"
              maxlength="120"
              required
            >

          </div>


          <div class="field">

            <label for="email">
              E-mail
            </label>

            <input
              id="email"
              name="email"
              type="email"
              placeholder="voce@email.com"
              autocomplete="email"
              maxlength="180"
              required
            >

          </div>

        </div>


        <!-- MENSAGEM -->

        <div class="field">

          <label for="mensagem">
            Mensagem
          </label>

          <textarea
            id="mensagem"
            name="mensagem"
            rows="5"
            placeholder="Como podemos ajudar?"
            maxlength="3000"
            required
          ></textarea>

        </div>


        <!-- BOTÃO -->

        <button
          type="submit"
          class="btn"
          id="submit-btn"
        >
          Enviar mensagem
        </button>


        <!-- STATUS -->

        <div
          class="status-msg"
          id="form-status"
          role="alert"
        ></div>

      </form>


      <p class="note">
        Mensagens são gravadas na tabela
        <code>mensagens_contato</code>
        do Supabase.
      </p>

    </div>

  </section>

</div><footer>

  relpjam marketplace —

  <a
    class="accent"
    href="mailto:relpjamtcc@gmail.com"
    style="color:inherit;text-decoration:none;"
  >
    relpjamtcc@gmail.com
  </a>

  ·

  <a
    class="accent"
    href="https://instagram.com/relpjammarketplace_oficial"
    target="_blank"
    rel="noopener noreferrer"
    style="color:inherit;text-decoration:none;"
  >
    @relpjammarketplace_oficial
  </a>

</footer>


<script>

/* ============================================================
   CONFIGURAÇÃO DO SUPABASE
   ============================================================ */

const SUPABASE_URL =
  "https://enkfnnaebiiqyycmegyp.supabase.co";

const SUPABASE_PUBLISHABLE_KEY =
  "sb_publishable_OQBSy-7qdGiEbMtrW9_Mhw_CEgtnosV";


/* ============================================================
   FUNÇÃO DE REQUISIÇÃO
   ============================================================ */

async function supabaseRequest(path, options = {}) {

  const response = await fetch(
    `${SUPABASE_URL}/rest/v1/${path}`,
    {
      ...options,

      headers: {

        "apikey":
          SUPABASE_PUBLISHABLE_KEY,

        "Authorization":
          `Bearer ${SUPABASE_PUBLISHABLE_KEY}`,

        "Content-Type":
          "application/json",

        "Prefer":
          "return=representation",

        ...(options.headers || {})

      }
    }
  );


  const text =
    await response.text();


  /* =========================================
     ERRO
  ========================================== */

  if (!response.ok) {

    console.error(
      "Erro Supabase:",
      response.status,
      text
    );

    throw new Error(
      `Supabase error: ${response.status} - ${text}`
    );
  }


  /* =========================================
     RESPOSTA VAZIA
  ========================================== */

  if (!text) {
    return null;
  }


  /* =========================================
     JSON
  ========================================== */

  try {

    return JSON.parse(text);

  } catch (error) {

    console.error(
      "Resposta inválida do Supabase:",
      text
    );

    throw error;
  }

}


/* ============================================================
   FORMULÁRIO
   ============================================================ */

const form =
  document.getElementById("contact-form");

const statusEl =
  document.getElementById("form-status");

const submitBtn =
  document.getElementById("submit-btn");


/* ============================================================
   ENVIO DA MENSAGEM
   ============================================================ */

form.addEventListener(
  "submit",
  async function(event) {

    event.preventDefault();


    /* =======================================
       LIMPA STATUS
    ======================================== */

    statusEl.className =
      "status-msg";

    statusEl.textContent =
      "";


    /* =======================================
       DESABILITA BOTÃO
    ======================================== */

    submitBtn.disabled = true;

    submitBtn.textContent =
      "Enviando...";


    /* =======================================
       PEGA DADOS DO FORMULÁRIO
    ======================================== */

    const nome =
      document
        .getElementById("nome")
        .value
        .trim();

    const email =
      document
        .getElementById("email")
        .value
        .trim();

    const mensagem =
      document
        .getElementById("mensagem")
        .value
        .trim();


    /* =======================================
       VALIDAÇÃO
    ======================================== */

    if (!nome || !email || !mensagem) {

      statusEl.textContent =
        "Preencha todos os campos.";

      statusEl.classList.add("err");

      submitBtn.disabled = false;

      submitBtn.textContent =
        "Enviar mensagem";

      return;
    }


    /* =======================================
       PAYLOAD
    ======================================== */

    const payload = {

      nome: nome,

      email: email,

      mensagem: mensagem

    };


    console.log(
      "Enviando mensagem:",
      payload
    );


    /* =======================================
       ENVIA PARA SUPABASE
    ======================================== */

    try {

      await supabaseRequest(
        "mensagens_contato",
        {
          method: "POST",

          body:
            JSON.stringify(payload)
        }
      );


      /* =====================================
         SUCESSO
      ====================================== */

      statusEl.textContent =
        "Mensagem enviada com sucesso! Obrigado pelo contato.";

      statusEl.classList.add("ok");


      /* Limpa formulário */

      form.reset();


    } catch (error) {

      /* =====================================
         ERRO
      ====================================== */

      console.error(
        "Erro ao enviar mensagem:",
        error
      );


      statusEl.textContent =
        "Não foi possível enviar sua mensagem. Tente novamente.";

      statusEl.classList.add("err");


    } finally {

      /* =====================================
         RESTAURA BOTÃO
      ====================================== */

      submitBtn.disabled = false;

      submitBtn.textContent =
        "Enviar mensagem";

    }

  }
);

</script>
</body>
</html>