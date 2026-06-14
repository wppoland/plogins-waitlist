/**
 * Restock – Admin UI helpers.
 *
 * Progressive enhancement for the inline help tooltips. The "?" buttons are
 * usable on hover/focus via CSS alone; this script adds keyboard niceties:
 * toggle on click, dismiss on Escape, and aria-expanded state so screen
 * readers announce the disclosure. The tooltip text is already wired to the
 * trigger via aria-describedby in the markup, so the help is read out on focus
 * regardless of whether this script loads.
 */
(() => {
  const init = () => {
    const triggers = document.querySelectorAll('.restock-help');

    triggers.forEach((trigger) => {
      const close = () => trigger.setAttribute('aria-expanded', 'false');

      trigger.addEventListener('click', (event) => {
        event.preventDefault();
        const open = trigger.getAttribute('aria-expanded') === 'true';
        trigger.setAttribute('aria-expanded', open ? 'false' : 'true');
      });

      trigger.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          close();
        }
      });

      trigger.addEventListener('blur', close);
    });

    // Dismiss any open tooltip when clicking elsewhere.
    document.addEventListener('click', (event) => {
      triggers.forEach((trigger) => {
        if (!trigger.contains(event.target)) {
          trigger.setAttribute('aria-expanded', 'false');
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
