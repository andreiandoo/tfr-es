<header class="app-header transition-content sticky top-0 z-20 flex h-[65px] shrink-0 items-center justify-between px-(--margin-x) backdrop-blur-sm backdrop-saturate-150  bg-gradient-to-l from-sky-800 via-sky-700 to-sky-600">

  <div 
   <?php if ($vp === 'mobile') : ?>
      :class="sidebarOpen ? 'ml-18' : 'ml-18'"
      class="mr-auto applogo"
    <?php else : ?>
      :class="sidebarOpen ? '' : ''"
      class="ml-2 mr-auto applogo"
    <?php endif; ?>>
    <!-- <img src="<?php //echo esc_url(get_stylesheet_directory_uri()); ?>/resources/images/edustart-logo.png" alt="EduStart" class="h-6" /> -->
     <a href="<?php echo get_site_url();?>" class="flex items-center mb-1 text-3xl leading-5 text-white gap-x-1 group"><span class="font-bold">edu</span><span class="mb-1 text-2xl italic transition-all duration-150 ease-in-out text-sky-300 group-hover:text-white/80 group-hover:scale-110">start</span></a>
  </div>

  <div class="flex items-center gap-2 ltr:-mr-1.5 rtl:-ml-1.5">

    <?php if ($vp != 'mobile') : ?>
      <!-- SEARCH: container -->
      <div id="es-search-container" class="relative max-sm:hidden">
        <div id="es-search-wrap" class="relative">
          <span class="absolute inset-y-0 flex items-center pointer-events-none left-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-white/80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
            </svg>
          </span>
          <input id="es-search-input"
                class="w-[24rem] h-8 max-w-[90vw] transition-all duration-150 ease-in-out rounded-full border border-white/10 bg-white/10 text-white pl-9 pr-9 text-sm outline-none ring-2 ring-white/20 focus:border-white focus:ring-1 focus:ring-white focus:bg-white focus:text-slate-800 focus:outline-none focus:placeholder:text-slate-600 placeholder:text-white/80"
                type="text"
                autocomplete="off"
                placeholder="Caută elev…"
                aria-expanded="false" />
          <!-- buton „clear” -->
          <button id="es-search-clear"
                  class="absolute inset-y-0 hidden my-auto transition-all duration-150 ease-in-out rounded-full right-2 size-6 hover:bg-gray-100 hover:text-white"
                  type="button" aria-label="Șterge">
            <svg xmlns="http://www.w3.org/2000/svg" class="m-auto size-5" viewBox="0 0 24 24" fill="none">
              <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>

        <!-- rezultate -->
        <ul id="es-search-results"
            class="hidden absolute z-30 mt-2 w-[24rem] max-w-[90vw] rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-dark-600 dark:bg-dark-900 overflow-hidden">
        </ul>
      </div>

      <!-- buton search pe mobil (opțional, îl poți lega tot de logica de mai sus) -->
      <button class="relative p-0 text-gray-700 rounded-full btn-base btn shrink-0 hover:bg-gray-300/20 focus:bg-gray-300/20 active:bg-gray-300/25 dark:text-dark-200 dark:hover:bg-dark-300/10 dark:focus:bg-dark-300/10 dark:active:bg-dark-300/20 size-9 sm:hidden" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="text-gray-900 size-6 dark:text-dark-100">
          <path fill="currentColor" d="M10.5 19a8.5 8.5 0 1 0 0-17 8.5 8.5 0 0 0 0 17Z" opacity="0.3"></path>
          <path fill="currentColor" d="M20.92 22a1.07 1.07 0 0 1-.752-.308l-2.857-2.859a1.086 1.086 0 0 1 0-1.522 1.084 1.084 0 0 1 1.52 0l2.858 2.86a1.086 1.086 0 0 1 0 1.521c-.215.2-.492.308-.768.308Z"></path>
        </svg>
      </button>
    <?php endif; ?>

    <!-- NOTIFICATIONS -->
    <div class="relative flex items-center justify-center mr-8">
      <button class="relative flex items-center justify-center p-0 rounded-full text-slate-800 notifications btn-base btn shrink-0 size-9" type="button" aria-expanded="false">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
             xmlns="http://www.w3.org/2000/svg" class="text-white transition-all duration-150 ease-in-out size-6 hover:text-yellow-200">
          <path d="M6 8C6 4.68629 8.68629 2 12 2C15.3137 2 18 4.68629 18 8V9.83095C18 11.2503 18.3857 12.6429 19.116 13.8599L19.6694 14.7823C20.0364 15.3941 20.22 15.7 20.2325 15.9497C20.252 16.3366 20.0463 16.6999 19.7045 16.8823C19.4839 17 19.1272 17 18.4138 17H5.5863C4.87286 17 4.51614 17 4.29549 16.8823C3.95374 16.6999 3.74803 16.3366 3.7675 15.9497C3.78006 15.7 3.96359 15.3941 4.33065 14.7823L4.88407 13.8599C5.61428 12.6429 6 11.2503 6 9.83098V8Z" fill="currentColor" fill-opacity="1"></path>
          <path d="M14.35 18C14.4328 18 14.5007 18.0673 14.493 18.1498C14.4484 18.6254 14.1923 19.0746 13.7678 19.4142C13.2989 19.7893 12.663 20 12 20C11.337 20 10.7011 19.7893 10.2322 19.4142C9.80772 19.0746 9.55165 18.6254 9.50702 18.1498C9.49928 18.0673 9.56716 18 9.65 18L12 18L14.35 18Z" fill="currentColor"></path>
        </svg>

        <div id="es-notif-dot" class="hidden absolute -top-0.5 right-0.5">
          <span class="relative inline-flex w-3 h-3 rounded-full bg-rose-500">
            <span class="absolute inline-flex w-full h-full rounded-full animate-ping bg-rose-400 opacity-80"></span>
          </span>
        </div>

        <!-- cifră mică (0–9+) -->
        <span id="es-notif-count"
              class="hidden absolute -top-1 -right-2 min-w-[18px] h-[18px] text-[11px] leading-[18px] text-white text-center rounded-full bg-rose-600 px-1"></span>
      </button>
    </div>
  </div>
</header>
