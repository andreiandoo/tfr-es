/*!
 * EDU-START Tooltips (singleton)
 * Usage în pagină: <div class="info" data-tid="12" data-placement="top"></div>
 * Configurează harta TIPS mai jos. Titlu + conținut la cheie numerică.
 */
(() => {
  // =========================
  // 1) CONFIG centralizat
  // =========================
  const TIPS = {
    12: {
      title: "Scor Remedial",
      content:
        "Media procentelor obținute la LIT în T0 și T1 pentru acest elev. Comparația cu generația folosește doar elevii aflați în Remedial.",
    },
    13: {
      title: "Nivel Acuratețe",
      content:
        "Nivelul de acuratețe la citire (PP, P, 1..4) și diferența față de nivelul clasei. Medie calculată pe Δ față de clasă, nu pe valori brute.",
    },
    14: {
      title: "Nivel Comprehensiune",
      content:
        "Nivelul de comprehensiune la citire (PP, P, 1..4) și diferența față de nivelul clasei. Medie calculată pe Δ față de clasă.",
    },
    // Adaugă aici toate tooltips-urile tale:
    // 15: { title: "Titlu", content: "Text explicativ..." },
  };

  // ========= utils =========
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  // Creează o singură instanță de tooltip (balon + săgeată)
  const tip = document.createElement("div");
  tip.id = "edus-tooltip";
  tip.setAttribute("role", "tooltip");
  tip.style.position = "fixed";
  tip.style.width = "250px";
  tip.style.background = "#0284c7";
  tip.style.zIndex = "999999";
  tip.style.pointerEvents = "none";
  tip.style.opacity = "0";
  tip.style.transform = "translateY(2px)";
  tip.style.transition = "opacity .12s ease, transform .12s ease";
  tip.className =
    "max-w-[320px] rounded-xl shadow-xl border border-slate-800/40 bg-slate-900/95 text-white p-3 bg-sky-800/95 backdrop-blur-sm";

  // conținut intern (titlu + text)
  const tipTitle = document.createElement("div");
  tipTitle.className = "text-[13px] font-semibold mb-1";
  const tipBody = document.createElement("div");
  tipBody.className = "text-[12px] leading-5 opacity-90";
  tip.appendChild(tipTitle);
  tip.appendChild(tipBody);

  let hideTO = null;
  const GAP = 10;

  function showTipFor(el) {
    const id = Number(el.getAttribute("data-tid"));
    if (!id || !TIPS[id]) return;

    const { title, content } = TIPS[id];

    // populează conținut
    tipTitle.textContent = title || "";
    tipBody.textContent = content || "";

    // inserează tooltip în DOM dacă nu e deja
    if (!document.body.contains(tip)) document.body.appendChild(tip);

    // poziționare
    const rect = el.getBoundingClientRect();
    const placement = (
      el.getAttribute("data-placement") || "top"
    ).toLowerCase();

    // măsurare
    tip.style.opacity = "0";
    tip.style.transform = "translateY(2px)";
    tip.style.left = "-9999px";
    tip.style.top = "-9999px";
    tip.style.display = "block"; // forțează măsurarea
    const tw = tip.offsetWidth;
    const th = tip.offsetHeight;

    let x = 0,
      y = 0;
    if (placement === "bottom") {
      x = rect.left + (rect.width - tw) / 2;
      y = rect.bottom + GAP;
    } else if (placement === "left") {
      x = rect.left - GAP - tw;
      y = rect.top + (rect.height - th) / 2;
    } else if (placement === "right") {
      x = rect.right + GAP;
      y = rect.top + (rect.height - th) / 2;
    } else {
      // top implicit
      x = rect.left + (rect.width - tw) / 2;
      y = rect.top - GAP - th;
    }

    // clamp în viewport
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    x = Math.max(8, Math.min(vw - (tw + 8), x));
    y = Math.max(8, Math.min(vh - (th + 8), y));

    tip.style.left = Math.round(x) + "px";
    tip.style.top = Math.round(y) + "px";
    clearTimeout(hideTO);
    requestAnimationFrame(() => {
      tip.style.opacity = "1";
      tip.style.transform = "translateY(0)";
    });
  }

  function hideTip() {
    hideTO = setTimeout(() => {
      tip.style.opacity = "0";
      tip.style.transform = "translateY(2px)";
    }, 60);
  }

  // Creează butonul/iconița și o atașează în <div class="info" data-tid="...">
  function enhanceInfoPlaceholders() {
    $$(".info[data-tid]").forEach((holder) => {
      // evită dublarea dacă e deja injectat
      if (holder.__edusTooltipInit) return;
      holder.__edusTooltipInit = true;

      const id = Number(holder.getAttribute("data-tid"));
      if (!id || !TIPS[id]) {
        holder.title = "Tooltip ID inexistent în TIPS";
        return;
      }

      // construiește butonul
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className =
        "inline-flex items-center justify-center p-1 align-middle rounded text-emerald-800";
      btn.setAttribute("aria-label", TIPS[id].title || "Info");
      btn.setAttribute("data-tid", String(id)); // păstrăm pentru lookup
      // păstrăm și placement de pe holder, dacă există
      const placement = holder.getAttribute("data-placement");
      if (placement) btn.setAttribute("data-placement", placement);

      // iconița (SVG) — mică, neutră
      btn.innerHTML =
        '<svg viewBox="0 0 24 24" class="w-4 h-4 text-emerald-600" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 .001 20.001A10 10 0 0 0 12 2Zm0 4.75a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5ZM10.75 18a.75.75 0 0 1 0-1.5h.75v-4H10a.75.75 0 0 1 0-1.5h2.25a.75.75 0 0 1 .75.75v4.75h.25a.75.75 0 0 1 0 1.5H10.75Z"/></svg>';

      // injectează în holder (după orice conținut existent)
      holder.appendChild(btn);
    });
  }

  // Delegare evenimente pe document: funcționează pentru oricâte butoane
  function setupDelegation() {
    const isInfoBtn = (el) =>
      el && (el.matches("[data-tid]") || el.closest("[data-tid]"));

    document.addEventListener("mouseover", (e) => {
      const el = e.target.closest("[data-tid]");
      if (!el || !isInfoBtn(el)) return;
      showTipFor(el);
    });
    document.addEventListener("mouseout", (e) => {
      const el = e.target.closest("[data-tid]");
      if (!el || !isInfoBtn(el)) return;
      hideTip();
    });
    document.addEventListener("focusin", (e) => {
      const el = e.target.closest("[data-tid]");
      if (!el || !isInfoBtn(el)) return;
      showTipFor(el);
    });
    document.addEventListener("focusout", (e) => {
      const el = e.target.closest("[data-tid]");
      if (!el || !isInfoBtn(el)) return;
      hideTip();
    });
  }

  // Init
  document.addEventListener("DOMContentLoaded", () => {
    // adaugă tooltip-ul în DOM (invizibil la start)
    document.body.appendChild(tip);
    // transformă toate .info[data-tid] în butoane info
    enhanceInfoPlaceholders();
    // delegare globală pentru hover/focus
    setupDelegation();

    // dacă apar dinamic noi .info[data-tid], le prindem cu MutationObserver
    const mo = new MutationObserver((muts) => {
      let shouldEnhance = false;
      muts.forEach((m) => {
        if (m.addedNodes && m.addedNodes.length) shouldEnhance = true;
      });
      if (shouldEnhance) enhanceInfoPlaceholders();
    });
    mo.observe(document.body, { childList: true, subtree: true });
  });
})();
