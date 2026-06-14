/**
 * Restock – Waitlist form (storefront).
 *
 * Vanilla JS, no dependencies. Submits the form over fetch(), reflects the
 * result inline with an accessible, colour-coded status message, and guards
 * against double submissions. The button label swaps to a "busy" state while
 * the request is in flight (its min-width is reserved in CSS, so no reflow).
 */
(() => {
  const config = window.restockWaitlist;

  if (!config) {
    return;
  }

  const init = () => {
    document.querySelectorAll('.restock-waitlist-form').forEach((form) => {
      // Bind once, even if init runs twice (defensive against re-injection).
      if (form.dataset.restockBound === '1') {
        return;
      }
      form.dataset.restockBound = '1';

      const message = form.querySelector('[data-restock-waitlist-message]');
      const submitButton = form.querySelector('[type="submit"]');
      const defaultLabel = submitButton ? submitButton.textContent : '';
      const busyLabel =
        (submitButton && submitButton.getAttribute('data-busy-label')) || defaultLabel;

      // Announce results to assistive tech the moment they appear.
      if (message) {
        message.setAttribute('role', 'status');
        message.setAttribute('aria-live', 'polite');
      }

      const showMessage = (text, state) => {
        if (!message || !text) {
          return;
        }
        message.hidden = false;
        message.textContent = text;
        if (state) {
          message.setAttribute('data-state', state);
        } else {
          message.removeAttribute('data-state');
        }
      };

      const setBusy = (busy) => {
        if (busy) {
          form.setAttribute('aria-busy', 'true');
        } else {
          form.removeAttribute('aria-busy');
        }
        if (submitButton) {
          submitButton.disabled = busy;
          submitButton.textContent = busy ? busyLabel : defaultLabel;
        }
      };

      form.addEventListener('submit', async (event) => {
        event.preventDefault();

        // Ignore re-entrant submits while a request is in flight.
        if (form.getAttribute('aria-busy') === 'true') {
          return;
        }

        const body = new URLSearchParams(new FormData(form));
        body.set('action', config.action || 'restock_waitlist_subscribe');
        body.set('nonce', config.nonce || '');

        setBusy(true);

        try {
          const response = await fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString(),
          });

          let payload = null;
          try {
            payload = await response.json();
          } catch (parseError) {
            payload = null;
          }

          const text =
            payload?.data?.message ||
            payload?.data?.error ||
            (payload?.success ? '' : config.errorText) ||
            config.errorText ||
            '';

          if (payload?.success) {
            showMessage(text, 'success');
            form.reset();
          } else {
            showMessage(text, 'error');
          }
        } catch (networkError) {
          showMessage(config.errorText || '', 'error');
        } finally {
          setBusy(false);
        }
      });
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
