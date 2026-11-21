/* ==========================================================================
   Innovative Engineering — site behaviour
   Content lives in data.js (window.IE_DATA). No external JS dependencies.
   ========================================================================== */
(() => {
  "use strict";

  const D = window.IE_DATA;
  const LANG = document.documentElement.lang === "bn" ? "bn" : "en";
  const UI = D.ui[LANG];
  const S = D.site;
  const REDUCE = matchMedia("(prefers-reduced-motion: reduce)").matches;

  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const t = (v) => (v && typeof v === "object" && !Array.isArray(v) ? (v[LANG] ?? v.en) : v);
  const esc = (s) =>
    String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);
  const waLink = (text) => `https://wa.me/${S.whatsapp}?text=${encodeURIComponent(text)}`;

  /* ---- Contact links from config --------------------------------------- */
  const LINKS = {
    whatsapp: (a) => waLink(a.dataset.text || UI.waHello),
    messenger: () => S.messenger,
    facebook: () => S.facebook,
    call: () => `tel:${S.phone}`,
    call2: () => `tel:${S.phone2}`,
    email: () => `mailto:${S.email}`,
  };
  $$("[data-link]").forEach((a) => {
    const fn = LINKS[a.dataset.link];
    if (fn) a.href = fn(a);
  });
  $$("[data-year]").forEach((el) => (el.textContent = new Date().getFullYear()));

  /* ---- Loader: elevator arrives at G, doors open ----------------------- */
  const loader = $("#loader");
  if (loader) {
    const disp = $("#loaderFloor");
    const seq = ["5", "4", "3", "2", "1", "G"];
    let i = 0;
    let arrived = false;
    let loaded = document.readyState === "complete";
    const open = () => {
      if (loader.classList.contains("is-open")) return;
      loader.classList.add("is-open");
      setTimeout(() => loader.classList.add("is-gone"), 1100);
    };
    const tick = setInterval(() => {
      disp.textContent = seq[i];
      if (++i >= seq.length) {
        clearInterval(tick);
        arrived = true;
        if (loaded) setTimeout(open, 200);
      }
    }, REDUCE ? 1 : 130);
    addEventListener("load", () => {
      loaded = true;
      if (arrived) setTimeout(open, 200);
    });
    setTimeout(open, 3500); // failsafe
  }

  /* ---- Header, progress, back-to-top, scroll spy ----------------------- */
  const hdr = $("#hdr");
  const toTop = $("#toTop");
  const floorsNav = $("#floors");
  const sections = $$("main section[data-floor]");
  const navLinks = $$(".menu a, #mMenu a");

  // Build the side floor navigator from sections
  if (floorsNav) {
    const ol = $("ol", floorsNav);
    ol.innerHTML = sections
      .map(
        (s) =>
          `<li><a href="#${s.id}" aria-label="${esc(s.dataset.name)}"><span>${esc(s.dataset.floor)} · ${esc(s.dataset.name)}</span></a></li>`,
      )
      .join("");
  }
  const floorLinks = floorsNav ? $$("a", floorsNav) : [];
  const floorDisp = $("#floorsDisplay");

  let current = "";
  const spy = () => {
    const line = innerHeight * 0.35;
    let id = sections[0]?.id;
    for (const s of sections) if (s.getBoundingClientRect().top <= line) id = s.id;
    if (id === current) return;
    current = id;
    navLinks.forEach((a) => a.classList.toggle("is-active", a.getAttribute("href") === `#${id}`));
    floorLinks.forEach((a) => a.classList.toggle("is-active", a.getAttribute("href") === `#${id}`));
    const sec = document.getElementById(id);
    if (floorDisp && sec) floorDisp.textContent = sec.dataset.floor;
  };

  let ticking = false;
  let lastY = scrollY;
  const onScroll = () => {
    ticking = false;
    const y = scrollY;
    hdr.classList.toggle("is-solid", y > 40);
    // back-to-top only appears while scrolling up, so it never sits on content
    if (Math.abs(y - lastY) > 4) {
      toTop?.classList.toggle("is-on", y > 600 && y < lastY);
      lastY = y;
    }
    floorsNav?.classList.toggle("is-on", y > innerHeight * 0.6);
    const h = document.documentElement.scrollHeight - innerHeight;
    hdr.style.setProperty("--p", h > 0 ? (y / h).toFixed(4) : 0);
    spy();
  };
  addEventListener(
    "scroll",
    () => {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(onScroll);
      }
    },
    { passive: true },
  );
  addEventListener("resize", onScroll);
  onScroll();
  toTop?.addEventListener("click", () => scrollTo({ top: 0, behavior: REDUCE ? "auto" : "smooth" }));

  /* ---- Mobile menu (☰) ------------------------------------------------- */
  const ham = $("#ham");
  const root = document.documentElement;
  const setMenu = (open) => {
    root.classList.toggle("menu-open", open);
    document.body.classList.toggle("no-scroll", open);
    ham.setAttribute("aria-expanded", String(open));
  };
  ham?.addEventListener("click", () => setMenu(!root.classList.contains("menu-open")));
  $$("#mMenu a").forEach((a) => a.addEventListener("click", () => setMenu(false)));
  addEventListener("keydown", (e) => e.key === "Escape" && setMenu(false));
  matchMedia("(min-width: 992px)").addEventListener("change", (e) => e.matches && setMenu(false));

  /* ---- Hero background slideshow -------------------------------------- */
  const slides = $$(".hero__bg .slide");
  addEventListener("load", () =>
    slides.forEach((s) => s.dataset.bg && (s.style.backgroundImage = `url("${s.dataset.bg}")`)),
  );
  if (slides.length > 1 && !REDUCE) {
    let si = 0;
    setInterval(() => {
      slides[si].classList.remove("is-active");
      si = (si + 1) % slides.length;
      slides[si].classList.add("is-active");
    }, 6500);
  }

  /* ---- Rotating service word ------------------------------------------ */
  const rot = $(".rotator");
  if (rot && !REDUCE) {
    const words = JSON.parse(rot.dataset.words);
    const span = $("span", rot);
    let wi = 0;
    setInterval(() => {
      span.classList.add("out");
      setTimeout(() => {
        wi = (wi + 1) % words.length;
        span.textContent = words[wi];
        span.classList.remove("out");
        span.classList.add("in");
        setTimeout(() => span.classList.remove("in"), 450);
      }, 350);
    }, 2800);
  }

  /* ---- Animated lift shaft (hero) ------------------------------------- */
  const shaft = $(".shaft");
  if (shaft && !REDUCE) {
    const car = $("#liftCar");
    const disp = $("#liftFloor");
    const arrow = $("#liftArrow");
    const floorEls = $$(".shaft__floors li").reverse(); // index 0 = ground
    const top = floorEls.length - 1;
    const label = (n) => (n === 0 ? "G" : String(n));
    let pos = 0;
    shaft.style.setProperty("--top", top);
    const mark = (n) => floorEls.forEach((li, i) => li.classList.toggle("is-here", i === n));

    const travel = () => {
      if (!shaft.offsetParent || document.hidden) return setTimeout(travel, 2000);
      let next;
      do next = Math.floor(Math.random() * (top + 1));
      while (next === pos);
      const up = next > pos;
      const dist = Math.abs(next - pos);
      const dur = 0.8 + dist * 0.45;
      car.classList.remove("is-open");
      arrow.className = `fas fa-caret-${up ? "up" : "down"} is-moving`;
      setTimeout(() => {
        shaft.style.setProperty("--travel", `${dur}s`);
        shaft.style.setProperty("--pos", next);
        let cur = pos;
        const iv = setInterval(() => {
          cur += up ? 1 : -1;
          disp.textContent = label(cur);
          if (cur === next) clearInterval(iv);
        }, (dur * 1000) / dist);
        setTimeout(() => {
          pos = next;
          mark(pos);
          arrow.className = "fas fa-caret-up is-idle";
          car.classList.add("is-open");
          setTimeout(travel, 2400);
        }, dur * 1000 + 120);
      }, 750);
    };
    mark(0);
    setTimeout(() => {
      car.classList.add("is-open");
      setTimeout(travel, 2000);
    }, 1400);
  }

  /* ---- Count-up numbers ----------------------------------------------- */
  const countUp = (el) => {
    const end = Number(el.dataset.count);
    const suf = el.dataset.suffix || "";
    if (REDUCE) return (el.textContent = end + suf);
    const t0 = performance.now();
    const step = (now) => {
      const p = Math.min((now - t0) / 1600, 1);
      el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))) + suf;
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };

  /* ---- Reveal on scroll ----------------------------------------------- */
  const io = new IntersectionObserver(
    (entries) =>
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        en.target.classList.add("is-in");
        if (en.target.dataset.count) countUp(en.target);
        io.unobserve(en.target);
      }),
    // threshold 0 so tall blocks (e.g. the checklist on phones) don't sit blank
    { threshold: 0, rootMargin: "0px 0px -8% 0px" },
  );
  const observe = (scope = document) =>
    $$("[data-reveal], .door-reveal, .steps, [data-count]", scope).forEach((el) => io.observe(el));
  observe();

  /* ---- Modal (services + products) ------------------------------------ */
  const modal = $("#modal");
  const modalBody = $("#modalBody");
  const openModal = (html) => {
    modalBody.innerHTML = html;
    modal.showModal();
    const door = $(".door-reveal", modalBody);
    if (door) requestAnimationFrame(() => requestAnimationFrame(() => door.classList.add("is-in")));
  };
  $("#modalX").addEventListener("click", () => modal.close());
  modal.addEventListener("click", (e) => e.target === modal && modal.close());

  const mediaHtml = (item, cls) =>
    item.img
      ? `<div class="${cls} door-reveal"><img src="${item.img}" alt="${esc(t(item.name || item.title))}"></div>`
      : `<div class="${cls} prod__media--ico door-reveal"><i class="fas ${item.icon}"></i></div>`;

  const actionBtns = (label, waText, waLabel = UI.askPrice) => `
    <div class="modal__btns">
      <a class="btn btn--wa" href="${waLink(waText)}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> ${esc(waLabel)}</a>
      <a class="btn btn--navy" href="tel:${S.phone}"><i class="fas fa-phone"></i> ${esc(UI.call)}</a>
      <button class="btn btn--outline" type="button" data-enquire="${esc(label)}"><i class="fas fa-paper-plane"></i> ${esc(UI.enquire)}</button>
    </div>`;

  const listHtml = (arr) =>
    `<ul class="modal__list">${arr.map((x) => `<li><i class="fas fa-circle-check"></i><span>${esc(x)}</span></li>`).join("")}</ul>`;

  // Service "Learn more"
  document.addEventListener("click", (e) => {
    const b = e.target.closest("[data-service]");
    if (!b) return;
    const sv = D.services[b.dataset.service];
    if (!sv) return;
    const title = t(sv.title);
    openModal(`
      <div class="modal__in">
        ${mediaHtml({ img: sv.img, title: sv.title }, "modal__media")}
        <div class="modal__body">
          <span class="modal__cat"><i class="fas ${sv.icon}"></i> ${esc(UI.service)}</span>
          <h3>${esc(title)}</h3>
          <p>${esc(t(sv.intro))}</p>
          <h4>${esc(UI.included)}</h4>
          ${listHtml(t(sv.points))}
          ${actionBtns(title, UI.waService + title, "WhatsApp")}
        </div>
      </div>`);
    modalBody.dataset.service = b.dataset.service;
  });

  // "Send enquiry" → prefill contact form
  document.addEventListener("click", (e) => {
    const b = e.target.closest("[data-enquire]");
    if (!b) return;
    const form = $("#contactForm");
    const svc = modalBody.dataset.service;
    const isProduct = !!modalBody.dataset.product;
    if (modal.open) modal.close();
    if (!form) return;
    const F = (n) => form.elements.namedItem(n);
    if (svc && F("service").querySelector(`option[value="${svc}"]`)) F("service").value = svc;
    else if (isProduct) F("service").value = "product";
    F("message").value = UI.enquireMsg + b.dataset.enquire;
    $("#contact").scrollIntoView({ behavior: REDUCE ? "auto" : "smooth" });
    setTimeout(() => F("name").focus({ preventScroll: true }), 700);
  });

  /* ---- Products ------------------------------------------------------- */
  const prodGrid = $("#prodGrid");
  const prodFilters = $("#prodFilters");
  if (prodGrid && prodFilters) {
    const cats = [{ id: "all", icon: "fa-border-all", name: UI.all }, ...D.categories];
    const catOf = (id) => D.categories.find((c) => c.id === id);
    prodFilters.innerHTML = cats
      .map(
        (c) =>
          `<button class="filter${c.id === "all" ? " is-active" : ""}" type="button" data-filter="${c.id}" aria-pressed="${c.id === "all"}"><i class="fas ${c.icon}"></i>${esc(t(c.name))}</button>`,
      )
      .join("");

    const card = (p, i) => `
      <article class="prod" tabindex="0" role="button" data-prod="${p.id}" style="--d:${Math.min(i, 8) * 0.05}s" aria-label="${esc(t(p.name))} — ${esc(UI.details)}">
        <div class="prod__media${p.img ? "" : " prod__media--ico"}">
          ${p.img ? `<img src="${p.img}" alt="${esc(t(p.name))}" loading="lazy">` : `<i class="fas ${p.icon}"></i>`}
          <span class="prod__tag">${esc(t(catOf(p.cat).name))}</span>
        </div>
        <div class="prod__body">
          <h3>${esc(t(p.name))}</h3>
          <p>${esc(t(p.short))}</p>
          <div class="prod__foot">
            <span class="prod__price"><b>${esc(UI.onRequest)}</b></span>
            <span class="prod__more"><i class="fas fa-arrow-right"></i></span>
          </div>
        </div>
      </article>`;

    const render = (f) => {
      prodGrid.innerHTML = D.products
        .filter((p) => f === "all" || p.cat === f)
        .map(card)
        .join("");
    };
    render("all");

    prodFilters.addEventListener("click", (e) => {
      const b = e.target.closest("[data-filter]");
      if (!b || b.classList.contains("is-active")) return;
      $$(".filter", prodFilters).forEach((x) => {
        x.classList.toggle("is-active", x === b);
        x.setAttribute("aria-pressed", String(x === b));
      });
      render(b.dataset.filter);
    });

    const openProduct = (id) => {
      const p = D.products.find((x) => x.id === id);
      if (!p) return;
      const c = catOf(p.cat);
      const name = t(p.name);
      const specs = t(p.specs);
      openModal(`
        <div class="modal__in">
          ${mediaHtml(p, "modal__media")}
          <div class="modal__body">
            <span class="modal__cat"><i class="fas ${c.icon}"></i> ${esc(t(c.name))}</span>
            <h3>${esc(name)}</h3>
            <p>${esc(t(p.short))}</p>
            ${
              specs
                ? `<h4>${esc(UI.specs)}</h4><table class="modal__specs"><tbody>${specs
                    .map(([k, v]) => `<tr><th>${esc(k)}</th><td>${esc(v)}</td></tr>`)
                    .join("")}</tbody></table>`
                : ""
            }
            ${p.features ? `<h4>${esc(UI.features)}</h4>${listHtml(t(p.features))}` : ""}
            <p class="modal__hint"><i class="fas fa-circle-info"></i> ${esc(UI.specNote)}</p>
            ${actionBtns(name, UI.waProduct + name)}
          </div>
        </div>`);
      delete modalBody.dataset.service;
      modalBody.dataset.product = id;
    };
    prodGrid.addEventListener("click", (e) => {
      const c = e.target.closest("[data-prod]");
      if (c) openProduct(c.dataset.prod);
    });
    prodGrid.addEventListener("keydown", (e) => {
      const c = e.target.closest("[data-prod]");
      if (c && (e.key === "Enter" || e.key === " ")) {
        e.preventDefault();
        openProduct(c.dataset.prod);
      }
    });
  }
  modal.addEventListener("close", () => {
    delete modalBody.dataset.service;
    delete modalBody.dataset.product;
  });

  /* ---- Projects gallery + lightbox ------------------------------------ */
  const projGrid = $("#projGrid");
  const projFilters = $("#projFilters");
  const lb = $("#lightbox");
  if (projGrid && projFilters && lb) {
    const types = [{ id: "all", name: UI.all }, ...D.projectTypes];
    const typeName = (id) => t(D.projectTypes.find((x) => x.id === id)?.name || "");
    projFilters.innerHTML = types
      .map(
        (c) =>
          `<button class="filter${c.id === "all" ? " is-active" : ""}" type="button" data-filter="${c.id}" aria-pressed="${c.id === "all"}">${esc(t(c.name))}</button>`,
      )
      .join("");
    let shown = D.projects;
    const render = (f) => {
      shown = D.projects.filter((p) => f === "all" || p.type === f);
      projGrid.innerHTML = shown
        .map(
          (p, i) => `
          <figure class="proj${p.size && f === "all" ? ` proj--${p.size}` : ""}" style="--d:${i * 0.06}s" data-i="${i}" tabindex="0" role="button" aria-label="${esc(t(p.title))}">
            <img src="${p.img}" alt="${esc(t(p.title))}" loading="lazy">
            <span class="proj__zoom"><i class="fas fa-expand"></i></span>
            <figcaption class="proj__cap"><span>${esc(typeName(p.type))}</span><h3>${esc(t(p.title))}</h3><p>${esc(t(p.place))}</p></figcaption>
          </figure>`,
        )
        .join("");
    };
    render("all");
    projFilters.addEventListener("click", (e) => {
      const b = e.target.closest("[data-filter]");
      if (!b || b.classList.contains("is-active")) return;
      $$(".filter", projFilters).forEach((x) => {
        x.classList.toggle("is-active", x === b);
        x.setAttribute("aria-pressed", String(x === b));
      });
      render(b.dataset.filter);
    });

    let li = 0;
    const lbImg = $("img", lb);
    const show = (i) => {
      li = (i + shown.length) % shown.length;
      const p = shown[li];
      lbImg.src = p.img;
      lbImg.alt = t(p.title);
      $("strong", lb).textContent = t(p.title);
      $("figcaption span", lb).textContent = `${typeName(p.type)} · ${t(p.place)}`;
    };
    const openLb = (i) => {
      show(i);
      lb.showModal();
    };
    projGrid.addEventListener("click", (e) => {
      const f = e.target.closest("[data-i]");
      if (f) openLb(Number(f.dataset.i));
    });
    projGrid.addEventListener("keydown", (e) => {
      const f = e.target.closest("[data-i]");
      if (f && (e.key === "Enter" || e.key === " ")) {
        e.preventDefault();
        openLb(Number(f.dataset.i));
      }
    });
    $("#lbX").addEventListener("click", () => lb.close());
    $("#lbPrev").addEventListener("click", () => show(li - 1));
    $("#lbNext").addEventListener("click", () => show(li + 1));
    lb.addEventListener("click", (e) => e.target === lb && lb.close());
    lb.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") show(li - 1);
      if (e.key === "ArrowRight") show(li + 1);
    });
  }

  /* ---- Monthly checklist tabs ----------------------------------------- */
  const chkTabs = $("#chkTabs");
  const chkPanel = $("#chkPanel");
  if (chkTabs && chkPanel) {
    const total = D.checklist.reduce((n, g) => n + g.items.en.length, 0);
    $$("[data-chk-total]").forEach((el) => (el.textContent = total));
    chkTabs.innerHTML = D.checklist
      .map(
        (g, i) =>
          `<button class="chk-tab${i === 0 ? " is-active" : ""}" type="button" role="tab" aria-selected="${i === 0}" data-chk="${g.id}"><i class="fas ${g.icon}"></i>${esc(t(g.name))}<small>${t(g.items).length}</small></button>`,
      )
      .join("");
    const show = (id) => {
      const g = D.checklist.find((x) => x.id === id);
      chkPanel.innerHTML = `
        <h3><i class="fas ${g.icon}"></i>${esc(t(g.name))} — ${t(g.items).length} ${esc(UI.points)}</h3>
        <ol class="chk-list">${t(g.items)
          .map((x, i) => `<li style="--i:${i}">${esc(x)}</li>`)
          .join("")}</ol>`;
    };
    show(D.checklist[0].id);
    chkTabs.addEventListener("click", (e) => {
      const b = e.target.closest("[data-chk]");
      if (!b) return;
      $$(".chk-tab", chkTabs).forEach((x) => {
        x.classList.toggle("is-active", x === b);
        x.setAttribute("aria-selected", String(x === b));
      });
      show(b.dataset.chk);
    });
  }

  /* ---- Contact form → WhatsApp ----------------------------------------
     A static site cannot send email by itself, so the enquiry is composed
     into a WhatsApp message. Swap this for a form backend later if needed. */
  const form = $("#contactForm");
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      if (!form.reportValidity()) return;
      const F = (n) => form.elements.namedItem(n);
      const sel = F("service");
      const svc = sel.selectedIndex > 0 ? sel.options[sel.selectedIndex].text : "-";
      const msg = [
        `*${UI.formHead}*`,
        `${UI.fName}: ${F("name").value.trim()}`,
        `${UI.fPhone}: ${F("phone").value.trim()}`,
        `${UI.fService}: ${svc}`,
        `${UI.fMsg}: ${F("message").value.trim()}`,
      ].join("\n");
      window.open(waLink(msg), "_blank", "noopener");
      $("#formOk").classList.add("is-on");
      form.reset();
    });
  }
})();
