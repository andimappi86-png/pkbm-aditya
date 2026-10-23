/**
 * Interaksi halaman publik (landing).
 *
 * Hanya berisi dua perilaku dekoratif yang tidak boleh menghalangi akses konten:
 * - reveal elemen saat masuk viewport (IntersectionObserver),
 * - hitung angka statistik (Number Ticker).
 *
 * Konten final selalu sudah tercetak di HTML. Bila JavaScript gagal,
 * prefers-reduced-motion aktif, atau IntersectionObserver tidak tersedia,
 * halaman tetap tampil utuh tanpa animasi.
 */
const root = document.documentElement;
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const supportsObserver = 'IntersectionObserver' in window;

const revealItems = Array.from(document.querySelectorAll('.lp-reveal'));

if (revealItems.length > 0) {
    if (prefersReducedMotion || !supportsObserver) {
        root.classList.remove('js-reveal');
    } else {
        const revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                });
            },
            { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
        );

        revealItems.forEach((item) => revealObserver.observe(item));

        // Jaring pengaman: bila sesuatu membuat observer tidak pernah menyala,
        // pastikan konten tidak tertinggal dalam keadaan transparan.
        window.setTimeout(() => {
            revealItems.forEach((item) => item.classList.add('is-visible'));
        }, 4000);
    }
}

const countItems = Array.from(document.querySelectorAll('[data-count-to]'));

const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value);

const animateCount = (element) => {
    const target = Number.parseInt(element.dataset.countTo ?? '', 10);

    if (!Number.isFinite(target) || target <= 0) {
        return;
    }

    const duration = 900;
    const start = performance.now();

    const step = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);

        if (progress < 1) {
            element.textContent = formatNumber(Math.round(target * eased));
            window.requestAnimationFrame(step);
            return;
        }

        element.textContent = formatNumber(target);
    };

    element.textContent = formatNumber(0);
    window.requestAnimationFrame(step);
};

if (countItems.length > 0 && !prefersReducedMotion && supportsObserver) {
    const countObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                animateCount(entry.target);
                countObserver.unobserve(entry.target);
            });
        },
        { threshold: 0.4 }
    );

    countItems.forEach((item) => countObserver.observe(item));
}
