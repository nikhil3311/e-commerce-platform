(() => {
  const parse = (html) => new DOMParser().parseFromString(html, "text/html");
  const internal = (url) => url.origin === window.location.origin && !url.pathname.match(/\.(pdf|jpg|jpeg|png|gif|webp|zip)$/i);
  const loading = (value) => {
    document.body.classList.toggle("is-loading", value);
    document.querySelectorAll("button[type=submit], input[type=submit]").forEach((button) => { button.disabled = value; });
  };
  const error = (message) => {
    const toast = document.createElement("div");
    toast.className = "toast error";
    toast.textContent = message;
    document.querySelector("main")?.prepend(toast);
    setTimeout(() => toast.remove(), 4500);
  };
  const update = (html, url, push = true, scrollPosition = 0) => {
    const next = parse(html);
    const main = next.querySelector("main");
    const header = next.querySelector(".nav-wrap");
    if (!main) {
      window.location.assign(url);
      return;
    }
    document.querySelector("main")?.replaceWith(main);
    if (header) document.querySelector(".nav-wrap")?.replaceWith(header);
    document.title = next.title || document.title;
    if (push) history.pushState({}, "", url);
    window.scrollTo(0, 0);
    initialize();
  };
  const request = async (url, options = {}, push = true, scrollPosition = window.scrollY) => {
    loading(true);
    try {
      const response = await fetch(url, { ...options, headers: { "X-Requested-With": "fetch", ...(options.headers || {}) } });
      const contentType = response.headers.get("content-type") || "";
      if (contentType.includes("application/json")) {
        const data = await response.json();
        if (data.message) error(data.message);
        return;
      }
      const html = await response.text();
      if (!response.ok) throw new Error("Request failed. Please try again.");
      update(html, response.url, push, scrollPosition);
    } catch (exception) {
      error(exception.message || "Something went wrong. Please try again.");
    } finally {
      loading(false);
    }
  };
  const initialize = () => {
    const toggle = document.querySelector(".menu-toggle");
    toggle?.addEventListener("click", () => document.querySelector(".main-nav")?.classList.toggle("open"));
    document.querySelectorAll("[data-quantity]").forEach((input) => input.addEventListener("change", () => { if (input.value < 1) input.value = 1; }));
    document.querySelectorAll(".toast").forEach((item) => setTimeout(() => item.remove(), 4000));
    document.querySelectorAll("[data-confirm]").forEach((item) => item.addEventListener("click", (event) => { if (!confirm(item.dataset.confirm)) event.preventDefault(); }));
    const addressSelect = document.querySelector("[data-address-select]");
    addressSelect?.addEventListener("change", () => {
      const option = addressSelect.selectedOptions[0];
      document.querySelectorAll("[data-address-field]").forEach((field) => { if (option?.dataset[field.dataset.addressField]) field.value = option.dataset[field.dataset.addressField]; });
    });
    const zoom = document.querySelector("[data-zoom]");
    const image = document.querySelector("#product-main-image");
    zoom?.addEventListener("mousemove", (event) => { const box = zoom.getBoundingClientRect(); image.style.transformOrigin = `${((event.clientX - box.left) / box.width) * 100}% ${((event.clientY - box.top) / box.height) * 100}%`; image.classList.add("zoomed"); });
    zoom?.addEventListener("mouseleave", () => image?.classList.remove("zoomed"));
  };
  document.addEventListener("click", (event) => {
    const link = event.target.closest("a");
    if (!link || event.defaultPrevented || link.target || link.hasAttribute("download")) return;
    const url = new URL(link.href, location.href);
    if (url.hash && url.pathname === location.pathname || !internal(url)) return;
    event.preventDefault();
    request(url.href, {}, true, 0);
  });
  document.addEventListener("submit", (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.dataset.native === "true") return;
    const url = new URL(form.getAttribute("action") || location.href, location.href);
    if (!internal(url) || !form.reportValidity()) return;
    event.preventDefault();
    if ((form.method || "GET").toUpperCase() === "GET") {
      url.search = new URLSearchParams(new FormData(form)).toString();
      request(url.href, {}, true, 0);
    } else {
      const data = new FormData(form);
      if (event.submitter?.name) data.append(event.submitter.name, event.submitter.value);
      request(url.href, { method: form.method.toUpperCase(), body: data }, true, window.scrollY);
    }
  });
  window.addEventListener("popstate", () => request(location.href, { method: "GET" }, false, 0));
  initialize();
})();
