<?php
/*
Template Name: EDU – LIT (descriere)
Template Post Type: page
*/
/* ===== FILE: page-lit.php ===== */
get_header();
?>
<main id="content" class="bg-slate-50 text-slate-900">
  <!-- Hero -->
  <section class="bg-gradient-to-b from-primary-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <h1 class="text-3xl sm:text-4xl font-bold">LIT – Literație (citire)</h1>
        <p class="mt-4 text-lg text-slate-700">Instrumentul LIT urmărește evoluția <strong>Comprehensiunii</strong> și <strong>Acurateții</strong> lecturii. Scopul este progresul pe parcursul anului, nu etichetarea.</p>
        <div class="mt-6 flex gap-3">
          <a href="#ce-masuram" class="px-6 py-3 rounded-2xl bg-primary-600 text-white hover:bg-primary-700">Ce măsurăm</a>
          <a href="#rapoarte" class="px-6 py-3 rounded-2xl border">Rapoarte</a>
        </div>
      </div>
      <div class="relative">
        <div class="relative bg-white rounded-3xl shadow-2xl ring-1 ring-slate-100 overflow-hidden">
          <img alt="Vizualizare LIT" src="https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover"/>
        </div>
      </div>
    </div>
  </section>

  <section id="ce-masuram" class="py-16 lg:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-3 gap-8">
      <div class="lg:col-span-1">
        <h2 class="text-2xl font-bold">Ce măsurăm</h2>
        <p class="mt-3 text-slate-700">LIT e centrat pe două axe simple, raportate ca <strong>Δ</strong> între etape:</p>
      </div>
      <div class="lg:col-span-2 grid sm:grid-cols-2 gap-6">
        <div class="p-6 rounded-2xl bg-white border">
          <h3 class="font-semibold">Δ Comprehensiune</h3>
          <p class="mt-2 text-sm text-slate-700">Înțelegerea textului (idei principale, inferențe, vocabular). Itemi adaptați nivelului clasei.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border">
          <h3 class="font-semibold">Δ Acuratețe</h3>
          <p class="mt-2 text-sm text-slate-700">Citire corectă și fluentă. Evidențiem tiparele erorilor pentru intervenții țintite.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border">
          <h3 class="font-semibold">T0 – T1 (opțional Ti)</h3>
          <p class="mt-2 text-sm text-slate-700">Stabilim baseline (T0), apoi măsurăm progresul la final (T1). Etapa intermediară (Ti) e disponibilă pentru programe.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border">
          <h3 class="font-semibold">Remediere & practică</h3>
          <p class="mt-2 text-sm text-slate-700">Sarcini scurte pentru decodare, fluență și comprehensiune, integrate în rutina săptămânală.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="rapoarte" class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-2xl font-bold text-center">Cum arată rapoartele LIT</h2>
      <div class="mt-10 grid md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl border">
          <h3 class="font-semibold">Elev</h3>
          <p class="mt-2 text-sm text-slate-700">Progres individual pe Δ Comprehensiune și Δ Acuratețe, recomandări personalizate și fișe de lucru.</p>
        </div>
        <div class="p-6 rounded-2xl border">
          <h3 class="font-semibold">Clasă</h3>
          <p class="mt-2 text-sm text-slate-700">Distribuții, grupuri de nivel, identificarea elevilor care au nevoie de sprijin.</p>
        </div>
        <div class="p-6 rounded-2xl border">
          <h3 class="font-semibold">Program</h3>
          <p class="mt-2 text-sm text-slate-700">Compari clase, școli sau filiale. Exporturi pentru raportarea către finanțatori.</p>
        </div>
      </div>
      <div class="mt-8 text-center">
        <a href="<?php echo esc_url( home_url('/esantion-lit') ); ?>" class="px-6 py-3 rounded-2xl bg-primary-600 text-white hover:bg-primary-700">Descarcă un eșantion</a>
      </div>
    </div>
  </section>
</main>
<?php get_footer(); ?>