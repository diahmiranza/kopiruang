/* ============================================
   Kopi Ruang — Main JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

    // ============ NAVBAR SCROLL ============
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 60) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // ============ HAMBURGER MENU ============
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('open');
            const spans = hamburger.querySelectorAll('span');
            if (navLinks.classList.contains('open')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
            } else {
                spans.forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
            }
        });

        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('open');
                hamburger.querySelectorAll('span').forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
            });
        });
    }

    // ============ SMOOTH SCROLL FOR NAV LINKS ============
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                const offset = 80;
                window.scrollTo({
                    top: target.offsetTop - offset,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ============ SCROLL REVEAL ============
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.reveal, .reveal-left').forEach(el => {
        revealObserver.observe(el);
    });

    // ============ MENU TABS ============
    const tabs = document.querySelectorAll('.menu-tab');
    const cards = document.querySelectorAll('.menu-card');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const category = tab.dataset.category;

            cards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'flex';
                    setTimeout(() => { card.style.opacity = '1'; card.style.transform = 'translateY(0)'; }, 10);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(10px)';
                    setTimeout(() => { card.style.display = 'none'; }, 300);
                }
            });
        });
    });

    // ============ GUEST PICKER ============
    const guestBtns = document.querySelectorAll('.guest-btn');
    const guestInput = document.getElementById('res-guests');

    guestBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            guestBtns.forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            if (guestInput) guestInput.value = btn.dataset.val;
        });
    });

    // ============ RESERVATION FORM — WhatsApp Based ============
    const reservationForm = document.getElementById('reservationForm');
    if (reservationForm) {
        reservationForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Ambil nilai — toleran terhadap null
            const nameEl   = document.getElementById('res-name');
            const phoneEl  = document.getElementById('res-phone');
            const dateEl   = document.getElementById('res-date');
            const timeEl   = document.getElementById('res-time');
            const guestsEl = document.getElementById('res-guests');
            const notesEl  = document.getElementById('res-notes');

            const name   = (nameEl   ? nameEl.value   : '').trim();
            const phone  = (phoneEl  ? phoneEl.value  : '').trim();
            const date   = (dateEl   ? dateEl.value   : '').trim();
            const time   = (timeEl   ? timeEl.value   : '').trim();
            const guests = (guestsEl ? guestsEl.value : '2') || '2';
            const notes  = (notesEl  ? notesEl.value  : '').trim();

            // Validasi hanya field wajib
            const missing = [];
            if (!name)  missing.push('Nama');
            if (!phone) missing.push('No. WhatsApp');
            if (!date)  missing.push('Tanggal');
            if (!time)  missing.push('Jam');

            if (missing.length > 0) {
                showNotification('Harap isi: ' + missing.join(', '), 'error');
                // Highlight field kosong
                if (!name  && nameEl)  { nameEl.focus();  nameEl.style.borderColor  = 'rgba(224,85,85,0.6)'; }
                if (!phone && phoneEl) { phoneEl.style.borderColor = 'rgba(224,85,85,0.6)'; }
                if (!date  && dateEl)  { dateEl.style.borderColor  = 'rgba(224,85,85,0.6)'; }
                if (!time  && timeEl)  { timeEl.style.borderColor  = 'rgba(224,85,85,0.6)'; }
                return;
            }

            // Reset border merah kalau ada
            [nameEl, phoneEl, dateEl, timeEl].forEach(el => {
                if (el) el.style.borderColor = '';
            });

            const btn = this.querySelector('.btn-form');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Membuka WhatsApp...';
            btn.disabled = true;

            // Format date
            const dateObj  = new Date(date);
            const dayNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const months   = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            const dateStr  = `${dayNames[dateObj.getDay()]}, ${dateObj.getDate()} ${months[dateObj.getMonth()]} ${dateObj.getFullYear()}`;

            // Build WA message
            let msg = `Halo Kopi Ruang! 👋\n\nSaya ingin *reservasi meja* dengan detail berikut:\n\n`;
            msg += `👤 *Nama:* ${name}\n`;
            msg += `📱 *No. WA:* ${phone}\n`;
            msg += `📅 *Tanggal:* ${dateStr}\n`;
            msg += `🕐 *Jam:* ${time} WIB\n`;
            msg += `👥 *Tamu:* ${guests} orang\n`;
            if (notes) msg += `💬 *Catatan:* ${notes}\n`;
            msg += `\nTerima kasih! 🙏`;

            const waNumber = '6281234567890';
            const waURL    = `https://wa.me/${waNumber}?text=${encodeURIComponent(msg)}`;

            // Store fallback URL
            const fallbackBtn = document.getElementById('waFallbackBtn');
            if (fallbackBtn) fallbackBtn.href = waURL;

            // Show success
            setTimeout(() => {
                this.style.display = 'none';
                document.getElementById('formSuccess').style.display = 'block';
                // Open WA
                window.open(waURL, '_blank');
                btn.innerHTML = originalHTML;
                btn.disabled  = false;
            }, 600);
        });
    }

    // ============ NOTIFICATION ============
    function showNotification(message, type = 'success') {
        let notif = document.querySelector('.notification');
        if (!notif) {
            notif = document.createElement('div');
            notif.classList.add('notification');
            document.body.appendChild(notif);
        }

        notif.textContent = (type === 'success' ? '✓ ' : '⚠ ') + message;
        notif.className = 'notification ' + type;
        notif.classList.add('show');

        setTimeout(() => notif.classList.remove('show'), 4000);
    }

    // ============ COUNTER ANIMATION ============
    function animateCounter(el) {
        const target = parseInt(el.dataset.count);
        const suffix = el.dataset.suffix || '';
        const duration = 1800;
        const start = performance.now();

        function update(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(eased * target) + suffix;
            if (progress < 1) requestAnimationFrame(update);
        }

        requestAnimationFrame(update);
    }

    const counterObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

    // ============ CURRENT YEAR IN FOOTER ============
    const yearEl = document.getElementById('currentYear');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // ============ ACTIVE NAV ON SCROLL ============
    const sections = document.querySelectorAll('section[id]');
    const navItems = document.querySelectorAll('.nav-links a[href^="#"]');

    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 120;
            if (window.scrollY >= sectionTop) current = section.id;
        });

        navItems.forEach(a => {
            a.style.color = '';
            if (a.getAttribute('href') === '#' + current) {
                a.style.color = '#c9a96e';
            }
        });
    });

});
