<?php
/*
Template Name: EDU – Pentru Profesori
Template Post Type: page
*/
get_header();
?>
<style>
  /* ===== Animations & visuals (no external deps) ===== */
  .reveal{opacity:0;transform:translateY(18px);transition:opacity .7s ease,transform .7s ease;}
  .reveal.is-visible{opacity:1;transform:none}
  .reveal-delayed{transition-delay:.2s}
  .reveal-more{transition-delay:.35s}
  .floaty{animation:floaty 8s ease-in-out infinite}
  @keyframes floaty{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
  .blob{filter:blur(40px);opacity:.4}
  @keyframes bgshift{0%,100%{transform:translate3d(0,0,0) scale(1)}50%{transform:translate3d(2%, -2%, 0) scale(1.05)}}
  .bg-animated{animation:bgshift 16s ease-in-out infinite}
</style>
<main id="content" class="min-h-screen bg-slate-50 text-slate-900">
  <!-- Hero -->
  <section class="relative overflow-hidden">
    <!-- animated blobs background -->
    <div class="absolute inset-0 -z-10">
      <div class="absolute -top-24 -left-24 w-[36rem] h-[36rem] rounded-full bg-primary-300/40 blob bg-animated"></div>
      <div class="absolute -bottom-24 -right-24 w-[40rem] h-[40rem] rounded-full bg-indigo-300/40 blob bg-animated" style="animation-delay: -8s"></div>
      <div class="absolute inset-0 bg-gradient-to-b from-primary-50 to-transparent"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
      <div class="grid lg:grid-cols-2 items-center gap-12">
        <div class="relative z-10">
          <p class="reveal text-sm font-medium tracking-wide text-primary-700 uppercase">Platformă pentru profesori</p>
          <h1 class="reveal reveal-delayed text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight">
            Transformă feedbackul elevilor în decizii <span class="text-primary-700">smart</span>
          </h1>
          <p class="reveal reveal-more mt-5 text-lg text-slate-700 max-w-prose">
            EDU‑START îți oferă chestionare agile, analize automate și rapoarte clare pentru <strong>SEL</strong>, <strong>LIT</strong> și <strong>NUM</strong>. Închizi buclele de feedback mai repede și îți adaptezi predarea pe date reale.
          </p>
          <div class="reveal mt-8 flex flex-wrap gap-3">
            <a href="<?php echo esc_url( home_url('/inregistrare') ); ?>" class="px-6 py-3 rounded-2xl bg-primary-600 text-white hover:bg-primary-700 shadow transition focus:outline-none focus:ring-2 focus:ring-primary-400">Începe gratuit</a>
            <a href="#cum-functioneaza" class="px-6 py-3 rounded-2xl border border-slate-300 hover:bg-white transition">Cum funcționează</a>
          </div>
          <div class="mt-10 grid grid-cols-3 gap-4">
            <div class="reveal p-4 bg-white/80 backdrop-blur rounded-2xl shadow hover:shadow-lg transition floaty">
              <div class="text-2xl font-bold" x-data="counter(0, 1200, 1400)" x-init="start()" x-text="display">1200</div>
              <div class="text-xs uppercase tracking-wide text-slate-500">profesori</div>
            </div>
            <div class="reveal reveal-delayed p-4 bg-white/80 backdrop-blur rounded-2xl shadow hover:shadow-lg transition floaty" style="animation-delay:.8s">
              <div class="text-2xl font-bold" x-data="counter(0, 120, 1500)" x-init="start()" x-text="display">120</div>
              <div class="text-xs uppercase tracking-wide text-slate-500">școli</div>
            </div>
            <div class="reveal reveal-more p-4 bg-white/80 backdrop-blur rounded-2xl shadow hover:shadow-lg transition floaty" style="animation-delay:1.2s">
              <div class="text-2xl font-bold" x-data="counter(0, 250000, 1600, true)" x-init="start()" x-text="display">250k+</div>
              <div class="text-xs uppercase tracking-wide text-slate-500">răspunsuri</div>
            </div>
          </div>
        </div>
        <div class="relative">
          <div class="reveal relative bg-white rounded-3xl shadow-2xl ring-1 ring-slate-100 overflow-hidden group">
            <img alt="Dashboard profesori" src="https://images.unsplash.com/photo-1553877522-43269d4ea984?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover transition duration-500 group-hover:scale-[1.02]"/>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-tr from-primary-600/10 to-primary-300/10"></div>
          </div>
          <!-- floating badges -->
          <div class="hidden md:block">
            <div class="absolute -top-6 -left-6 reveal p-3 rounded-2xl bg-white shadow-xl ring-1 ring-slate-100 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12l2 2 4-4 1.5 1.5L11 16 7.5 12.5 9 11z"/></svg>
              <span class="text-sm font-medium">Feedback anonim</span>
            </div>
            <div class="absolute -bottom-6 -right-6 reveal reveal-delayed p-3 rounded-2xl bg-white shadow-xl ring-1 ring-slate-100 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5v14m-7-7h14"/></svg>
              <span class="text-sm font-medium">T0 / Ti / T1</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Flow: Colectezi / Analizezi / Acționezi / Comunici -->
  <section id="cum-functioneaza" class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid lg:grid-cols-3 gap-8 lg:gap-12 items-start">
        <div class="lg:col-span-1">
          <h2 class="reveal text-2xl sm:text-3xl font-bold">Închizi bucla de feedback, cap‑coadă</h2>
          <p class="reveal reveal-delayed mt-4 text-slate-700">De la colectare la acțiune, în câteva minute. Fără logare pentru elevi, fără bătăi de cap.</p>
        </div>
        <div class="lg:col-span-2 grid sm:grid-cols-2 gap-6">
          <div class="reveal p-6 bg-white rounded-2xl shadow transition hover:shadow-lg">
            <div class="text-primary-600 font-semibold">1) Colectezi</div>
            <h3 class="mt-2 font-bold text-lg">Chestionare gata de folosit</h3>
            <p class="mt-2 text-slate-700">Template‑uri pentru SEL, LIT, NUM + itemi personalizabili. Link unic per clasă; elevii răspund anonim.</p>
          </div>
          <div class="reveal reveal-delayed p-6 bg-white rounded-2xl shadow transition hover:shadow-lg">
            <div class="text-primary-600 font-semibold">2) Analizezi</div>
            <h3 class="mt-2 font-bold text-lg">Analize automate & insight‑uri</h3>
            <p class="mt-2 text-slate-700">Scoruri pe capitole, delte între etape (T0/Ti/T1), progres pe elev și grup, recomandări asistate de AI.</p>
          </div>
          <div class="reveal p-6 bg-white rounded-2xl shadow transition hover:shadow-lg">
            <div class="text-primary-600 font-semibold">3) Acționezi</div>
            <h3 class="mt-2 font-bold text-lg">Micro‑intervenții la clasă</h3>
            <p class="mt-2 text-slate-700">Rutine de gândire și activități de remediere țintite pe indicatorii sub nivel.</p>
          </div>
          <div class="reveal reveal-delayed p-6 bg-white rounded-2xl shadow transition hover:shadow-lg">
            <div class="text-primary-600 font-semibold">4) Comunici</div>
            <h3 class="mt-2 font-bold text-lg">Rapoarte clare</h3>
            <p class="mt-2 text-slate-700">PDF pe elev, clasă și generație. Vizualizări cu bare, linii și hărți de căldură ușor de explicat părinților.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Funcționalități -->
  <section class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="reveal text-2xl sm:text-3xl font-bold text-center">Funcționalități pentru profesori</h2>
      <p class="reveal reveal-delayed mt-3 text-center text-slate-700 max-w-2xl mx-auto">Concepute să economisească timp și să crească impactul.</p>
      <div class="mt-12 grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php
          $features = [
            ['Bibliotecă de instrumente', 'Chestionare validate pentru SEL, LIT și NUM + posibilitatea de a crea instrumente proprii.'],
            ['Rapoarte T0/Ti/T1', 'Captezi nivelul inițial, urmărești progresul și măsori rezultatele finale prin vizualizări intuitive.'],
            ['Feedback anonim', 'Fără cont pentru elevi. Link securizat, timp de completare sub 5 minute.'],
            ['Recomandări asistate de AI', 'Sugestii de strategii diferențiate în funcție de profilul clasei și obiective.'],
            ['Exporturi & partajare', 'Export PDF/CSV, link share pentru colegi, directori și consilieri.'],
            ['Integrare rutine & planificare', 'Leagă indicatorii de rutine de gândire, obiective de lecție și intervenții rapide.'],
          ];
          $i=0; foreach($features as $f): $i++; ?>
            <div class="reveal <?php echo $i%3==0?'reveal-more':($i%2==0?'reveal-delayed':''); ?> p-6 rounded-2xl border bg-slate-50 transition hover:-translate-y-1 hover:shadow-lg">
              <h3 class="font-semibold"><?php echo esc_html($f[0]); ?></h3>
              <p class="mt-2 text-sm text-slate-700"><?php echo esc_html($f[1]); ?></p>
            </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FAQ + CTA -->
  <section class="py-20 lg:py-28">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="reveal text-2xl sm:text-3xl font-bold">Întrebări frecvente</h2>
      <div class="mt-8 divide-y rounded-2xl border bg-white overflow-hidden">
        <?php $faqs = [
          ['Elevii trebuie să se logheze?', 'Nu. Elevii completează pe baza unui link unic, anonim.'],
          ['Pot modifica instrumentele?', 'Da. Poți porni de la șabloanele noastre și poți adăuga sau edita itemi.'],
          ['Cum folosesc rapoartele la clasă?', 'Recomandările sunt mapate pe indicatorii sub nivel și pe obiectivele unității. Primești idei concrete de mini‑lecții, rutine și sarcini de antrenament.'],
        ]; foreach($faqs as $idx=>$faq): ?>
        <details class="p-6 group reveal <?php echo $idx===1?'reveal-delayed':($idx===2?'reveal-more':''); ?>">
          <summary class="font-semibold cursor-pointer flex items-center justify-between">
            <?php echo esc_html($faq[0]); ?>
            <span class="ml-4 text-slate-400 group-open:rotate-180 transition"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12 15.75l-7-7 1.5-1.5L12 12.75l5.5-5.5 1.5 1.5-7 7z"/></svg></span>
          </summary>
          <p class="mt-3 text-slate-700"><?php echo esc_html($faq[1]); ?></p>
        </details>
        <?php endforeach; ?>
      </div>
      <div class="reveal mt-10 flex items-center gap-3 justify-center">
        <a href="<?php echo esc_url( home_url('/inregistrare') ); ?>" class="px-6 py-3 rounded-2xl bg-primary-600 text-white hover:bg-primary-700 shadow">Creează-ți cont</a>
        <a href="<?php echo esc_url( home_url('/demo') ); ?>" class="px-6 py-3 rounded-2xl border border-slate-300 hover:bg-white">Cere un tur ghidat</a>
      </div>
    </div>
  </section>
</main>
<script>
  // Reveal on scroll (no Alpine plugins needed)
  (function(){
    const els = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('is-visible'); io.unobserve(e.target); } });
    }, {threshold: 0.2});
    els.forEach(el=>io.observe(el));
  })();
  // Animated counters (Alpine component)
  document.addEventListener('alpine:init', () => {
    Alpine.data('counter', (from=0, to=100, duration=1000, compact=false) => ({
      display: from,
      start(){
        const start = performance.now();
        const step = (now)=>{
          const p = Math.min(1, (now - start)/duration);
          const val = Math.floor(from + (to - from) * p);
          this.display = compact ? new Intl.NumberFormat('en', {notation:'compact'}).format(val) : val.toLocaleString('ro-RO');
          if(p<1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
      }
    }));
  });
</script>
<?php get_footer(); ?>