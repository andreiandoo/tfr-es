<?php
/*
Template Name: EDU – SEL (descriere)
Template Post Type: page
*/
/* ===== FILE: page-sel.php ===== */
get_header();
?>
<main id="content" class="bg-slate-50 text-slate-900">
  <!-- Hero -->
  <section class="bg-gradient-to-b from-primary-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <h1 class="text-3xl sm:text-4xl font-bold">SEL – Dezvoltarea competențelor socio‑emoționale</h1>
        <p class="mt-4 text-lg text-slate-700">Instrumentul SEL măsoară progresul elevilor în arii precum conștientizarea de sine, autoreglarea, relațiile sănătoase și luarea deciziilor responsabile.</p>
        <div class="mt-6 flex gap-3">
          <a href="#structura" class="px-6 py-3 rounded-2xl bg-primary-600 text-white hover:bg-primary-700">Vezi structura</a>
          <a href="#rapoarte" class="px-6 py-3 rounded-2xl border">Exemple de rapoarte</a>
        </div>
      </div>
      <div class="relative">
        <div class="relative bg-white rounded-3xl shadow-2xl ring-1 ring-slate-100 overflow-hidden">
          <img alt="Vizualizare SEL" src="https://images.unsplash.com/photo-1510936111840-65e151ad71bb?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover"/>
        </div>
      </div>
    </div>
  </section>

  <!-- Structură & ce măsurăm -->
  <section id="structura" class="py-16 lg:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
          <h2 class="text-2xl font-bold">Ce măsurăm</h2>
          <p class="mt-3 text-slate-700">SEL este organizat pe <strong>capitole</strong> (ex.: Conștientizare de sine, Autoreglare, Empatie, Relații, Decizii). Fiecare capitol include itemi observaționali și de auto‑raportare adaptați vârstei.</p>
        </div>
        <div class="lg:col-span-2 grid sm:grid-cols-2 gap-6">
          <div class="p-6 rounded-2xl bg-white border">
            <h3 class="font-semibold">Etape T0 – Ti – T1</h3>
            <p class="mt-2 text-sm text-slate-700">T0 = nivel inițial, Ti = intermediar, T1 = final. Rapoartele compară scorurile pe capitole și arată progresul.</p>
          </div>
          <div class="p-6 rounded-2xl bg-white border">
            <h3 class="font-semibold">Scoruri pe capitole</h3>
            <p class="mt-2 text-sm text-slate-700">Medii pe clasă și distribuții pe elevi. Hărți de căldură pentru a vizualiza punctele forte și ariile de lucru.</p>
          </div>
          <div class="p-6 rounded-2xl bg-white border">
            <h3 class="font-semibold">Status & completare</h3>
            <p class="mt-2 text-sm text-slate-700">Procent de completare, status de validare și audit trail pentru transparență.</p>
          </div>
          <div class="p-6 rounded-2xl bg-white border">
            <h3 class="font-semibold">Recomandări didactice</h3>
            <p class="mt-2 text-sm text-slate-700">Rutine de gândire, strategii de reglare emoțională și exerciții de relaționare mapate pe indicatorii sub nivel.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Rapoarte -->
  <section id="rapoarte" class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-2xl font-bold text-center">Cum arată rapoartele SEL</h2>
      <div class="mt-10 grid md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl border">
          <h3 class="font-semibold">Raport pe elev</h3>
          <p class="mt-2 text-sm text-slate-700">Scoruri pe capitole cu trend T0→Ti→T1, recomandări personalizate și întrebări de reflecție.</p>
        </div>
        <div class="p-6 rounded-2xl border">
          <h3 class="font-semibold">Raport pe clasă</h3>
          <p class="mt-2 text-sm text-slate-700">Medii și distribuții, elevi care au nevoie de suport, propuneri de micro‑intervenții.</p>
        </div>
        <div class="p-6 rounded-2xl border">
          <h3 class="font-semibold">Raport pe generație</h3>
          <p class="mt-2 text-sm text-slate-700">Compari clase și ani, vezi impactul programelor și al rutinei SEL în timp.</p>
        </div>
      </div>
      <div class="mt-8 text-center">
        <a href="<?php echo esc_url( home_url('/demo') ); ?>" class="px-6 py-3 rounded-2xl bg-primary-600 text-white hover:bg-primary-700">Încearcă un demo</a>
      </div>
    </div>
  </section>
</main>
<?php get_footer(); ?>