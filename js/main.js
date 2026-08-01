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

  /* ---------- Current year ---------- */
  function initYear() {
    var el = document.querySelector("[data-year]");
    if (el) el.textContent = new Date().getFullYear();
  }

  document.addEventListener("DOMContentLoaded", function () {
    initSplash();
    initHeaderScroll();
    initMobileNav();
    initReveal();
    initAccordion();
    initContactForm();
    initYear();
  });
})();
