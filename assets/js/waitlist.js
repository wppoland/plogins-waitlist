document.addEventListener('DOMContentLoaded', () => {
  const config = window.restockWaitlist;

  if (!config) {
    return;
  }

  const showMessage = (element, text, state) => {
    if (!element || !text) {
      return;
    }

    element.hidden = false;
    element.textContent = text;

    if (state) {
      element.setAttribute('data-state', state);
    }

    // The signal catches: fire the one-shot ignite on a confirmed signup.
    // Presentation only - re-armed each time by removing then re-adding the
    // class on the next frame so a repeat success re-ignites.
    element.classList.remove('restock-waitlist__message--ignite');

    if (state === 'success') {
      requestAnimationFrame(() => {
        element.classList.add('restock-waitlist__message--ignite');
      });
    }
  };

  document.querySelectorAll('.restock-waitlist-form').forEach((form) => {
    const wrapper = form.closest('.restock-waitlist');
    const message = form.querySelector('[data-restock-waitlist-message]');
    const productInput = form.querySelector('[data-restock-product-id]');

    if (message) {
      message.setAttribute('role', 'status');
      message.setAttribute('aria-live', 'polite');
    }

    const setWrapperVisible = (visible) => {
      if (!wrapper) {
        return;
      }

      wrapper.hidden = !visible;
    };

    const variationIsWaitlistable = (variation) => {
      if (!variation || !variation.variation_id) {
        return false;
      }

      if (variation.is_in_stock === true || variation.is_in_stock === 'yes') {
        return false;
      }

      return variation.backorders_allowed === true
        || variation.backorders_allowed === 'yes'
        || variation.availability_html === ''
        || (typeof variation.availability_html === 'string'
          && variation.availability_html.toLowerCase().includes('out of stock'));
    };

    if (wrapper && wrapper.dataset.restockVariable === '1' && productInput) {
      const variationsForm = document.querySelector('form.variations_form');

      if (variationsForm && typeof jQuery !== 'undefined') {
        jQuery(variationsForm).on('found_variation', (_event, variation) => {
          if (variationIsWaitlistable(variation)) {
            productInput.value = String(variation.variation_id);
            setWrapperVisible(true);
            return;
          }

          setWrapperVisible(false);
        });

        jQuery(variationsForm).on('reset_data', () => {
          setWrapperVisible(false);
        });
      }
    }

    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      const submitButton = form.querySelector('[type="submit"]');

      if (form.getAttribute('aria-busy') === 'true') {
        return;
      }

      const body = new URLSearchParams(new FormData(form));
      body.set('action', config.action || 'restock_waitlist_subscribe');
      body.set('nonce', config.nonce);

      form.setAttribute('aria-busy', 'true');

      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const response = await fetch(config.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString(),
        });

        const payload = await response.json();
        const text = payload?.data?.message || payload?.data?.error || '';
        const state = payload?.success ? 'success' : 'error';

        showMessage(message, text, state);

        if (payload?.success) {
          form.reset();

          if (productInput && wrapper && wrapper.dataset.restockVariable === '1') {
            productInput.value = wrapper.dataset.restockParentId || productInput.value;
          }
        }
      } catch (error) {
        showMessage(message, config.errorText || '', 'error');
      } finally {
        form.removeAttribute('aria-busy');

        if (submitButton) {
          submitButton.disabled = false;
        }
      }
    });
  });

  document.querySelectorAll('[data-restock-unsubscribe]').forEach((button) => {
    button.addEventListener('click', async () => {
      if (!config.unsubscribeAction) {
        return;
      }

      const row = button.closest('[data-restock-subscription-row]');
      const message = document.querySelector('[data-restock-account-message]');
      const subscriptionId = button.getAttribute('data-subscription-id');

      if (!subscriptionId || button.disabled) {
        return;
      }

      button.disabled = true;

      try {
        const body = new URLSearchParams({
          action: config.unsubscribeAction,
          nonce: config.nonce,
          subscription_id: subscriptionId,
        });

        const response = await fetch(config.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString(),
        });

        const payload = await response.json();

        if (payload?.success) {
          row?.remove();
          showMessage(message, payload?.data?.message || config.unsubscribeSuccess || '', 'success');

          if (!document.querySelector('[data-restock-subscription-row]')) {
            window.location.reload();
          }

          return;
        }

        showMessage(message, payload?.data?.message || config.errorText || '', 'error');
      } catch (error) {
        showMessage(message, config.errorText || '', 'error');
      } finally {
        button.disabled = false;
      }
    });
  });
});
