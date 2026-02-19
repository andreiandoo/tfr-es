<?php
/* Template Name: Admin Options */
if (!is_user_logged_in()) { wp_redirect(home_url('/login')); exit; }
if (!current_user_can('administrator')) { wp_redirect(home_url('/')); exit; }

$flash = ['type'=>null,'msg'=>null];

// Option keys (păstrăm fix aceleași ca în plugin)
$OPT_API_KEY       = 'edustart_api_key';
$OPT_WEBHOOK_SECRET= 'edustart_webhook_secret';

// Helpers
function es_mask_key($k, $show = 4){
  $k = (string)$k;
  if ($k === '') return '—';
  $len = strlen($k);
  if ($len <= $show) return $k;
  return str_repeat('•', max(0, $len - $show)) . substr($k, -$show);
}
function es_regen_api_key(){
  // compat cu pluginul (32 chars, doar alfanumeric)
  return wp_generate_password(32, false, false);
}
function es_regen_webhook_secret(){
  // compat cu pluginul (48 chars, include simboluri)
  return wp_generate_password(48, true, true);
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'admin_options_api')) {

  // Regenerate API key
  if (!empty($_POST['regen_api'])) {
    $new = es_regen_api_key();
    if (update_option($OPT_API_KEY, $new)) {
      $flash = ['type'=>'success','msg'=>'API Key a fost regenerată.'];
    } else {
      $flash = ['type'=>'error','msg'=>'Nu am putut salva API Key.'];
    }
  }

  // Regenerate Webhook secret
  if (!empty($_POST['regen_webhook'])) {
    $new = es_regen_webhook_secret();
    if (update_option($OPT_WEBHOOK_SECRET, $new)) {
      $flash = ['type'=>'success','msg'=>'Webhook Secret a fost regenerat.'];
    } else {
      $flash = ['type'=>'error','msg'=>'Nu am putut salva Webhook Secret.'];
    }
  }

  // Regenerate both
  if (!empty($_POST['regen_both'])) {
    $ok1 = update_option($OPT_API_KEY, es_regen_api_key());
    $ok2 = update_option($OPT_WEBHOOK_SECRET, es_regen_webhook_secret());
    if ($ok1 && $ok2) {
      $flash = ['type'=>'success','msg'=>'Ambele chei au fost regenerate.'];
    } else {
      $flash = ['type'=>'error','msg'=>'Nu am putut regenera ambele chei. Încearcă separat.'];
    }
  }
}

// Read current options
$api_key   = get_option($OPT_API_KEY);
$webhook   = get_option($OPT_WEBHOOK_SECRET);
?>
<?php if ($flash['type']): ?>
  <script>window.__FLASH__ = <?= wp_json_encode($flash, JSON_UNESCAPED_UNICODE); ?>;</script>
<?php endif; ?>

<section class="w-full px-6 pb-8 my-6">
  <!-- Secondary menu / Tabs header -->
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-slate-900">Opțiuni Admin</h1>
  </div>

  <!-- Tabs bar -->
  <div class="mt-6 border-b border-slate-200">
    <nav class="flex gap-6 -mb-px" aria-label="Tabs">
      <button data-tab="api" class="px-1 pb-3 text-sm font-medium border-b-2 border-transparent tab-btn text-slate-600 hover:text-slate-800"
              aria-controls="tab-api" aria-selected="true">
        API
      </button>
      <!-- viitoare tab-uri: <button data-tab="notificari" ...>Notificări</button> etc. -->
    </nav>
  </div>

  <!-- Tabs content -->
  <div class="mt-6">
    <!-- TAB: API -->
    <section id="tab-api" class="tab-panel">
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- API Key -->
        <div class="bg-white border shadow-sm rounded-2xl border-slate-200">
          <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-700">API Key</h2>
            <p class="mt-1 text-xs text-slate-500">Folosită pentru autentificarea pe endpoint-urile de test (<code>X-Api-Key</code>).</p>
          </div>
          <div class="px-6 py-6 space-y-4">
            <div>
              <label class="block mb-2 text-xs font-semibold uppercase text-slate-600">Valoare</label>
              <div class="relative">
                <input id="api_key" type="password" value="<?= esc_attr($api_key ?: '') ?>" readonly
                        class="w-full px-3 py-2 pr-24 text-sm border shadow-sm rounded-xl border-slate-300 bg-slate-50">
                <div class="absolute inset-y-0 right-0 flex items-center gap-2 pr-2">
                  <button type="button" data-reveal="#api_key"
                          class="inline-flex items-center px-2 py-1 text-xs font-medium border rounded-lg border-slate-300 text-slate-700 hover:bg-slate-100">
                    Afișează
                  </button>
                  <button type="button" data-copy="#api_key"
                          class="inline-flex items-center px-2 py-1 text-xs font-medium text-white rounded-lg bg-slate-800 hover:bg-slate-900">
                    Copiază
                  </button>
                </div>
              </div>
              <p class="mt-1 text-xs text-slate-500">Mascată implicit. Poți afișa temporar pentru a verifica.</p>
            </div>

            <form method="post" class="flex items-center gap-3">
              <?php wp_nonce_field('admin_options_api'); ?>
              <button type="submit" name="regen_api"
                      class="inline-flex items-center px-3 py-2 text-xs font-medium text-white rounded-xl bg-amber-600 hover:bg-amber-700">
                Regenerare API Key
              </button>
              <button type="submit" name="regen_both"
                      class="inline-flex items-center px-3 py-2 text-xs font-medium text-white rounded-xl bg-slate-800 hover:bg-slate-900">
                Regenerare ambele
              </button>
            </form>

            <div class="text-xs text-slate-500">
              <p><strong>Heads-up:</strong> după regenerare, actualizează clienții care trimit <code>X-Api-Key</code>.</p>
            </div>
          </div>
        </div>

        <!-- Webhook Secret -->
        <div class="bg-white border shadow-sm rounded-2xl border-slate-200">
          <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-700">Webhook Secret</h2>
            <p class="mt-1 text-xs text-slate-500">Cheie HMAC pentru validarea semnăturilor webhook (header-ele <code>X-Signature</code>, <code>X-Timestamp</code>).</p>
          </div>
          <div class="px-6 py-6 space-y-4">
            <div>
              <label class="block mb-2 text-xs font-semibold uppercase text-slate-600">Valoare</label>
              <div class="relative">
                <input id="webhook_secret" type="password" value="<?= esc_attr($webhook ?: '') ?>" readonly
                        class="w-full px-3 py-2 pr-24 text-sm border shadow-sm rounded-xl border-slate-300 bg-slate-50">
                <div class="absolute inset-y-0 right-0 flex items-center gap-2 pr-2">
                  <button type="button" data-reveal="#webhook_secret"
                          class="inline-flex items-center px-2 py-1 text-xs font-medium border rounded-lg border-slate-300 text-slate-700 hover:bg-slate-100">
                    Afișează
                  </button>
                  <button type="button" data-copy="#webhook_secret"
                          class="inline-flex items-center px-2 py-1 text-xs font-medium text-white rounded-lg bg-slate-800 hover:bg-slate-900">
                    Copiază
                  </button>
                </div>
              </div>
              <p class="mt-1 text-xs text-slate-500">Păstrează secretul doar între servere (EduStart ↔ Cube).</p>
            </div>

            <form method="post" class="flex items-center gap-3">
              <?php wp_nonce_field('admin_options_api'); ?>
              <button type="submit" name="regen_webhook"
                      class="inline-flex items-center px-3 py-2 text-xs font-medium text-white rounded-xl bg-amber-600 hover:bg-amber-700">
                Regenerare Webhook Secret
              </button>
              <button type="submit" name="regen_both"
                      class="inline-flex items-center px-3 py-2 text-xs font-medium text-white rounded-xl bg-slate-800 hover:bg-slate-900">
                Regenerare ambele
              </button>
            </form>

            <div class="text-xs text-slate-500">
              <p><strong>Heads-up:</strong> după regenerare, actualizează secretul și pe partea Cube. Semnăturile vechi vor pica.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- /TAB: API -->
  </div>
</section>

<!-- Toast root -->
<div id="toast-root" class="fixed z-50 space-y-2 top-4 right-4"></div>

<script>
(function(){
  // Tabs
  const btns = document.querySelectorAll('.tab-btn');
  const panels = document.querySelectorAll('.tab-panel');
  function activate(tab){
    btns.forEach(b=>{
      const on = b.getAttribute('data-tab') === tab;
      b.classList.toggle('border-slate-900', on);
      b.classList.toggle('text-slate-900', on);
      b.classList.toggle('border-transparent', !on);
      b.classList.toggle('text-slate-600', !on);
      b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    panels.forEach(p=>{
      p.style.display = (p.id === 'tab-' + tab) ? '' : 'none';
    });
  }
  // Default: API
  activate('api');
  btns.forEach(b=>b.addEventListener('click', ()=> activate(b.getAttribute('data-tab'))));

  // Reveal / Copy
  document.querySelectorAll('[data-reveal]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const sel = btn.getAttribute('data-reveal');
      const el  = document.querySelector(sel);
      if (!el) return;
      el.type = (el.type === 'password') ? 'text' : 'password';
      btn.textContent = (el.type === 'password') ? 'Afișează' : 'Ascunde';
    });
  });
  document.querySelectorAll('[data-copy]').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      const sel = btn.getAttribute('data-copy');
      const el  = document.querySelector(sel);
      if (!el) return;
      el.type = 'text'; // temporar ca să copiem exact
      try {
        await navigator.clipboard.writeText(el.value || '');
        showToast('success', 'Copiat în clipboard.');
      } catch(e){
        // fallback
        el.select(); document.execCommand('copy');
        showToast('success', 'Copiat în clipboard.');
      } finally {
        el.type = 'password';
      }
    });
  });

  // Toasts
  const root = document.getElementById('toast-root');
  function showToast(type, msg, timeout=3000) {
    if (!root) return;
    const base = document.createElement('div');
    const palette = type === 'success'
      ? {wrap:'bg-white border-emerald-200', bar:'bg-emerald-500', title:'text-emerald-700'}
      : {wrap:'bg-white border-rose-200',    bar:'bg-rose-500',    title:'text-rose-700'};
    base.className = 'toast-item pointer-events-auto relative w-80 overflow-hidden rounded-xl border shadow-lg ring-1 ring-black/5 translate-y-[-8px] opacity-0 transition-all';
    base.innerHTML = `
      <div class="absolute left-0 top-0 h-full w-1 ${palette.bar}"></div>
      <div class="p-3 ${palette.wrap}">
        <div class="flex items-start gap-3">
          <div class="mt-0.5">
            ${type==='success'
              ? '<svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>'
              : '<svg class="size-5 text-rose-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10.012 10.012 0 0 0 12 2Zm1 15h-2v-2h2Zm0-4h-2V7h2Z"/></svg>'
            }
          </div>
          <div class="min-w-0">
            <div class="text-sm font-semibold ${palette.title}">${type==='success'?'Succes':'Eroare'}</div>
            <div class="mt-0.5 text-sm text-slate-700 break-words">${msg}</div>
          </div>
          <button class="ml-auto text-slate-400 hover:text-slate-600" aria-label="Închide" onclick="this.closest('.toast-item').remove()">✕</button>
        </div>
      </div>
    `;
    root.appendChild(base);
    requestAnimationFrame(()=>{ base.style.opacity = '1'; base.style.transform = 'translateY(0)'; });
    setTimeout(()=>{ base.style.opacity = '0'; base.style.transform = 'translateY(-8px)'; setTimeout(()=> base.remove(), 250); }, timeout);
  }

  if (window.__FLASH__ && window.__FLASH__.type && window.__FLASH__.msg) {
    showToast(window.__FLASH__.type, window.__FLASH__.msg);
  }
})();
</script>
