/* ── Mobile nav toggle ── */
const navToggle = document.querySelector("[data-nav-toggle]");
const mainNav = document.querySelector("[data-main-nav]");

if (navToggle && mainNav) {
  navToggle.addEventListener("click", () => {
    const isOpen = mainNav.classList.toggle("open");
    navToggle.setAttribute("aria-expanded", String(isOpen));
  });

  // Close main nav when clicking a link (but NOT when clicking a dropdown toggle)
  mainNav.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", (e) => {
      // If it's a link inside a dropdown, we should close the nav
      // If it's a top-level link that HAS a dropdown, we still navigate and close nav
      if (!link.classList.contains('dropdown-toggle')) {
        mainNav.classList.remove("open");
        navToggle.setAttribute("aria-expanded", "false");
      }
    });
  });

  /* ── Mobile Submenu Toggles ── */
  document.querySelectorAll(".dropdown-toggle").forEach((toggle) => {
    toggle.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const parent = toggle.closest('.has-dropdown');
      if (parent) {
        const menu = parent.querySelector(".dropdown-menu");
        toggle.classList.toggle("active");
        if (menu) menu.classList.toggle("open");
      }
    });
  });
}

/* ── Copyright year ── */
const yearNode = document.querySelector("[data-current-year]");
if (yearNode) {
  yearNode.textContent = String(new Date().getFullYear());
}

/* ── Scroll-triggered reveal (IntersectionObserver) ── */
const revealElements = document.querySelectorAll(
  ".reveal, .reveal-left, .reveal-right, .reveal-scale"
);

if (revealElements.length) {
  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          revealObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
  );

  revealElements.forEach((el) => revealObserver.observe(el));
}

/* ── Button ripple position tracking ── */
document.querySelectorAll(".btn").forEach((btn) => {
  btn.addEventListener("mousemove", (e) => {
    const rect = btn.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    btn.style.setProperty("--ripple-x", x + "%");
    btn.style.setProperty("--ripple-y", y + "%");
  });
});

/* ── Card tilt on hover (subtle 3D) ── */
document.querySelectorAll(".card").forEach((card) => {
  card.addEventListener("mousemove", (e) => {
    const rect = card.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width - 0.5;
    const y = (e.clientY - rect.top) / rect.height - 0.5;
    card.style.transform = `translateY(-6px) perspective(600px) rotateX(${-y * 4}deg) rotateY(${x * 4}deg)`;
  });

  card.addEventListener("mouseleave", () => {
    card.style.transform = "";
  });
});

/* ── Smooth header shadow on scroll ── */
const header = document.querySelector(".site-header");
if (header) {
  let lastScrollY = 0;
  window.addEventListener("scroll", () => {
    const scrollY = window.scrollY;
    if (scrollY > 10) {
      header.style.boxShadow = "0 4px 20px rgba(20,40,80,0.08)";
    } else {
      header.style.boxShadow = "";
    }
    lastScrollY = scrollY;
  }, { passive: true });
}

/* ── Parallax floating shapes ── */
const shapes = document.querySelectorAll(".float-shape");
if (shapes.length) {
  window.addEventListener("scroll", () => {
    const scrollY = window.scrollY;
    shapes.forEach((shape, i) => {
      const speed = i % 2 === 0 ? 0.03 : -0.02;
      shape.style.transform = `translateY(${scrollY * speed}px)`;
    });
  }, { passive: true });
}
