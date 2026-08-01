/* =========================================================
   FO PSICOLOGIA — Scripts principais
   ========================================================= */

(function () {
  "use strict";

  var prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  /* ---------- Splash screen ---------- */
  function initSplash() {
    var splash = document.querySelector("[data-splash]");
    if (!splash) return;

    var alreadyShown = sessionStorage.getItem("fo_splash_shown");

    if (alreadyShown || prefersReducedMotion) {
      splash.setAttribute("hidden", "");
      document.body.classList.remove("no-scroll");
      return;
    }

    document.body.classList.add("no-scroll");
    sessionStorage.setItem("fo_splash_shown", "1");

    var hide = function () {
      splash.classList.add("is-hidden");
      document.body.classList.remove("no-scroll");
      window.setTimeout(function () {
        splash.setAttribute("hidden", "");
      }, 900);
    };

    var timer = window.setTimeout(hide, 2400);

    splash.addEventListener("click", function () {
      window.clearTimeout(timer);
      hide();
    });
  }

  /* ---------- Header on scroll ---------- */
  function initHeaderScroll() {
    var header = document.querySelector("[data-header]");
    if (!header) return;

    var onScroll = function () {
      if (window.scrollY > 40) {
        header.classList.add("is-scrolled");
      } else {
        header.classList.remove("is-scrolled");
      }
    };

    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  /* ---------- Mobile nav ---------- */
  function initMobileNav() {
    var toggle = document.querySelector("[data-nav-toggle]");
    var panel = document.querySelector("[data-nav-mobile]");
    if (!toggle || !panel) return;

    var closeNav = function () {
      toggle.setAttribute("aria-expanded", "false");
      panel.classList.remove("is-open");
      document.body.classList.remove("no-scroll");
    };

    var openNav = function () {
      toggle.setAttribute("aria-expanded", "true");
      panel.classList.add("is-open");
      document.body.classList.add("no-scroll");
    };

    toggle.addEventListener("click", function () {
      var expanded = toggle.getAttribute("aria-expanded") === "true";
      if (expanded) {
        closeNav();
      } else {
        openNav();
      }
    });

    panel.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", closeNav);
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeNav();
    });
  }

  /* ---------- Scroll reveal ---------- */
  function initReveal() {
    var items = document.querySelectorAll(".reveal");
    if (!items.length) return;

    if (prefersReducedMotion || !("IntersectionObserver" in window)) {
      items.forEach(function (el) {
        el.classList.add("is-visible");
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15, rootMargin: "0px 0px -60px 0px" }
    );

    items.forEach(function (el) {
      observer.observe(el);
    });
  }

  /* ---------- Hero parallax (mousemove) ---------- */
  function initHeroParallax() {
    var hero = document.querySelector(".hero");
    if (!hero) return;

    var isFinePointer = window.matchMedia("(pointer: fine)").matches;
    if (prefersReducedMotion || !isFinePointer) return;

    var photo = hero.querySelector(".hero__photo-wrap");
    var glows = hero.querySelectorAll(".hero__glow");
    var badges = hero.querySelectorAll(".float-badge");

    hero.addEventListener("mousemove", function (e) {
      var rect = hero.getBoundingClientRect();
      var x = (e.clientX - rect.left) / rect.width - 0.5;
      var y = (e.clientY - rect.top) / rect.height - 0.5;

      if (photo) {
        photo.style.transform =
          "translate(" + x * -14 + "px," + y * -14 + "px)";
      }
      glows.forEach(function (glow, i) {
        var depth = i % 2 === 0 ? 18 : -18;
        glow.style.transform =
          "translate(" + x * depth + "px," + y * depth + "px)";
      });
      badges.forEach(function (badge, i) {
        var depth = i % 2 === 0 ? -10 : 10;
        badge.style.marginLeft = x * depth + "px";
        badge.style.marginTop = y * depth + "px";
      });
    });

    hero.addEventListener("mouseleave", function () {
      if (photo) photo.style.transform = "";
      glows.forEach(function (glow) {
        glow.style.transform = "";
      });
      badges.forEach(function (badge) {
        badge.style.marginLeft = "";
        badge.style.marginTop = "";
      });
    });
  }

  /* ---------- Animated counters ---------- */
  function initCounters() {
    var counters = document.querySelectorAll("[data-count-to]");
    if (!counters.length) return;

    var animate = function (el) {
      var target = parseFloat(el.getAttribute("data-count-to"));
      var prefix = el.getAttribute("data-prefix") || "";
      var suffix = el.getAttribute("data-suffix") || "";

      if (prefersReducedMotion) {
        el.textContent = prefix + target + suffix;
        return;
      }

      var duration = 1400;
      var start = null;

      var step = function (timestamp) {
        if (!start) start = timestamp;
        var progress = Math.min((timestamp - start) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        var value = Math.round(target * eased);
        el.textContent = prefix + value + suffix;
        if (progress < 1) {
          window.requestAnimationFrame(step);
        }
      };

      window.requestAnimationFrame(step);
    };

    if (!("IntersectionObserver" in window)) {
      counters.forEach(animate);
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            animate(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.4 }
    );

    counters.forEach(function (el) {
      observer.observe(el);
    });
  }

  /* ---------- Accordion (FAQ) ---------- */
  function initAccordion() {
    var triggers = document.querySelectorAll(".accordion-trigger");
    if (!triggers.length) return;

    triggers.forEach(function (trigger) {
      trigger.addEventListener("click", function () {
        var item = trigger.closest(".accordion-item");
        var panel = item.querySelector(".accordion-panel");
        var isOpen = item.classList.contains("is-open");

        item
          .closest(".accordion")
          .querySelectorAll(".accordion-item.is-open")
          .forEach(function (openItem) {
            if (openItem !== item) {
              openItem.classList.remove("is-open");
              openItem.querySelector(".accordion-panel").style.maxHeight = null;
              openItem
                .querySelector(".accordion-trigger")
                .setAttribute("aria-expanded", "false");
            }
          });

        if (isOpen) {
          item.classList.remove("is-open");
          panel.style.maxHeight = null;
          trigger.setAttribute("aria-expanded", "false");
        } else {
          item.classList.add("is-open");
          panel.style.maxHeight = panel.scrollHeight + "px";
          trigger.setAttribute("aria-expanded", "true");
        }
      });
    });
  }

  /* ---------- Contact form -> WhatsApp ---------- */
  function initContactForm() {
    var form = document.querySelector("[data-contact-form]");
    if (!form) return;

    var feedback = form.querySelector("[data-form-feedback]");
    var whatsNumber = "5531986453396";

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      var name = form.querySelector("#name").value.trim();
      var phone = form.querySelector("#phone").value.trim();
      var subject = form.querySelector("#subject").value;
      var message = form.querySelector("#message").value.trim();

      if (!name || !phone || !message) {
        return;
      }

      var text =
        "Olá! Meu nome é " +
        name +
        ". Telefone: " +
        phone +
        (subject ? ". Assunto: " + subject : "") +
        ". Mensagem: " +
        message;

      var url =
        "https://wa.me/" + whatsNumber + "?text=" + encodeURIComponent(text);

      if (feedback) {
        feedback.classList.add("is-visible");
        feedback.textContent =
          "Obrigada pelo contato! Você será redirecionada ao WhatsApp para concluir o envio.";
      }

      window.setTimeout(function () {
        window.open(url, "_blank", "noopener");
      }, 500);

      form.reset();
    });
  }

  /* ---------- Testimonials carousel ---------- */
  function initCarousel() {
    var root = document.querySelector("[data-carousel]");
    if (!root) return;

    var slides = Array.prototype.slice.call(
      root.querySelectorAll("[data-slide]")
    );
    var dotsWrap = root.querySelector("[data-carousel-dots]");
    var prevBtn = root.querySelector("[data-carousel-prev]");
    var nextBtn = root.querySelector("[data-carousel-next]");
    if (!slides.length) return;

    var index = slides.findIndex(function (s) {
      return s.classList.contains("is-active");
    });
    if (index < 0) index = 0;

    var timer = null;
    var interval = 7000;

    var renderDots = function () {
      if (!dotsWrap) return;
      dotsWrap.innerHTML = "";
      slides.forEach(function (_, i) {
        var btn = document.createElement("button");
        btn.type = "button";
        btn.className = "carousel-dot" + (i === index ? " is-active" : "");
        btn.setAttribute("aria-label", "Ir para depoimento " + (i + 1));
        btn.addEventListener("click", function () {
          goTo(i);
          restart();
        });
        dotsWrap.appendChild(btn);
      });
    };

    var viewport = root.querySelector(".testimonials-carousel__viewport");

    var syncHeight = function () {
      if (!viewport) return;
      var active = slides[index];
      if (!active) return;
      viewport.style.minHeight = active.offsetHeight + "px";
    };

    var goTo = function (next) {
      if (next === index) return;
      var current = slides[index];
      var target = slides[next];

      current.classList.remove("is-active");
      if (!prefersReducedMotion) {
        current.classList.add("is-leaving");
        window.setTimeout(function () {
          current.classList.remove("is-leaving");
        }, 550);
      }

      target.classList.add("is-active");
      index = next;
      renderDots();
      syncHeight();
    };

    var next = function () {
      goTo((index + 1) % slides.length);
    };

    var prev = function () {
      goTo((index - 1 + slides.length) % slides.length);
    };

    var stop = function () {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    };

    var start = function () {
      if (prefersReducedMotion || slides.length < 2) return;
      stop();
      timer = window.setInterval(next, interval);
    };

    var restart = function () {
      stop();
      start();
    };

    if (prevBtn) {
      prevBtn.addEventListener("click", function () {
        prev();
        restart();
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener("click", function () {
        next();
        restart();
      });
    }

    root.addEventListener("mouseenter", stop);
    root.addEventListener("mouseleave", start);
    root.addEventListener("focusin", stop);
    root.addEventListener("focusout", start);

    document.addEventListener("visibilitychange", function () {
      if (document.hidden) stop();
      else start();
    });

    renderDots();
    syncHeight();
    window.addEventListener("resize", syncHeight);
    start();
  }

  /* ---------- Current year ---------- */
  function initYear() {
    var el = document.querySelector("[data-year]");
    if (el) el.textContent = new Date().getFullYear();
  }

  /* ---------- Scroll progress bar ---------- */
  function initScrollProgress() {
    var bar = document.querySelector("[data-scroll-progress]");
    if (!bar) return;

    var onScroll = function () {
      var scrollTop = window.scrollY || document.documentElement.scrollTop;
      var height =
        document.documentElement.scrollHeight - window.innerHeight;
      var progress = height > 0 ? (scrollTop / height) * 100 : 0;
      bar.style.width = progress + "%";
    };

    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll);
  }

  /* ---------- Page transitions ---------- */
  function initPageTransitions() {
    var overlay = document.querySelector("[data-page-fade]");
    if (!overlay || prefersReducedMotion) return;

    var links = document.querySelectorAll('a[href$=".html"]');

    links.forEach(function (link) {
      if (link.target === "_blank") return;
      if (link.hasAttribute("download")) return;

      link.addEventListener("click", function (e) {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        var href = link.getAttribute("href");
        var samePage = href === window.location.pathname.split("/").pop();
        if (samePage) return;

        e.preventDefault();
        overlay.classList.add("is-active");
        window.setTimeout(function () {
          window.location.href = href;
        }, 380);
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initSplash();
    initHeaderScroll();
    initMobileNav();
    initReveal();
    initHeroParallax();
    initCounters();
    initAccordion();
    initContactForm();
    initCarousel();
    initYear();
    initScrollProgress();
    initPageTransitions();
  });
})();
