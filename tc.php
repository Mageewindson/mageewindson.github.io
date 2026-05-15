<?php
// ─── Config ────────
// @todo Set your partnercode
$p       = null;    // Your partnercode
// @todo Set the offer id you want to use
$offerId = null;    // Offer ID
$lang    = 'en';    // Language code
$trackingParameters = array_intersect_key($_GET, array_flip([
    'click_id', 'sub_id', 'transaction_id', 'cookie_id', 'pi', 'p2', 'source',
]));
$tracker = $_GET['tid'] ?? $_GET['tracker'] ?? null;
$next    = $_GET['next'] ?? null;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" dir="auto">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    :root {
      --color-primary: #78cc4d;
      --color-bg:        linear-gradient(#3658ca 0%, #252a36 20%);
      --color-text:      #fff;
      --color-card-bg:   #fff;
      --color-card-text: #000;
      --color-error:     #e53e3e;
      --font-base:       system-ui, sans-serif;
      --radius-card:     30px;
      --radius-btn:      25px;
    }

    body, h1, h2, p { margin: 0; }
    img { max-width: 100%; display: block; }

    body {
      min-height: 100vh;
      font-family: var(--font-base);
      font-size: 16px;
      line-height: 1.5;
      color: var(--color-text);
      background: var(--color-bg);
    }

    #app {
      max-width: 750px;
      margin: 0 auto;
      padding: 2.5rem 1.5rem;
      text-align: center;
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    /* ── Logo ── */
    .logo {
      max-width: 150px;
      margin: 0 auto;
    }

    /* ── Headings ── */
    h1 { font-size: clamp(1.2rem, 5vw, 1.75rem); font-weight: 700; }

    form > div {
        border-radius: var(--radius-btn);
    }

    /* ── Cards (forms / screens) ── */
    #msisdn-form,
    #pin-form,
    #sms-screen,
    #success-screen,
    #fail-screen {
      background: var(--color-card-bg);
      color: var(--color-card-text);
      border-radius: var(--radius-card);
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    /* ── Labels ── */
    label {
      display: block;
      font-weight: 700;
      font-size: 1.2rem;
      text-align: center;
    }

    /* ── Phone prefix + input row ── */
    #msisdn-form > div,
    #pin-form > div {
      display: flex;
      width: 100%;
      max-width: 300px;
      margin: 0 auto;
      background: #f1f1f1;
    }

    #msisdn-prefix {
      padding: 0.375rem 0.625rem;
      font-size: 1.125rem;
      border-right: 1px solid #000;
      white-space: nowrap;
    }

    input[type="text"],
    input[type="tel"] {
      flex: 1;
      padding: 0.375rem 0.625rem;
      font-size: 1.125rem;
      font-family: inherit;
      color: #495057;
      background: transparent;
      border: 0;
      outline: none;
      width: 100%;
    }

    /* ── Buttons ── */
    button {
      display: block;
      width: 60%;
      margin: 0 auto;
      padding: 0.5em 1em;
      font-size: 1.25rem;
      font-weight: 600;
      font-family: inherit;
      text-transform: uppercase;
      color: #fff;
      background: var(--color-primary);
      border: 0;
      border-radius: var(--radius-btn);
      box-shadow: 0 3px 1px rgba(0,0,0,.21);
      cursor: pointer;
      transition: filter 0.2s ease;
    }
    button:hover,
    button:focus { filter: brightness(1.1); }
    button:disabled { opacity: 0.5; cursor: not-allowed; }

    /* ── Errors ── */
    .error {
      color: var(--color-error);
      font-size: 0.875rem;
      margin-top: 0.25rem;
      text-align: left;
    }

    /* ── Small text ── */
    .tariff,
    .terms {
      font-size: 0.833rem;
      opacity: 0.8;
    }

    /* ── Utility ── */
    .hidden { display: none !important; }

    @media (min-width: 769px) {
      #msisdn-form > div,
      #pin-form > div { max-width: 75%; }
    }
  </style>
</head>
<body>
  <div id="app">

    <img id="logo" class="logo hidden" src="" alt="">

    <h1 id="start-error" class="hidden"></h1>
    <h1 id="heading" class="hidden"></h1>

    <!-- Step 1: MSISDN form -->
    <form id="msisdn-form" class="hidden">
      <label id="msisdn-label" for="msisdn-input"></label>
      <div>
        <span id="msisdn-prefix" class="hidden"></span>
        <input id="msisdn-input" type="tel" name="msisdn" pattern="[0-9]*" autocomplete="tel">
      </div>
      <div id="msisdn-error" class="error hidden"></div>
      <button type="submit" id="msisdn-btn"></button>
    </form>

    <!-- Step 2a: PIN form -->
    <form id="pin-form" class="hidden">
      <label id="pin-label" for="pin-input"></label>
      <input id="pin-input" type="text" name="pin" autocomplete="one-time-code">
      <div id="pin-error" class="error hidden"></div>
      <button type="submit" id="pin-btn"></button>
    </form>

    <!-- Step 2b: SMS screen (click2sms) -->
    <div id="sms-screen" class="hidden">
      <p id="sms-message"></p>
      <button id="redirect-btn" type="button" class="hidden">Continue</button>
    </div>

    <!-- Success screen -->
    <div id="success-screen" class="hidden">
      <h1 id="success-title"></h1>
      <p id="success-close"></p>
    </div>

    <!-- Fail screen -->
    <div id="fail-screen" class="hidden">
      <h1>Something went wrong</h1>
      <p id="fail-message"></p>
    </div>

    <p id="tariff" class="tariff hidden"></p>
    <p id="terms" class="terms hidden"></p>

  </div>

  <script>
    const _config = {
      p:        <?= json_encode($p) ?>,
      offer:    <?= json_encode($offerId) ?>,
      language: <?= json_encode($lang) ?>,
      tracker:  <?= json_encode($tracker) ?>,
      next:     <?= json_encode($next) ?>,
      <?php foreach ($trackingParameters as $k => $v): ?>
      <?= json_encode($k) ?>: <?= json_encode($v) ?>,
      <?php endforeach; ?>
    };

    const _state = {
      tracker:   null,
      flow:      null,
      useMsisdn: false,
      smsLabels: { send: 'Send', to: 'to', access: 'to access the content' },
    };

    function show(id) { document.getElementById(id).classList.remove('hidden'); }
    function hide(id) { document.getElementById(id).classList.add('hidden'); }
    function setError(id, msg) {
      const el = document.getElementById(id);
      el.textContent = msg;
      el.classList.toggle('hidden', !msg);
    }
    function setText(id, text) {
      const el = document.getElementById(id);
      if (el) el.textContent = text;
    }

    function injectScript(code) {
      if (!code) return;
      const el = document.createElement('script');
      el.textContent = code;
      document.head.appendChild(el);
    }

    function showRedirectBtn(url) {
      const btn = document.getElementById('redirect-btn');
      btn.classList.remove('hidden');
      btn.addEventListener('click', () => { window.location.href = url; }, { once: true });
      show('sms-screen');
    }

    async function apiPost(action, body) {
      const res = await fetch('api.php?action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
      });
      return res.json();
    }

    // ── Start ──────────────────────────────────────────────────────────────────
    async function init() {
      const start = await apiPost('start', _config);

      injectScript(start.script ?? null);

      const ls      = start.landerSettings ?? {};
      const sms     = start.sms ?? null;
      const isError = start.status === 'error';
      const flow    = start.flow ?? 'pin';

      const computedFlow = ['click2sms', 'click2sms_with_msisdn'].includes(flow)
        ? 'click2sms'
        : (flow === 'redirect_to_aoc' ? 'redirect_to_aoc' : 'pin');

      const useMsisdn = [
        'pin', 'pin_he', 'click2sms_with_msisdn', 'click2sms_with_antifraud',
        'pin_antifraud', 'pin_af_onload', 'pin_af_verify', 'pin_af_redirect', 'redirect_to_aoc',
      ].includes(flow);

      _state.tracker   = start.tracker ?? _config.tracker ?? '';
      _state.flow      = computedFlow;
      _state.useMsisdn = useMsisdn;

      const l = {
        serviceName:  ls.serviceName  ?? 'Service',
        logo:         ls.logo         ?? '',
        heading:      ls.heading      ?? '',
        tariff:       ls.tariff       ?? '',
        terms:        ls.terms?.text  ?? '',
        msisdnLabel:  ls.msisdnInput?.label ?? 'Enter your mobile number to get the activation code:',
        msisdnBtn:    ls.msisdnBtn?.text    ?? 'Continue',
        pinLabel:     ls.pinInput?.label    ?? 'Enter the activation code:',
        pinBtn:       ls.pinBtn?.text       ?? 'Continue',
        smsSend:      ls.sms?.label?.send   ?? 'Send',
        smsTo:        ls.sms?.label?.to     ?? 'to',
        smsAccess:    ls.sms?.label?.access ?? 'to access the content',
        successTitle: ls.success?.label?.title ?? 'You have participated successfully',
        successClose: ls.success?.label?.close ?? 'You can now close this window',
      };

      _state.smsLabels = { send: l.smsSend, to: l.smsTo, access: l.smsAccess };

      document.title = l.serviceName;

      if (l.logo) {
        const img = document.getElementById('logo');
        img.src = l.logo;
        img.alt = l.serviceName;
        show('logo');
      }
      if (l.heading)      { setText('heading',       l.heading);      show('heading'); }
      if (l.tariff)       { setText('tariff',        l.tariff);       show('tariff'); }
      if (l.terms)        { setText('terms',         l.terms);        show('terms'); }
      setText('msisdn-label',  l.msisdnLabel);
      setText('msisdn-btn',    l.msisdnBtn);
      setText('pin-label',     l.pinLabel);
      setText('pin-btn',       l.pinBtn);
      setText('success-title', l.successTitle);
      setText('success-close', l.successClose);

      const prefix = start.prefix ?? ls.msisdnInput?.prefix ?? '';
      if (prefix) {
        setText('msisdn-prefix', '+' + prefix);
        show('msisdn-prefix');
      }

      if (isError) {
        setText('start-error', start.message ?? 'Error');
        show('start-error');
        return;
      }

      const startRedirectUrl = start.redirectUrl ?? null;
      if (startRedirectUrl) {
        if (!useMsisdn && ['click2sms', 'redirect_to_aoc'].includes(computedFlow)) {
          if (sms) {
            setText('sms-message', `${l.smsSend} ${sms.message} ${l.smsTo} ${sms.shortcode} ${l.smsAccess}`);
          }
          showRedirectBtn(startRedirectUrl);
        } else {
          window.location.href = startRedirectUrl;
        }
        return;
      }

      if (useMsisdn) {
        show('msisdn-form');
      } else if (computedFlow === 'click2sms') {
        if (sms) {
          setText('sms-message', `${l.smsSend} ${sms.message} ${l.smsTo} ${sms.shortcode} ${l.smsAccess}`);
        }
        show('sms-screen');
      }
    }

    init().catch(() => {
      setText('start-error', 'Failed to load. Please refresh the page.');
      show('start-error');
    });

    // ── Msisdn form ────────────────────────────────────────────────────────────
    const msisdnForm = document.getElementById('msisdn-form');
    if (msisdnForm) {
      msisdnForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        setError('msisdn-error', '');
        const msisdn = document.getElementById('msisdn-input').value.trim();
        const btn    = msisdnForm.querySelector('button[type=submit]');
        btn.disabled = true;

        try {
          let data;

          if (_state.flow === 'click2sms') {
            data = await apiPost('click2sms', { tracker: _state.tracker, msisdn });
            injectScript(data.script);
            if (data.status === 'error') { setError('msisdn-error', data.message); return; }
            if (data.sms) {
              const { send, to, access } = _state.smsLabels;
              setText('sms-message', `${send} ${data.sms.message} ${to} ${data.sms.shortcode} ${access}`);
            }
            hide('msisdn-form');
            show('sms-screen');
            if (data.redirectUrl) { showRedirectBtn(data.redirectUrl); return; }

          } else if (_state.flow === 'redirect_to_aoc') {
            data = await apiPost('redirectToAoc', { tracker: _state.tracker, msisdn });
            injectScript(data.script);
            if (data.status === 'error') { setError('msisdn-error', data.message); return; }
            hide('msisdn-form');
            if (data.redirectUrl) { showRedirectBtn(data.redirectUrl); return; }
            show('success-screen');

          } else {
            // pin flow
            data = await apiPost('sendPin', { tracker: _state.tracker, msisdn });
            injectScript(data.script);
            if (data.status === 'error') { setError('msisdn-error', data.message); return; }
            if (data.tracker) _state.tracker = data.tracker;
            if (data.redirectUrl) { window.location.href = data.redirectUrl; return; }
            hide('msisdn-form');
            show('pin-form');
          }
        } catch (_) {
          setError('msisdn-error', 'Request failed. Please try again.');
        } finally {
          btn.disabled = false;
        }
      });
    }

    // ── PIN form ───────────────────────────────────────────────────────────────
    const pinForm = document.getElementById('pin-form');
    if (pinForm) {
      pinForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        setError('pin-error', '');
        const pin = document.getElementById('pin-input').value.trim();
        const btn = pinForm.querySelector('button[type=submit]');
        btn.disabled = true;

        try {
          const data = await apiPost('verify', { tracker: _state.tracker, pin });
          injectScript(data.script);
          if (data.status === 'error') { setError('pin-error', data.message); return; }
          if (data.redirectUrl) { window.location.href = data.redirectUrl; return; }
          hide('pin-form');
          show('success-screen');
        } catch (_) {
          setError('pin-error', 'Request failed. Please try again.');
        } finally {
          btn.disabled = false;
        }
      });
    }
  </script>

</body>
</html>
api.php
<?php
const API_BASE_URL = 'https://api.tc-clicks.com/api/v3/';

// ─── Helpers ──────────────────────────────────────────────────────────────────
function getIp(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return $ip;
}

function getHeaders(): string
{
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (str_starts_with((string) $name, 'HTTP_')) {
            $key           = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr((string) $name, 5)))));
            $headers[$key] = $value;
        }
    }
    return base64_encode(json_encode($headers, JSON_THROW_ON_ERROR));
}

function getReturnUrl(): string
{
    $scheme = (($_SERVER['HTTPS'] ?? 'off') !== 'off') ? 'https' : 'http';
    $uri    = preg_replace('/\/[^\/]*$/', '/index.php', (string) $_SERVER['REQUEST_URI']);
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . $uri;
}

function callApi(string $url, array $params): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $params,
    ]);
    $body   = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    return json_decode($body, true) ?? ['status' => 'error', 'message' => 'HTTP ' . $status];
}

// ─── Request ──────────────────────────────────────────────────────────────────
header('Content-Type: application/json');

$input   = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
$action  = $_GET['action'] ?? '';
$tracker = $input['tracker'] ?? $input['tid'] ?? '';
$msisdn  = $input['msisdn'] ?? '';
$pin     = $input['pin'] ?? '';
$p       = $input['p'] ?? 0;
$offerId = $input['offer'] ?? 0;
$lang    = $input['language'] ?? 'en';
$next    = $input['next'] ?? null;

// Pass through any extra inputs (e.g. hidden fields) to subsequent API calls
$hidden = $input;
foreach (['tracker', 'tid', 'msisdn', 'pin', 'p', 'offer', 'language', 'next'] as $k) {
    unset($hidden[$k]);
}

try {
    $response = match ($action) {
        'start' => callApi(API_BASE_URL . 'start/' . $offerId, array_filter(array_merge($hidden, [
            'headers'    => getHeaders(),
            'p'          => $p,
            'ip'         => getIp(),
            'language'   => $lang,
            'tracker'    => $tracker ?: null,
            'return-url' => getReturnUrl(),
            'next'       => $next,
        ]))),
        'sendPin' => callApi(API_BASE_URL . 'sendpin/' . $tracker, array_filter(array_merge($hidden, [
            'headers'    => getHeaders(),
            'msisdn'     => $msisdn,
            'return-url' => getReturnUrl(),
        ]))),
        'verify' => callApi(API_BASE_URL . 'verify/' . $tracker, array_filter(array_merge($hidden, [
            'headers' => getHeaders(),
            'pin'     => $pin,
        ]))),
        'click2sms' => callApi(API_BASE_URL . 'click2sms/' . $tracker, array_filter(array_merge($hidden, [
            'headers' => getHeaders(),
            'msisdn'  => $msisdn ?: null,
        ]))),
        'redirectToAoc' => callApi(API_BASE_URL . 'redirect-url/' . $tracker, array_filter(array_merge($hidden, [
            'headers' => getHeaders(),
            'msisdn'  => $msisdn ?: null,
        ]))),
        default => ['status' => 'error', 'message' => 'Invalid action'],
    };

    echo json_encode($response, JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
