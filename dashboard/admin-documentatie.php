<?php

?>
<main class="relative w-full">

    <div class="sticky top-0 z-50 w-full bg-white border-b border-slate-200">
        <ul class="flex items-center px-4 py-2 gap-x-4">
            <li class="">
                <a href="#profesori" class="inline-block px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:underline">
                    Profesori
                </a>
            </li>
            <li class="">
                <a href="#adaugareutilizatori" class="inline-block px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:underline">
                    Utilizatori
                </a>
            </li>
        </ul>
    </div>
    
    <!-- ================ /DOCUMENTAȚIE — Pagina Profesori ================ -->
    <div id="profesori" x-data="{ tocOpen: true }" class="px-4 py-8 mx-auto sm:px-6 lg:px-8 text-slate-900">

        <!-- Header -->
        <header class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">📚 Pagina Profesori — Ghid de utilizare & note tehnice</h1>
        </header>

        <div class="flex items-start gap-x-6">

            <!-- TOC -->
            <section class="w-[30%] sticky top-16">
                <nav class="p-4 bg-white border rounded-xl border-slate-200" x-show="tocOpen" x-collapse>
                    <ol class="space-y-2 text-sm leading-6">
                    <li><a class="font-semibold text-sky-700 hover:underline" href="#profesori-intro">1. Ce face pagina</a></li>
                    <li><a class="font-semibold text-sky-700 hover:underline" href="#profesori-acces">2. Acces & Permisii</a></li>

                    <!-- 3. Interfața (toggle) -->
                    <li x-data="{ open: false }" class="space-y-1">
                        <div class="flex items-center justify-between">
                            <a class="font-semibold text-sky-700 hover:underline" href="#profesori-ui">3. Interfața</a>
                            <button type="button"
                                    class="inline-flex items-center px-2 py-1 text-xs text-slate-600"
                                    @click.stop="open = !open"
                                    :aria-expanded="open.toString()"
                                    aria-controls="toc-ui">
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                        <ul id="toc-ui" class="mt-1 ml-4 space-y-1 list-disc" x-show="open" x-collapse>
                            <li><a class="hover:underline" href="#profesori-ui-actiuni">Bară acțiuni</a></li>
                            <li><a class="hover:underline" href="#profesori-ui-filtre">Filtre & căutare</a></li>
                            <li><a class="hover:underline" href="#profesori-ui-coloane">Toggle coloane</a></li>
                            <li><a class="hover:underline" href="#profesori-ui-tabel">Tabelul</a></li>
                        </ul>
                    </li>

                    <!-- 4. Operațiuni (toggle) -->
                    <li x-data="{ open: false }" class="space-y-1">
                        <div class="flex items-center justify-between">
                            <a class="font-semibold text-sky-700 hover:underline" href="#profesori-operatiuni">4. Operațiuni</a>
                            <button type="button"
                                    class="inline-flex items-center px-2 py-1 text-xs text-slate-600"
                                    @click.stop="open = !open"
                                    :aria-expanded="open.toString()"
                                    aria-controls="toc-ops">
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                        <ul id="toc-ops" class="mt-1 ml-4 space-y-1 list-disc" x-show="open" x-collapse>
                            <li><a class="hover:underline" href="#profesori-add">Adăugare</a></li>
                            <li><a class="hover:underline" href="#profesori-edit">Editare</a></li>
                            <li><a class="hover:underline" href="#profesori-scoli">Alocare școli (AJAX)</a></li>
                            <li><a class="hover:underline" href="#profesori-reset">Resetare parolă</a></li>
                            <li><a class="hover:underline" href="#profesori-delete">Ștergere</a></li>
                            <li><a class="hover:underline" href="#profesori-export">Export CSV</a></li>
                        </ul>
                    </li>

                    <li><a class="text-sky-700 hover:underline" href="#profesori-paginare">5. Paginare & performanță</a></li>

                    <!-- 6. Model de date (toggle) -->
                    <li x-data="{ open: false }" class="space-y-1">
                        <div class="flex items-center justify-between">
                            <a class="font-semibold text-sky-700 hover:underline" href="#profesori-model">6. Model de date</a>
                            <button type="button"
                                    class="inline-flex items-center px-2 py-1 text-xs text-slate-600 "
                                    @click.stop="open = !open"
                                    :aria-expanded="open.toString()"
                                    aria-controls="toc-model">
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                        <ul id="toc-model" class="mt-1 ml-4 space-y-1 list-disc" x-show="open" x-collapse>
                            <li><a class="hover:underline" href="#profesori-db">Tabele DB</a></li>
                            <li><a class="hover:underline" href="#profesori-usermeta">Chei usermeta</a></li>
                        </ul>
                    </li>

                    <li><a class="font-semibold text-sky-700 hover:underline" href="#profesori-endpoints">7. Endpoints & Hook-uri</a></li>
                    <li><a class="font-semibold text-sky-700 hover:underline" href="#profesori-securitate">8. Securitate</a></li>
                    <li><a class="font-semibold text-sky-700 hover:underline" href="#profesori-extensii">9. Extensibilitate</a></li>
                    <li><a class="font-semibold text-sky-700 hover:underline" href="#profesori-troubleshooting">10. Troubleshooting</a></li>
                    <li><a class="font-semibold text-sky-700 hover:underline" href="#profesori-faq">11. FAQ</a></li>
                    <li><a class="font-semibold text-sky-700 hover:underline" href="#profesori-debug">12. Modul DEBUG</a></li>
                    </ol>
                </nav>
            </section>

            <!-- Conținut documentație -->
            <div class="p-6 prose bg-white border max-w-none rounded-xl border-slate-200">

            <!-- 1 -->
            <section id="profesori-intro" class="scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">1) Ce face pagina</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-intro')">Copiaza link</button>
                </div>
                <p class="mt-2 text-slate-700">
                Pagina <span class="font-medium">Profesori</span> listează utilizatorii cu rol <code class="rounded bg-slate-100 px-1.5 py-0.5 text-[12px]">profesor</code>,
                permițând: <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-800">căutare & filtrare</span>,
                <span class="rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs text-indigo-800">adăugare / editare</span>,
                <span class="rounded-full border border-sky-200 bg-sky-50 px-2 py-0.5 text-xs text-sky-800">alocare școli</span>,
                <span class="rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs text-amber-800">resetare parolă</span>,
                <span class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs text-rose-800">ștergere</span> și
                <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-700">export CSV</span>.
                </p>
            </section>

            <!-- 2 -->
            <section id="profesori-acces" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">2) Acces & Permisii</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-acces')">Copiaza link</button>
                </div>
                <ul class="pl-5 mt-2 list-disc text-slate-700">
                <li><span class="font-medium">Admin</span> (<code class="bg-slate-100 px-1 rounded text-[12px]">manage_options</code>): acces total, poate șterge.</li>
                <li><span class="font-medium">Tutor</span> (rol <code class="bg-slate-100 px-1 rounded text-[12px]">tutor</code>): vede și gestionează doar profesorii cu
                    <code class="bg-slate-100 px-1 rounded text-[12px]">assigned_tutor_id = ID tutor</code>; nu vede butonul „Șterge”.</li>
                <li>Alți utilizatori: ecran <span class="text-rose-700">Acces restricționat</span>.</li>
                </ul>
            </section>

            <!-- 3 -->
            <section id="profesori-ui" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">3) Interfața</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-ui')">Copiaza link</button>
                </div>

                <h3 id="profesori-ui-actiuni" class="mt-4 text-base font-semibold scroll-mt-24">Bară acțiuni</h3>
                <ul class="pl-5 mt-1 list-disc text-slate-700">
                <li><span class="font-medium">Adaugă profesor</span> — deschide modalul comun (Add/Edit) construit cu Tailwind UI + Alpine pentru stările de deschidere/închidere.</li>
                <li><span class="font-medium">Export CSV</span> — descarcă tot setul filtrat (nu doar pagina curentă).</li>
                <li><span class="font-medium">Documentație</span> — link către această pagină.</li>
                </ul>

                <h3 id="profesori-ui-filtre" class="mt-6 text-base font-semibold scroll-mt-24">Filtre & căutare</h3>
                <p class="mt-1 text-slate-700">
                Căutare nume/email + filtre: <em>Nivel</em>, <em>Statut</em>, <em>An generație</em>, <em>Județ</em>, <em>An program</em>, <em>RSOI</em>.
                Filtrarea pentru „An generație” și „Județ” se aplică în memorie după agregările din DB.
                </p>

                <h3 id="profesori-ui-coloane" class="mt-6 text-base font-semibold scroll-mt-24">Toggle coloane</h3>
                <p class="mt-1 text-slate-700">
                Preferințele de afișare se salvează în <code class="bg-slate-100 px-1 rounded text-[12px]">localStorage</code> la cheia
                <code class="bg-slate-100 px-1 rounded text-[12px]">professors_table_cols_v1</code>.
                </p>

                <h3 id="profesori-ui-tabel" class="mt-6 text-base font-semibold scroll-mt-24">Tabelul</h3>
                <p class="mt-1 text-slate-700">
                Coloane cheie: Tutor, Cod SLF, Statut (badge), Nivel, An program, RSOI, Teach, Materie, #Elevi, Școli, Județ, Ultima activitate, Înregistrare, Generații.
                În ultima coloană: <em>Edit</em>, <em>Reset parolă</em> și (doar Admin) <em>Șterge</em>.
                </p>
            </section>

            <!-- 4 -->
            <section id="profesori-operatiuni" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">4) Operațiuni</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-operatiuni')">Copiaza link</button>
                </div>

                <h3 id="profesori-add" class="mt-4 text-base font-semibold scroll-mt-24">Adăugare</h3>
                <p class="mt-1 text-slate-700">
                Click pe <em>Adaugă profesor</em> → completezi câmpurile → <em>Salvează</em>. Opțional, bifezi „Trimite email de resetare parolă după creare”.
                Modalul e Tailwind-based; logica de stare (deschis/închis, validări ușoare) poate folosi Alpine (<code class="bg-slate-100 px-1 rounded text-[12px]">x-data</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">x-show</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">@click</code>).
                </p>

                <h3 id="profesori-edit" class="mt-6 text-base font-semibold scroll-mt-24">Editare</h3>
                <p class="mt-1 text-slate-700">
                Butonul <em>Edit</em> deschide același modal, precompletat cu datele existente (payload JSON în <code class="bg-slate-100 px-1 rounded text-[12px]">data-prof</code>).
                La „Salvează”, datele se suprascriu pentru <code class="bg-slate-100 px-1 rounded text-[12px]">user_id</code>.
                </p>

                <h3 id="profesori-scoli" class="mt-6 text-base font-semibold scroll-mt-24">Alocare școli (AJAX)</h3>
                <div class="p-4 mt-1 bg-white border rounded-lg border-slate-200">
                <p class="text-slate-700">
                    Scrii minim 2 caractere: se face apel AJAX către <code class="bg-slate-100 px-1 rounded text-[12px]">edu_search_schools</code>.
                    Acceptă <code class="bg-slate-100 px-1 rounded text-[12px]">q</code>/<code class="bg-slate-100 px-1 rounded text-[12px]">term</code>/<code class="bg-slate-100 px-1 rounded text-[12px]">search</code>/<code class="bg-slate-100 px-1 rounded text-[12px]">s</code> + <code class="bg-slate-100 px-1 rounded text-[12px]">nonce</code>.
                    Selectarea adaugă „chips” cu școlile alese (input-uri ascunse <code class="bg-slate-100 px-1 rounded text-[12px]">assigned_school_ids[]</code>).
                </p>
                <pre class="p-4 mt-3 overflow-x-auto text-xs rounded-lg bg-slate-900 text-slate-100">
                    <code>POST /wp-admin/admin-ajax.php?action=edu_search_schools&amp;nonce=...&amp;q=iasi
                    Răspuns (ex.):
                    [
                    {"id":123,"name":"Școala X","city":"Iași","county":"Iași","cod":"IS123"},
                    ...
                    ]</code>
                    </pre>
                </div>

                <h3 id="profesori-reset" class="mt-6 text-base font-semibold scroll-mt-24">Resetare parolă</h3>
                <p class="mt-1 text-slate-700">
                Butonul <em>Reset parolă</em> apelează endpointul de salvare cu <code class="bg-slate-100 px-1 rounded text-[12px]">send_reset_link=1</code>.
                Emailul standard WP este trimis către utilizator.
                </p>

                <h3 id="profesori-delete" class="mt-6 text-base font-semibold scroll-mt-24">Ștergere</h3>
                <p class="mt-1 text-slate-700">
                Vizibil doar pentru Admin. La click, se afișează un modal de confirmare (Tailwind + Alpine) și se face apel către
                <code class="bg-slate-100 px-1 rounded text-[12px]">edu_delete_user</code>. Pe succes, rândul e scos din tabel.
                </p>

                <h3 id="profesori-export" class="mt-6 text-base font-semibold scroll-mt-24">Export CSV</h3>
                <p class="mt-1 text-slate-700">
                Exportă întregul set <em>filtrat</em>, include BOM pentru Excel și cap de tabel. Construit via <code class="bg-slate-100 px-1 rounded text-[12px]">admin-post.php</code>
                cu acțiunea <code class="bg-slate-100 px-1 rounded text-[12px]">edus_export_teachers_csv</code> și aceiași parametri de filtrare.
                </p>
            </section>

            <!-- 5 -->
            <section id="profesori-paginare" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">5) Paginare & performanță</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-paginare')">Copiaza link</button>
                </div>
                <ul class="pl-5 mt-2 list-disc text-slate-700">
                <li>Query-ul ia toți profesorii (fără limită) → filtrări suplimentare în memorie → paginare manuală.</li>
                <li>La volume mari (zeci de mii), ia în calcul mutarea filtrelor pe SQL (joinuri + <code class="bg-slate-100 px-1 rounded text-[12px]">WHERE</code>) și/sau caching.</li>
                </ul>
            </section>

            <!-- 6 -->
            <section id="profesori-model" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">6) Model de date</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-model')">Copiaza link</button>
                </div>

                <h3 id="profesori-db" class="mt-4 text-base font-semibold scroll-mt-24">Tabele DB</h3>
                <ul class="pl-5 mt-1 list-disc text-slate-700">
                <li><code class="bg-slate-100 px-1 rounded text-[12px]">wp_users</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">wp_usermeta</code></li>
                <li><code class="bg-slate-100 px-1 rounded text-[12px]">wp_edu_generations</code> — generații pe profesor</li>
                <li><code class="bg-slate-100 px-1 rounded text-[12px]">wp_edu_students</code> — #elevi/profesor</li>
                <li><code class="bg-slate-100 px-1 rounded text-[12px]">wp_edu_schools</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">wp_edu_cities</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">wp_edu_counties</code></li>
                </ul>

                <h3 id="profesori-usermeta" class="mt-6 text-base font-semibold scroll-mt-24">Chei usermeta</h3>
                <div class="mt-2 overflow-hidden border rounded-xl border-slate-200">
                <table class="min-w-full text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 font-semibold text-left text-slate-700">Cheie</th>
                        <th class="px-3 py-2 font-semibold text-left text-slate-700">Rol</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">assigned_tutor_id</code></td><td class="px-3 py-2">vizibilitate tutor</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">user_status_profesor</code></td><td class="px-3 py-2">badge „în așteptare / activ / …”</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">nivel_predare</code></td><td class="px-3 py-2">prescolar / primar / gimnazial / liceu (normalizat)</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">materia_predata</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">materia_alta</code></td><td class="px-3 py-2">materie + fallback text liber</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">cod_slf</code></td><td class="px-3 py-2">cod intern</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">statut_prof</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">calificare</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">experienta</code></td><td class="px-3 py-2">detalii HR</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">segment_rsoi</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">generatie</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">an_program</code></td><td class="px-3 py-2">filtre & afișare</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">assigned_school_ids</code></td><td class="px-3 py-2">array ID-uri din <code class="bg-slate-100 px-1 rounded text-[12px]">edu_schools</code></td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">profile_image</code></td><td class="px-3 py-2">ID media avatar</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">last_login</code>/<code class="bg-slate-100 px-1 rounded text-[12px]">last_activity</code>/<code class="bg-slate-100 px-1 rounded text-[12px]">last_seen</code></td><td class="px-3 py-2">ultimă activitate</td></tr>
                    </tbody>
                </table>
                </div>
            </section>

            <!-- 7 -->
            <section id="profesori-endpoints" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">7) Endpoints & Hook-uri</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-endpoints')">Copiaza link</button>
                </div>

                <div class="grid gap-4 mt-3 md:grid-cols-2">
                <div class="p-4 bg-white border rounded-xl border-slate-200">
                    <h4 class="font-medium">Căutare școli</h4>
                    <p class="mt-1 text-sm text-slate-700"><code class="bg-slate-100 px-1 rounded text-[12px]">action=edu_search_schools</code></p>
                    <pre class="p-4 mt-3 overflow-x-auto text-xs rounded-lg bg-slate-900 text-slate-100"><code>POST /wp-admin/admin-ajax.php
            action=edu_search_schools&amp;nonce=...&amp;q=cluj</code></pre>
                </div>
                <div class="p-4 bg-white border rounded-xl border-slate-200">
                    <h4 class="font-medium">Creare/Update profesor</h4>
                    <p class="mt-1 text-sm text-slate-700"><code class="bg-slate-100 px-1 rounded text-[12px]">action=edu_save_user_form</code></p>
                    <pre class="p-4 mt-3 overflow-x-auto text-xs rounded-lg bg-slate-900 text-slate-100"><code>POST /wp-admin/admin-ajax.php
            action=edu_save_user_form&amp;nonce=...&amp;user_id=123&amp;first_name=...&amp;...&amp;send_reset_link=0|1</code></pre>
                </div>
                <div class="p-4 bg-white border rounded-xl border-slate-200">
                    <h4 class="font-medium">Ștergere profesor (Admin)</h4>
                    <p class="mt-1 text-sm text-slate-700"><code class="bg-slate-100 px-1 rounded text-[12px]">action=edu_delete_user</code></p>
                    <pre class="p-4 mt-3 overflow-x-auto text-xs rounded-lg bg-slate-900 text-slate-100"><code>POST /wp-admin/admin-ajax.php
            action=edu_delete_user&amp;nonce=...&amp;user_id=123</code></pre>
                </div>
                <div class="p-4 bg-white border rounded-xl border-slate-200">
                    <h4 class="font-medium">Export CSV</h4>
                    <p class="mt-1 text-sm text-slate-700"><code class="bg-slate-100 px-1 rounded text-[12px]">admin-post.php?action=edus_export_teachers_csv</code></p>
                    <pre class="p-4 mt-3 overflow-x-auto text-xs rounded-lg bg-slate-900 text-slate-100"><code>GET /wp-admin/admin-post.php?action=edus_export_teachers_csv&amp;nonce=...&amp;s=...&amp;nivel=...&amp;...</code></pre>
                </div>
                </div>

                <div class="p-3 mt-4 text-sm border rounded-lg border-emerald-200 bg-emerald-50 text-emerald-900">
                Toate apelurile AJAX folosesc <span class="font-medium">nonce</span> (<code class="bg-emerald-100 px-1 rounded text-[12px]">edu_nonce</code>) și trimit cookie-urile
                (<code class="bg-emerald-100 px-1 rounded text-[12px]">credentials:'same-origin'</code>).
                </div>
            </section>

            <!-- 8 -->
            <section id="profesori-securitate" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">8) Securitate</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-securitate')">Copiaza link</button>
                </div>
                <ul class="pl-5 mt-2 list-disc text-slate-700">
                <li>Gărzile de acces în PHP: doar <span class="font-medium">Admin</span> + <span class="font-medium">Tutor</span>.</li>
                <li>Nonce la fiecare apel și verificare capabilități pe server.</li>
                <li>Sanitizare + escape în UI (<code class="bg-slate-100 px-1 rounded text-[12px]">sanitize_text_field</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">esc_attr</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">esc_html</code>).</li>
                </ul>
            </section>

            <!-- 9 -->
            <section id="profesori-extensii" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">9) Extensibilitate</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-extensii')">Copiaza link</button>
                </div>
                <p class="mt-2 text-slate-700">
                Vrei un câmp nou (ex. <code class="bg-slate-100 px-1 rounded text-[12px]">facebook_profile</code>)?
                </p>
                <ol class="pl-5 mt-2 list-decimal text-slate-700">
                <li><span class="font-medium">UI</span> — adaugi input în modal (Tailwind). Include-l și în payloadul de <span class="font-medium">Edit</span> (atributul <code class="bg-slate-100 px-1 rounded text-[12px]">data-prof</code>).</li>
                <li><span class="font-medium">Server</span> — în <code class="bg-slate-100 px-1 rounded text-[12px]">edu_save_user_form</code> faci <code class="bg-slate-100 px-1 rounded text-[12px]">update_user_meta</code>.</li>
                <li><span class="font-medium">Tabel</span> — adaugi coloană nouă (inclusiv în toggle și CSV).</li>
                </ol>
                <p class="mt-2 text-xs text-slate-500">
                „Soft delete” alternativ: setezi <code class="bg-slate-100 px-1 rounded text-[12px]">user_status_profesor = eliminat</code> și ascunzi prin filtrare (păstrezi istoricul).
                </p>
            </section>

            <!-- 10 -->
            <section id="profesori-troubleshooting" class="mt-10 scroll-mt-24" x-data="{openA:true, openB:false, openC:false, openD:false, openE:false}">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">10) Troubleshooting</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-troubleshooting')">Copiaza link</button>
                </div>

                <!-- Acordeon Alpine -->
                <div class="mt-3 space-y-2">
                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="openA=!openA">
                    Căutarea de școli nu întoarce nimic
                    <span x-text="openA ? '–' : '+'"></span>
                    </button>
                    <div x-show="openA" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Verifică endpointul <code class="bg-slate-100 px-1 rounded text-[12px]">edu_search_schools</code>, <span class="font-medium">nonce</span>-ul și că trimiți
                    <code class="bg-slate-100 px-1 rounded text-[12px]">q/term/search/s</code>. Requestul trebuie să includă <code class="bg-slate-100 px-1 rounded text-[12px]">credentials:'same-origin'</code>.
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="openB=!openB">
                    Nu se salvează modificările
                    <span x-text="openB ? '–' : '+'"></span>
                    </button>
                    <div x-show="openB" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Confirmă <code class="bg-slate-100 px-1 rounded text-[12px]">edu_save_user_form</code>, capabilitățile utilizatorului curent și validările server.
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="openC=!openC">
                    Reset parolă nu trimite email
                    <span x-text="openC ? '–' : '+'"></span>
                    </button>
                    <div x-show="openC" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Trimite <code class="bg-slate-100 px-1 rounded text-[12px]">send_reset_link=1</code> și verifică livrarea emailurilor (ex: WP Mail/SMTP).
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="openD=!openD">
                    Butonul „Șterge” nu apare
                    <span x-text="openD ? '–' : '+'"></span>
                    </button>
                    <div x-show="openD" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    E vizibil doar pentru Admin (<code class="bg-slate-100 px-1 rounded text-[12px]">current_user_can('manage_options')</code>).
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="openE=!openE">
                    Exportul CSV nu include un câmp
                    <span x-text="openE ? '–' : '+'"></span>
                    </button>
                    <div x-show="openE" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Adaugă-l și în headerul CSV și în valorile din loop-ul de generare.
                    </div>
                </div>
                </div>
            </section>

            <!-- 11 -->
            <section id="profesori-faq" class="mt-10 scroll-mt-24" x-data="{open1:true, open2:false, open3:false}">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">11) FAQ</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-faq')">Copiaza link</button>
                </div>

                <div class="mt-3 space-y-2">
                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="open1=!open1">
                    Tutorul poate edita orice profesor?
                    <span x-text="open1 ? '–' : '+'"></span>
                    </button>
                    <div x-show="open1" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Doar profesorii cu <code class="bg-slate-100 px-1 rounded text-[12px]">assigned_tutor_id</code> = ID-ul lui.
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="open2=!open2">
                    Putem importa profesori din CSV?
                    <span x-text="open2 ? '–' : '+'"></span>
                    </button>
                    <div x-show="open2" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Momentan, doar export. Pentru import, recomand un endpoint dedicat sau WP-CLI.
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="open3=!open3">
                    Cum schimb denumirile opțiunilor (statut, calificare etc.)?
                    <span x-text="open3 ? '–' : '+'"></span>
                    </button>
                    <div x-show="open3" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Actualizezi array-urile din PHP: <code class="bg-slate-100 px-1 rounded text-[12px]">$prof_status</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">$calificare_opts</code>, etc.
                    </div>
                </div>
                </div>
            </section>

            <!-- 12 -->
            <section id="profesori-debug" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-semibold">12) Modul DEBUG</h2>
                    <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                        @click="navigator.clipboard.writeText(location.origin+location.pathname+'#profesori-debug')">Copiaza link</button>
                </div>
                <p class="mt-2 text-slate-700">
                Adaugă <code class="bg-slate-100 px-1 rounded text-[12px]">&amp;debug=1</code> în URL pentru blocul cu rolul curent, filtrele active și totaluri — rapid pentru testare.
                </p>
                <div class="mt-6">
                <a href="#profesori" class="text-sm text-sky-700 hover:underline">↑ Înapoi sus</a>
                </div>
                <p class="mt-8 text-xs text-slate-500">Ultima actualizare: 17.10.2025</p>
            </section>
            </div>
        </div>
    </div>
    <!-- ================ /DOCUMENTAȚIE — Pagina Profesori ================ -->

    <!-- ================ /DOCUMENTAȚIE — Pagina Utilizatori ================ -->
    <div id="adaugareutilizatori" x-data="{ tocOpen: true }" class="px-4 py-8 mx-auto sm:px-6 lg:px-8 text-slate-900">

        <!-- Header -->
        <header class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">👥 Pagina Utilizatori — Ghid de utilizare & note tehnice</h1>
        </header>

        <div class="flex items-start gap-x-6">

            <!-- TOC -->
            <section class="w-[30%] sticky top-16">
            <nav class="p-4 bg-white border rounded-xl border-slate-200" x-show="tocOpen" x-collapse>
                <ol class="space-y-2 text-sm leading-6">
                <li><a class="font-semibold text-sky-700 hover:underline" href="#users-intro">1. Ce face pagina</a></li>
                <li><a class="font-semibold text-sky-700 hover:underline" href="#users-acces">2. Acces & Permisii</a></li>

                <!-- 3. Interfața (toggle) -->
                <li x-data="{ open: false }" class="space-y-1">
                    <div class="flex items-center justify-between">
                    <a class="font-semibold text-sky-700 hover:underline" href="#users-ui">3. Interfața</a>
                    <button type="button"
                        class="inline-flex items-center px-2 py-1 text-xs text-slate-600"
                        @click.stop="open = !open"
                        :aria-expanded="open.toString()"
                        aria-controls="toc-ui-users">
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    </div>
                    <ul id="toc-ui-users" class="mt-1 ml-4 space-y-1 list-disc" x-show="open" x-collapse>
                    <li><a class="hover:underline" href="#users-ui-actiuni">Bară acțiuni</a></li>
                    <li><a class="hover:underline" href="#users-ui-filtre">Filtre & căutare</a></li>
                    <li><a class="hover:underline" href="#users-ui-tabel">Tabelul</a></li>
                    </ul>
                </li>

                <!-- 4. Operațiuni (toggle) -->
                <li x-data="{ open: false }" class="space-y-1">
                    <div class="flex items-center justify-between">
                    <a class="font-semibold text-sky-700 hover:underline" href="#users-operatiuni">4. Operațiuni</a>
                    <button type="button"
                        class="inline-flex items-center px-2 py-1 text-xs text-slate-600"
                        @click.stop="open = !open"
                        :aria-expanded="open.toString()"
                        aria-controls="toc-ops-users">
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    </div>
                    <ul id="toc-ops-users" class="mt-1 ml-4 space-y-1 list-disc" x-show="open" x-collapse>
                    <li><a class="hover:underline" href="#users-add">Adăugare utilizator</a></li>
                    <li><a class="hover:underline" href="#users-edit">Editare utilizator</a></li>
                    <li><a class="hover:underline" href="#users-pass">Setare/ schimbare parolă</a></li>
                    <li><a class="hover:underline" href="#users-prof">Câmpuri extinse — Profesor</a></li>
                    <li><a class="hover:underline" href="#users-tutor">Status — Tutor</a></li>
                    <li><a class="hover:underline" href="#users-schools">Alocare școli (AJAX)</a></li>
                    <li><a class="hover:underline" href="#users-delete">Ștergere</a></li>
                    </ul>
                </li>

                <li><a class="font-semibold text-sky-700 hover:underline" href="#users-paginare">5. Paginare & performanță</a></li>

                <!-- 6. Model de date (toggle) -->
                <li x-data="{ open: false }" class="space-y-1">
                    <div class="flex items-center justify-between">
                    <a class="font-semibold text-sky-700 hover:underline" href="#users-model">6. Model de date</a>
                    <button type="button"
                        class="inline-flex items-center px-2 py-1 text-xs text-slate-600"
                        @click.stop="open = !open"
                        :aria-expanded="open.toString()"
                        aria-controls="toc-model-users">
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    </div>
                    <ul id="toc-model-users" class="mt-1 ml-4 space-y-1 list-disc" x-show="open" x-collapse>
                    <li><a class="hover:underline" href="#users-db">Tabele DB</a></li>
                    <li><a class="hover:underline" href="#users-usermeta">Chei usermeta</a></li>
                    </ul>
                </li>

                <li><a class="font-semibold text-sky-700 hover:underline" href="#users-endpoints">7. Endpoints & Hook-uri</a></li>
                <li><a class="font-semibold text-sky-700 hover:underline" href="#users-securitate">8. Securitate</a></li>
                <li><a class="font-semibold text-sky-700 hover:underline" href="#users-extensii">9. Extensibilitate</a></li>
                <li><a class="font-semibold text-sky-700 hover:underline" href="#users-troubleshooting">10. Troubleshooting</a></li>
                <li><a class="font-semibold text-sky-700 hover:underline" href="#users-faq">11. FAQ</a></li>
                <li><a class="font-semibold text-sky-700 hover:underline" href="#users-debug">12. Modul DEBUG</a></li>
                </ol>
            </nav>
            </section>

            <!-- Conținut -->
            <div class="p-6 prose bg-white border max-w-none rounded-xl border-slate-200">

            <!-- 1 -->
            <section id="users-intro" class="scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">1) Ce face pagina</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-intro')">Copiază link</button>
                </div>
                <p class="mt-2 text-slate-700">
                Pagina <span class="font-medium">Utilizatori</span> listează toți userii din WordPress și oferă:
                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-800">căutare & filtrare</span>,
                <span class="rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs text-indigo-800">adăugare / editare</span>,
                <span class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs text-rose-800">ștergere</span> și
                <span class="rounded-full border border-sky-200 bg-sky-50 px-2 py-0.5 text-xs text-sky-800">câmpuri extinse pentru Profesor</span> (inclusiv alocarea de școli via AJAX).
                </p>
            </section>

            <!-- 2 -->
            <section id="users-acces" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">2) Acces & Permisii</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-acces')">Copiază link</button>
                </div>
                <ul class="pl-5 mt-2 list-disc text-slate-700">
                <li><span class="font-medium">Admin</span> (<code class="bg-slate-100 px-1 rounded text-[12px]">manage_options</code>): acces total, poate crea/edita/șterge orice, inclusiv roluri <em>administrator</em> și <em>editor</em>.</li>
                <li><span class="font-medium">Tutor</span>: vizualizează lista și poate crea/edita <em>profesori</em> (doar pe cei coordonați), <span class="font-medium">nu</span> poate crea/edita/șterge Admin/Editor.</li>
                <li>Alți utilizatori: ecran <span class="text-rose-700">Acces restricționat</span>.</li>
                </ul>
            </section>

            <!-- 3 -->
            <section id="users-ui" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">3) Interfața</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-ui')">Copiază link</button>
                </div>

                <h3 id="users-ui-actiuni" class="mt-4 text-base font-semibold scroll-mt-24">Bară acțiuni</h3>
                <ul class="pl-5 mt-1 list-disc text-slate-700">
                <li><span class="font-medium">Adaugă utilizator</span> — deschide modalul comun Add/Edit (Tailwind). Afișează dinamic câmpurile în funcție de rol.</li>
                </ul>

                <h3 id="users-ui-filtre" class="mt-6 text-base font-semibold scroll-mt-24">Filtre & căutare</h3>
                <p class="mt-1 text-slate-700">
                Căutare după nume/email + filtru după rol. Paginarea este manuală, pe setul deja filtrat în memorie.
                </p>

                <h3 id="users-ui-tabel" class="mt-6 text-base font-semibold scroll-mt-24">Tabelul</h3>
                <p class="mt-1 text-slate-700">
                Coloane: Nume, Email, Rol (plural dacă userul are mai multe), Status, Înregistrare, Ultima activitate, Acțiuni.
                În acțiuni: <em>Edit</em> și (doar Admin, non-sensibil) <em>Șterge</em>.
                </p>
            </section>

            <!-- 4 -->
            <section id="users-operatiuni" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">4) Operațiuni</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-operatiuni')">Copiază link</button>
                </div>

                <h3 id="users-add" class="mt-4 text-base font-semibold scroll-mt-24">Adăugare utilizator</h3>
                <ul class="pl-5 mt-1 list-disc text-slate-700">
                <li>Câmpuri de bază: Prenume, Nume, Email (este și <code class="bg-slate-100 px-1 rounded text-[12px]">user_login</code>), Telefon, Rol.</li>
                <li>Parolă opțională la creare (vezi secțiunea de mai jos).</li>
                <li>Doar <span class="font-medium">Admin</span> poate selecta rolurile <em>administrator</em> sau <em>editor</em>.</li>
                </ul>

                <h3 id="users-edit" class="mt-6 text-base font-semibold scroll-mt-24">Editare utilizator</h3>
                <p class="mt-1 text-slate-700">
                Butonul <em>Edit</em> deschide modalul precompletat (payload JSON în atributul <code class="bg-slate-100 px-1 rounded text-[12px]">data-user</code>).
                La salvare, datele se suprascriu pentru <code class="bg-slate-100 px-1 rounded text-[12px]">user_id</code>. Tutorii nu pot edita Admin/Editor.
                </p>

                <h3 id="users-pass" class="mt-6 text-base font-semibold scroll-mt-24">Setare/ schimbare parolă</h3>
                <ul class="pl-5 mt-1 list-disc text-slate-700">
                <li>Câmpurile <em>Parolă</em> și <em>Confirmă parola</em> sunt disponibile în modal (creare și editare).</li>
                <li>Validare minimă: ≥ 8 caractere și potrivire între câmpuri. Se trimite atât <code class="bg-slate-100 px-1 rounded text-[12px]">user_pass</code>, cât și <code class="bg-slate-100 px-1 rounded text-[12px]">password</code> (compat handler).</li>
                </ul>

                <h3 id="users-prof" class="mt-6 text-base font-semibold scroll-mt-24">Câmpuri extinse — Profesor</h3>
                <p class="mt-1 text-slate-700">
                Dacă rolul selectat este <span class="font-medium">profesor</span>, se afișează setul complet: Status profesor, Nivel predare, Materie (+ „Alta”), Statut/Calificare/Experiență, RSOI, Teach, An program, Cod SLF, Tutor coordonator, Mentor SEL/LIT/NUM, Alocare școli. Câmpurile sunt identice cu cele din pagina „Profesori”.
                </p>

                <h3 id="users-tutor" class="mt-6 text-base font-semibold scroll-mt-24">Status — Tutor</h3>
                <p class="mt-1 text-slate-700">
                Pentru rolul <span class="font-medium">tutor</span>, modalul expune câmpul <em>Status tutor</em> (valori livrate prin <code class="bg-slate-100 px-1 rounded text-[12px]">apply_filters('edu_tutor_status_options')</code> în PHP).
                </p>

                <h3 id="users-schools" class="mt-6 text-base font-semibold scroll-mt-24">Alocare școli (AJAX)</h3>
                <div class="p-4 mt-1 bg-white border rounded-lg border-slate-200">
                <p class="text-slate-700">
                    Căutarea pornește după ≥ 2 caractere. Se apelează <code class="bg-slate-100 px-1 rounded text-[12px]">action=edu_search_schools</code> cu <code class="bg-slate-100 px-1 rounded text-[12px]">q</code> și <code class="bg-slate-100 px-1 rounded text-[12px]">nonce</code>; rezultatele se pot selecta ca „chips”.
                </p>
                <pre class="p-4 mt-3 overflow-x-auto text-xs rounded-lg bg-slate-900 text-slate-100"><code>POST /wp-admin/admin-ajax.php
        action=edu_search_schools&amp;nonce=...&amp;q=bucuresti

        Răspuns:
        [
        {"id":101,"name":"Școala A","city":"Sector 3","county":"București","cod":"B123"},
        ...
        ]</code></pre>
                </div>

                <h3 id="users-delete" class="mt-6 text-base font-semibold scroll-mt-24">Ștergere</h3>
                <p class="mt-1 text-slate-700">
                Disponibilă doar pentru <span class="font-medium">Admin</span>, cu prompt de confirmare. Se apelează <code class="bg-slate-100 px-1 rounded text-[12px]">action=edu_delete_user</code>; pe succes, rândul este eliminat din tabel.
                </p>
            </section>

            <!-- 5 -->
            <section id="users-paginare" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">5) Paginare & performanță</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-paginare')">Copiază link</button>
                </div>
                <ul class="pl-5 mt-2 list-disc text-slate-700">
                <li>Se încarcă toți utilizatorii din query-ul WP, se aplică filtrarea în memorie și <em>paginarea manuală</em>.</li>
                <li>Pentru volume mari: mută filtrarea în SQL (inclusiv <code class="bg-slate-100 px-1 rounded text-[12px]">meta_query</code> targetat) și/sau adaugă caching.</li>
                </ul>
            </section>

            <!-- 6 -->
            <section id="users-model" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">6) Model de date</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-model')">Copiază link</button>
                </div>

                <h3 id="users-db" class="mt-4 text-base font-semibold scroll-mt-24">Tabele DB</h3>
                <ul class="pl-5 mt-1 list-disc text-slate-700">
                <li><code class="bg-slate-100 px-1 rounded text-[12px]">wp_users</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">wp_usermeta</code></li>
                <li>(pentru profesor) tabelele educaționale sunt folosite doar pentru referințe/afișare: <code class="bg-slate-100 px-1 rounded text-[12px]">wp_edu_schools</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">wp_edu_cities</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">wp_edu_counties</code></li>
                </ul>

                <h3 id="users-usermeta" class="mt-6 text-base font-semibold scroll-mt-24">Chei usermeta</h3>
                <div class="mt-2 overflow-hidden border rounded-xl border-slate-200">
                <table class="min-w-full text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 font-semibold text-left text-slate-700">Cheie</th>
                        <th class="px-3 py-2 font-semibold text-left text-slate-700">Rol/Utilizare</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">assigned_tutor_id</code></td><td class="px-3 py-2">vizibilitate tutor pentru profesori</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">user_status_profesor</code></td><td class="px-3 py-2">badge status profesor (activ/în așteptare/...)</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">nivel_predare</code></td><td class="px-3 py-2">prescolar/primar/gimnazial/liceu</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">materia_predata</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">materia_alta</code></td><td class="px-3 py-2">materie + fallback text</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">cod_slf</code></td><td class="px-3 py-2">cod intern</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">statut_prof</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">calificare</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">experienta</code></td><td class="px-3 py-2">detalii HR profesor</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">segment_rsoi</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">generatie</code>, <code class="bg-slate-100 px-1 rounded text-[12px]">an_program</code></td><td class="px-3 py-2">filtre & afișare</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">assigned_school_ids</code></td><td class="px-3 py-2">array ID-uri școli</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">user_status_tutor</code>/<code class="bg-slate-100 px-1 rounded text-[12px]">tutor_status</code></td><td class="px-3 py-2">status tutor</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">user_status</code></td><td class="px-3 py-2">fallback generic pentru alte roluri</td></tr>
                    <tr><td class="px-3 py-2"><code class="bg-slate-100 px-1 rounded text-[12px]">last_login</code>/<code class="bg-slate-100 px-1 rounded text-[12px]">last_activity</code>/<code class="bg-slate-100 px-1 rounded text-[12px]">last_seen</code></td><td class="px-3 py-2">ultimă activitate</td></tr>
                    </tbody>
                </table>
                </div>
            </section>

            <!-- 7 -->
            <section id="users-endpoints" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">7) Endpoints & Hook-uri</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-endpoints')">Copiază link</button>
                </div>

                <div class="grid gap-4 mt-3 md:grid-cols-2">
                <div class="p-4 bg-white border rounded-xl border-slate-200">
                    <h4 class="font-medium">Creare/Update utilizator</h4>
                    <p class="mt-1 text-sm text-slate-700"><code class="bg-slate-100 px-1 rounded text-[12px]">action=edu_save_user_form</code></p>
                    <pre class="p-4 mt-3 overflow-x-auto text-xs rounded-lg bg-slate-900 text-slate-100"><code>POST /wp-admin/admin-ajax.php
        action=edu_save_user_form&amp;nonce=...&amp;user_id=OPTIONAL&amp;user_role=profesor|tutor|...&amp;email=...&amp;first_name=...&amp;last_name=...
        # Parolă (opțional):
        user_pass=... &amp; password=...
        # Profesor (exemple):
        nivel_predare=primar&amp;materia_predata=Matematică&amp;an_program=2024-2025&amp;assigned_school_ids[]=101</code></pre>
                </div>

                <div class="p-4 bg-white border rounded-xl border-slate-200">
                    <h4 class="font-medium">Ștergere utilizator (Admin)</h4>
                    <p class="mt-1 text-sm text-slate-700"><code class="bg-slate-100 px-1 rounded text-[12px]">action=edu_delete_user</code></p>
                    <pre class="p-4 mt-3 overflow-x-auto text-xs rounded-lg bg-slate-900 text-slate-100"><code>POST /wp-admin/admin-ajax.php
        action=edu_delete_user&amp;nonce=...&amp;user_id=123</code></pre>
                </div>

                <div class="p-4 bg-white border rounded-xl border-slate-200">
                    <h4 class="font-medium">Căutare școli (pentru Profesor)</h4>
                    <p class="mt-1 text-sm text-slate-700"><code class="bg-slate-100 px-1 rounded text-[12px]">action=edu_search_schools</code></p>
                    <pre class="p-4 mt-3 overflow-x-auto text-xs rounded-lg bg-slate-900 text-slate-100"><code>POST /wp-admin/admin-ajax.php
        action=edu_search_schools&amp;nonce=...&amp;q=iasi</code></pre>
                </div>

                <div class="p-4 bg-white border rounded-xl border-slate-200">
                    <h4 class="font-medium">Filtre PHP extensibile</h4>
                    <ul class="mt-2 text-sm list-disc list-inside text-slate-700">
                    <li><code class="bg-slate-100 px-1 rounded text-[12px]">apply_filters('edu_professor_status_options', ...)</code></li>
                    <li><code class="bg-slate-100 px-1 rounded text-[12px]">apply_filters('edu_tutor_status_options', ...)</code></li>
                    </ul>
                </div>
                </div>

                <div class="p-3 mt-4 text-sm border rounded-lg border-emerald-200 bg-emerald-50 text-emerald-900">
                Toate apelurile AJAX folosesc <span class="font-medium">nonce</span> (<code class="bg-emerald-100 px-1 rounded text-[12px]">edu_nonce</code>) și trimit cookie-urile
                (<code class="bg-emerald-100 px-1 rounded text-[12px]">credentials:'same-origin'</code>).
                </div>
            </section>

            <!-- 8 -->
            <section id="users-securitate" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">8) Securitate</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-securitate')">Copiază link</button>
                </div>
                <ul class="pl-5 mt-2 list-disc text-slate-700">
                <li>Gărzile de acces: doar <span class="font-medium">Admin</span> și <span class="font-medium">Tutor</span> (cu restricții de capabilități).</li>
                <li>Validări simple în UI (parolă, potrivire confirmare); sanitizare și <code class="bg-slate-100 px-1 rounded text-[12px]">esc_*</code> în UI; validări server-side în handler.</li>
                <li>Tutorii nu pot crea/edita/șterge Admin/Editor; nici pe sine nu se pot promova.</li>
                </ul>
            </section>

            <!-- 9 -->
            <section id="users-extensii" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">9) Extensibilitate</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-extensii')">Copiază link</button>
                </div>
                <p class="mt-2 text-slate-700">
                Pentru câmpuri noi (ex. <code class="bg-slate-100 px-1 rounded text-[12px]">department</code>), adaugă inputul în modal (condiționat de rol, dacă e cazul) și salvează în <code class="bg-slate-100 px-1 rounded text-[12px]">edu_save_user_form</code> via <code class="bg-slate-100 px-1 rounded text-[12px]">update_user_meta</code>. Dacă trebuie în tabel, adaugă o coloană nouă.
                </p>
            </section>

            <!-- 10 -->
            <section id="users-troubleshooting" class="mt-10 scroll-mt-24" x-data="{a:true,b:false,c:false,d:false}">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">10) Troubleshooting</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-troubleshooting')">Copiază link</button>
                </div>

                <div class="mt-3 space-y-2">
                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="a=!a">
                    Nu pot crea Admin/Editor
                    <span x-text="a ? '–' : '+'"></span>
                    </button>
                    <div x-show="a" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Doar <span class="font-medium">Admin</span>. Dacă ești tutor, rolul se forțează pe „profesor” în backend.
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="b=!b">
                    Parolele nu se salvează
                    <span x-text="b ? '–' : '+'"></span>
                    </button>
                    <div x-show="b" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Verifică lungimea (≥ 8), potrivirea confirmării și că trimiți atât <code class="bg-slate-100 px-1 rounded text-[12px]">user_pass</code>, cât și <code class="bg-slate-100 px-1 rounded text-[12px]">password</code>.
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="c=!c">
                    Câmpurile Profesor nu apar în modal
                    <span x-text="c ? '–' : '+'"></span>
                    </button>
                    <div x-show="c" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Asigură-te că rolul selectat este „profesor”. Blocul este afișat condiționat în UI.
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="d=!d">
                    Căutarea de școli nu returnează rezultate
                    <span x-text="d ? '–' : '+'"></span>
                    </button>
                    <div x-show="d" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Verifică endpointul <code class="bg-slate-100 px-1 rounded text-[12px]">edu_search_schools</code>, <span class="font-medium">nonce</span>-ul și faptul că request-ul include <code class="bg-slate-100 px-1 rounded text-[12px]">credentials:'same-origin'</code>.
                    </div>
                </div>
                </div>
            </section>

            <!-- 11 -->
            <section id="users-faq" class="mt-10 scroll-mt-24" x-data="{f1:true,f2:false,f3:false}">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">11) FAQ</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-faq')">Copiază link</button>
                </div>

                <div class="mt-3 space-y-2">
                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="f1=!f1">
                    Pot schimba emailul (care e și user_login)?
                    <span x-text="f1 ? '–' : '+'"></span>
                    </button>
                    <div x-show="f1" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Da, însă handlerul aliniază <code class="bg-slate-100 px-1 rounded text-[12px]">user_login</code> la email. Ai grijă la unicitate.
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="f2=!f2">
                    Unde setez statusul pentru tutor?
                    <span x-text="f2 ? '–' : '+'"></span>
                    </button>
                    <div x-show="f2" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    În modal, când rolul este „tutor”. Valorile vin din <code class="bg-slate-100 px-1 rounded text-[12px]">apply_filters('edu_tutor_status_options')</code>.
                    </div>
                </div>

                <div class="bg-white border rounded-lg border-slate-200">
                    <button class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-left" @click="f3=!f3">
                    Pot adăuga imagine de profil la creare?
                    <span x-text="f3 ? '–' : '+'"></span>
                    </button>
                    <div x-show="f3" x-collapse class="px-3 pb-3 text-sm text-slate-700">
                    Nu din această pagină; a fost simplificată intenționat (fără upload avatar). Poți păstra uploadul în pagina dedicată profilului.
                    </div>
                </div>
                </div>
            </section>

            <!-- 12 -->
            <section id="users-debug" class="mt-10 scroll-mt-24">
                <div class="flex items-center gap-2">
                <h2 class="text-xl font-semibold">12) Modul DEBUG</h2>
                <button class="ml-6 text-xxs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 border border-slate-300 rounded px-1 py-0.5"
                    @click="navigator.clipboard.writeText(location.origin+location.pathname+'#users-debug')">Copiază link</button>
                </div>
                <p class="mt-2 text-slate-700">
                Paginarea și filtrele sunt vizibile în URL. Dacă dorești un bloc de debug (rol curent, totaluri, filtre), poți replica mecanismul din pagina „Profesori” cu un parametru <code class="bg-slate-100 px-1 rounded text-[12px]">&amp;debug=1</code>.
                </p>
                <div class="mt-6">
                <a href="#adaugareutilizatori" class="text-sm text-sky-700 hover:underline">↑ Înapoi sus</a>
                </div>
                <p class="mt-8 text-xs text-slate-500">Ultima actualizare: 17.10.2025</p>
            </section>
            </div>
        </div>
    </div>
    <!-- ================ /DOCUMENTAȚIE — Pagina Utilizatori ================ -->
</main>
