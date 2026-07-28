/**
 * Fetch harga emas Antam menggunakan headless browser.
 *
 * DISCLAIMER:
 * Script ini mengambil data harga emas dari sumber publik (logammulia.com) semata-mata
 * untuk keperluan personal dashboard. Tidak ada niat untuk membebani server, mendistribusikan
 * ulang data secara komersial, atau melanggar ketentuan layanan pemilik situs.
 * Penggunaan dibatasi 1x per hari via scheduler. Jika pemilik situs menyediakan API resmi
 * di kemudian hari, script ini akan diganti dengan integrasi resmi tersebut.
 *
 * Flow:
 * 1. Buka halaman captcha untuk ambil CSRF token (via real browser session)
 * 2. Hit API endpoint dengan token tersebut
 *
 * Env vars (diterima dari PHP caller):
 * - ANTAM_CAPTCHA_URL  → URL halaman captcha untuk ambil token
 * - ANTAM_API_URL      → URL API harga emas
 *
 * Output: JSON ke stdout { "price": <int> } atau { "error": "<message>" }
 */
import puppeteer from 'puppeteer';

const CAPTCHA_URL = process.env.ANTAM_CAPTCHA_URL;
const API_URL = process.env.ANTAM_API_URL;
const TIMEOUT = 30_000;

if (!CAPTCHA_URL || !API_URL) {
    process.stdout.write(JSON.stringify({ error: 'ANTAM_CAPTCHA_URL and ANTAM_API_URL env vars are required' }));
    process.exitCode = 1;
    process.exit();
}

async function fetchGoldPrice() {
    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
    });

    try {
        const page = await browser.newPage();

        await page.setUserAgent(
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );

        // Step 1: Buka halaman captcha untuk dapet session cookie + CSRF token
        await page.goto(CAPTCHA_URL, { waitUntil: 'domcontentloaded', timeout: TIMEOUT });

        const token = await page.evaluate(() => {
            const input = document.querySelector('input[name="_token"]');
            return input ? input.getAttribute('value') : null;
        });

        if (!token) {
            process.stdout.write(JSON.stringify({ error: 'Unable to extract CSRF token' }));
            process.exitCode = 1;
            return;
        }

        // Step 2: Fetch harga dari API (cookies session sudah di-set oleh browser)
        const today = new Date().toISOString().split('T')[0];
        const url = `${API_URL}?_token=${encodeURIComponent(token)}&transition=1&transition_date=${today}`;

        const response = await page.evaluate(async (apiUrl) => {
            const res = await fetch(apiUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });

            if (!res.ok) {
                return { error: `HTTP ${res.status}` };
            }

            return res.json();
        }, url);

        if (response.error) {
            process.stdout.write(JSON.stringify({ error: response.error }));
            process.exitCode = 1;
            return;
        }

        // Extract price dari response
        const price = extractPrice(response);

        if (price && price > 0) {
            process.stdout.write(JSON.stringify({ price }));
        } else {
            process.stdout.write(JSON.stringify({ error: 'Could not extract price from API response' }));
            process.exitCode = 1;
        }
    } catch (err) {
        process.stdout.write(JSON.stringify({ error: err.message }));
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
}

function extractPrice(data) {
    // [[timestamp, price], ...]
    if (Array.isArray(data) && Array.isArray(data[0])) {
        const last = data[data.length - 1];
        return last && last[1] ? parseInt(last[1], 10) : null;
    }

    // { "price": ... }
    if (data && data.price) {
        return parseInt(data.price, 10);
    }

    // { "data": { "price": ... } }
    if (data && data.data && data.data.price) {
        return parseInt(data.data.price, 10);
    }

    // { "sell": ... }
    if (data && data.sell) {
        return parseInt(data.sell, 10);
    }

    return null;
}

fetchGoldPrice();
