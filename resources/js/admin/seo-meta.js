// SEO meta box: tab ↔ preview switching, character counters, live snippets.
// Chuỗi hiển thị lấy từ data-* trên .seo-meta-container để tránh nội suy vào JS.

export default function initSeoMeta() {
    const containers = document.querySelectorAll('.seo-meta-container');
    if (!containers.length) return;

    containers.forEach(initContainer);
    bindSlugSync();
}

function initContainer(container) {
    const langCode = container.dataset.lang || '';

    // Không cần JS ẩn/hiện preview nữa: mỗi preview nằm trong tab-pane tương ứng,
    // Bootstrap pills tự lo phần hiển thị (tab "Nâng cao" không có preview).
    bindPreviewToggle(container);

    const handle = () => renderPreviews(container, langCode);

    container
        .querySelectorAll('.seo-meta-title, .seo-meta-description, .seo-og-title, .seo-og-description, .seo-og-image-input')
        .forEach((input) => input.addEventListener('input', handle));

    const sourceInputs = new Set([
        ...document.querySelectorAll(`.seo-source-name[data-lang="${langCode}"]`),
        ...document.querySelectorAll(`input[name="translations[${langCode}][name]"]`),
        ...document.querySelectorAll('input[name="name"]'),
        ...document.querySelectorAll(`textarea[name="translations[${langCode}][excerpt]"]`),
        ...document.querySelectorAll('textarea[name="excerpt"]'),
    ]);
    sourceInputs.forEach((input) => input.addEventListener('input', handle));

    handle();
}

function bindPreviewToggle(container) {
    container.querySelectorAll('.seo-preview-toggle').forEach((group) => {
        const btnDesktop = group.querySelector('.btn-preview-desktop');
        const btnMobile = group.querySelector('.btn-preview-mobile');
        const preview = group.closest('.google-preview-card')?.querySelector('.google-preview-container');
        if (!btnDesktop || !btnMobile || !preview) return;

        const setMode = (isMobile) => {
            // Kích thước do CSS quyết định (.is-mobile) — không gán inline style nữa.
            btnDesktop.classList.toggle('active', !isMobile);
            btnMobile.classList.toggle('active', isMobile);
            btnDesktop.setAttribute('aria-pressed', String(!isMobile));
            btnMobile.setAttribute('aria-pressed', String(isMobile));
            preview.classList.toggle('is-mobile', isMobile);
        };

        btnDesktop.addEventListener('click', (e) => {
            e.preventDefault();
            setMode(false);
        });
        btnMobile.addEventListener('click', (e) => {
            e.preventDefault();
            setMode(true);
        });
    });
}

function getSourceValue(selectors) {
    for (const selector of selectors) {
        const input = document.querySelector(selector);
        if (input) return input.value.trim();
    }
    return '';
}

function renderPreviews(container, langCode) {
    const metaTitleInput = container.querySelector('.seo-meta-title');
    const metaDescInput = container.querySelector('.seo-meta-description');
    if (!metaTitleInput || !metaDescInput) return;

    const fallbackTitle = container.dataset.fallbackTitle || '';
    const fallbackDesc = container.dataset.fallbackDesc || '';
    const placeholderTitle = container.dataset.placeholderTitle || '';
    const placeholderDesc = container.dataset.placeholderDesc || '';

    const title = metaTitleInput.value.trim();
    const desc = metaDescInput.value.trim();
    const ogTitle = container.querySelector('.seo-og-title')?.value.trim() || '';
    const ogDesc = container.querySelector('.seo-og-description')?.value.trim() || '';
    const ogImage = container.querySelector('.seo-og-image-input')?.value.trim() || '';

    const sourceTitle = getSourceValue([
        `.seo-source-name[data-lang="${langCode}"]`,
        `input[name="translations[${langCode}][name]"]`,
        'input[name="name"]',
    ]);
    const sourceDesc = getSourceValue([
        `textarea[name="translations[${langCode}][excerpt]"]`,
        'textarea[name="excerpt"]',
    ]);

    const evalTitle = title || sourceTitle;
    const evalDesc = desc || sourceDesc;
    const displayTitle = evalTitle || fallbackTitle;
    const displayDesc = evalDesc || fallbackDesc;

    metaTitleInput.placeholder = sourceTitle || placeholderTitle;
    metaDescInput.placeholder = sourceDesc || placeholderDesc;

    updateCounter(container, {
        counter: '.seo-counter-title',
        bar: '.seo-progress-title',
        length: evalTitle.length,
        max: 60,
        minRecommended: 40,
    });
    updateCounter(container, {
        counter: '.seo-counter-description',
        bar: '.seo-progress-description',
        length: evalDesc.length,
        max: 160,
        minRecommended: 120,
    });

    const googleTitle = container.querySelector('.google-preview-card .seo-preview-title');
    const googleDesc = container.querySelector('.google-preview-card .seo-preview-description');
    if (googleTitle) {
        googleTitle.textContent = displayTitle;
        googleTitle.classList.toggle('is-placeholder', !evalTitle);
    }
    if (googleDesc) {
        googleDesc.textContent = displayDesc;
        googleDesc.classList.toggle('is-placeholder', !evalDesc);
    }

    const socialTitle = container.querySelector('.social-preview-card .seo-preview-og-title');
    const socialDesc = container.querySelector('.social-preview-card .seo-preview-og-description');
    const socialImage = container.querySelector('.social-preview-card .seo-preview-og-image');
    if (socialTitle) {
        socialTitle.textContent = ogTitle || displayTitle;
        socialTitle.classList.toggle('is-placeholder', !(ogTitle || evalTitle));
    }
    if (socialDesc) {
        socialDesc.textContent = ogDesc || displayDesc;
        socialDesc.classList.toggle('is-placeholder', !(ogDesc || evalDesc));
    }
    if (socialImage) {
        if (ogImage) {
            socialImage.style.backgroundImage = `url('${ogImage}')`;
            socialImage.innerHTML = '';
        } else {
            socialImage.style.backgroundImage = 'none';
            socialImage.innerHTML =
                '<svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
        }
    }
}

function updateCounter(container, { counter, bar, length, max, minRecommended }) {
    const counterEl = container.querySelector(counter);
    const barEl = container.querySelector(bar);
    if (!counterEl || !barEl) return;

    counterEl.textContent = `${length}/${max}`;
    barEl.style.width = `${Math.min((length / max) * 100, 100)}%`;

    counterEl.classList.remove('text-body-secondary', 'text-warning', 'text-success', 'text-danger');
    barEl.classList.remove('bg-secondary', 'bg-warning', 'bg-success', 'bg-danger');

    let stateClass;
    if (length === 0) {
        stateClass = 'secondary';
    } else if (length < minRecommended) {
        stateClass = 'warning';
    } else if (length <= max) {
        stateClass = 'success';
    } else {
        stateClass = 'danger';
    }

    counterEl.classList.add(`text-${stateClass}`);
    barEl.classList.add(`bg-${stateClass}`);
}

function bindSlugSync() {
    const slugInputs = new Set([
        ...document.querySelectorAll('.seo-source-slug'),
        ...document.querySelectorAll('input[name*="[slug]"]'),
    ]);

    const sync = (input) => {
        let langCode = input.dataset.lang;
        if (!langCode) {
            const match = input.name.match(/\[([a-zA-Z-]{2,5})\]\[slug\]/) || input.name.match(/\[([a-zA-Z-]{2,5})\]/);
            if (match) langCode = match[1];
        }
        if (!langCode) return;

        const el = document.querySelector(`.google-preview-card[data-lang="${langCode}"] .seo-preview-slug`);
        if (el) el.textContent = input.value.trim() || '...';
    };

    slugInputs.forEach((input) => {
        input.addEventListener('input', () => sync(input));
        if (input.value) sync(input);
    });
}
