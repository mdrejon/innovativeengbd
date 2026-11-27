/**
 * JSON requests for the live chat admin screens (no axios in this app).
 * Laravel accepts the XSRF-TOKEN cookie value as the X-XSRF-TOKEN header.
 */
const xsrfToken = () => decodeURIComponent((document.cookie.match(/XSRF-TOKEN=([^;]+)/) || [])[1] || '');

export async function chatRequest(method, url, payload = null) {
    const options = {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken(),
        },
    };

    if (payload) {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(payload);
    }

    const response = await fetch(url, options);
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
        const error = new Error(firstError || data.message || `Request failed (${response.status})`);
        error.status = response.status;
        throw error;
    }

    return data;
}

/**
 * Short two-tone chime for new visitor messages.
 */
export function playChime() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        [660, 880].forEach((frequency, index) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            const start = ctx.currentTime + index * 0.16;
            osc.frequency.value = frequency;
            gain.gain.setValueAtTime(0.09, start);
            gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.3);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(start);
            osc.stop(start + 0.3);
        });
    } catch (e) {
        // Audio can be blocked until the user interacts with the page.
    }
}

/**
 * "now", "5m", "3h", "Mon", "12 Nov".
 */
export function shortTime(iso) {
    if (!iso) return '';
    const date = new Date(iso);
    const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
    if (seconds < 60) return 'now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h`;
    if (seconds < 604800) return date.toLocaleDateString([], { weekday: 'short' });
    return date.toLocaleDateString([], { day: 'numeric', month: 'short' });
}

export function clockTime(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

export function fullDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });
}
