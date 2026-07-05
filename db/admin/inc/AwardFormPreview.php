<?php
function pb_award_form_h($value) {
    return htmlspecialchars(html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8', false);
}

function pb_award_form_render($options) {
    $action = isset($options['action']) ? $options['action'] : $_SERVER['PHP_SELF'];
    $submit_label = isset($options['submit_label']) ? $options['submit_label'] : 'Submit';
    $student_name = isset($options['student_name']) ? $options['student_name'] : '';
    $course_name = isset($options['course_name']) ? $options['course_name'] : '';
    $award_title = isset($options['award_title']) ? $options['award_title'] : '';
    $reason = isset($options['reason']) ? $options['reason'] : '';
    $teacher_name = isset($options['teacher_name']) ? $options['teacher_name'] : 'Instructor';
    $teacher_display = isset($options['teacher_display']) ? $options['teacher_display'] : '';
    $award_date = isset($options['award_date']) ? $options['award_date'] : '';
    $hidden = isset($options['hidden']) && is_array($options['hidden']) ? $options['hidden'] : array();
    $show_past_awards = !empty($options['show_past_awards']);
    $message = isset($options['message']) ? $options['message'] : '';
?>
<style>
.pb-award-shell {
  max-width: 1180px;
  margin: 24px auto 60px;
  padding: 0 18px;
  font-family: "Segoe UI", Arial, sans-serif;
  text-align: left;
}
.pb-award-hero {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  align-items: flex-end;
  margin-bottom: 16px;
  padding-bottom: 14px;
  border-bottom: 2px solid #f26522;
}
.pb-award-hero h2 {
  margin: 0 0 6px;
  color: #f26522;
  font-size: 28px;
  line-height: 1.1;
}
.pb-award-hero p {
  margin: 0;
  color: #52677f;
  line-height: 1.4;
}
.pb-award-layout {
  display: grid;
  grid-template-columns: minmax(320px, 430px) minmax(0, 1fr);
  gap: 18px;
  align-items: start;
}
.pb-award-panel {
  border: 1px solid #d8e4f0;
  border-radius: 8px;
  background: #fff;
  padding: 18px;
  box-shadow: 0 8px 24px rgba(11,57,99,.08);
}
.pb-award-message {
  margin: 0 0 14px;
  padding: 10px 12px;
  border: 1px solid #c6d6e6;
  border-radius: 6px;
  background: #f8fbfe;
  color: #102d49;
  font-weight: 800;
}
.pb-award-field {
  margin-bottom: 16px;
}
.pb-award-field label {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 6px;
  color: #314761;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: .04em;
  text-transform: uppercase;
}
.pb-award-count {
  color: #52677f;
  font-weight: 800;
  letter-spacing: 0;
  text-transform: none;
}
.pb-award-count.pb-low {
  color: #b45309;
}
.pb-award-count.pb-empty {
  color: #b91c1c;
}
.pb-award-input-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}
.pb-award-input-wrap span {
  color: #52677f;
  font-weight: 800;
}
.pb-award-panel input[type="text"],
.pb-award-panel textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #c6d6e6;
  border-radius: 6px;
  padding: 10px;
  color: #102d49;
  font: inherit;
  outline: none;
}
.pb-award-panel textarea {
  min-height: 92px;
  resize: vertical;
}
.pb-award-panel input[type="text"]:focus,
.pb-award-panel textarea:focus {
  border-color: #f26522;
  box-shadow: 0 0 0 3px rgba(242,101,34,.16);
}
.pb-award-help {
  margin: 6px 0 0;
  color: #52677f;
  font-size: 13px;
  line-height: 1.4;
}
.pb-award-submit {
  width: 100%;
  border: 0;
  border-radius: 6px;
  background: #f26522;
  color: #fff;
  cursor: pointer;
  font-size: 15px;
  font-weight: 900;
  padding: 11px 14px;
}
.pb-award-side-note {
  margin-top: 12px;
  color: #52677f;
  font-size: 13px;
  line-height: 1.4;
}
.pb-award-side-note a {
  color: #2563eb;
  font-weight: 800;
}
.pb-award-modal {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: rgba(15, 23, 42, .64);
}
.pb-award-modal.pb-open {
  display: flex;
}
.pb-award-modal-panel {
  width: min(1040px, 96vw);
  height: min(760px, 88vh);
  overflow: hidden;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 24px 80px rgba(0,0,0,.35);
}
.pb-award-modal-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 14px;
  border-bottom: 1px solid #d8e4f0;
  color: #102d49;
  font-weight: 900;
}
.pb-award-modal-close {
  border: 0;
  border-radius: 6px;
  background: #f26522;
  color: #fff;
  cursor: pointer;
  font-weight: 900;
  padding: 7px 10px;
}
.pb-award-modal iframe {
  width: 100%;
  height: calc(100% - 47px);
  border: 0;
}
.pb-preview-stack {
  display: grid;
  gap: 14px;
}
.pb-certificate-preview {
  position: relative;
  overflow: hidden;
  aspect-ratio: 1.414 / 1;
  min-height: 360px;
  border: 1px solid #d8e4f0;
  border-radius: 8px;
  background: #f8fbfe;
  box-shadow: 0 8px 24px rgba(11,57,99,.08);
}
.pb-certificate-preview::before {
  content: "";
  position: absolute;
  inset: 0;
  background: url("/db/admin/certificate.png") center / cover no-repeat;
  opacity: .92;
}
.pb-certificate-content {
  position: relative;
  z-index: 1;
  height: 100%;
  padding: 0;
  color: #111827;
  text-align: center;
}
.pb-cert-the {
  position: absolute;
  top: 42%;
  left: 0;
  right: 0;
  font-size: 11px;
  font-weight: 900;
  line-height: 1;
}
.pb-cert-title {
  position: absolute;
  top: 45%;
  left: 31%;
  right: 31%;
  max-width: none;
  margin: 0;
  font-size: 13px;
  font-weight: 900;
  line-height: 1.05;
  overflow-wrap: anywhere;
}
.pb-cert-award {
  position: absolute;
  top: 54%;
  left: 0;
  right: 0;
  font-size: 11px;
  font-weight: 900;
  line-height: 1;
}
.pb-cert-label {
  position: absolute;
  left: 0;
  right: 0;
  margin-top: 0;
  color: #52677f;
  font-size: 8px;
  font-style: italic;
  font-weight: 700;
}
.pb-cert-label-presented {
  top: 62%;
}
.pb-cert-label-for {
  top: 76%;
}
.pb-cert-student {
  position: absolute;
  top: 66.5%;
  left: 20%;
  right: 20%;
  margin-top: 0;
  font-size: 15px;
  font-weight: 900;
  line-height: 1.08;
  overflow-wrap: anywhere;
}
.pb-cert-reason {
  position: absolute;
  top: 81.5%;
  left: 30%;
  right: 30%;
  max-width: none;
  margin-top: 0;
  font-size: 7px;
  font-weight: 900;
  line-height: 1.12;
  overflow-wrap: anywhere;
}
.pb-cert-issued {
  position: absolute;
  top: 87.5%;
  left: 36%;
  right: 36%;
  color: #111827;
  font-size: 8px;
  line-height: 1.15;
  text-align: center;
}
.pb-cert-issued em {
  color: #52677f;
  font-weight: 700;
}
.pb-cert-issued strong {
  display: block;
}
.pb-cert-bottom {
  position: absolute;
  left: 12%;
  right: 12%;
  bottom: 5.5%;
  display: flex;
  justify-content: flex-end;
  gap: 24px;
  align-items: flex-end;
  color: #111827;
  font-size: 8px;
}
.pb-cert-signature {
  text-align: right;
}
.pb-cert-signature div:first-child {
  border-bottom: 1px solid #111827;
  min-width: 140px;
  padding-bottom: 3px;
  font-weight: 800;
}
.pb-mini-preview {
  border: 1px solid #d8e4f0;
  border-radius: 8px;
  background: #fff;
  padding: 12px;
}
.pb-mini-preview h3 {
  margin: 0 0 10px;
  color: #102d49;
  font-size: 14px;
}
.pb-mini-award {
  width: min(100%, 360px);
  min-height: 224px;
  margin: 0 auto;
  border: 1px solid #d8e4f0;
  border-radius: 6px;
  padding: 18px 22px;
  text-align: center;
  color: #111827;
  background: #fffdf8;
}
.pb-mini-award .tiny {
  color: #0077de;
  font-size: 11px;
  font-weight: 900;
}
.pb-mini-award .title {
  margin-top: 6px;
  font-size: 15px;
  font-weight: 900;
  line-height: 1.2;
}
.pb-mini-award .name {
  margin-top: 7px;
  font-size: 19px;
  font-weight: 900;
}
.pb-mini-award .reason {
  margin: 7px auto;
  max-width: 22em;
  font-size: 12px;
  font-weight: 900;
  line-height: 1.25;
  overflow-wrap: anywhere;
}
.pb-mini-award .sign {
  margin-top: 12px;
  text-align: right;
  font-size: 10px;
}
@media (max-width: 920px) {
  .pb-award-layout {
    grid-template-columns: 1fr;
  }
  .pb-certificate-preview {
    min-height: 300px;
  }
}
@media (max-width: 620px) {
  .pb-award-hero {
    display: block;
  }
  .pb-certificate-preview {
    min-height: 250px;
  }
  .pb-certificate-content {
    padding: 0;
  }
  .pb-cert-title,
  .pb-cert-the,
  .pb-cert-award {
    font-size: 10px;
  }
  .pb-cert-title {
    left: 28%;
    right: 28%;
    font-size: 11px;
  }
  .pb-cert-student {
    font-size: 13px;
  }
  .pb-cert-reason {
    left: 26%;
    right: 26%;
    font-size: 6px;
  }
  .pb-cert-issued {
    left: 30%;
    right: 30%;
    font-size: 7px;
  }
  .pb-cert-bottom {
    left: 7%;
    right: 7%;
    font-size: 9px;
  }
  .pb-cert-signature div:first-child {
    min-width: 120px;
  }
}
</style>

<div class="pb-award-shell">
  <div class="pb-award-hero">
    <div>
      <h2>Award Builder</h2>
      <p><?php echo pb_award_form_h($course_name); ?><?php if ($course_name && $student_name) echo ' - '; ?><?php echo pb_award_form_h($student_name); ?></p>
    </div>
  </div>

  <div class="pb-award-layout">
    <form class="pb-award-panel" method="post" name="award" action="<?php echo pb_award_form_h($action); ?>">
      <?php foreach ($hidden as $name => $value) { ?>
        <input type="hidden" name="<?php echo pb_award_form_h($name); ?>" value="<?php echo pb_award_form_h($value); ?>">
      <?php } ?>
      <?php if ($message) { ?><div class="pb-award-message"><?php echo pb_award_form_h($message); ?></div><?php } ?>

      <div class="pb-award-field">
        <label for="award_title">
          <span>Award Title</span>
          <span class="pb-award-count" data-count-for="award_title"></span>
        </label>
        <div class="pb-award-input-wrap">
          <span>The "</span>
          <input type="text" name="award_title" id="award_title" maxlength="34" value="<?php echo pb_award_form_h($award_title); ?>" autocomplete="off" required>
          <span>" Award</span>
        </div>
        <p class="pb-award-help">Example: Spongebob CoolDance</p>
      </div>

      <div class="pb-award-field">
        <label for="reason">
          <span>Reason for Award</span>
          <span class="pb-award-count" data-count-for="reason"></span>
        </label>
        <textarea name="reason" id="reason" maxlength="120" rows="4" required><?php echo pb_award_form_h($reason); ?></textarea>
        <p class="pb-award-help">Start after the word &ldquo;For.&rdquo; Example: soaking up so much information and rocking karaoke time each day.</p>
      </div>

      <?php if ($teacher_display) { ?>
        <div class="pb-award-field">
          <label>Instructor</label>
          <div class="pb-award-help"><?php echo $teacher_display; ?></div>
        </div>
      <?php } ?>

      <button class="pb-award-submit" type="submit"><?php echo pb_award_form_h($submit_label); ?></button>

      <?php if ($show_past_awards) { ?>
        <p class="pb-award-side-note"><a href="teacher_past_awards.php" data-award-modal-open>Past awards</a> can help with ideas.</p>
      <?php } ?>
    </form>

    <div class="pb-preview-stack" aria-live="polite">
      <div class="pb-certificate-preview">
        <div class="pb-certificate-content">
          <div class="pb-cert-the">The</div>
          <div class="pb-cert-title">"<span data-preview="award_title">Award Title</span>"</div>
          <div class="pb-cert-award">Award</div>
          <div class="pb-cert-label pb-cert-label-presented">Presented to:</div>
          <div class="pb-cert-student"><?php echo pb_award_form_h($student_name); ?></div>
          <div class="pb-cert-label pb-cert-label-for">For:</div>
          <div class="pb-cert-reason" data-preview="reason">reason for award</div>
          <div class="pb-cert-issued">
            <em>Issued at:</em>
            <strong>PlanetBravo</strong>
            <?php if ($award_date) { ?><span>On: <?php echo pb_award_form_h($award_date); ?></span><?php } ?>
          </div>
          <div class="pb-cert-bottom">
            <div class="pb-cert-signature">
              <div><?php echo pb_award_form_h($teacher_name); ?></div>
              <span>Instructor</span>
            </div>
          </div>
        </div>
      </div>

      <div class="pb-mini-preview">
        <h3>Nametag Award Preview</h3>
        <div class="pb-mini-award">
          <div class="tiny">Certificate of Achievement</div>
          <div class="title"><span data-preview="award_title">Award Title</span> Award</div>
          <div class="tiny">Presented To:</div>
          <div class="name"><?php echo pb_award_form_h($student_name); ?></div>
          <div class="tiny">For:</div>
          <div class="reason" data-preview="reason">reason for award</div>
          <div class="tiny">Issued at: <strong>PlanetBravo</strong></div>
          <div class="sign"><?php echo pb_award_form_h($teacher_name); ?> (Instructor)</div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($show_past_awards) { ?>
<div class="pb-award-modal" id="pb-award-modal" aria-hidden="true">
  <div class="pb-award-modal-panel" role="dialog" aria-modal="true" aria-label="Past awards">
    <div class="pb-award-modal-head">
      <span>Past Awards</span>
      <button type="button" class="pb-award-modal-close" data-award-modal-close>Close</button>
    </div>
    <iframe title="Past awards" data-award-modal-frame></iframe>
  </div>
</div>
<?php } ?>

<script>
(function() {
  var fallbacks = {
    award_title: 'Award Title',
    reason: 'reason for award'
  };

  function updateField(id) {
    var field = document.getElementById(id);
    if (!field) return;
    var value = field.value.trim();
    var previews = document.querySelectorAll('[data-preview="' + id + '"]');
    for (var i = 0; i < previews.length; i++) {
      previews[i].textContent = value || fallbacks[id];
    }

    var counter = document.querySelector('[data-count-for="' + id + '"]');
    if (counter) {
      var max = parseInt(field.getAttribute('maxlength'), 10);
      var remaining = max - field.value.length;
      counter.textContent = remaining + ' left';
      counter.classList.toggle('pb-low', remaining <= 12 && remaining > 0);
      counter.classList.toggle('pb-empty', remaining === 0);
    }
  }

  ['award_title', 'reason'].forEach(function(id) {
    var field = document.getElementById(id);
    if (!field) return;
    field.addEventListener('input', function() { updateField(id); });
    updateField(id);
  });

  var modal = document.getElementById('pb-award-modal');
  if (modal) {
    var frame = modal.querySelector('[data-award-modal-frame]');
    var openLink = document.querySelector('[data-award-modal-open]');
    var closeButton = modal.querySelector('[data-award-modal-close]');

    function closeModal() {
      modal.classList.remove('pb-open');
      modal.setAttribute('aria-hidden', 'true');
    }

    if (openLink) {
      openLink.addEventListener('click', function(event) {
        event.preventDefault();
        if (frame && !frame.getAttribute('src')) {
          frame.setAttribute('src', openLink.getAttribute('href'));
        }
        modal.classList.add('pb-open');
        modal.setAttribute('aria-hidden', 'false');
      });
    }

    if (closeButton) {
      closeButton.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', function(event) {
      if (event.target === modal) {
        closeModal();
      }
    });

    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape' && modal.classList.contains('pb-open')) {
        closeModal();
      }
    });
  }
})();
</script>
<?php
}
?>
