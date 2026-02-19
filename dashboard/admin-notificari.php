<?php
if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) { wp_redirect(home_url('/login')); exit; }
if (!current_user_can('administrator')) { wp_redirect(home_url('/')); exit; }

// chei
$CPT        = 'notificari';
$TAX        = 'grup_notificare';
$META_CORP  = 'corp';
$META_VIEWS = 'vizualizari';

// Flash
$flash = ['type'=>null,'msg'=>null];
if (isset($_GET['ok'])) {
  $flash = ['type'=>'success','msg'=>'Notificarea a fost trimisă.'];
} elseif (!empty($_GET['err'])) {
  $flash = ['type'=>'error','msg'=> sanitize_text_field(wp_unslash($_GET['err'])) ];
}

// Re-populare din transient (dacă am venit după eroare)
$old_title  = '';
$old_corp   = '';
$old_target = [];
$transient_key = 'edustart_notif_form_' . get_current_user_id();
if ($tmp = get_transient($transient_key)) {
  $old_title  = isset($tmp['title'])  ? (string) $tmp['title']  : '';
  $old_corp   = isset($tmp['corp'])   ? (string) $tmp['corp']   : '';
  $old_target = isset($tmp['target']) ? (array)  $tmp['target'] : [];
  delete_transient($transient_key);
}

// Paginare
$paged = max(1, (int)($_GET['paged'] ?? 1));
$q = new WP_Query([
  'post_type'      => $CPT,
  'post_status'    => 'publish',
  'orderby'        => 'date',
  'order'          => 'DESC',
  'posts_per_page' => 12,
  'paged'          => $paged,
]);

// Terms pentru formular
$terms = get_terms([
  'taxonomy'   => $TAX,
  'hide_empty' => false,
]);
if (is_wp_error($terms) || empty($terms)) {
  $defaults = ['general','profesor','tutor'];
  foreach ($defaults as $slug) {
    if (!term_exists($slug, $TAX)) {
      wp_insert_term(ucfirst($slug), $TAX, ['slug' => $slug]);
    }
  }
  $terms = get_terms(['taxonomy'=>$TAX, 'hide_empty'=>false]);
}

// Helper URL curent fără parametrii zgomot
$current_url = get_permalink( get_queried_object_id() );
$base_url    = esc_url( remove_query_arg(['ok','err','paged'], $current_url) );
?>
<?php if ($flash['type']): ?>
  <script>window.__FLASH__ = <?= wp_json_encode($flash, JSON_UNESCAPED_UNICODE); ?>;</script>
<?php endif; ?>

<section class="w-full px-6 pb-8 my-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-slate-900">Notificări</h1>
  </div>

  <div class="mt-6 border-b border-slate-200">
    <nav class="flex gap-6 -mb-px" aria-label="Tabs">
      <button class="px-1 pb-3 text-sm font-medium border-b-2 border-slate-900 text-slate-900" aria-selected="true">
        Listă & Publicare
      </button>
    </nav>
  </div>

  <div class="grid grid-cols-1 gap-8 mt-6 lg:grid-cols-2">
    <!-- ===== Formular creare notificare ===== -->
    <div class="bg-white border shadow-sm rounded-2xl border-slate-200">
      <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-700">Publică notificare</h2>
        <p class="mt-1 text-xs text-slate-500">Adaugă un titlu, corpul notificării și alege grupurile-țintă.</p>
      </div>

      <form method="post" action="<?= esc_url( admin_url('admin-post.php') ); ?>" class="px-6 py-6 space-y-5">
        <?php wp_nonce_field('admin_notif_create'); ?>
        <input type="hidden" name="action" value="edustart_create_notif">
        <input type="hidden" name="_redirect_to" value="<?= esc_url($base_url); ?>">

        <div>
          <label class="block mb-2 text-xs font-semibold uppercase text-slate-600">Titlu</label>
          <input type="text" name="notif_title" required
                  value="<?= esc_attr($old_title); ?>"
                  class="w-full px-3 py-2 text-sm border shadow-sm rounded-xl border-slate-300 hover:border-slate-400 focus:border-slate-800 focus:ring-2 focus:ring-slate-800/30">
        </div>

        <div>
          <label class="block mb-2 text-xs font-semibold uppercase text-slate-600">Corp notificare</label>
          <textarea name="notif_corp" rows="6" required
                    class="w-full px-3 py-2 text-sm border shadow-sm rounded-xl border-slate-300 hover:border-slate-400 focus:border-slate-800 focus:ring-2 focus:ring-slate-800/30"
                    placeholder="Scrie mesajul notificării aici..."><?= esc_textarea($old_corp); ?></textarea>
          <p class="mt-1 text-xs text-slate-500">Se salvează în meta <code>corp</code> (și în content pentru compatibilitate).</p>
        </div>

        <div>
          <label class="block mb-2 text-xs font-semibold uppercase text-slate-600">Trimite către</label>
          <div class="flex flex-wrap gap-3">
            <?php foreach ($terms as $t): ?>
              <?php $checked = in_array($t->slug, (array)$old_target, true) ? 'checked' : ''; ?>
              <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                <input type="checkbox" name="target_groups[]" value="<?= esc_attr($t->slug); ?>" <?= $checked; ?>
                        class="rounded size-4 border-slate-300 text-slate-800 focus:ring-slate-700">
                <span><?= esc_html($t->name . ' '); ?><span class="text-slate-400">(#<?= (int)$t->term_id; ?>)</span></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2">
          <button type="submit"
                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-xl bg-slate-800 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-800/40">
            Publică
          </button>
        </div>
      </form>
    </div>

    <!-- ===== Listă notificări ===== -->
    <div class="bg-white border shadow-sm rounded-2xl border-slate-200">
      <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-sm font-semibold tracking-wide uppercase text-slate-700">Notificări trimise</h2>
        <p class="mt-1 text-xs text-slate-500">Titlu, corp, dată, grupuri și număr de vizualizări.</p>
      </div>

      <div class="divide-y divide-slate-100">
        <?php if ($q->have_posts()): ?>
          <?php while ($q->have_posts()): $q->the_post();
            $pid   = get_the_ID();
            $title = get_the_title();
            $date  = get_the_date('d.m.Y H:i');
            $corp  = get_post_meta($pid, $META_CORP, true);
            if (!$corp) $corp = get_the_content(null, false, $pid);
            $corp_safe = wp_kses_post($corp);
            $views = (int) get_post_meta($pid, $META_VIEWS, true);
            $assigned_terms = get_the_terms($pid, $TAX);
          ?>
            <article class="px-6 py-4 transition bg-white hover:bg-slate-50">
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                  <h3 class="text-[15px] font-semibold text-slate-900 leading-tight">
                    <?= esc_html($title ?: '—'); ?>
                  </h3>
                  <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1">
                      <!-- calendar icon -->
                      <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                      <?= esc_html($date); ?>
                    </span>

                    <span class="text-slate-300">•</span>

                    <span class="inline-flex items-center gap-1">
                      <!-- users/groups icon -->
                      <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                      <?php if (!empty($assigned_terms) && !is_wp_error($assigned_terms)): ?>
                        <?php
                          $names = array_map(fn($t) => $t->name, $assigned_terms);
                          echo esc_html(implode(', ', $names));
                        ?>
                      <?php else: ?>
                        <span class="text-slate-400">Fără grup</span>
                      <?php endif; ?>
                    </span>

                    <span class="text-slate-300">•</span>

                    <span class="inline-flex items-center gap-1">
                      <!-- eye icon -->
                      <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                      <?= (int)$views; ?> vizualizări
                    </span>
                  </div>
                </div>
              </div>

              <div class="mt-3 prose prose-sm max-w-none text-slate-700 [&>p]:my-2">
                <!-- clamp text la ~6 rânduri (dacă ai pluginul line-clamp) -->
                <div class="line-clamp-6">
                  <?= $corp_safe ?: '<p class="text-slate-500">—</p>'; ?>
                </div>
              </div>
            </article>
          <?php endwhile; wp_reset_postdata(); ?>
        <?php else: ?>
          <div class="px-6 py-8 text-sm text-center text-slate-500">Nu există notificări publicate încă.</div>
        <?php endif; ?>
      </div>

      <?php if ($q->max_num_pages > 1): ?>
        <nav class="flex items-center justify-between px-6 py-4 border-t border-slate-100" aria-label="Pagination">
          <div class="text-xs text-slate-500">
            Pagina <span class="font-semibold"><?= (int)$paged; ?></span> din <?= (int)$q->max_num_pages; ?>
          </div>
          <div class="flex items-center gap-1.5">
            <?php
              $prev_url = ($paged > 1) ? esc_url( add_query_arg(['paged'=>$paged-1], $base_url) ) : '#';
              $next_url = ($paged < $q->max_num_pages) ? esc_url( add_query_arg(['paged'=>$paged+1], $base_url) ) : '#';
            ?>
            <a href="<?= $prev_url; ?>"
              class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1.5 text-xs font-medium
                      <?= $paged>1 ? 'border-slate-300 text-slate-700 hover:bg-slate-100' : 'border-slate-200 text-slate-300 cursor-not-allowed'; ?>">
              <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m15 18-6-6 6-6"/></svg>
              Înapoi
            </a>
            <a href="<?= $next_url; ?>"
              class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium
                      <?= $paged<$q->max_num_pages ? 'bg-slate-800 text-white hover:bg-slate-900' : 'bg-slate-200 text-slate-400 cursor-not-allowed'; ?>">
              Înainte
              <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m9 18 6-6-6-6"/></svg>
            </a>
          </div>
        </nav>
      <?php endif; ?>

    </div>
  </div>
</section>

<!-- Toast root -->
<div id="toast-root" class="fixed z-50 space-y-2 top-4 right-4"></div>
<script>
(function(){
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
