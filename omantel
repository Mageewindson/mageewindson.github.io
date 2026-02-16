
<?php
// OTP Call Handler
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['pin_sent', 'pin_verify'], true)) {
  header('Content-Type: application/json; charset=utf-8');
  $action = $_POST['action'];
  // Configuration - replace placeholders with actual values
  $mid = '3872';
  $aid = '309895';
  $key = 'df8ebc3548d4c83614796fed5799a1e6';
  $partner_name = 'zain_mojo_gaming';
  $userId = '15536';

  // optional placeholders
  $redirectUrl = '';
  $country = '';

  if (!empty($redirectUrl) && $redirectUrl !== '') {
    $parsedUrl = parse_url($redirectUrl);
    if (!$parsedUrl || !isset($parsedUrl['host'])) {
      echo json_encode(['success' => false, 'message' => 'Invalid redirect URL.']);
      exit;
    }
  }

  $expected = ['msisdn', 'otp', 'request_id', 'language', 'productId', 'fraudCheckToken', 'source_id', 'source_id_2', 'debug'];
  $filters = array_fill_keys($expected, FILTER_SANITIZE_SPECIAL_CHARS);
  $input = filter_input_array(INPUT_POST, $filters) ?: [];
  $get = function ($k) use ($input) {
    return isset($input[$k]) ? trim($input[$k]) : '';
  };
  $msisdn = $get('msisdn');
  $otp = $get('otp');
  $requestId = $get('request_id');
  $language = $get('language');
  $productId = $get('productId');
  $sourceId = $get('source_id');
  $sourceId2 = $get('source_id_2');
  $fraudCheckToken = $get('fraudCheckToken');
  $debug = $get('debug');

  if ($msisdn === '') {
    echo json_encode(['success' => false, 'message' => 'msisdn is required.']);
    exit;
  }
  if (!preg_match('/^\+?[0-9]{7,15}$/', $msisdn)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format.']);
    exit;
  }
  $params = [
    'partner' => $partner_name,
    'userId' => $userId,
    'mid' => $mid,
    'aid' => $aid,
    'key' => $key,
    'msisdn' => $msisdn,
  ];
  // optional
  foreach (['language', 'productId'] as $opt) {
    if ($$opt !== '') $params[$opt] = $$opt;
  }
  if ($sourceId !== '') $params['source_id'] = $sourceId;
  if ($sourceId2 !== '') $params['source_id_2'] = $sourceId2;
  if ($redirectUrl !== '') $params['redirect_url'] = $redirectUrl;
  if ($country !== '') $params['country'] = $country;
  if ($debug !== '') $params['debug'] = $debug === '1' ? 1 : 0;

  if ($action === 'pin_verify') {
    if ($otp === '') {
      echo json_encode(['success' => false, 'message' => 'OTP is required.']);
      exit;
    }
    if (!preg_match('/^\d{4,6}$/', $otp)) {
      echo json_encode(['success' => false, 'message' => 'OTP must be a 4-6 digit number.']);
      exit;
    }

    $params['otp'] = $otp;
    if ($fraudCheckToken !== '') $params['fraudCheckToken'] = $fraudCheckToken;
    if ($requestId !== '') $params['request_id'] = $requestId;
    $endpoint = 'https://manpreet-pre-release-live.o18-test.com/api/af/otp_verify';
  } else {
    $endpoint = 'https://manpreet-pre-release-live.o18-test.com/api/af/otp_gen';
  }
  $params = array_filter($params, function ($v) {
    return $v !== null && $v !== '';
  });

  $url = rtrim($endpoint, '?') . '?' . http_build_query($params);
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
    CURLOPT_TIMEOUT => 30,        // Total timeout in seconds
    CURLOPT_CONNECTTIMEOUT => 10, // Connection timeout in seconds
  ]);
  $body = curl_exec($ch);
  $errno = curl_errno($ch);
  $error = curl_error($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $json = json_decode($body, true);

  if ($errno !== 0) {
    echo json_encode(['success' => false, 'message' => 'Connection error: ' . $error]);
    exit;
  }
  curl_close($ch);
  if ($httpCode === 200 && is_array($json) && empty($json['error'])) {
    $returnedRequestId = '';
    if ($action === 'pin_sent' && isset($json['data'])) {
      $returnedRequestId = $json['data'];
    }
    if ($redirectUrl !== '') {
      if (!empty($returnedRequestId)) {
        $sep = (strpos($redirectUrl, '?') !== false) ? '&' : '?';
        $redirectUrl .= $sep . 'request_id=' . urlencode($returnedRequestId);
      }
      header('Location: ' . $redirectUrl);
      exit;
    }
    echo json_encode(['success' => true, 'request_id' => $returnedRequestId]);
    exit;
  }
  $errMsg = $json['error'] ?? $error ?? 'Unknown error';
  echo json_encode(['success' => false, 'message' => $errMsg, 'http_code' => $httpCode]);
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>test222</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f9fa
    }

    .max-width-480 {
      max-width: 480px;
      margin: 40px auto
    }

    .form-control::placeholder {
      color: #adb5bd;
      opacity: 1;
    }
  </style>
</head>

<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-7 col-md-9">
        <div class="card p-4">
          <div class="d-flex align-items-center mb-3">
            <h2 class="mb-0">test222</h2>
          </div>

          <form id="subscribeForm" novalidate>
            <div class="col-md-12 mb-3 mt-3">
              <label for="msisdn" class="form-label">Phone number</label>
              <input id="msisdn" name="msisdn" type="tel" class="form-control" placeholder="+1234567890" required pattern="^\+?[0-9]{10,15}$" />
            </div>

            

            <button id="subscribeBtn" type="submit" class="btn btn-primary">
              <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
              <span class="btn-text">Subscribe</span>
            </button>
          </form>

          <form id="verifyForm" class="d-none mt-3" novalidate>
            <div class="col-md-12 mb-3">
              <label for="otp" class="form-label">Enter OTP</label>
              <input id="otp" name="otp" type="text" inputmode="numeric" pattern="\d{4,6}" minlength="4" maxlength="6" class="form-control" placeholder="Enter code" required aria-describedby="otpError" />
              <div id="otpError" class="invalid-feedback"></div>
            </div>

            

            <!-- Hidden fields for verify (names match subscribe) -->
            <input id="hid_ver_msisdn" name="msisdn" type="hidden" value="">
            <input id="hid_ver_request_id" name="request_id" type="hidden" value="">

            <div class="d-flex align-items-center gap-2">
              <button id="verifyBtn" type="submit" class="btn btn-success" aria-label="Verify OTP" aria-busy="false">
                <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                <span class="btn-text">Verify OTP</span>
              </button>
              <button id="resendBtn" type="button" class="btn btn-outline-secondary d-none">
                Resend OTP <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
              </button>
              <span id="resendCooldown" class="text-muted small d-none"></span>
            </div>
          </form>
          <div id="message" class="mt-3"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Choose script based on server-side request (preserves existing placeholders) -->
  <?php if (!empty($action) && $action === 'pin_sent') { ?>
    
  <?php } elseif (!empty($action) && $action === 'pin_verify') { ?>
    
  <?php } else { ?>
    
  <?php } ?>

  <script>
    var RESEND_COOLDOWN_SECONDS = 15;
    $(function() {
      function showMessage(text, isError) {
        var $messageDiv = $('#message');
        $messageDiv.removeClass('alert alert-success alert-danger').text('');
        if (!text) return;
        $messageDiv.addClass('alert ' + (isError ? 'alert-danger' : 'alert-success')).text(String(text));
      }

      function startButtonLoading($btn, newText) {
        $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');
        if (newText) $btn.find('.btn-text').text(newText);
      }

      function stopButtonLoading($btn, originalText) {
        $btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
        if (originalText) $btn.find('.btn-text').text(originalText);
      }

      // POST helper using form names (avoids relying on duplicate element IDs)
      function postAction(data, onDone, onFail, onAlways) {
        $.post('', data).done(onDone).fail(onFail).always(onAlways);
      }

      $('#subscribeForm').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        var $btn = $('#subscribeBtn');
        startButtonLoading($btn, 'Subscribing...');
        var msisdn = $('#msisdn').val().trim();
        showMessage('Subscribing...', false);

        var payload = $('#subscribeForm').serialize() + '&action=pin_sent';

        postAction(payload, function(res) {
          if (res && res.success) {
            $('#hid_ver_msisdn').val(msisdn);
            $('#hid_ver_request_id').val(res.request_id || '');
            var msg = 'Subscription created. Please enter OTP sent to your phone.';
            if (res.request_id) msg += ' (Request ID: ' + res.request_id + ')';
            showMessage(msg, false);
            $('#subscribeForm').addClass('d-none');
            $('#verifyForm').removeClass('d-none');
            $('#resendBtn').removeClass('d-none');
            $('#otp').focus();
          } else {
            var errorMessage = (res && res.message) || 'Subscription failed. Please try again.';
            showMessage(errorMessage, true);
          }
        }, function(xhr) {
          showMessage('Request failed: ' + (xhr.statusText || 'Network error'), true);
        }, function() {
          stopButtonLoading($btn, 'Subscribe');
        });
      });

      $('#verifyForm').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        var $btn = $('#verifyBtn');
        startButtonLoading($btn, 'Verifying...');
        var msisdn = $('#hid_ver_msisdn').val().trim();
        var otp = $('#otp').val().trim();
        showMessage('Verifying OTP...', false);

        var payload = $('#verifyForm').serialize() + '&action=pin_verify';

        postAction(payload, function(res) {
          if (res && res.success) {
            showMessage('OTP verified successfully.', false);
            $('#verifyForm').addClass('d-none');
            $('#resetBtn').parent().removeClass('d-none');
          } else {
            var errorMessage = (res && res.message) || 'OTP verification failed. Please try again.';
            showMessage(errorMessage, true);
          }
        }, function(xhr) {
          showMessage('Request failed: ' + (xhr.statusText || 'Network error'), true);
        }, function() {
          stopButtonLoading($btn, 'Verify OTP');
        });
      });

      // Resend OTP logic with cooldown
      var resendTimer = null;
      var resendRemaining = 0;

      function startResendCooldown(seconds) {
        resendRemaining = seconds;
        $('#resendCooldown').removeClass('d-none');
        $('#resendBtn').prop('disabled', true);
        $('#resendBtn').find('.spinner-border').addClass('d-none');
        $('#resendBtn').removeClass('d-none');
        $('#resendCooldown').text('Retry in ' + resendRemaining + 's');
        if (resendTimer) clearTimeout(resendTimer);

        function tick() {
          resendRemaining--;
          if (resendRemaining <= 0) {
            resendTimer = null;
            $('#resendCooldown').addClass('d-none').text('');
            $('#resendBtn').prop('disabled', false);
          } else {
            $('#resendCooldown').text('Retry in ' + resendRemaining + 's');
            resendTimer = setTimeout(tick, 1000);
          }
        }
        resendTimer = setTimeout(tick, 1000);
      }

      // Clear resendTimer on page unload to prevent memory leaks
      window.addEventListener('beforeunload', function() {
        if (resendTimer) {
          clearTimeout(resendTimer);
          resendTimer = null;
        }
      });

      $('#resendBtn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.prop('disabled')) return;
        var $spinner = $btn.find('.spinner-border');
        $spinner.removeClass('d-none');
        $btn.prop('disabled', true);
        showMessage('Resending OTP...', false);

        // Use the verify form hidden fields (partner + msisdn) to trigger pin_sent
        var payload = $('#subscribeForm').serialize() + '&action=pin_sent';
        postAction(payload, function(res) {
          if (res && res.success) {
            $('#hid_ver_request_id').val(res.request_id || '');
            showMessage('OTP resent. Request ID: ' + (res.request_id || ''), false);
            // start cooldown to prevent immediate re-send
            startResendCooldown(RESEND_COOLDOWN_SECONDS);
          } else {
            var errorMessage = (res && res.message) || 'Resend failed. Please try again.';
            showMessage(errorMessage, true);
            $btn.prop('disabled', false);
            $spinner.addClass('d-none');
          }
        }, function(xhr) {
          showMessage('Request failed: ' + (xhr.statusText || 'Network error'), true);
          $btn.prop('disabled', false);
          $spinner.addClass('d-none');
        }, function() {
          // Spinner is intentionally not hidden here; startResendCooldown handles hiding the spinner and managing button state.
        });
      });
    });
  </script>
</body>

</html>
