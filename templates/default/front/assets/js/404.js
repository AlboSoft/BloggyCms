const canvas = document.getElementById('bgCanvas');
const ctx = canvas.getContext('2d');
let W, H;
let mouseX = -1000, mouseY = -1000;
const particles = [];
const portalCenter = { x: 0, y: 0 };

function resize() {
    W = canvas.width = window.innerWidth;
    H = canvas.height = window.innerHeight;
    portalCenter.x = W / 2;
    portalCenter.y = H / 2 - 40;
}
resize();
window.addEventListener('resize', resize);

class Particle {
    constructor() {
        this.reset();
    }
    reset() {
        const edge = Math.random();
        if (edge < 0.25) { this.x = Math.random() * W; this.y = -10; }
        else if (edge < 0.5) { this.x = Math.random() * W; this.y = H + 10; }
        else if (edge < 0.75) { this.x = -10; this.y = Math.random() * H; }
        else { this.x = W + 10; this.y = Math.random() * H; }

        this.size = Math.random() * 2.5 + 0.5;
        this.speedX = (Math.random() - 0.5) * 0.5;
        this.speedY = (Math.random() - 0.5) * 0.5;
        this.opacity = Math.random() * 0.6 + 0.1;
        this.life = 1;
        this.sucked = false;
    }
    update() {
        const dx = portalCenter.x - this.x;
        const dy = portalCenter.y - this.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        const portalRadius = Math.min(W, H) * 0.08;

        if (dist < portalRadius * 4) {
            const force = Math.max(0, (portalRadius * 4 - dist) / (portalRadius * 4));
            this.speedX += (dx / dist) * force * 0.15;
            this.speedY += (dy / dist) * force * 0.15;
            this.sucked = true;
        }

        if (this.sucked && dist < portalRadius * 3) {
            this.speedX += (-dy / dist) * 0.03;
            this.speedY += (dx / dist) * 0.03;
        }

        this.x += this.speedX;
        this.y += this.speedY;
        this.speedX *= 0.995;
        this.speedY *= 0.995;

        if (dist < portalRadius * 0.3 || this.x < -50 || this.x > W + 50 || this.y < -50 || this.y > H + 50) {
            this.reset();
        }
    }
    draw() {
        const dx = portalCenter.x - this.x;
        const dy = portalCenter.y - this.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        const portalRadius = Math.min(W, H) * 0.08;

        let alpha = this.opacity;
        if (dist < portalRadius) {
            alpha *= dist / portalRadius;
        }

        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(23, 100, 206, ${alpha})`;
        ctx.fill();

        if (this.sucked) {
            ctx.beginPath();
            ctx.moveTo(this.x, this.y);
            ctx.lineTo(this.x - this.speedX * 5, this.y - this.speedY * 5);
            ctx.strokeStyle = `rgba(74, 143, 224, ${alpha * 0.3})`;
            ctx.lineWidth = this.size * 0.5;
            ctx.stroke();
        }
    }
}

for (let i = 0; i < 120; i++) {
    const p = new Particle();
    p.x = Math.random() * W;
    p.y = Math.random() * H;
    particles.push(p);
}

const cursorTrail = [];
document.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    for (let i = 0; i < 2; i++) {
        cursorTrail.push({
            x: e.clientX + (Math.random() - 0.5) * 8,
            y: e.clientY + (Math.random() - 0.5) * 8,
            size: Math.random() * 2 + 1,
            life: 1,
            decay: Math.random() * 0.03 + 0.02
        });
    }
    if (cursorTrail.length > 60) cursorTrail.splice(0, cursorTrail.length - 60);
});

function animate() {
    ctx.clearRect(0, 0, W, H);
    ctx.strokeStyle = 'rgba(23, 100, 206, 0.03)';
    ctx.lineWidth = 1;
    const gridSize = 60;
    for (let x = 0; x < W; x += gridSize) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, H);
        ctx.stroke();
    }
    for (let y = 0; y < H; y += gridSize) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(W, y);
        ctx.stroke();
    }

    particles.forEach(p => { p.update(); p.draw(); });

    cursorTrail.forEach((t, i) => {
        t.life -= t.decay;
        if (t.life <= 0) { cursorTrail.splice(i, 1); return; }
        ctx.beginPath();
        ctx.arc(t.x, t.y, t.size * t.life, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(23, 100, 206, ${t.life * 0.5})`;
        ctx.fill();
    });

    requestAnimationFrame(animate);
}
animate();

const homeBtn = document.getElementById('homeBtn');
const btnWrapper = document.getElementById('btnWrapper');
const attemptCounter = document.getElementById('attemptCounter');
const glitchOverlay = document.getElementById('glitchOverlay');

let attempts = 0;
let caught = false;
const maxAttempts = 7;

const taunts = [
    lang === 'ru' ? 'Не так быстро! 😏' : 'Not so fast! 😏',
    lang === 'ru' ? 'Почти поймал!' : 'Almost caught!',
    lang === 'ru' ? 'Попробуй ещё 👀' : 'Try again 👀',
    lang === 'ru' ? 'Слишком медленно!' : 'Too slow!',
    lang === 'ru' ? 'Ха-ха, мимо!' : 'Ha-ha, missed!',
    lang === 'ru' ? 'Ловкость — не твоё 🫠' : 'Agility is not your thing 🫠',
    lang === 'ru' ? 'Уже близко... нет.' : 'So close... no.',
    lang === 'ru' ? 'Ладно, ладно, держи...' : 'Okay, okay, take it...'
];

function showTaunt(x, y) {
    const taunt = document.createElement('div');
    taunt.className = 'taunt';
    taunt.textContent = taunts[Math.min(attempts, taunts.length - 1)];
    taunt.style.left = x + 'px';
    taunt.style.top = y + 'px';
    document.body.appendChild(taunt);
    setTimeout(() => taunt.remove(), 1500);
}

function triggerGlitch() {
    glitchOverlay.classList.add('active');
    setTimeout(() => glitchOverlay.classList.remove('active'), 300);
}

homeBtn.addEventListener('mouseenter', (e) => {
    if (caught) return;
    if (attempts >= maxAttempts) {
        caught = true;
        homeBtn.classList.add('caught');
        attemptCounter.textContent = lang === 'ru' ? 'Ладно, ты победил. Нажимай.' : 'Okay, you win. Click it.';
        attemptCounter.style.color = 'rgba(23, 100, 206, 0.6)';
        homeBtn.style.background = '#1764CE';
        homeBtn.style.color = '#fff';
        homeBtn.style.boxShadow = '0 0 40px rgba(23,100,206,0.6)';
        return;
    }

    attempts++;
    triggerGlitch();

    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const padding = 30;
    const btnW = homeBtn.offsetWidth;
    const btnH = homeBtn.offsetHeight;

    let newX, newY;
    let safeTries = 0;
    do {
        newX = padding + Math.random() * (vw - btnW - padding * 2);
        newY = padding + Math.random() * (vh - btnH - padding * 2);
        safeTries++;
    } while (
        safeTries < 20 &&
        Math.abs(newX - e.clientX) < 150 &&
        Math.abs(newY - e.clientY) < 150
    );

    homeBtn.style.position = 'fixed';
    homeBtn.style.left = newX + 'px';
    homeBtn.style.top = newY + 'px';

    showTaunt(e.clientX - 60, e.clientY - 30);
    attemptCounter.textContent = (lang === 'ru' ? 'Попыток поймать кнопку: ' : 'Attempts to catch the button: ') + attempts;

    if (navigator.vibrate) navigator.vibrate(50);
});

homeBtn.addEventListener('click', (e) => {
    if (!caught && attempts < maxAttempts) {
        e.preventDefault();
    }
});

const digits = document.querySelectorAll('.digit');
digits.forEach(d => {
    d.addEventListener('click', () => {
        d.style.transition = 'transform 0.1s';
        d.style.transform = `scale(0.9) rotate(${(Math.random()-0.5)*10}deg)`;
        triggerGlitch();
        setTimeout(() => {
            d.style.transform = '';
        }, 150);

        const rect = d.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;
        for (let i = 0; i < 8; i++) {
            cursorTrail.push({
                x: cx + (Math.random() - 0.5) * 40,
                y: cy + (Math.random() - 0.5) * 40,
                size: Math.random() * 4 + 2,
                life: 1,
                decay: 0.015
            });
        }
    });
});

const konamiCode = ['ArrowUp','ArrowUp','ArrowDown','ArrowDown','ArrowLeft','ArrowRight','ArrowLeft','ArrowRight'];
let konamiIndex = 0;

document.addEventListener('keydown', (e) => {
    if (e.key === konamiCode[konamiIndex]) {
        konamiIndex++;
        if (konamiIndex === konamiCode.length) {
            activateKonami();
            konamiIndex = 0;
        }
    } else {
        konamiIndex = 0;
    }
});

function activateKonami() {
    document.body.classList.toggle('konami-activated');

    const subtitle = document.getElementById('subtitle');
    const msg = document.getElementById('messageText');
    subtitle.textContent = lang === 'ru' ? '🌀 ПОРТАЛ АКТИВИРОВАН 🌀' : '🌀 PORTAL ACTIVATED 🌀';
    subtitle.style.color = '#1764CE';
    msg.textContent = lang === 'ru' 
        ? 'Вы разблокировали секрет! Но страница всё равно не найдена... Зато красиво!'
        : 'You unlocked the secret! But the page is still not found... At least it\'s beautiful!';
    msg.style.color = 'rgba(23,100,206,0.6)';

    for (let i = 0; i < 50; i++) {
        const p = new Particle();
        p.x = portalCenter.x;
        p.y = portalCenter.y;
        p.speedX = (Math.random() - 0.5) * 8;
        p.speedY = (Math.random() - 0.5) * 8;
        p.size = Math.random() * 4 + 1;
        particles.push(p);
    }

    triggerGlitch();
    setTimeout(triggerGlitch, 200);
    setTimeout(triggerGlitch, 500);

    setTimeout(() => {
        while (particles.length > 120) particles.pop();
    }, 5000);
}

let gravityActive = false;
document.getElementById('errorCode').addEventListener('dblclick', () => {
    if (gravityActive) return;
    gravityActive = true;
    document.getElementById('errorCode').classList.add('gravity-mode');

    setTimeout(() => {
        document.getElementById('errorCode').classList.remove('gravity-mode');
        gravityActive = false;
    }, 2500);
});

const subtitleEl = document.getElementById('subtitle');
const originalText = subtitleEl.textContent;
let charIndex = 0;
subtitleEl.textContent = '';

function typeSubtitle() {
    if (charIndex < originalText.length) {
        subtitleEl.textContent += originalText[charIndex];
        charIndex++;
        setTimeout(typeSubtitle, 50 + Math.random() * 40);
    }
}
setTimeout(typeSubtitle, 600);

document.addEventListener('mousemove', (e) => {
    const x = (e.clientX / W - 0.5) * 10;
    const y = (e.clientY / H - 0.5) * 10;
    document.getElementById('errorCode').style.transform = `translate(${x}px, ${y}px)`;
});