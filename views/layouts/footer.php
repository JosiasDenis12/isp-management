</main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Topbar global search (suggestions)
        (function initTopbarSearch() {
            const input = document.getElementById('topbarSearch');
            const menu = document.getElementById('topbarSearchMenu');
            if (!input || !menu) return;

            const suggestUrl = input.getAttribute('data-suggest-url');
            const form = input.closest('form');
            const minLen = 2;
            let abortController = null;
            let debounceTimer = null;
            let items = [];
            let activeIndex = -1;

            function setExpanded(expanded) {
                input.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }

            function hideMenu() {
                menu.classList.remove('show');
                setExpanded(false);
                activeIndex = -1;
                updateActiveItem();
            }

            function showMenu() {
                menu.classList.add('show');
                setExpanded(true);
            }

            function clearMenu() {
                menu.innerHTML = '';
                items = [];
                activeIndex = -1;
                setExpanded(false);
            }

            function updateActiveItem() {
                items.forEach((el, idx) => {
                    if (idx === activeIndex) el.classList.add('active');
                    else el.classList.remove('active');
                });
            }

            function createSection(title) {
                const div = document.createElement('div');
                div.className = 'search-suggest-section';
                div.textContent = title;
                return div;
            }

            function createHint(text) {
                const div = document.createElement('div');
                div.className = 'search-suggest-hint';
                div.textContent = text;
                return div;
            }

            function typeMeta(type) {
                switch (type) {
                    case 'cliente':
                        return { icon: 'fa-users', iconClass: 'bg-primary-subtle text-primary' };
                    case 'pago':
                        return { icon: 'fa-file-invoice-dollar', iconClass: 'bg-success-subtle text-success' };
                    case 'equipo':
                        return { icon: 'fa-router', iconClass: 'bg-info-subtle text-info' };
                    default:
                        return { icon: 'fa-magnifying-glass', iconClass: 'bg-secondary-subtle text-secondary' };
                }
            }

            function createItem(item) {
                const a = document.createElement('a');
                a.href = item.url;
                a.className = 'search-suggest-item';
                a.setAttribute('role', 'option');
                a.tabIndex = -1;

                const meta = typeMeta(item.type);

                const icon = document.createElement('div');
                icon.className = 'search-suggest-icon ' + meta.iconClass;
                const i = document.createElement('i');
                i.className = 'fas ' + meta.icon;
                icon.appendChild(i);

                const content = document.createElement('div');
                content.className = 'flex-grow-1';

                const titleRow = document.createElement('div');
                titleRow.className = 'd-flex align-items-center gap-2';

                const title = document.createElement('div');
                title.className = 'search-suggest-title text-truncate';
                title.textContent = item.title || '';
                titleRow.appendChild(title);

                content.appendChild(titleRow);

                if (item.subtitle) {
                    const subtitle = document.createElement('div');
                    subtitle.className = 'search-suggest-subtitle text-truncate';
                    subtitle.textContent = item.subtitle;
                    content.appendChild(subtitle);
                }

                const right = document.createElement('div');
                right.className = 'search-suggest-meta';
                if (item.badge) {
                    const badge = document.createElement('span');
                    badge.className = 'badge rounded-pill bg-light text-dark border';
                    badge.textContent = item.badge;
                    right.appendChild(badge);
                }

                a.appendChild(icon);
                a.appendChild(content);
                a.appendChild(right);

                a.addEventListener('mouseenter', () => {
                    activeIndex = items.indexOf(a);
                    updateActiveItem();
                });

                return a;
            }

            function render(groups, q) {
                menu.innerHTML = '';
                items = [];
                activeIndex = -1;

                const clientes = (groups && groups.clientes) ? groups.clientes : [];
                const pagos = (groups && groups.pagos) ? groups.pagos : [];
                const equipos = (groups && groups.equipos) ? groups.equipos : [];

                const total = clientes.length + pagos.length + equipos.length;
                if (!total) {
                    menu.appendChild(createHint('Sin resultados. Presiona Enter para buscar en detalle.'));
                    showMenu();
                    return;
                }

                if (clientes.length) {
                    menu.appendChild(createSection('Clientes'));
                    clientes.forEach((it) => {
                        const el = createItem(it);
                        items.push(el);
                        menu.appendChild(el);
                    });
                }
                if (pagos.length) {
                    menu.appendChild(createSection('Pagos'));
                    pagos.forEach((it) => {
                        const el = createItem(it);
                        items.push(el);
                        menu.appendChild(el);
                    });
                }
                if (equipos.length) {
                    menu.appendChild(createSection('Equipos'));
                    equipos.forEach((it) => {
                        const el = createItem(it);
                        items.push(el);
                        menu.appendChild(el);
                    });
                }

                menu.appendChild(createHint('Enter para ver todos los resultados'));
                showMenu();
            }

            async function fetchSuggest(q) {
                if (!suggestUrl) return;

                if (abortController) abortController.abort();
                abortController = new AbortController();

                const url = suggestUrl + '?q=' + encodeURIComponent(q);
                const res = await fetch(url, { signal: abortController.signal, headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                render(data.groups, q);
            }

            function scheduleSuggest() {
                const q = (input.value || '').trim();
                if (q.length < minLen) {
                    clearMenu();
                    hideMenu();
                    return;
                }
                if (debounceTimer) window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(() => {
                    fetchSuggest(q).catch(() => {});
                }, 180);
            }

            input.addEventListener('input', scheduleSuggest);
            input.addEventListener('focus', () => {
                if (menu.children.length) showMenu();
            });

            input.addEventListener('keydown', (e) => {
                if (!menu.classList.contains('show') && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                    if (menu.children.length) showMenu();
                }

                if (!menu.classList.contains('show')) return;
                if (!items.length) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = Math.min(activeIndex + 1, items.length - 1);
                    updateActiveItem();
                    items[activeIndex].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = Math.max(activeIndex - 1, 0);
                    updateActiveItem();
                    items[activeIndex].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'Enter') {
                    if (activeIndex >= 0 && items[activeIndex]) {
                        e.preventDefault();
                        window.location.href = items[activeIndex].href;
                    }
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    hideMenu();
                }
            });

            document.addEventListener('mousedown', (e) => {
                if (!form) return;
                if (!form.contains(e.target)) {
                    hideMenu();
                }
            });

            if (form) {
                form.addEventListener('submit', () => {
                    hideMenu();
                });
            }
        })();
    </script>
</body>
</html>
