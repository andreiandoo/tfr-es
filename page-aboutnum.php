<?php
/*
Template Name: EDU – NUM (descriere)
Template Post Type: page
*/
/* ===== FILE: page-num.php ===== */
get_header();
?>
<main id="content" class="bg-slate-50 text-slate-900">
  <!-- Hero -->
  <section class="bg-gradient-to-b from-primary-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <h1 class="text-3xl sm:text-4xl font-bold">NUM – Numeracy (matematică de bază)</h1>
        <p class="mt-4 text-lg text-slate-700">Instrumentul NUM monitorizează competențele de numerație: înțelegerea numerelor, operații, raționament și rezolvare de probleme – cu accent pe <strong>acuratețe</strong> și <strong>strategie</strong>.</p>
        <div class="mt-6 flex gap-3">
          <a href="#ce-masuram" class="px-6 py-3 rounded-2xl bg-primary-600 text-white hover:bg-primary-700">Ce măsurăm</a>
          <a href="#rapoarte" class="px-6 py-3 rounded-2xl border">Rapoarte</a>
        </div>
      </div>
      <div class="relative">
        <div class="relative bg-white rounded-3xl shadow-2xl ring-1 ring-slate-100 overflow-hidden">
          <img alt="Vizualizare NUM" src="https://images.unsplash.com/photo-1516979187457-637abb4f9353?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover"/>
        </div>
      </div>
    </div>
  </section>

  <section id="ce-masuram" class="py-16 lg:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-3 gap-8">
      <div class="lg:col-span-1">
        <h2 class="text-2xl font-bold">Ce măsurăm</h2>
        <p class="mt-3 text-slate-700">Proiectat pentru ciclul primar și gimnazial, NUM include itemi scurți și probleme ancorate în viața reală.</p>
      </div>
      <div class="lg:col-span-2 grid sm:grid-cols-2 gap-6">
        <div class="p-6 rounded-2xl bg-white border">
          <h3 class="font-semibold">Number sense</h3>
          <p class="mt-2 text-sm text-slate-700">Comparații, ordonări, compuneri/descompuneri, reprezentări multiple.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border">
          <h3 class="font-semibold">Operații & proceduri</h3>
          <p class="mt-2 text-sm text-slate-700">Aritmetică de bază, fracții, proporții. Verificăm atât rezultatul, cât și metoda.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border">
          <h3 class="font-semibold">Raționament & probleme</h3>
          <p class="mt-2 text-sm text-slate-700">Alegerea strategiei, justificarea pașilor, estimare și verificare.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border">
          <h3 class="font-semibold">Δ Progres</h3>
          <p class="mt-2 text-sm text-slate-700">Comparam scorurile T0–T1 (opțional Ti) pe arii. Evidențiem câștiguri și goluri.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="rapoarte" class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-2xl font-bold text-center">Cum arată rapoartele NUM</h2>
      <div class="mt-10 grid md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl border">
          <h3 class="font-semibold">Elev</h3>
          <p class="mt-2 text-sm text-slate-700">Profil pe arii, tipare de erori, recomandări de exerciții și probleme.</p>
        </div>
        <div class="p-6 rounded-2xl border">
          <h3 class="font-semibold">Clasă</h3>
          <p class="mt-2 text-sm text-slate-700">Grupe de nivel, recomandări de ateliere și perechi tutor‑peer.</p>
        </div>
        <div class="p-6 rounded-2xl border">
          <h3 class="font-semibold">Program</h3>
          <p class="mt-2 text-sm text-slate-700">Comparații între clase/școli, progres pe cohorte, exporturi CSV/PDF.</p>
        </div>
      </div>
      <div class="mt-8 text-center">
        <a href="<?php echo esc_url( home_url('/esantion-num') ); ?>" class="px-6 py-3 rounded-2xl bg-primary-600 text-white hover:bg-primary-700">Vezi un eșantion</a>
      </div>
    </div>
  </section>
</main>
<?php get_footer(); ?>