/* FO Psicologia — Dashboard stats */
(function () {
  "use strict";

  var charts = {
    hourly: null,
    weekday: null,
    timeline: null,
  };

  var gold = "#c79a5d";
  var charcoal = "#322e29";
  var sand = "#ecd9bb";

  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  function $all(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function formatDuration(seconds) {
    var s = Math.max(0, Math.round(Number(seconds) || 0));
    var m = Math.floor(s / 60);
    var r = s % 60;
    if (m <= 0) return r + "s";
    return m + "m " + String(r).padStart(2, "0") + "s";
  }

  function formatNumber(n) {
    return new Intl.NumberFormat("pt-BR").format(Number(n) || 0);
  }

  function destroyChart(key) {
    if (charts[key]) {
      charts[key].destroy();
      charts[key] = null;
    }
  }

  function barChart(canvasId, labels, values, key) {
    destroyChart(key);
    var canvas = document.getElementById(canvasId);
    if (!canvas || typeof Chart === "undefined") return;

    charts[key] = new Chart(canvas, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            data: values,
            backgroundColor: charcoal,
            borderRadius: 8,
            maxBarThickness: 28,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: "#746c62", font: { family: "Manrope", size: 11 } },
          },
          y: {
            beginAtZero: true,
            grid: { color: "rgba(50,46,41,0.06)" },
            ticks: { color: "#746c62", font: { family: "Manrope", size: 11 } },
          },
        },
      },
    });
  }

  function lineChart(canvasId, labels, users, sessions, key) {
    destroyChart(key);
    var canvas = document.getElementById(canvasId);
    if (!canvas || typeof Chart === "undefined") return;

    charts[key] = new Chart(canvas, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Visitantes",
            data: users,
            borderColor: charcoal,
            backgroundColor: "rgba(50,46,41,0.08)",
            fill: true,
            tension: 0.35,
            pointRadius: 0,
            borderWidth: 2,
          },
          {
            label: "Sessões",
            data: sessions,
            borderColor: gold,
            backgroundColor: "transparent",
            tension: 0.35,
            pointRadius: 0,
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: "bottom",
            labels: { boxWidth: 10, font: { family: "Manrope", size: 12 } },
          },
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              color: "#746c62",
              maxTicksLimit: 8,
              font: { family: "Manrope", size: 11 },
            },
          },
          y: {
            beginAtZero: true,
            grid: { color: "rgba(50,46,41,0.06)" },
            ticks: { color: "#746c62", font: { family: "Manrope", size: 11 } },
          },
        },
      },
    });
  }

  function renderMetrics(summary) {
    var root = $("[data-metrics]");
    if (!root || !summary) return;

    var cards = [
      {
        label: "Online agora",
        value: formatNumber(summary.onlineNow),
        meta: "Visitantes no site neste momento (heartbeat)",
        highlight: true,
      },
      {
        label: "Visitantes Únicos",
        value: formatNumber(summary.users),
        meta: "Total de visitas: " + formatNumber(summary.sessions),
      },
      {
        label: "Visualizações",
        value: formatNumber(summary.views),
        meta: "Média por visita: " + (summary.viewsPerVisit || 0),
      },
      {
        label: "Cliques WhatsApp",
        value: formatNumber(summary.whatsappClicks),
        meta: "Evento whatsapp_click (já instrumentado no site)",
      },
      {
        label: "Tempo Médio de Sessão",
        value: formatDuration(summary.avgSessionDuration),
        meta: "Duração média por sessão",
      },
      {
        label: "Taxa de Rejeição",
        value: (summary.bounceRate || 0) + "%",
        meta: "Sessões com pouco engajamento",
      },
      {
        label: "Taxa de Conversão",
        value: (summary.conversionRate || 0) + "%",
        meta: "Cliques WhatsApp / sessões",
      },
      {
        label: "Páginas por Sessão",
        value: summary.pagesPerSession || 0,
        meta: "Média de páginas visitadas",
      },
      {
        label: "Eventos Totais",
        value: formatNumber(summary.events),
        meta: "Interações registradas no GA4",
      },
    ];

    root.innerHTML = cards
      .map(function (c) {
        return (
          '<article class="metric-card' +
          (c.highlight ? " metric-card--live" : "") +
          '">' +
          '<p class="metric-card__label">' +
          c.label +
          "</p>" +
          '<p class="metric-card__value">' +
          c.value +
          "</p>" +
          '<p class="metric-card__meta">' +
          c.meta +
          "</p>" +
          "</article>"
        );
      })
      .join("");
  }

  function renderList(name, items, formatter) {
    var root = $('[data-list="' + name + '"]');
    if (!root) return;

    if (!items || !items.length) {
      root.innerHTML = '<p class="empty">Sem dados neste período.</p>';
      return;
    }

    root.innerHTML = items
      .map(function (item) {
        return formatter(item);
      })
      .join("");
  }

  function itemHtml(title, meta, value) {
    return (
      '<div class="list-item">' +
      "<div>" +
      '<div class="list-item__title">' +
      title +
      "</div>" +
      (meta ? '<div class="list-item__meta">' + meta + "</div>" : "") +
      "</div>" +
      '<div class="list-item__value">' +
      value +
      "</div>" +
      "</div>"
    );
  }

  function renderAllLists(data) {
    renderList("topPages", data.topPages, function (p) {
      return itemHtml(
        p.path || "/",
        formatDuration(p.avgDuration) + " médio",
        formatNumber(p.views) + " visualizações"
      );
    });

    renderList("devices", data.devices, function (d) {
      return itemHtml(
        d.label,
        "",
        formatNumber(d.value) + " · " + d.percent + "%"
      );
    });

    renderList("browsers", data.browsers, function (d) {
      return itemHtml(d.label, "", formatNumber(d.value));
    });

    renderList("os", data.os, function (d) {
      return itemHtml(d.label, "", formatNumber(d.value));
    });

    renderList("sources", data.sources, function (d) {
      return itemHtml(d.label, "", formatNumber(d.value));
    });

    renderList("landings", data.landings, function (d) {
      return itemHtml(d.label || "/", "", formatNumber(d.value) + " entradas");
    });

    renderList("exits", data.exits, function (d) {
      return itemHtml(
        d.path || "/",
        "",
        formatNumber(d.sessions) + " · rejeição " + d.bounceRate + "%"
      );
    });

    renderList("countries", data.countries, function (d) {
      return itemHtml(
        d.label,
        "",
        formatNumber(d.sessions) +
          " sessões · " +
          formatNumber(d.views) +
          " views"
      );
    });

    renderList("cities", data.cities, function (d) {
      return itemHtml(
        d.label,
        d.country || "",
        formatNumber(d.sessions) +
          " sessões · " +
          formatNumber(d.views) +
          " views"
      );
    });
  }

  function renderCharts(data) {
    var hourlyLabels = (data.hourly || []).map(function (h) {
      var n = parseInt(h.label, 10);
      return isNaN(n) ? h.label : String(n).padStart(2, "0") + "h";
    });
    var hourlyValues = (data.hourly || []).map(function (h) {
      return h.value;
    });
    // Preencher 0-23 se necessário
    if (hourlyLabels.length && hourlyLabels.length < 24) {
      var filled = [];
      var map = {};
      (data.hourly || []).forEach(function (h) {
        map[String(parseInt(h.label, 10))] = h.value;
      });
      for (var i = 0; i < 24; i++) {
        filled.push({ label: String(i).padStart(2, "0") + "h", value: map[String(i)] || 0 });
      }
      hourlyLabels = filled.map(function (x) { return x.label; });
      hourlyValues = filled.map(function (x) { return x.value; });
    }
    barChart("chart-hourly", hourlyLabels, hourlyValues, "hourly");

    barChart(
      "chart-weekday",
      (data.weekday || []).map(function (d) { return d.label; }),
      (data.weekday || []).map(function (d) { return d.value; }),
      "weekday"
    );

    lineChart(
      "chart-timeline",
      (data.timeline || []).map(function (d) { return d.label; }),
      (data.timeline || []).map(function (d) { return d.users; }),
      (data.timeline || []).map(function (d) { return d.sessions; }),
      "timeline"
    );
  }

  function setStatus(text, isError) {
    var el = $("[data-status]");
    if (!el) return;
    el.textContent = text;
    el.classList.toggle("is-error", !!isError);
  }

  function loadStats(range) {
    setStatus("Carregando dados do Google Analytics…", false);

    fetch("api/stats.php?range=" + encodeURIComponent(range), {
      headers: { Accept: "application/json" },
    })
      .then(function (r) {
        return r.json().then(function (d) {
          return { ok: r.ok, data: d };
        });
      })
      .then(function (res) {
        if (!res.ok || !res.data.ok) {
          throw new Error(
            (res.data && res.data.error) || "Não foi possível carregar as estatísticas."
          );
        }

        var data = res.data;
        renderMetrics(data.summary);
        renderCharts(data);
        renderAllLists(data);

        var note = $("[data-note]");
        if (note) note.textContent = data.note || "";

        var cacheLabel = data.cached ? " (cache)" : "";
        setStatus(
          "Atualizado em " +
            new Date(data.generatedAt || Date.now()).toLocaleString("pt-BR") +
            cacheLabel +
            " · período " +
            (data.startDate || "") +
            " → " +
            (data.endDate || ""),
          false
        );
      })
      .catch(function (err) {
        setStatus(err.message || "Erro ao carregar dados.", true);
        renderMetrics({
          onlineNow: 0,
          users: 0,
          sessions: 0,
          views: 0,
          viewsPerVisit: 0,
          whatsappClicks: 0,
          avgSessionDuration: 0,
          bounceRate: 0,
          conversionRate: 0,
          pagesPerSession: 0,
          events: 0,
        });
      });
  }

  function refreshOnlineOnly() {
    fetch("api/online.php", { headers: { Accept: "application/json" } })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !data.summary) return;
        var card = document.querySelector(".metric-card--live .metric-card__value");
        if (card) {
          card.textContent = formatNumber(data.summary.online);
        }
      })
      .catch(function () {});
  }

  function initRangeTabs() {
    var tabs = $("[data-range-tabs]");
    if (!tabs) return;

    tabs.addEventListener("click", function (e) {
      var btn = e.target.closest("[data-range]");
      if (!btn) return;
      $all("[data-range]", tabs).forEach(function (b) {
        b.classList.toggle("is-active", b === btn);
      });
      loadStats(btn.getAttribute("data-range"));
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    if (!$("[data-metrics]")) return;
    initRangeTabs();
    loadStats("30d");
    window.setInterval(refreshOnlineOnly, 20000);
  });
})();
