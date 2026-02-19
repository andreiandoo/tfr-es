(function ($) {
  if (typeof ES_HEADER === "undefined") return;

  // ===== SEARCH =====
  const $input = $("#es-search-input");
  const $clear = $("#es-search-clear");
  const $list = $("#es-search-results");
  let selIndex = -1; // index item selectat pt. navigare cu săgeți

  const debounce = (fn, ms = 250) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), ms);
    };
  };

  function render(items) {
    if (!items || !items.length) {
      $list
        .removeClass("hidden")
        .html(
          '<li class="px-3 py-2 text-sm text-gray-500">Niciun rezultat</li>'
        );
      selIndex = -1;
      return;
    }
    const html = items
      .map((it, i) => {
        const meta = [it.class, it.school].filter(Boolean).join(" • ");
        return `
        <li class="es-item cursor-pointer px-3 py-2 hover:bg-gray-50 dark:hover:bg-dark-700"
            data-id="${it.id}" data-index="${i}">
          <div class="text-sm font-medium">${it.name}</div>
          ${meta ? `<div class="text-xs text-gray-500">${meta}</div>` : ""}
        </li>
      `;
      })
      .join("");
    $list.removeClass("hidden").html(html);
    selIndex = -1;
  }

  function selectIndex(i) {
    const $items = $list.find(".es-item");
    $items.removeClass("bg-gray-50 dark:bg-dark-700");
    if (i >= 0 && i < $items.length) {
      $($items[i]).addClass("bg-gray-50 dark:bg-dark-700");
      selIndex = i;
    }
  }

  const doSearch = debounce(function () {
    const q = $input.val().trim();
    if (!q) {
      $list.addClass("hidden").empty();
      $clear.addClass("hidden");
      return;
    }

    $clear.toggleClass("hidden", q.length === 0);

    $.get(ES_HEADER.ajax, {
      action: "es_student_search",
      nonce: ES_HEADER.nonce,
      q,
    })
      .done(function (res) {
        if (!res || !res.success) {
          // dacă serverul a dat eroare utilă, o vezi în Network → Response
          $list.addClass("hidden").empty();
          return;
        }
        render(res.data.items || []);
      })
      .fail(function (xhr) {
        // debug rapid în consolă
        console.warn("Search failed", xhr?.responseJSON || xhr?.responseText);
        $list.addClass("hidden").empty();
      });
  }, 250);

  // input events
  $input.on("input focus", doSearch);

  // clear
  $clear.on("click", function () {
    $input.val("");
    $clear.addClass("hidden");
    $list.addClass("hidden").empty();
    $input.trigger("focus");
  });

  // click pe rezultat
  $list.on("click", ".es-item", function () {
    const id = $(this).data("id");
    if (id) window.location.href = ES_HEADER.reportBase + id;
  });

  // navigare tastatură: ↑/↓, Enter, Esc
  $input.on("keydown", function (e) {
    const $items = $list.find(".es-item");
    if (!$items.length) return;

    if (e.key === "ArrowDown") {
      e.preventDefault();
      selectIndex(Math.min(selIndex + 1, $items.length - 1));
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      selectIndex(Math.max(selIndex - 1, 0));
    } else if (e.key === "Enter") {
      if (selIndex >= 0 && selIndex < $items.length) {
        e.preventDefault();
        $($items[selIndex]).trigger("click");
      }
    } else if (e.key === "Escape") {
      $list.addClass("hidden");
      selIndex = -1;
      $input.blur();
    }
  });

  // click în afara listei → ascunde
  $(document).on("click", function (e) {
    if (!$(e.target).closest("#es-search-container").length) {
      $list.addClass("hidden");
    }
  });

  // ==== NOTIFICATIONS =====

  const $notifBtn = $(".notifications");
  const $notifDot = $("#es-notif-dot");
  const $notifCountBubble = $("#es-notif-count");
  let drawerMounted = false;

  function pingNotifications() {
    $.get(ES_HEADER.ajax, {
      action: "es_notifications_ping",
      nonce: ES_HEADER.nonce,
    }).done(function (res) {
      if (!res || !res.success) return;

      const hasNew = !!res.data.hasNew;
      const count = parseInt(res.data.newCount || 0, 10);

      // aprindem/ stingem becul
      $notifDot.toggleClass("hidden", !hasNew);
    });
  }

  // La încărcare + re-pinguri
  $(document).ready(function () {
    setTimeout(pingNotifications, 150); // imediat după load
    setTimeout(pingNotifications, 2000); // după 2s ca fallback
    setInterval(pingNotifications, 60000); // la minut
  });

  function mountDrawer() {
    if (drawerMounted) return;
    drawerMounted = true;

    const markup = `
      <aside id="es-notif-drawer"
             class="fixed inset-y-0 right-0 z-40 w-[380px] max-w-[90vw] translate-x-full transition-transform duration-300 bg-white dark:bg-dark-900 border-l border-gray-200 dark:border-dark-600 shadow-xl flex flex-col">
        <div class="flex items-center justify-between px-4 h-[65px] border-b border-gray-200 dark:border-dark-600">
          <h3 class="text-base font-semibold">Notificări</h3>
          <button id="es-notif-close" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-dark-700" aria-label="Închide">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none">
              <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
        <div id="es-notif-list" class="flex-1 overflow-y-auto">
          <div class="p-4 text-sm text-gray-500">Se încarcă…</div>
        </div>
      </aside>
      <div id="es-notif-overlay" class="fixed inset-0 z-30 bg-black/20 backdrop-blur-[1px] opacity-0 pointer-events-none transition-opacity"></div>
    `;
    $("body").append(markup);

    $("#es-notif-close, #es-notif-overlay").on("click", closeDrawer);
    $(document).on("keydown", function (e) {
      if (e.key === "Escape") closeDrawer();
    });
  }

  function openDrawer() {
    mountDrawer();

    // încarcă lista
    $.get(ES_HEADER.ajax, {
      action: "es_notifications_list",
      nonce: ES_HEADER.nonce,
    }).done(function (res) {
      const $list = $("#es-notif-list");
      if (!res || !res.success) {
        $list.html(
          '<div class="p-4 text-sm text-red-600">Eroare la încărcare.</div>'
        );
        return;
      }
      const items = res.data.items || [];
      if (!items.length) {
        $list.html(
          '<div class="p-4 text-sm text-gray-500">Nu există notificări.</div>'
        );
        return;
      }

      const html = items
        .map(
          (it) => `
          <article class="border-b border-gray-100 dark:border-dark-700 px-4 py-3">
            <div class="text-xs text-gray-500 mb-1">${it.date}</div>
            <h4 class="font-medium mb-1">${it.title}</h4>
            <div class="prose prose-sm max-w-none dark:prose-invert">${it.body}</div>
          </article>
        `
        )
        .join("");
      $list.html(html);
    });

    // marchează ca văzute
    $.post(ES_HEADER.ajax, {
      action: "es_notifications_mark_seen",
      nonce: ES_HEADER.nonce,
    });

    // UI show
    $("#es-notif-drawer").removeClass("translate-x-full");
    $("#es-notif-overlay").removeClass("pointer-events-none").css("opacity", 1);
    // oprește beculețul și balonul
    $notifDot.addClass("hidden");
    $notifCountBubble.addClass("hidden").text("");
  }

  function closeDrawer() {
    $("#es-notif-drawer").addClass("translate-x-full");
    $("#es-notif-overlay").addClass("pointer-events-none").css("opacity", 0);
  }

  $notifBtn.on("click", function () {
    openDrawer();
  });

  // La încărcare, facem un ping rapid pentru “beculeț”
  pingNotifications();
  // (opțional) re-ping la interval
  setInterval(pingNotifications, 60 * 1000);
})(jQuery);
