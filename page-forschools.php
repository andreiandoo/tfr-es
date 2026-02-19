<?php
/*
Template Name: EDU – Școli & Programe (Final – no Alpine dependency)
Template Post Type: page
*/
get_header();
?>
<style>
  /* No-FOUC for optional [x-cloak] if exists in theme */
  [x-cloak]{display:none !important;}
  /* Hide panels when [hidden] is present (for vanilla tabs) */
  [hidden]{display:none !important;}

  /* Smooth reveal on scroll */
  .reveal{opacity:0;transform:translateY(18px);transition:opacity .6s ease,transform .6s ease}
  .reveal.is-visible{opacity:1;transform:none}
  .reveal-d1{transition-delay:.12s}
  .reveal-d2{transition-delay:.24s}
  .reveal-d3{transition-delay:.36s}
  .reveal-d4{transition-delay:.48s}

  /* Floating background blobs */
  .blob{filter:blur(40px);opacity:.35}
  @keyframes bgshift{0%,100%{transform:translate3d(0,0,0) scale(1)}50%{transform:translate3d(2%, -2%, 0) scale(1.05)}}
  .bg-animated{animation:bgshift 16s ease-in-out infinite}

  /* Active tab button */
  .tab-btn-active{background-color:rgb(37 99 235);color:#fff;box-shadow:0 8px 24px -12px rgba(37,99,235,.6)}
  .tab-btn{background:#fff;border:1px solid rgb(226 232 240);color:rgb(71 85 105)}
  .tab-btn:hover{background:rgb(248 250 252)}
</style>

<main id="content" class="bg-slate-50 text-slate-900">
  <!-- HERO -->
  <section class="relative overflow-hidden">
    <!-- animated bg -->
    <div class="absolute inset-0 -z-10">
      <div class="absolute -top-24 -left-24 w-[38rem] h-[38rem] rounded-full bg-primary-300/40 blob bg-animated"></div>
      <div class="absolute -bottom-28 -right-24 w-[42rem] h-[42rem] rounded-full bg-indigo-300/40 blob bg-animated" style="animation-delay:-8s"></div>
      <div class="absolute inset-0 bg-gradient-to-b from-primary-50 to-transparent"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div class="relative z-10">
          <p class="reveal text-sm font-medium tracking-wide text-primary-700 uppercase">Pentru școli & programe</p>
          <h1 class="reveal reveal-d1 text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight">
            Managementul datelor la nivel de școală și program
          </h1>
          <p class="reveal reveal-d2 mt-4 text-lg text-slate-700">
            Unificați evaluările <strong>SEL</strong>, <strong>LIT</strong> și <strong>NUM</strong> într-o singură platformă.
            Obțineți rapoarte comparative pe clase, generații, filiale și programe – compatibile PNRAS.
          </p>
          <div class="reveal reveal-d3 mt-6 flex flex-wrap gap-3">
            <a href="<?php echo esc_url( home_url('/demo') ); ?>" class="px-6 py-3 rounded-2xl bg-primary-600 text-white hover:bg-primary-700 shadow transition focus:outline-none focus:ring-2 focus:ring-primary-400">Solicită demo</a>
            <a href="#beneficii" class="px-6 py-3 rounded-2xl border border-slate-300 hover:bg-white transition">Beneficii</a>
          </div>

          <!-- KPIs -->
          <div class="mt-10 grid grid-cols-3 gap-4">
            <div class="reveal p-4 bg-white/80 backdrop-blur rounded-2xl shadow ring-1 ring-slate-100 text-center">
              <div class="text-2xl font-bold" data-counter-to="75" data-counter-duration="1200">0</div>
              <div class="text-[11px] uppercase tracking-wide text-slate-500">Unități</div>
            </div>
            <div class="reveal reveal-d1 p-4 bg-white/80 backdrop-blur rounded-2xl shadow ring-1 ring-slate-100 text-center">
              <div class="text-2xl font-bold" data-counter-to="420" data-counter-duration="1300">0</div>
              <div class="text-[11px] uppercase tracking-wide text-slate-500">Clase</div>
            </div>
            <div class="reveal reveal-d2 p-4 bg-white/80 backdrop-blur rounded-2xl shadow ring-1 ring-slate-100 text-center">
              <div class="text-2xl font-bold" data-counter-to="26000" data-counter-duration="1400" data-counter-compact="1">0</div>
              <div class="text-[11px] uppercase tracking-wide text-slate-500">Răspunsuri</div>
            </div>
          </div>
        </div>

        <!-- mockup -->
        <div class="relative">
          <div class="reveal relative bg-white rounded-3xl shadow-2xl ring-1 ring-slate-100 overflow-hidden group">
            <img alt="Dashboard școli" src="https://images.unsplash.com/photo-1557800636-894a64c1696f?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover transition duration-500 group-hover:scale-[1.02]"/>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-tr from-primary-600/10 to-primary-300/10"></div>
          </div>
          <!-- floating badges -->
          <div class="hidden md:block">
            <div class="absolute -top-6 -left-6 reveal p-3 rounded-2xl bg-white shadow-xl ring-1 ring-slate-100 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12l2 2 4-4 1.5 1.5L11 16 7.5 12.5 9 11z"/></svg>
              <span class="text-sm font-medium">Acces pe roluri</span>
            </div>
            <div class="absolute -bottom-6 -right-6 reveal reveal-d1 p-3 rounded-2xl bg-white shadow-xl ring-1 ring-slate-100 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5v14m-7-7h14"/></svg>
              <span class="text-sm font-medium">T0 / Ti / T1</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- STICKY TABS BAR -->
  <section id="tabsBar" class="sticky z-50 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/70 border-b border-slate-200" style="top: 72px;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div role="tablist" aria-label="Secțiuni" class="flex items-center gap-2 h-14">
        <button role="tab" id="tab-beneficii" aria-controls="panel-beneficii" aria-selected="true"
                class="px-4 py-2 rounded-xl transition tab-btn tab-btn-active"
                data-tab-target="beneficii">Beneficii</button>
        <button role="tab" id="tab-guvernanta" aria-controls="panel-guvernanta" aria-selected="false"
                class="px-4 py-2 rounded-xl transition tab-btn"
                data-tab-target="guvernanta">Guvernanță & acces</button>
        <button role="tab" id="tab-implementare" aria-controls="panel-implementare" aria-selected="false"
                class="px-4 py-2 rounded-xl transition tab-btn"
                data-tab-target="implementare">Implementare</button>
      </div>
    </div>
  </section>

  <!-- PANELS -->
  <section class="py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Beneficii -->
      <div id="panel-beneficii" role="tabpanel" aria-labelledby="tab-beneficii">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php
          // icon SVGs
          $icons = [
            '<path d="M4 6h16v4H4zM4 14h8v4H4z"/>', // bars
            '<path d="M12 2l7 4v6c0 5-3.5 9-7 10-3.5-1-7-5-7-10V6l7-4z"/>', // shield
            '<path d="M5 4h14v6H5zM5 14h9v6H5z"/>', // panels
            '<path d="M4 12l4 4 8-8 2 2-10 10-6-6z"/>', // check
            '<path d="M4 6h16M4 12h10M4 18h7"/>', // lines
            '<path d="M12 5v14m-7-7h14"/>', // plus
          ];
          $items = [
            ['Rapoarte multi-nivel', 'Elev, clasă, generație, școală, program. Filtrare pe perioade și etape (T0/Ti/T1).'],
            ['Indicatori simpli & clari', 'SEL – scoruri pe capitole; LIT – Δ Comprehensiune/Δ Acuratețe; NUM – progres pe operații & probleme.'],
            ['Controlul calității datelor', 'Validări, completare minimă, completări unice, loguri de audit și statusuri.'],
            ['Acces pe roluri', 'Director, coordonator program, profesor, consilier — drepturi diferențiate.'],
            ['Compatibil PNRAS', 'Indicatori și exporturi aliniate cerințelor de raportare/monitorizare.'],
            ['Suport & formare', 'Onboarding, ghiduri, webinarii și bune practici pentru implementare.'],
          ];
          foreach ($items as $i => $it): ?>
            <div class="reveal <?php echo 'reveal-d'.($i%4); ?> p-6 rounded-2xl bg-white border ring-1 ring-transparent hover:ring-primary-200 hover:shadow-lg transition">
              <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-700 ring-1 ring-primary-100">
                  <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><?php echo $icons[$i]; ?></svg>
                </span>
                <div>
                  <h3 class="font-semibold"><?php echo esc_html($it[0]); ?></h3>
                  <p class="mt-2 text-sm text-slate-700"><?php echo esc_html($it[1]); ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Guvernanță & acces -->
      <div id="panel-guvernanta" role="tabpanel" aria-labelledby="tab-guvernanta" hidden>
        <div class="grid lg:grid-cols-2 gap-6">
          <!-- Policies -->
          <div class="reveal p-6 rounded-2xl bg-white border">
            <h3 class="font-semibold">Securitate & confidențialitate</h3>
            <ul class="mt-3 text-sm list-disc pl-5 space-y-2 text-slate-700">
              <li>Răspunsuri anonime pentru elevi; agregare la nivel de grup.</li>
              <li>Conformitate GDPR și control granular al permisiunilor.</li>
              <li>Backup automat și jurnalizare a acțiunilor.</li>
            </ul>

            <!-- Accordion (native <details>) -->
            <div class="mt-6 space-y-3">
              <details class="rounded-xl border p-4 group">
                <summary class="cursor-pointer font-medium flex items-center justify-between">
                  Politici de acces pe roluri
                  <span class="text-slate-400 group-open:rotate-180 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 15.75l-7-7 1.5-1.5L12 12.75l5.5-5.5 1.5 1.5-7 7z"/></svg>
                  </span>
                </summary>
                <p class="mt-3 text-sm text-slate-700">
                  Directorii văd toate clasele, profesorii doar clasele proprii; coordonatorii programului au acces agregat pe școli/filiale.
                </p>
              </details>
              <details class="rounded-xl border p-4 group">
                <summary class="cursor-pointer font-medium flex items-center justify-between">
                  Protecția datelor elevilor (GDPR)
                  <span class="text-slate-400 group-open:rotate-180 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 15.75l-7-7 1.5-1.5L12 12.75l5.5-5.5 1.5 1.5-7 7z"/></svg>
                  </span>
                </summary>
                <p class="mt-3 text-sm text-slate-700">
                  Date minime, păstrate pe perioade limitate; exporturi auditate, pseudonimizare în rapoarte publice.
                </p>
              </details>
            </div>
          </div>

          <!-- Roles & flows -->
          <div class="reveal reveal-d1 p-6 rounded-2xl bg-white border">
            <h3 class="font-semibold">Roluri & fluxuri</h3>
            <p class="mt-2 text-sm text-slate-700">
              Configurați unități, clase și utilizatori. Alocați chestionare la nivel de școală sau program și urmăriți progresul în timp real.
            </p>
            <img class="mt-4 rounded-xl" src="https://images.unsplash.com/photo-1527474305487-b87b222841cc?q=80&w=1200&auto=format&fit=crop" alt="Acces pe roluri">
            <!-- mini-matrix -->
            <div class="mt-6 overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="text-left text-slate-600">
                    <th class="py-2 pr-4">Rol</th>
                    <th class="py-2 pr-4">Vizualizare</th>
                    <th class="py-2 pr-4">Export</th>
                    <th class="py-2">Administrare</th>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <?php
                  $rows = [
                    ['Director', 'Toate clasele', 'Da', 'Parțial'],
                    ['Coordonator program', 'Școli/filiale', 'Da', 'Parțial'],
                    ['Profesor', 'Clase proprii', 'Da', 'Nu'],
                    ['Consilier', 'Clase alocate', 'Limitat', 'Nu'],
                  ];
                  foreach ($rows as $r): ?>
                  <tr>
                    <td class="py-2 pr-4 font-medium"><?php echo esc_html($r[0]); ?></td>
                    <td class="py-2 pr-4"><?php echo esc_html($r[1]); ?></td>
                    <td class="py-2 pr-4"><?php echo esc_html($r[2]); ?></td>
                    <td class="py-2"><?php echo esc_html($r[3]); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Implementare -->
      <div id="panel-implementare" role="tabpanel" aria-labelledby="tab-implementare" hidden>
        <ol class="relative border-l border-slate-200 ml-2 pl-6 space-y-8">
          <?php
          $steps = [
            ['Kick-off & mapare', 'Stabilim obiectivele (SEL/LIT/NUM), calendarul T0–Ti–T1 și cine face ce.'],
            ['Onboarding', 'Creăm conturi, importăm clasele și oferim training pentru echipă.'],
            ['Colectare & monitorizare', 'Deschidem ferestrele de completare, urmărim statusul și intervenim unde e nevoie.'],
            ['Analiză & decizii', 'Rapoarte comparative, planuri de acțiune și comunicare către stakeholderi.'],
          ];
          foreach ($steps as $i => $st): ?>
          <li class="reveal <?php echo 'reveal-d'.($i%4); ?>">
            <span class="absolute -left-[11px] top-0 inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary-600 text-white text-xs font-semibold"><?php echo $i+1; ?></span>
            <div class="p-6 rounded-2xl bg-white border hover:shadow-lg transition">
              <div class="font-semibold"><?php echo esc_html($st[0]); ?></div>
              <p class="text-sm text-slate-700 mt-2"><?php echo esc_html($st[1]); ?></p>
            </div>
          </li>
          <?php endforeach; ?>
        </ol>

        <div class="reveal mt-8">
          <a href="<?php echo esc_url( home_url('/pilot-scoala') ); ?>" class="px-6 py-3 rounded-2xl bg-primary-600 text-white hover:bg-primary-700 shadow">Începe pilotul în școala ta</a>
        </div>
      </div>

    </div>
  </section>

  <!-- CTA -->
  <section class="py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="reveal bg-gradient-to-tr from-primary-700 to-indigo-600 text-white rounded-3xl p-8 md:p-12 shadow grid md:grid-cols-2 gap-6 items-center ring-1 ring-white/10">
        <div>
          <h3 class="text-2xl font-bold">Rulați evaluările pentru întreaga școală</h3>
          <p class="mt-2 text-white/90">Cont instituțional cu acces pe roluri și rapoarte dedicate conducerii.</p>
        </div>
        <div class="flex md:justify-end items-center gap-3">
          <a href="<?php echo esc_url( home_url('/oferta') ); ?>" class="px-6 py-3 rounded-2xl bg-white text-primary-700 font-semibold hover:shadow">Solicită ofertă</a>
          <a href="<?php echo esc_url( home_url('/demo') ); ?>" class="px-6 py-3 rounded-2xl ring-1 ring-white/40 hover:bg-white/10">Programează un demo</a>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
  // ===== Sticky tabs bar: place under your header automatically
  (function(){
    const bar = document.getElementById('tabsBar');
    if(!bar) return;
    const header = document.querySelector('.site-header, header');
    const h = header ? header.offsetHeight : 72;
    bar.style.top = (h || 72) + 'px';
  })();

  // ===== Tabs (vanilla JS, accessible)
  (function(){
    const buttons = document.querySelectorAll('[data-tab-target]');
    const panels  = {
      beneficii:    document.getElementById('panel-beneficii'),
      guvernanta:   document.getElementById('panel-guvernanta'),
      implementare: document.getElementById('panel-implementare')
    };
    function activate(name){
      // buttons
      buttons.forEach(btn=>{
        const on = btn.getAttribute('data-tab-target')===name;
        btn.classList.toggle('tab-btn-active', on);
        btn.classList.toggle('tab-btn', !on);
        btn.setAttribute('aria-selected', on ? 'true':'false');
      });
      // panels
      Object.entries(panels).forEach(([key, el])=>{
        if(!el) return;
        if(key===name){ el.removeAttribute('hidden'); }
        else { el.setAttribute('hidden',''); }
      });
      // update hash without jumping
      if(history.replaceState){ history.replaceState(null, '', '#' + name); }
    }
    // click handlers
    buttons.forEach(btn=>btn.addEventListener('click', (e)=>{
      e.preventDefault();
      activate(btn.getAttribute('data-tab-target'));
    }));
    // initial from hash
    const hash = (location.hash||'').replace('#','');
    const initial = ['beneficii','guvernanta','implementare'].includes(hash) ? hash : 'beneficii';
    activate(initial);
  })();

  // ===== Counters (vanilla)
  (function(){
    const els = document.querySelectorAll('[data-counter-to]');
    const io = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(!e.isIntersecting) return;
        const el = e.target;
        const to = parseInt(el.dataset.counterTo || '0', 10);
        const dur = parseInt(el.dataset.counterDuration || '1000', 10);
        const compact = !!el.dataset.counterCompact;
        const start = performance.now();
        const step = (now)=>{
          const p = Math.min(1, (now-start)/dur);
          const val = Math.floor(to * p);
          el.textContent = compact
            ? new Intl.NumberFormat('en',{notation:'compact'}).format(val)
            : val.toLocaleString('ro-RO');
          if(p<1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
        io.unobserve(el);
      });
    },{threshold:.35});
    els.forEach(el=>io.observe(el));
  })();

  // ===== Reveal on scroll
  (function(){
    const els = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('is-visible'); io.unobserve(e.target);} });
    }, {threshold: .18});
    els.forEach(el=>io.observe(el));
  })();

  // ===== Open tab if user clicks on a deep link (#beneficii / #guvernanta / #implementare)
  (function(){
    window.addEventListener('hashchange', ()=>{
      const name = (location.hash||'').replace('#','');
      const btn = document.querySelector('[data-tab-target="'+name+'"]');
      if(btn){ btn.click(); }
    });
  })();
</script>
<?php get_footer(); ?>
